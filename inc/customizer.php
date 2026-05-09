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
