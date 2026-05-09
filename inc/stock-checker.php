<?php
/**
 * inc/stock-checker.php — In-Store Stock Checker
 *
 * Queries your MySQL DB for per-store stock by SKU/barcode.
 * Supports two modes:
 *   1. WooCommerce native postmeta (_stock_quantity per store via custom meta)
 *   2. External stock table (configure DB connection below)
 *
 * Custom fields on product:
 *   _paint_colour       — hex value e.g. #4A90D9
 *   _paint_colour_name  — display name e.g. "Ocean Blue"
 *   _paint_colour_code  — manufacturer code e.g. "OB-4490"
 */

defined( 'ABSPATH' ) || exit;

/* ══════════════════════════════════════════════════════
   CONFIG — adjust to match your DB / stock table schema
══════════════════════════════════════════════════════ */

/**
 * Returns the stock DB config.
 * Mode 'wc'       — reads WooCommerce postmeta (no extra DB needed)
 * Mode 'external' — connects to a separate MySQL table
 *
 * For external mode, set these constants in wp-config.php:
 *   define( 'mica_STOCK_DB_HOST', 'localhost' );
 *   define( 'mica_STOCK_DB_NAME', 'stock_db' );
 *   define( 'mica_STOCK_DB_USER', 'stock_user' );
 *   define( 'mica_STOCK_DB_PASS', 'secret' );
 *   define( 'mica_STOCK_TABLE',   'store_stock' );   // table name
 *   define( 'mica_STOCK_COL_SKU', 'barcode' );       // SKU/barcode column
 *   define( 'mica_STOCK_COL_STORE', 'store_id' );
 *   define( 'mica_STOCK_COL_QTY',   'qty_on_hand' );
 */
function mica_stock_config(): array {
    return [
        'mode'       => defined( 'mica_STOCK_DB_HOST' ) ? 'external' : 'wc',
        'db_host'    => defined( 'mica_STOCK_DB_HOST' ) ? mica_STOCK_DB_HOST : DB_HOST,
        'db_name'    => defined( 'mica_STOCK_DB_NAME' ) ? mica_STOCK_DB_NAME : DB_NAME,
        'db_user'    => defined( 'mica_STOCK_DB_USER' ) ? mica_STOCK_DB_USER : DB_USER,
        'db_pass'    => defined( 'mica_STOCK_DB_PASS' ) ? mica_STOCK_DB_PASS : DB_PASSWORD,
        'table'      => defined( 'mica_STOCK_TABLE'   ) ? mica_STOCK_TABLE   : 'store_stock',
        'col_sku'    => defined( 'mica_STOCK_COL_SKU'  ) ? mica_STOCK_COL_SKU   : 'sku',
        'col_store'  => defined( 'mica_STOCK_COL_STORE') ? mica_STOCK_COL_STORE : 'store_id',
        'col_qty'    => defined( 'mica_STOCK_COL_QTY'  ) ? mica_STOCK_COL_QTY   : 'quantity',
    ];
}

/**
 * Classify qty into a stock level.
 * Returns: 'high' | 'low' | 'none'
 */
function mica_stock_level( $qty ): string {
    $qty = (int) $qty;
    if ( $qty <= 0 )  return 'none';
    if ( $qty <= 5 )  return 'low';
    return 'high';
}

/**
 * Human-readable stock status label.
 */
function mica_stock_status_label( string $level, int $qty ): string {
    switch ( $level ) {
        case 'high': return $qty . ' in stock';
        case 'low':  return 'Only ' . $qty . ' left';
        case 'none': return 'Out of stock';
    }
    return 'Unknown';
}

/**
 * Query stock levels for all stores for a given SKU.
 *
 * Returns array of:
 *   [ 'store_id', 'store_name', 'city', 'qty', 'level', 'label' ]
 */
function mica_get_all_store_stock( string $sku ): array {
    $cfg = mica_stock_config();

    if ( $cfg['mode'] === 'external' ) {
        $stores = mica_get_stores();
        return mica_query_external_stock( $sku, $stores, $cfg );
    }

    // Auto-detect: use wp_store_stock / wp_store_names tables if they exist
    global $wpdb;
    $stock_table = $wpdb->prefix . 'store_stock';
    if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $stock_table ) ) === $stock_table ) {
        return mica_query_wp_tables_stock( $sku );
    }

    // Fallback: WC postmeta mode
    $stores = mica_get_stores();
    return mica_query_wc_stock( $sku, $stores );
}

/**
 * Mode: WooCommerce — reads _stock_qty_{store_id} postmeta per store.
 * If no per-store meta, falls back to global stock.
 *
 * Convention: set per-store stock via postmeta key: _stock_qty_store-001
 */
function mica_query_wc_stock( string $sku, array $stores ): array {
    // Find product by SKU
    $product_id = wc_get_product_id_by_sku( $sku );
    if ( ! $product_id ) return [];

    $result = [];
    foreach ( $stores as $store ) {
        // Try per-store meta first
        $meta_key = '_stock_qty_' . sanitize_key( $store['id'] );
        $qty      = get_post_meta( $product_id, $meta_key, true );

        // Fall back to global WC stock
        if ( $qty === '' || $qty === false ) {
            $product = wc_get_product( $product_id );
            $qty     = $product ? (int) $product->get_stock_quantity() : 0;
        } else {
            $qty = (int) $qty;
        }

        $level    = mica_stock_level( $qty );
        $result[] = [
            'store_id'   => $store['id'],
            'store_name' => $store['name'],
            'city'       => $store['city'],
            'phone'      => $store['phone'],
            'hours'      => $store['hours'],
            'qty'        => $qty,
            'level'      => $level,
            'label'      => mica_stock_status_label( $level, $qty ),
        ];
    }

    // Sort: high stock first
    usort( $result, fn( $a, $b ) => $b['qty'] - $a['qty'] );
    return $result;
}

/**
 * Mode: External MySQL table.
 * Connects to a separate DB and queries the stock table by SKU + store_id.
 */
/**
 * Mode: wp_store_stock / wp_store_names tables in the WordPress DB.
 * Mirrors the old site's query logic; store list is built dynamically from the join.
 *
 * wp_store_stock columns: Barcode, `Member Code`, Quantity
 * wp_store_names columns: StoreCode, StoreName, StoreArea
 */
function mica_query_wp_tables_stock( string $sku ): array {
    global $wpdb;

    $stock_table = $wpdb->prefix . 'store_stock';
    $names_table = $wpdb->prefix . 'store_names';

    $stock_rows = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT `Member Code` AS member_code, `Quantity` AS qty
               FROM {$stock_table}
              WHERE `Barcode` = %s",
            $sku
        ),
        ARRAY_A
    );

    if ( empty( $stock_rows ) ) return [];

    $result = [];

    foreach ( $stock_rows as $row ) {
        $member_code = preg_replace( '/\s+/', '', $row['member_code'] );
        $qty         = (int) $row['qty'];

        $store = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT `StoreName`, `StoreArea`, `Lat`, `Long`
                   FROM {$names_table}
                  WHERE `StoreCode` = %s
                    AND `Lat` != 0
                  LIMIT 1",
                $member_code
            ),
            ARRAY_A
        );

        if ( ! $store ) continue;

        $level    = mica_stock_level( $qty );
        $result[] = [
            'store_id'   => $member_code,
            'store_name' => $store['StoreName'],
            'city'       => $store['StoreArea'],
            'lat'        => (float) $store['Lat'],
            'lng'        => (float) $store['Long'],
            'phone'      => '',
            'hours'      => '',
            'qty'        => $qty,
            'level'      => $level,
            'label'      => mica_stock_status_label( $level, $qty ),
        ];
    }

    usort( $result, fn( $a, $b ) => $b['qty'] - $a['qty'] );
    return $result;
}

function mica_query_external_stock( string $sku, array $stores, array $cfg ): array {
    static $ext_wpdb = null;

    // Lazy-init external DB connection
    if ( $ext_wpdb === null ) {
        try {
            $ext_wpdb = new wpdb(
                $cfg['db_user'],
                $cfg['db_pass'],
                $cfg['db_name'],
                $cfg['db_host']
            );
            $ext_wpdb->show_errors();
        } catch ( \Exception $e ) {
            error_log( '[mica Stock] External DB connection failed: ' . $e->getMessage() );
            return [];
        }
    }

    if ( ! $ext_wpdb ) return [];

    $store_ids = array_column( $stores, 'id' );
    if ( empty( $store_ids ) ) return [];

    // Build placeholders
    $placeholders = implode( ',', array_fill( 0, count( $store_ids ), '%s' ) );
    $query_values = array_merge( [ $sku ], $store_ids );

    $rows = $ext_wpdb->get_results(
        $ext_wpdb->prepare(
            "SELECT {$cfg['col_store']} as store_id,
                    {$cfg['col_qty']}   as qty
             FROM   {$cfg['table']}
             WHERE  {$cfg['col_sku']}   = %s
               AND  {$cfg['col_store']} IN ({$placeholders})",
            $query_values
        ),
        ARRAY_A
    );

    // Index results by store_id
    $stock_by_store = [];
    foreach ( (array) $rows as $row ) {
        $stock_by_store[ $row['store_id'] ] = (int) $row['qty'];
    }

    $result = [];
    foreach ( $stores as $store ) {
        $qty      = $stock_by_store[ $store['id'] ] ?? 0;
        $level    = mica_stock_level( $qty );
        $result[] = [
            'store_id'   => $store['id'],
            'store_name' => $store['name'],
            'city'       => $store['city'],
            'phone'      => $store['phone'],
            'hours'      => $store['hours'],
            'qty'        => $qty,
            'level'      => $level,
            'label'      => mica_stock_status_label( $level, $qty ),
        ];
    }

    usort( $result, fn( $a, $b ) => $b['qty'] - $a['qty'] );
    return $result;
}

/* ══════════════════════════════════════════════════════
   AJAX HANDLER
══════════════════════════════════════════════════════ */
add_action( 'wp_ajax_mica_check_store_stock',        'mica_ajax_check_store_stock' );
add_action( 'wp_ajax_nopriv_mica_check_store_stock', 'mica_ajax_check_store_stock' );

function mica_ajax_check_store_stock(): void {
    // Verify nonce
    if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'mica_stock_check' ) ) {
        wp_send_json_error( [ 'message' => 'Security check failed.' ], 403 );
    }

    $product_id = (int) ( $_POST['product_id'] ?? 0 );
    $sku        = sanitize_text_field( $_POST['sku'] ?? '' );

    if ( ! $product_id && ! $sku ) {
        wp_send_json_error( [ 'message' => 'No product specified.' ] );
    }

    // Resolve SKU from product_id if not passed
    if ( ! $sku && $product_id ) {
        $product = wc_get_product( $product_id );
        $sku     = $product ? $product->get_sku() : '';
    }

    if ( ! $sku ) {
        wp_send_json_error( [ 'message' => 'This product has no SKU/barcode set.' ] );
    }

    // Transient cache — 15 minutes per SKU
    $cache_key    = 'mica_stock_' . md5( $sku );
    $cached       = get_transient( $cache_key );

    if ( $cached !== false ) {
        wp_send_json_success( $cached );
    }

    $stock_data = mica_get_all_store_stock( $sku );

    if ( empty( $stock_data ) ) {
        wp_send_json_error( [ 'message' => 'No stock data found for SKU: ' . esc_html( $sku ) . '. Ensure store stock is configured.' ] );
    }

    $payload = [
        'sku'    => $sku,
        'stores' => $stock_data,
    ];

    set_transient( $cache_key, $payload, 15 * MINUTE_IN_SECONDS );
    wp_send_json_success( $payload );
}

/* ── Clear stock transients when a product is saved ── */
add_action( 'save_post_product', function ( $post_id ) {
    $product = wc_get_product( $post_id );
    if ( ! $product ) return;

    $sku = $product->get_sku();
    if ( $sku ) {
        delete_transient( 'mica_stock_' . md5( $sku ) );
    }

    if ( $product->is_type( 'variable' ) ) {
        foreach ( $product->get_children() as $var_id ) {
            $v = wc_get_product( $var_id );
            if ( $v && $v->get_sku() ) {
                delete_transient( 'mica_stock_' . md5( $v->get_sku() ) );
            }
        }
    }
} );
