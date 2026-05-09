<?php
/**
 * inc/helpers.php — Reusable template helper functions
 * All functions prefixed mica_ to avoid conflicts.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Render a template part with args.
 * Usage: mica_part( 'content/product-card', [ 'product' => $product ] );
 */
function mica_part( string $slug, array $args = [] ): void {
    get_template_part( 'template-parts/' . $slug, null, $args );
}

/**
 * Format price with ZAR symbol.
 */
function mica_price( $price ): string {
    return function_exists( 'wc_price' ) ? wc_price( $price ) : 'R' . number_format( (float) $price, 2 );
}

/**
 * Get product badge HTML (Sale / New / Featured).
 */
function mica_product_badges( WC_Product $product ): string {
    $badges = [];

    if ( $product->is_on_sale() ) {
        $regular = (float) $product->get_regular_price();
        $sale    = (float) $product->get_sale_price();
        $pct     = $regular > 0 ? round( ( ( $regular - $sale ) / $regular ) * 100 ) : 0;
        $badges[] = '<span class="badge badge-orange">-' . $pct . '%</span>';
    }

    if ( $product->is_featured() ) {
        $badges[] = '<span class="badge badge-blue">Featured</span>';
    }

    $created = strtotime( $product->get_date_created() );
    if ( $created && ( time() - $created ) < ( 30 * DAY_IN_SECONDS ) ) {
        $badges[] = '<span class="badge badge-yellow">New</span>';
    }

    return $badges ? '<div class="product-card-badges">' . implode( '', $badges ) . '</div>' : '';
}

/**
 * Get stock status label + CSS class.
 */
function mica_stock_label( WC_Product $product ): array {
    if ( ! $product->is_in_stock() ) {
        return [ 'label' => 'Out of stock', 'class' => 'no-stock' ];
    }
    $qty = $product->get_stock_quantity();
    if ( $qty !== null && $qty <= 5 ) {
        return [ 'label' => 'Only ' . $qty . ' left', 'class' => 'low-stock' ];
    }
    return [ 'label' => 'In stock', 'class' => 'in-stock' ];
}

/**
 * Render breadcrumbs (no plugin needed).
 */
function mica_breadcrumbs(): void {
    global $post;

    $sep   = '<span class="sep" aria-hidden="true">›</span>';
    $home  = '<a href="' . home_url( '/' ) . '">' . __( 'Home', 'micaonline' ) . '</a>';
    $crumbs = [ $home ];

    if ( is_shop() ) {
        $shop_title = function_exists( 'wc_get_page_title' ) ? wc_get_page_title() : get_the_title( wc_get_page_id( 'shop' ) );
        $crumbs[] = '<span class="current">' . $shop_title . '</span>';

    } elseif ( is_product_category() ) {
        $term = get_queried_object();
        if ( $term->parent ) {
            $parent = get_term( $term->parent, 'product_cat' );
            $crumbs[] = '<a href="' . get_term_link( $parent ) . '">' . esc_html( $parent->name ) . '</a>';
        }
        $shop_url = get_permalink( wc_get_page_id( 'shop' ) );
        array_splice( $crumbs, 1, 0, [ '<a href="' . $shop_url . '">' . __( 'Shop', 'micaonline' ) . '</a>' ] );
        $crumbs[] = '<span class="current">' . esc_html( $term->name ) . '</span>';

    } elseif ( is_product() ) {
        $shop_url = get_permalink( wc_get_page_id( 'shop' ) );
        $crumbs[] = '<a href="' . $shop_url . '">' . __( 'Shop', 'micaonline' ) . '</a>';
        $cats = get_the_terms( $post->ID, 'product_cat' );
        if ( $cats && ! is_wp_error( $cats ) ) {
            $cat = array_shift( $cats );
            $crumbs[] = '<a href="' . get_term_link( $cat ) . '">' . esc_html( $cat->name ) . '</a>';
        }
        $crumbs[] = '<span class="current">' . get_the_title() . '</span>';

    } elseif ( is_page() ) {
        $crumbs[] = '<span class="current">' . get_the_title() . '</span>';

    } elseif ( is_singular() ) {
        $crumbs[] = '<span class="current">' . get_the_title() . '</span>';
    }

    echo '<nav class="breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'micaonline' ) . '">';
    echo implode( ' ' . $sep . ' ', $crumbs );
    echo '</nav>';
}

/**
 * SVG icon helper — reads from assets/img/icons/{name}.svg
 * Falls back to inline SVGs for common icons.
 */
function mica_icon( string $name, string $class = '' ): string {
    $attrs = $class ? ' class="' . esc_attr( $class ) . '"' : '';

    // Common inline icons (avoids file I/O for frequent ones)
    $inline = [
        'cart'   => '<svg' . $attrs . ' xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>',
        'search' => '<svg' . $attrs . ' xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>',
        'user'   => '<svg' . $attrs . ' xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>',
        'heart'  => '<svg' . $attrs . ' xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>',
        'store'  => '<svg' . $attrs . ' xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>',
        'truck'  => '<svg' . $attrs . ' xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>',
        'check'  => '<svg' . $attrs . ' xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>',
        'filter' => '<svg' . $attrs . ' xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>',
        'x'      => '<svg' . $attrs . ' xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>',
        'chevron'=> '<svg' . $attrs . ' xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>',
    ];

    if ( isset( $inline[ $name ] ) ) {
        return $inline[ $name ];
    }

    // Fallback: read from file
    $file = mica_DIR . '/assets/img/icons/' . sanitize_file_name( $name ) . '.svg';
    if ( file_exists( $file ) ) {
        $svg = file_get_contents( $file );
        if ( $class ) {
            $svg = str_replace( '<svg ', '<svg class="' . esc_attr( $class ) . '" ', $svg );
        }
        return $svg;
    }

    return '';
}

/**
 * Convert a string to Title Case (UTF-8 aware).
 */
function mica_title_case( string $str ): string {
    return mb_convert_case( $str, MB_CASE_TITLE, 'UTF-8' );
}

/**
 * Accurate product count for a category including all child categories.
 * Pre-computes all counts in one batched SQL query, cached in a 6h transient.
 */
function mica_cat_product_count( int $term_id ): int {
    static $counts = null;

    if ( $counts === null ) {
        $counts = get_transient( 'mica_cat_counts' );
        if ( $counts === false ) {
            $counts = mica_build_cat_counts();
            set_transient( 'mica_cat_counts', $counts, 6 * HOUR_IN_SECONDS );
        }
    }

    return (int) ( $counts[ $term_id ] ?? 0 );
}

/**
 * Build a [ term_id => count ] map covering all product_cat terms,
 * with each parent's count rolled up to include all descendant products.
 */
function mica_build_cat_counts(): array {
    global $wpdb;

    $rows = $wpdb->get_results(
        "SELECT tt.term_id, COUNT(DISTINCT tr.object_id) AS cnt
         FROM {$wpdb->term_relationships} tr
         INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
         INNER JOIN {$wpdb->posts} p           ON tr.object_id = p.ID
         WHERE tt.taxonomy   = 'product_cat'
         AND   p.post_type   = 'product'
         AND   p.post_status = 'publish'
         GROUP BY tt.term_id"
    );

    $direct = [];
    foreach ( $rows as $row ) {
        $direct[ (int) $row->term_id ] = (int) $row->cnt;
    }

    // Roll direct counts up to every ancestor
    $counts    = $direct;
    $all_terms = get_terms( [ 'taxonomy' => 'product_cat', 'hide_empty' => false, 'fields' => 'id=>parent' ] );
    foreach ( $all_terms as $id => $parent ) {
        if ( ! $parent || ! isset( $direct[ $id ] ) ) continue;
        $ancestors = get_ancestors( (int) $id, 'product_cat' );
        foreach ( $ancestors as $anc ) {
            $counts[ $anc ] = ( $counts[ $anc ] ?? 0 ) + $direct[ $id ];
        }
    }

    return $counts;
}

// Invalidate category count cache when products or categories change
add_action( 'save_post_product',   fn() => delete_transient( 'mica_cat_counts' ) );
add_action( 'edited_product_cat',  fn() => delete_transient( 'mica_cat_counts' ) );
add_action( 'created_product_cat', fn() => delete_transient( 'mica_cat_counts' ) );
add_action( 'deleted_product_cat', fn() => delete_transient( 'mica_cat_counts' ) );

/**
 * Get WooCommerce categories with product count, nested.
 *
 * @param int $parent Parent term ID (0 = top level)
 * @return array
 */
function mica_get_categories( int $parent = 0 ): array {
    return get_terms( [
        'taxonomy'   => 'product_cat',
        'parent'     => $parent,
        'hide_empty' => true,
        'orderby'    => 'name',
        'order'      => 'ASC',
    ] ) ?: [];
}

/**
 * Current category term or null.
 */
function mica_current_category(): ?WP_Term {
    if ( is_product_category() ) {
        return get_queried_object();
    }
    return null;
}

/**
 * Check if a term is an ancestor of another.
 */
function mica_is_ancestor( int $ancestor_id, int $term_id, string $taxonomy ): bool {
    $ancestors = get_ancestors( $term_id, $taxonomy );
    return in_array( $ancestor_id, $ancestors, true );
}
