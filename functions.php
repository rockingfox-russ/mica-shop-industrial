<?php
/**
 * micaonline Theme — functions.php
 * Bootstraps all modular includes. Nothing performance-critical should be broken here.
 */

defined('ABSPATH') || exit;

define('mica_VERSION', '1.0.1');
define('mica_DIR', get_template_directory());
define('mica_URI', get_template_directory_uri());

/* Load modules */
$includes = [
    'inc/setup.php',
    'inc/enqueue.php',
    'inc/menus.php',
    'inc/helpers.php',
    'inc/woocommerce.php',
    'inc/filters.php',
    'inc/click-collect.php',
    'inc/ajax.php',
    'inc/stock-checker.php',
    'inc/store-stock-db.php',
    'inc/customizer.php',
    'inc/schema.php',
];

foreach ($includes as $file) {
    $path = mica_DIR . '/' . $file;
    if (file_exists($path)) {
        require_once $path;
    }
}

/* GitHub theme updates — admin only, zero frontend cost */
if (is_admin()) {
    $puc_path = mica_DIR . '/inc/lib/plugin-update-checker/load-v5p5.php';
    if (file_exists($puc_path)) {
        require_once $puc_path;
        $update_checker = YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
            'https://github.com/rockingfox-russ/mica-shop/',
            __FILE__,
            'mica-shop'
        );
        $update_checker->setBranch('master');
    }
}

function mica_filter_empty_branches(&$categories) {
    $categories = array_filter($categories, function (&$cat) {
        if (!empty($cat['children'])) {
            mica_filter_empty_branches($cat['children']);
            return !empty($cat['children']) || $cat['count'] > 0;
        }
        return $cat['count'] > 0;
    });
    $categories = array_values($categories);
}

add_action('wp_ajax_get_all_categories', 'mica_get_all_categories');
add_action('wp_ajax_nopriv_get_all_categories', 'mica_get_all_categories');

function mica_get_all_categories() {

    $cache_key = 'mica_all_categories_tree';
    $cached = get_transient($cache_key);

    if ($cached !== false) {
        wp_send_json_success($cached);
        return;
    }

    $all_categories = get_terms([
        'taxonomy' => 'product_cat',
        'hide_empty' => true,
        'orderby' => 'name',
        'order' => 'ASC',
    ]);

    $category_tree = [];
    $category_map = [];

    foreach ($all_categories as $cat) {
        $category_map[$cat->term_id] = [
            'id' => $cat->term_id,
            'name' => $cat->name,
            'slug' => $cat->slug,
            'count' => $cat->count,
            'url' => get_term_link($cat),
            'parent' => $cat->parent,
            'children' => []
        ];
    }

    foreach ($category_map as &$cat) {
        if ($cat['parent'] == 0) {
            $category_tree[] = &$cat;
        } else {
            if (isset($category_map[$cat['parent']])) {
                $category_map[$cat['parent']]['children'][] = &$cat;
            }
        }
    }

    mica_filter_empty_branches($category_tree);

    set_transient($cache_key, $category_tree, 6 * HOUR_IN_SECONDS);

    wp_send_json_success($category_tree);
}

// Invalidate the AJAX category tree cache when categories or products change
foreach ( [ 'edited_product_cat', 'created_product_cat', 'deleted_product_cat' ] as $_hook ) {
    add_action( $_hook, fn() => delete_transient( 'mica_all_categories_tree' ) );
}
add_action( 'save_post_product', fn() => delete_transient( 'mica_all_categories_tree' ) );

add_action('wp_enqueue_scripts', function () {
    wp_dequeue_style('wp-block-library');
    wp_dequeue_style('wp-block-library-theme');
    wp_dequeue_style('wc-block-style');
}, 100);

add_filter('woocommerce_should_load_block_assets', function () {
    return is_cart() || is_checkout();
});

add_action('wp_enqueue_scripts', function () {

    if (!is_cart() && !is_checkout() && !is_account_page()) {
        wp_dequeue_script('loyaltyplus-blocks-integration');
        wp_dequeue_style('loyaltyplus-blocks-integration');
    }

}, 100);

add_action('wp_enqueue_scripts', function () {

    if (!is_cart() && !is_checkout() && !is_account_page()) {
        wp_dequeue_script('wc-cart-fragments');
    }

}, 100);

add_action('wp_enqueue_scripts', function () {

    if (is_product_category() || is_shop() || is_post_type_archive('product')) {

        wp_dequeue_script('wp-components');
        wp_dequeue_script('wp-data');
        wp_dequeue_script('wp-element');
        wp_dequeue_script('react');
        wp_dequeue_script('react-dom');
        wp_dequeue_script('wp-polyfill');

    }

}, 100);

add_image_size('archive-thumb', 400, 400, true);

add_filter('single_product_archive_thumbnail_size', function () {
    return 'archive-thumb';
});

// GOOLE ANALYTICS AND TRACKING

/**
 * Add meta tags and GTM scripts to <head>
 */
function mytheme_add_head_scripts() {
    ?>
    <meta name="google-site-verification" content="qPFIeY9-Hz7GJol5oP4S1cUD_um_nWYiB04lV4BmnTQ" />

    <!-- Google Tag Manager -->
    <script>
    (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({
        'gtm.start': new Date().getTime(),
        event:'gtm.js'
    });
    var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),
        dl=l!='dataLayer' ? '&l='+l : '';

    j.async=true;
    j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;
    f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-P64TTS8');
    </script>
    <!-- End Google Tag Manager -->
    <?php
}
add_action( 'wp_head', 'mytheme_add_head_scripts' );

/**
 * Add GTM noscript immediately after opening body tag
 */
function mytheme_gtm_noscript() {
    ?>
    <!-- Google Tag Manager (noscript) -->
    <noscript>
        <iframe
            src="https://www.googletagmanager.com/ns.html?id=GTM-P64TTS8"
            height="0"
            width="0"
            style="display:none;visibility:hidden">
        </iframe>
    </noscript>
    <!-- End Google Tag Manager (noscript) -->
    <?php
}
add_action( 'wp_body_open', 'mytheme_gtm_noscript' );