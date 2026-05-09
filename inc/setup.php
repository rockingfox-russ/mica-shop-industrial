<?php
/**
 * inc/setup.php — Theme setup (supports, image sizes)
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', function () {

    load_theme_textdomain( 'micaonline', mica_DIR . '/languages' );
    
    add_theme_support('custom-logo');
    add_theme_support( 'automatic-feed-links' );
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', [ 'search-form', 'comment-form', 'gallery', 'caption', 'script', 'style' ] );
    add_theme_support( 'woocommerce', [
        'thumbnail_image_width' => 480,
        'gallery_thumbnail_image_width' => 120,
        'product_grid' => [
            'default_rows'    => 4,
            'min_rows'        => 1,
            'default_columns' => 4,
            'min_columns'     => 2,
            'max_columns'     => 6,
        ],
    ] );
    add_theme_support( 'wc-product-gallery-zoom' );
    add_theme_support( 'wc-product-gallery-lightbox' );
    add_theme_support( 'wc-product-gallery-slider' );

    // Image sizes
    add_image_size( 'mica-product-card',   480, 480, true );
    add_image_size( 'mica-product-single', 800, 800, false );
    add_image_size( 'mica-category-card',  400, 300, true );
    add_image_size( 'mica-hero',           1600, 600, true );

    // Content width
    $GLOBALS['content_width'] = 1320;
} );
