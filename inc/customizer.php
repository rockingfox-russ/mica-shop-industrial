<?php
/**
 * inc/customizer.php — Theme Customizer options
 */
defined( 'ABSPATH' ) || exit;

add_action( 'customize_register', function ( WP_Customize_Manager $wp_customize ) {

    /* ── Store Information ── */
    $wp_customize->add_section( 'mica_store_info', [
        'title'    => __( 'Store Information', 'micaonline' ),
        'priority' => 30,
    ] );

    $fields = [
        'mica_store_phone'   => __( 'Phone Number', 'micaonline' ),
        'mica_store_email'   => __( 'Email Address', 'micaonline' ),
        'mica_store_hours'   => __( 'Trading Hours', 'micaonline' ),
        'mica_address'   => __( 'Head Office Address', 'micaonline' ),
        'mica_google_map'   => __( 'Google Map Location', 'micaonline' ),
        'mica_utility_bar'   => __( 'Utility Bar Text', 'micaonline' ),
    ];
    foreach ( $fields as $key => $label ) {
        $wp_customize->add_setting( $key, [ 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ] );
        $wp_customize->add_control( $key, [
            'label'   => $label,
            'section' => 'mica_store_info',
            'type'    => 'text',
        ] );
    }

    $wp_customize->add_setting( 'mica_store_hours', [
        'default'           => '',
        'sanitize_callback' => 'wp_kses_post', // allows safe HTML like <br>
    ] );

    /* ── Homepage Hero ── */
    $wp_customize->add_section( 'mica_hero', [
        'title'    => __( 'Homepage Hero', 'micaonline' ),
        'priority' => 32,
    ] );
    $hero_fields = [
        'mica_hero_eyebrow'   => [ 'label' => 'Hero eyebrow label',      'default' => 'Power Tools Week' ],
        'mica_hero_headline'  => [ 'label' => 'Hero headline (plain)',    'default' => 'Built for the next' ],
        'mica_hero_accent'    => [ 'label' => 'Hero headline (italic)',   'default' => 'weekend job.' ],
        'mica_hero_sub'       => [ 'label' => 'Hero subtitle',            'default' => 'Tools & kits from DeWalt, Bosch & Makita — handpicked by your local Mica team.' ],
        'mica_hero_cta1'      => [ 'label' => 'Primary CTA label',        'default' => 'Shop the deals' ],
        'mica_hero_cta2'      => [ 'label' => 'Secondary CTA label',      'default' => 'All products' ],
        'mica_hero_sale_pct'  => [ 'label' => 'Sale chip text',           'default' => 'Save up to 35%' ],
        'mica_trade_title'    => [ 'label' => 'Trade card headline',      'default' => 'Open a trade account — 30-day terms, monthly statement.' ],
        'mica_trade_cta'      => [ 'label' => 'Trade card CTA',           'default' => 'Apply in 5 min →' ],
    ];
    foreach ( $hero_fields as $key => $data ) {
        $wp_customize->add_setting( $key, [ 'default' => $data['default'], 'sanitize_callback' => 'sanitize_text_field' ] );
        $wp_customize->add_control( $key, [ 'label' => $data['label'], 'section' => 'mica_hero', 'type' => 'text' ] );
    }

    /* ── Local Store Strip ── */
    $wp_customize->add_section( 'mica_local', [
        'title'    => __( 'Local Store Strip', 'micaonline' ),
        'priority' => 34,
    ] );
    $local_fields = [
        'mica_local_name'      => [ 'label' => 'Store name',             'default' => 'Find a store' ],
        'mica_local_hours_mon' => [ 'label' => 'Hours Mon–Fri',          'default' => 'Mon–Fri · 07:30–17:30' ],
        'mica_local_hours_sat' => [ 'label' => 'Hours Sat',              'default' => 'Sat · 08:00–14:00 · Sun closed' ],
        'mica_local_headline'  => [ 'label' => 'Strip headline (plain)', 'default' => "We're around the corner." ],
        'mica_local_accent'    => [ 'label' => 'Strip headline (italic)','default' => 'Always have been.' ],
        'mica_local_sub'       => [ 'label' => 'Strip body copy',        'default' => 'Family-run stores across South Africa. We mix paint, cut keys, hire tools, and answer questions you\'d be embarrassed to Google.' ],
    ];
    foreach ( $local_fields as $key => $data ) {
        $wp_customize->add_setting( $key, [ 'default' => $data['default'], 'sanitize_callback' => 'wp_kses_post' ] );
        $wp_customize->add_control( $key, [ 'label' => $data['label'], 'section' => 'mica_local', 'type' => 'text' ] );
    }

    /* ── Brand Strip ── */
    $wp_customize->add_section( 'mica_brands', [
        'title'    => __( 'Brand Strip', 'micaonline' ),
        'priority' => 35,
    ] );
    $wp_customize->add_setting( 'mica_brand_list', [
        'default'           => 'DeWalt, Bosch, Makita, Stanley, Plascon, Ryobi, Dulux, Cobra, Hamilton, Lasher, Eurolux, Rust-Oleum',
        'sanitize_callback' => 'sanitize_text_field',
    ] );
    $wp_customize->add_control( 'mica_brand_list', [
        'label'       => __( 'Brands (comma-separated)', 'micaonline' ),
        'description' => __( 'Each brand becomes a clickable cell in the grid. Max 12 recommended.', 'micaonline' ),
        'section'     => 'mica_brands',
        'type'        => 'textarea',
    ] );

    /* ── Brand Colours ── */
    $wp_customize->add_section( 'mica_colours', [
        'title'    => __( 'Brand Colours', 'micaonline' ),
        'priority' => 40,
    ] );

    $colours = [
        'mica_color_orange' => [ 'label' => __( 'Primary (Orange)', 'micaonline' ), 'default' => '#E8590C' ],
        'mica_color_blue'   => [ 'label' => __( 'Secondary (Blue)', 'micaonline' ), 'default' => '#1A4E8A' ],
        'mica_color_yellow' => [ 'label' => __( 'Accent (Yellow)', 'micaonline' ), 'default' => '#F5B800' ],
    ];
    foreach ( $colours as $key => $data ) {
        $wp_customize->add_setting( $key, [
            'default'           => $data['default'],
            'sanitize_callback' => 'sanitize_hex_color',
            'transport'         => 'postMessage',
        ] );
        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, $key, [
            'label'   => $data['label'],
            'section' => 'mica_colours',
        ] ) );
    }

    /* ── Output CSS variables from customizer ── */
    add_action( 'wp_head', function () use ( $colours ) {
        $vars = [];
        foreach ( $colours as $key => $data ) {
            $val = get_theme_mod( $key, $data['default'] );
            if ( $val !== $data['default'] ) {
                $map = [
                    'mica_color_orange' => '--clr-orange',
                    'mica_color_blue'   => '--clr-blue',
                    'mica_color_yellow' => '--clr-yellow',
                ];
                $vars[] = $map[ $key ] . ':' . esc_attr( $val );
            }
        }
        if ( ! empty( $vars ) ) {
            echo '<style>:root{' . implode( ';', $vars ) . '}</style>' . "\n";
        }
    } );

} );
