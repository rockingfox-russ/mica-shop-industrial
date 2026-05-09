<?php
/**
 * inc/schema.php — JSON-LD structured data
 */
defined( 'ABSPATH' ) || exit;

add_action( 'wp_head', function () {

    if ( is_product() ) {
        global $post;
        $product = wc_get_product( $post->ID );
        if ( ! $product ) return;

        $schema = [
            '@context'    => 'https://schema.org',
            '@type'       => 'Product',
            'name'        => $product->get_name(),
            'description' => wp_strip_all_tags( $product->get_short_description() ?: $product->get_description() ),
            'sku'         => $product->get_sku(),
            'image'       => wp_get_attachment_url( $product->get_image_id() ),
            'url'         => get_permalink( $product->get_id() ),
            'offers'      => [
                '@type'         => 'Offer',
                'price'         => $product->get_price(),
                'priceCurrency' => get_woocommerce_currency(),
                'availability'  => $product->is_in_stock()
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/OutOfStock',
                'url'           => get_permalink( $product->get_id() ),
            ],
        ];

        $brand = get_post_meta( $post->ID, '_brand', true );
        if ( $brand ) {
            $schema['brand'] = [ '@type' => 'Brand', 'name' => $brand ];
        }

        echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
    }

    if ( is_front_page() ) {
        $stores = mica_get_stores();
        $schema = [
            '@context' => 'https://schema.org',
            '@type'    => 'HardwareStore',
            'name'     => get_bloginfo( 'name' ),
            'url'      => home_url( '/' ),
            'logo'     => get_site_icon_url(),
        ];
        if ( ! empty( $stores ) ) {
            $schema['location'] = array_map( function ( $s ) {
                return [
                    '@type'   => 'Place',
                    'name'    => $s['name'],
                    'address' => [ '@type' => 'PostalAddress', 'streetAddress' => $s['address'], 'addressLocality' => $s['city'], 'addressCountry' => 'ZA' ],
                    'telephone' => $s['phone'],
                ];
            }, $stores );
        }
        echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
    }

}, 10 );
