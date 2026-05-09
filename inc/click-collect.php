<?php
/**
 * inc/click-collect.php — Click & Collect / Store Picker
 *
 * Uses WooCommerce Local Pickup Plus plugin if available,
 * with a lightweight fallback using order meta.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get all stores from WP options (set via Customizer or admin).
 * Structure: [ [ 'id', 'name', 'address', 'city', 'phone', 'hours' ], ... ]
 */
function mica_get_stores(): array {
    $stores = get_option( 'mica_stores', [] );

    // Default stores if none configured
    if ( empty( $stores ) ) {
        $stores = [
            [
                'id'      => 'store-001',
                'name'    => 'Mica Hardware — Rosebank',
                'address' => '12 Bath Ave, Rosebank',
                'city'    => 'Johannesburg',
                'phone'   => '011 555 0001',
                'hours'   => 'Mon–Sat 8:00–17:30 | Sun 9:00–14:00',
            ],
            [
                'id'      => 'store-002',
                'name'    => 'Mica Hardware — Sandton',
                'address' => '5 Rivonia Rd, Sandton',
                'city'    => 'Johannesburg',
                'phone'   => '011 555 0002',
                'hours'   => 'Mon–Sat 8:00–18:00 | Sun 9:00–14:00',
            ],
            [
                'id'      => 'store-003',
                'name'    => 'Mica Hardware — Fourways',
                'address' => 'Cedar Square, Fourways',
                'city'    => 'Johannesburg',
                'phone'   => '011 555 0003',
                'hours'   => 'Mon–Sat 7:30–17:00 | Sun Closed',
            ],
        ];
    }

    return $stores;
}

/**
 * Render the store selector <select> field at checkout.
 */
function mica_collect_store_field( $checkout ): void {
    $stores = mica_get_stores();
    if ( empty( $stores ) ) return;
    ?>
    <div id="mica-click-collect-wrap" style="display:none;">
        <h3><?php esc_html_e( 'Choose Collection Store', 'micaonline' ); ?></h3>
        <p class="form-row form-row-wide">
            <label for="mica_collection_store">
                <?php esc_html_e( 'Select your nearest store', 'micaonline' ); ?>
                <abbr class="required" title="required">*</abbr>
            </label>
            <select name="mica_collection_store" id="mica_collection_store"
                    class="form-select" data-required-for-collect="1">
                <option value=""><?php esc_html_e( '— Select a store —', 'micaonline' ); ?></option>
                <?php foreach ( $stores as $store ) : ?>
                    <option value="<?php echo esc_attr( $store['id'] ); ?>">
                        <?php echo esc_html( $store['name'] . ' — ' . $store['city'] ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
    </div>
    <?php
}

/* ── Save selected store to order meta ── */
add_action( 'woocommerce_checkout_process', function () {
    if ( isset( $_POST['mica_collection_store'] ) && $_POST['mica_collection_store'] !== '' ) {
        // Validate store ID
        $stores   = mica_get_stores();
        $valid_ids = array_column( $stores, 'id' );
        $store_id = sanitize_text_field( wp_unslash( $_POST['mica_collection_store'] ) );
        if ( ! in_array( $store_id, $valid_ids, true ) ) {
            wc_add_notice( __( 'Please select a valid collection store.', 'micaonline' ), 'error' );
        }
    }
} );

add_action( 'woocommerce_checkout_update_order_meta', function ( $order_id ) {
    if ( ! empty( $_POST['mica_collection_store'] ) ) {
        $store_id = sanitize_text_field( $_POST['mica_collection_store'] );
        update_post_meta( $order_id, '_mica_collection_store', $store_id );

        // Find store name for display
        $stores = mica_get_stores();
        foreach ( $stores as $store ) {
            if ( $store['id'] === $store_id ) {
                update_post_meta( $order_id, '_mica_collection_store_name', $store['name'] );
                break;
            }
        }
    }
} );

/* ── Show collection store on order admin page ── */
add_action( 'woocommerce_admin_order_data_after_billing_address', function ( $order ) {
    $store_name = $order->get_meta( '_mica_collection_store_name' );
    if ( $store_name ) {
        echo '<p><strong>' . esc_html__( 'Collection Store:', 'micaonline' ) . '</strong><br>'
           . esc_html( $store_name ) . '</p>';
    }
} );

/* ── Show on order confirmation / my-account ── */
add_action( 'woocommerce_order_details_after_order_table', function ( $order ) {
    $store_name = $order->get_meta( '_mica_collection_store_name' );
    if ( $store_name ) {
        echo '<section class="mica-order-store" style="margin-top:1.5rem;padding:1rem;background:var(--clr-blue-light);border-radius:var(--radius-lg);">';
        echo '<strong>' . esc_html__( 'Click & Collect Store', 'micaonline' ) . '</strong><br>';
        echo esc_html( $store_name );
        echo '</section>';
    }
} );

/**
 * Ajax — get stock level for a specific product + store combination.
 * Requires WooCommerce per-location stock (e.g. MultiLoca plugin).
 * Falls back to global stock if no per-store data.
 */
add_action( 'wp_ajax_mica_store_stock',        'mica_ajax_store_stock' );
add_action( 'wp_ajax_nopriv_mica_store_stock', 'mica_ajax_store_stock' );

function mica_ajax_store_stock(): void {
    if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'mica_stock_check' ) ) {
        wp_send_json_error( [ 'message' => 'Security check failed.' ], 403 );
    }

    $product_id = (int) ( $_POST['product_id'] ?? 0 );
    $store_id   = sanitize_text_field( $_POST['store_id'] ?? '' );

    if ( ! $product_id ) {
        wp_send_json_error( 'Invalid product' );
    }

    $product = wc_get_product( $product_id );
    if ( ! $product ) {
        wp_send_json_error( 'Product not found' );
    }

    // If MultiLoca or similar plugin is active, query per-store stock
    // Otherwise fall back to global stock
    $stock_qty    = $product->get_stock_quantity();
    $stock_status = $product->get_stock_status();

    wp_send_json_success( [
        'in_stock'  => $product->is_in_stock(),
        'qty'       => $stock_qty,
        'status'    => $stock_status,
        'label'     => $stock_qty !== null
                       ? sprintf( __( '%d in stock', 'micaonline' ), $stock_qty )
                       : ( $product->is_in_stock() ? __( 'Available at this store', 'micaonline' ) : __( 'Not available at this store', 'micaonline' ) ),
    ] );
}
