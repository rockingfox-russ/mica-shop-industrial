<?php
/**
 * inc/enqueue.php — Scripts & styles
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_head', function () {
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">';
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';
}, 1 );

add_action( 'wp_enqueue_scripts', function () {

    $v = mica_VERSION;

    // Fonts (self-hosted or Google — swap URL as needed)
    wp_enqueue_style( 'mica-fonts',
        'https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@700;800;900&family=Inter:wght@400;500;600;700&display=swap',
        [], null
    );

    // CSS: stacked in dependency order (variables first)
    wp_enqueue_style( 'mica-variables',  mica_URI . '/assets/css/variables.css', [], $v );
    wp_enqueue_style( 'mica-base',       mica_URI . '/assets/css/base.css',      [ 'mica-variables' ], $v );
    wp_enqueue_style( 'mica-components', mica_URI . '/assets/css/components.css',[ 'mica-base' ], $v );
    wp_enqueue_style( 'mica-pages', mica_URI . '/assets/css/pages.css',[ 'mica-components' ], $v );
    wp_enqueue_style( 'mica-shop',       mica_URI . '/assets/css/shop.css',      [ 'mica-components' ], $v );
    wp_enqueue_style( 'mica-paint',      mica_URI . '/assets/css/paint-stock.css', [ 'mica-shop' ], $v );

    // Main JS
    wp_enqueue_script( 'mica-theme',       mica_URI . '/assets/js/theme.js',       [], $v, true );
    wp_enqueue_script( 'mica-filters',     mica_URI . '/assets/js/filters.js',     [ 'mica-theme' ], $v, true );
    wp_enqueue_script( 'mica-cart',        mica_URI . '/assets/js/cart.js',        [ 'mica-theme' ], $v, true );
    wp_enqueue_script( 'mica-header',      mica_URI . '/assets/js/header.js',      [ 'mica-theme' ], $v, true );

    // Store stock modal — only needed on single product pages
    if ( is_product() ) {
        wp_enqueue_script( 'mica-stock-modal', mica_URI . '/assets/js/stock-modal.js', [ 'mica-theme' ], $v, true );
    }

    // Pass data to JS
    wp_localize_script( 'mica-filters', 'micaData', [
        'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
        'nonce'     => wp_create_nonce( 'mica_filter_nonce' ),
        'currency'  => get_woocommerce_currency_symbol(),
        'shopUrl'   => get_permalink( wc_get_page_id( 'shop' ) ),
        'isShop'    => is_shop() || is_product_category() || is_product_tag(),
    ] );
} );

// WooCommerce — remove default styles (we replace them)
add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );
