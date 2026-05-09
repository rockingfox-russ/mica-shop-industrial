<?php
/**
 * inc/menus.php — Nav menu registration
 */
defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', function () {
    register_nav_menus( [
        'primary'      => __( 'Primary Navigation', 'micaonline' ),
        'navbar'       => __( 'Navbar Links (below header)', 'micaonline' ),
        'footer-help'  => __( 'Footer — Help Links', 'micaonline' ),
        'footer-1'     => __( 'Footer Column 1', 'micaonline' ),
        'footer-2'     => __( 'Footer Column 2', 'micaonline' ),
        'departments'  => __( 'Departments Sidebar', 'micaonline' ),
    ] );
} );

// ---------------------------------------------------------------------------
// Department tree — powers the header sidebar (server-rendered + inline JSON)
// ---------------------------------------------------------------------------

/**
 * Return the full department tree as an array, cached for 6 hours.
 * Uses the "Departments Sidebar" nav menu if one is assigned; otherwise
 * falls back to the live product_cat hierarchy.
 *
 * Shape: [ [ 'id', 'name', 'url', 'slug', 'count', 'children' => [...] ], ... ]
 */
function mica_get_dept_tree(): array {
    $cached = get_transient( 'mica_dept_tree' );
    if ( $cached !== false ) return $cached;

    $locations = get_nav_menu_locations();
    $tree      = [];

    if ( ! empty( $locations['departments'] ) ) {
        $items = wp_get_nav_menu_items( (int) $locations['departments'] );
        if ( $items && ! is_wp_error( $items ) ) {
            $tree = mica_menu_items_to_tree( $items );
        }
    }

    if ( empty( $tree ) ) {
        $tree = mica_cats_to_dept_tree( 0 );
    }

    set_transient( 'mica_dept_tree', $tree, 6 * HOUR_IN_SECONDS );
    return $tree;
}

/**
 * Convert a flat array of WP_Post nav-menu items into a nested tree.
 */
function mica_menu_items_to_tree( array $items ): array {
    $map = [];
    foreach ( $items as $item ) {
        $slug  = '';
        $count = 0;
        $tid   = (int) $item->object_id;

        if ( $item->type === 'taxonomy' && $item->object === 'product_cat' && $tid ) {
            $term  = get_term( $tid, 'product_cat' );
            $slug  = ( $term && ! is_wp_error( $term ) ) ? $term->slug : '';
            $count = mica_cat_product_count( $tid );
        }

        $map[ $item->ID ] = [
            'id'       => $tid ?: $item->ID,
            'name'     => $item->title,
            'url'      => $item->url,
            'slug'     => $slug,
            'count'    => $count,
            '_mid'     => $item->ID,
            '_parent'  => (int) $item->menu_item_parent,
            'children' => [],
        ];
    }

    $tree = [];
    foreach ( $map as $mid => &$node ) {
        if ( $node['_parent'] && isset( $map[ $node['_parent'] ] ) ) {
            $map[ $node['_parent'] ]['children'][] = &$node;
        } else {
            $tree[] = &$node;
        }
    }
    unset( $node );

    // Strip internal keys from output
    array_walk_recursive( $tree, function( &$val, $key ) {} ); // keep all; _mid/_parent are harmless

    return $tree;
}

/**
 * Recursively build a department tree from product_cat terms.
 */
function mica_cats_to_dept_tree( int $parent ): array {
    $cats = get_terms( [
        'taxonomy'   => 'product_cat',
        'parent'     => $parent,
        'hide_empty' => true,
        'orderby'    => 'name',
        'order'      => 'ASC',
    ] );

    if ( is_wp_error( $cats ) || empty( $cats ) ) return [];

    $tree = [];
    foreach ( $cats as $cat ) {
        $tree[] = [
            'id'       => $cat->term_id,
            'name'     => $cat->name,
            'url'      => get_term_link( $cat ),
            'slug'     => $cat->slug,
            'count'    => mica_cat_product_count( $cat->term_id ),
            'children' => mica_cats_to_dept_tree( $cat->term_id ),
        ];
    }
    return $tree;
}

/**
 * Auto-create a "Departments" nav menu from product categories on first run,
 * then assign it to the `departments` location so admin can edit it.
 */
function mica_maybe_create_departments_menu(): void {
    if ( get_option( 'mica_dept_menu_created' ) ) return;

    $locations = get_nav_menu_locations();
    if ( ! empty( $locations['departments'] ) ) {
        update_option( 'mica_dept_menu_created', 1 );
        return;
    }

    $menu_id = wp_create_nav_menu( 'Departments' );
    if ( is_wp_error( $menu_id ) ) return;

    $top_cats = get_terms( [
        'taxonomy'   => 'product_cat',
        'parent'     => 0,
        'hide_empty' => true,
        'orderby'    => 'name',
        'order'      => 'ASC',
    ] );
    if ( is_wp_error( $top_cats ) ) $top_cats = [];

    foreach ( $top_cats as $cat ) {
        $parent_item_id = wp_update_nav_menu_item( $menu_id, 0, [
            'menu-item-title'     => $cat->name,
            'menu-item-url'       => get_term_link( $cat ),
            'menu-item-status'    => 'publish',
            'menu-item-type'      => 'taxonomy',
            'menu-item-object'    => 'product_cat',
            'menu-item-object-id' => $cat->term_id,
        ] );

        $subcats = get_terms( [
            'taxonomy'   => 'product_cat',
            'parent'     => $cat->term_id,
            'hide_empty' => true,
            'orderby'    => 'name',
            'order'      => 'ASC',
        ] );
        if ( is_wp_error( $subcats ) ) $subcats = [];

        foreach ( $subcats as $sub ) {
            wp_update_nav_menu_item( $menu_id, 0, [
                'menu-item-title'     => $sub->name,
                'menu-item-url'       => get_term_link( $sub ),
                'menu-item-status'    => 'publish',
                'menu-item-type'      => 'taxonomy',
                'menu-item-object'    => 'product_cat',
                'menu-item-object-id' => $sub->term_id,
                'menu-item-parent-id' => $parent_item_id,
            ] );
        }
    }

    $locations['departments'] = $menu_id;
    set_theme_mod( 'nav_menu_locations', $locations );
    update_option( 'mica_dept_menu_created', 1 );
}
add_action( 'admin_init', 'mica_maybe_create_departments_menu' );

// Invalidate tree cache when the menu or categories are updated
add_action( 'wp_update_nav_menu',  fn() => delete_transient( 'mica_dept_tree' ) );
add_action( 'edited_product_cat',  fn() => delete_transient( 'mica_dept_tree' ) );
add_action( 'created_product_cat', fn() => delete_transient( 'mica_dept_tree' ) );
add_action( 'deleted_product_cat', fn() => delete_transient( 'mica_dept_tree' ) );
add_action( 'save_post_product',   fn() => delete_transient( 'mica_dept_tree' ) );

// Add .nav-link class to navbar menu anchors
add_filter( 'nav_menu_link_attributes', function ( $atts, $item, $args ) {
    if ( isset( $args->theme_location ) && $args->theme_location === 'navbar' ) {
        $atts['class'] = isset( $atts['class'] ) ? $atts['class'] . ' nav-link' : 'nav-link';
    }
    return $atts;
}, 10, 3 );

// Add .footer-link class to footer-help menu anchors
add_filter( 'nav_menu_link_attributes', function ( $atts, $item, $args ) {
    if ( isset( $args->theme_location ) && $args->theme_location === 'footer-help' ) {
        $atts['class'] = isset( $atts['class'] ) ? $atts['class'] . ' footer-link' : 'footer-link';
    }
    return $atts;
}, 10, 3 );
