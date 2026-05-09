<?php
/**
 * inc/woocommerce.php — WooCommerce hooks & theme tweaks
 */

defined( 'ABSPATH' ) || exit;

/* ── Tell WC this theme supports it ── */
add_action( 'after_setup_theme', function () {
    add_theme_support( 'woocommerce' );
} );

/* ── Remove default WC wrappers (we use our own) ── */
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content',  'woocommerce_output_content_wrapper_end', 10 );
remove_action( 'woocommerce_sidebar',             'woocommerce_get_sidebar', 10 );

/* ── Replace with our wrappers ── */
add_action( 'woocommerce_before_main_content', function () {
    echo '<div class="container"><div class="shop-layout">';
    // Sidebar rendered by archive-product.php directly
}, 10 );

add_action( 'woocommerce_after_main_content', function () {
    echo '</div></div>'; // .shop-layout + .container
}, 10 );

/* ── Product columns ── */
add_filter( 'loop_shop_columns', fn() => 4 );
add_filter( 'loop_shop_per_page', fn() => 24 );

/* ── Use our product card template ── */
remove_action( 'woocommerce_before_shop_loop_item',       'woocommerce_template_loop_product_link_open', 10 );
remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 );
remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );
remove_action( 'woocommerce_shop_loop_item_title',        'woocommerce_template_loop_product_title', 10 );
remove_action( 'woocommerce_after_shop_loop_item_title',  'woocommerce_template_loop_rating', 5 );
remove_action( 'woocommerce_after_shop_loop_item_title',  'woocommerce_template_loop_price', 10 );
remove_action( 'woocommerce_after_shop_loop_item',        'woocommerce_template_loop_product_link_close', 5 );
remove_action( 'woocommerce_after_shop_loop_item',        'woocommerce_template_loop_add_to_cart', 10 );

add_action( 'woocommerce_before_shop_loop_item', function () {
    mica_part( 'content/product-card', [ 'product' => $GLOBALS['product'] ] );
}, 10 );

/* ── Remove default breadcrumb (we use our own) ── */
remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );

/* ── Extended product search: title, SKU, barcode, category, tag ── */
function _mica_is_product_search( $query ): bool {
    return ! is_admin()
        && $query->is_main_query()
        && $query->is_search()
        && ( isset( $_GET['post_type'] ) && $_GET['post_type'] === 'product' );
}

add_filter( 'posts_join', function ( $join, $query ) {
    global $wpdb;
    if ( ! _mica_is_product_search( $query ) ) return $join;
    $join .= " LEFT JOIN {$wpdb->postmeta}         AS mpm  ON ( {$wpdb->posts}.ID = mpm.post_id ) ";
    $join .= " LEFT JOIN {$wpdb->term_relationships} AS mtr  ON ( {$wpdb->posts}.ID = mtr.object_id ) ";
    $join .= " LEFT JOIN {$wpdb->term_taxonomy}      AS mtt  ON ( mtr.term_taxonomy_id = mtt.term_taxonomy_id AND mtt.taxonomy IN ('product_cat','product_tag') ) ";
    $join .= " LEFT JOIN {$wpdb->terms}              AS mtm  ON ( mtt.term_id = mtm.term_id ) ";
    return $join;
}, 10, 2 );

add_filter( 'posts_search', function ( $search, $query ) {
    global $wpdb;
    if ( ! _mica_is_product_search( $query ) || empty( $query->query_vars['s'] ) ) return $search;
    $like = '%' . $wpdb->esc_like( $query->query_vars['s'] ) . '%';
    return $wpdb->prepare(
        " AND (
            {$wpdb->posts}.post_title   LIKE %s
            OR {$wpdb->posts}.post_excerpt LIKE %s
            OR ( mpm.meta_key = '_sku'          AND mpm.meta_value LIKE %s )
            OR ( mpm.meta_key = 'wpcf-barcode'  AND mpm.meta_value LIKE %s )
            OR mtm.name LIKE %s
        )",
        $like, $like, $like, $like, $like
    );
}, 10, 2 );

add_filter( 'posts_groupby', function ( $groupby, $query ) {
    global $wpdb;
    if ( ! _mica_is_product_search( $query ) ) return $groupby;
    return "{$wpdb->posts}.ID";
}, 10, 2 );

/* ── Related products: limit to 4 ── */
add_filter( 'woocommerce_output_related_products_args', function ( $args ) {
    $args['posts_per_page'] = 4;
    $args['columns']        = 4;
    return $args;
} );

/* ── My account: custom endpoints ── */
add_filter( 'woocommerce_account_menu_items', function ( $items ) {
    $new = [];
    foreach ( $items as $key => $label ) {
        $new[ $key ] = $label;
        if ( $key === 'orders' ) {
            $new['click-collect'] = __( 'Collection Orders', 'micaonline' );
        }
    }
    return $new;
} );

/* ── Cart fragments (AJAX mini-cart count) ── */
add_filter( 'woocommerce_add_to_cart_fragments', function ( $fragments ) {
    ob_start();
    ?>
    <span class="cart-count" id="cart-count">
        <?php echo WC()->cart->get_cart_contents_count(); ?>
    </span>
    <?php
    $fragments['#cart-count'] = ob_get_clean();
    return $fragments;
} );

/* ── Checkout Delivery (untick deliver to another address) ── */
add_filter( 'woocommerce_ship_to_different_address_checked', '__return_false' );

/* ── Single product: hide WC's qty field; theme custom stepper syncs value on submit ── */
add_action( 'woocommerce_before_add_to_cart_quantity', function () {
    if ( is_product() ) {
        echo '<div class="wc-qty-hidden" aria-hidden="true" style="display:none;">';
    }
} );
add_action( 'woocommerce_after_add_to_cart_quantity', function () {
    if ( is_product() ) {
        echo '</div>';
    }
} );

/* ── Checkout: add click & collect field ── */
add_action( 'woocommerce_after_checkout_billing_form', function ( $checkout ) {
    if ( function_exists( 'mica_collect_store_field' ) ) {
        mica_collect_store_field( $checkout );
    }
} );

// Only calculate shipping on cart/checkout pages, not everywhere
add_filter( 'woocommerce_cart_ready_to_calc_shipping', function( $calculate ) {
    return is_cart() || is_checkout() ? $calculate : false;
} );

add_filter( 'http_request_timeout', function( $timeout, $url ) {
    if ( str_contains( $url ?? '', 'thecourierguy' ) || str_contains( $url ?? '', 'tcg' ) ) {
        return 5; // 5 seconds max instead of default 30
    }
    return $timeout;
}, 10, 2 );

