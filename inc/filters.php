<?php
/**
 * inc/filters.php — Product filter query logic & AJAX handlers.
 *
 * Scope rules:
 * - Category page  → filters scoped to that category + children
 * - Main shop      → all products, full category tree shown
 * - All filters    → applied on top of the active category scope
 *
 * Brand filter uses product TAGS (WooCommerce product_tag taxonomy).
 */

defined( 'ABSPATH' ) || exit;

/**
 * Build WP_Query args from a normalised $params array.
 * Single source of truth for page render + AJAX.
 *
 * @param array $params {
 *   category_id       int      product_cat term ID; 0 = all
 *   min_price         float|'' empty string = no lower bound
 *   max_price         float|'' empty string = no upper bound
 *   orderby           string   menu_order|date|price|price-desc|rating|popularity
 *   tags              string[] product_tag slugs  (brand filter)
 *   attributes        array    [ 'pa_colour' => ['white','blue'], ... ]
 *   local_attributes  array    [ 'litres'    => ['1l','5l'],     ... ]
 *   on_sale           bool
 *   in_stock          bool
 *   out_of_stock      bool     mutually exclusive with in_stock
 *   paged             int
 * }
 */
function mica_build_query_args( array $params ): array {
    $defaults = [
        'category_id'      => 0,
        'min_price'        => '',
        'max_price'        => '',
        'orderby'          => 'menu_order',
        'tags'             => [],
        'attributes'       => [],
        'local_attributes' => [],
        'on_sale'          => false,
        'in_stock'         => false,
        'out_of_stock'     => false,
        'paged'            => 1,
    ];
    $p = wp_parse_args( $params, $defaults );

    $order_map = [
        'menu_order' => [ 'orderby' => 'menu_order',      'order' => 'ASC'  ],
        'date'       => [ 'orderby' => 'date',             'order' => 'DESC' ],
        'price'      => [ 'orderby' => 'meta_value_num',   'meta_key' => '_price', 'order' => 'ASC'  ],
        'price-desc' => [ 'orderby' => 'meta_value_num',   'meta_key' => '_price', 'order' => 'DESC' ],
        'rating'     => [ 'orderby' => 'meta_value_num',   'meta_key' => '_wc_average_rating', 'order' => 'DESC' ],
        'popularity' => [ 'orderby' => 'meta_value_num',   'meta_key' => 'total_sales',        'order' => 'DESC' ],
    ];
    $order_args = $order_map[ $p['orderby'] ] ?? $order_map['menu_order'];

    $args = array_merge( [
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => 24,
        'paged'          => max( 1, (int) $p['paged'] ),
        'meta_query'     => [ 'relation' => 'AND' ],
        'tax_query'      => [ 'relation' => 'AND' ],
    ], $order_args );

    // Category scope
    if ( ! empty( $p['category_id'] ) ) {
        $args['tax_query'][] = [
            'taxonomy'         => 'product_cat',
            'field'            => 'term_id',
            'terms'            => (int) $p['category_id'],
            'include_children' => true,
            'operator'         => 'IN',
        ];
    }

    // Brand filter via product tags
    $tags = array_filter( array_map( 'sanitize_text_field', (array) $p['tags'] ) );
    if ( ! empty( $tags ) ) {
        $args['tax_query'][] = [
            'taxonomy' => 'product_tag',
            'field'    => 'slug',
            'terms'    => $tags,
            'operator' => 'IN',
        ];
    }

    // Global attribute taxonomy filters (AND between groups, OR within)
    foreach ( (array) $p['attributes'] as $taxonomy => $values ) {
        $values = array_filter( array_map( 'sanitize_text_field', (array) $values ) );
        if ( ! empty( $values ) ) {
            $args['tax_query'][] = [
                'taxonomy' => sanitize_key( $taxonomy ),
                'field'    => 'slug',
                'terms'    => $values,
                'operator' => 'IN',
            ];
        }
    }

    // Local attribute filters via LIKE on serialised _product_attributes meta
    foreach ( (array) $p['local_attributes'] as $attr_key => $values ) {
        $values = array_filter( array_map( 'sanitize_text_field', (array) $values ) );
        if ( empty( $values ) ) continue;

        $clauses = array_map( fn( $v ) => [
            'key'     => '_product_attributes',
            'value'   => '"' . esc_sql( $v ) . '"',
            'compare' => 'LIKE',
        ], $values );

        $args['meta_query'][] = count( $clauses ) === 1
            ? $clauses[0]
            : array_merge( [ 'relation' => 'OR' ], $clauses );
    }

    // Sale items
    if ( ! empty( $p['on_sale'] ) ) {
        $args['post__in'] = array_merge( [ 0 ], wc_get_product_ids_on_sale() );
    }

    // Stock status — only apply when exactly one of the two is checked
    if ( ! empty( $p['in_stock'] ) && empty( $p['out_of_stock'] ) ) {
        $args['meta_query'][] = [ 'key' => '_stock_status', 'value' => 'instock' ];
    } elseif ( ! empty( $p['out_of_stock'] ) && empty( $p['in_stock'] ) ) {
        $args['meta_query'][] = [ 'key' => '_stock_status', 'value' => 'outofstock' ];
    }

    // Price range
    if ( $p['min_price'] !== '' ) {
        $args['meta_query'][] = [
            'key'     => '_price',
            'value'   => (float) $p['min_price'],
            'type'    => 'NUMERIC',
            'compare' => '>=',
        ];
    }
    if ( $p['max_price'] !== '' ) {
        $args['meta_query'][] = [
            'key'     => '_price',
            'value'   => (float) $p['max_price'],
            'type'    => 'NUMERIC',
            'compare' => '<=',
        ];
    }

    return $args;
}

// ---------------------------------------------------------------------------
// pre_get_posts: apply URL filter params to the main WooCommerce query
// This makes URLs like /shop/?on_sale=1 actually filter the product loop.
// ---------------------------------------------------------------------------
add_action( 'pre_get_posts', function ( WP_Query $q ) {
    if ( is_admin() || ! $q->is_main_query() ) return;
    if ( ! ( $q->is_post_type_archive( 'product' ) || $q->is_tax( get_object_taxonomies( 'product' ) ) ) ) return;

    // on_sale
    if ( ! empty( $_GET['on_sale'] ) ) {
        $q->set( 'post__in', array_merge( [ 0 ], wc_get_product_ids_on_sale() ) );
    }

    // orderby
    $order_map = [
        'menu_order' => [ 'orderby' => 'menu_order',      'order' => 'ASC'  ],
        'date'       => [ 'orderby' => 'date',             'order' => 'DESC' ],
        'price'      => [ 'orderby' => 'meta_value_num',   'meta_key' => '_price',              'order' => 'ASC'  ],
        'price-desc' => [ 'orderby' => 'meta_value_num',   'meta_key' => '_price',              'order' => 'DESC' ],
        'rating'     => [ 'orderby' => 'meta_value_num',   'meta_key' => '_wc_average_rating',  'order' => 'DESC' ],
        'popularity' => [ 'orderby' => 'meta_value_num',   'meta_key' => 'total_sales',         'order' => 'DESC' ],
    ];
    $orderby_key = sanitize_text_field( $_GET['orderby'] ?? '' );
    if ( isset( $order_map[ $orderby_key ] ) ) {
        foreach ( $order_map[ $orderby_key ] as $k => $v ) {
            $q->set( $k, $v );
        }
    }

    $meta_query = [ 'relation' => 'AND' ];
    $tax_query  = [ 'relation' => 'AND' ];

    // price range
    if ( isset( $_GET['min_price'] ) && $_GET['min_price'] !== '' ) {
        $meta_query[] = [ 'key' => '_price', 'value' => (float) $_GET['min_price'], 'compare' => '>=', 'type' => 'NUMERIC' ];
    }
    if ( isset( $_GET['max_price'] ) && $_GET['max_price'] !== '' ) {
        $meta_query[] = [ 'key' => '_price', 'value' => (float) $_GET['max_price'], 'compare' => '<=', 'type' => 'NUMERIC' ];
    }

    // stock
    if ( ! empty( $_GET['in_stock'] ) ) {
        $meta_query[] = [ 'key' => '_stock_status', 'value' => 'instock' ];
    } elseif ( ! empty( $_GET['out_of_stock'] ) ) {
        $meta_query[] = [ 'key' => '_stock_status', 'value' => 'outofstock' ];
    }

    // brand tags
    if ( ! empty( $_GET['filter_tag'] ) ) {
        $tax_query[] = [
            'taxonomy' => 'product_tag',
            'field'    => 'slug',
            'terms'    => array_map( 'sanitize_text_field', (array) $_GET['filter_tag'] ),
        ];
    }

    // WooCommerce attributes (pa_*)
    if ( ! empty( $_GET['filter_attr'] ) && is_array( $_GET['filter_attr'] ) ) {
        foreach ( $_GET['filter_attr'] as $tax => $values ) {
            $tax_query[] = [
                'taxonomy' => sanitize_key( $tax ),
                'field'    => 'slug',
                'terms'    => array_map( 'sanitize_text_field', (array) $values ),
            ];
        }
    }

    // Local (postmeta) attributes
    if ( ! empty( $_GET['filter_local_attr'] ) && is_array( $_GET['filter_local_attr'] ) ) {
        foreach ( $_GET['filter_local_attr'] as $tax => $values ) {
            $vals = array_map( 'sanitize_text_field', (array) $values );
            $meta_query[] = [
                'key'     => sanitize_key( $tax ),
                'value'   => count( $vals ) === 1 ? $vals[0] : $vals,
                'compare' => count( $vals ) === 1 ? '='       : 'IN',
            ];
        }
    }

    if ( count( $meta_query ) > 1 ) $q->set( 'meta_query', $meta_query );
    if ( count( $tax_query )  > 1 ) $q->set( 'tax_query',  $tax_query  );
} );

// ---------------------------------------------------------------------------
// AJAX: filter products
// ---------------------------------------------------------------------------
add_action( 'wp_ajax_mica_filter_products',        'mica_ajax_filter_products' );
add_action( 'wp_ajax_nopriv_mica_filter_products', 'mica_ajax_filter_products' );

function mica_ajax_filter_products(): void {
    check_ajax_referer( 'mica_filter_nonce', 'nonce' );

    $params = [
        'category_id'      => (int) ( $_POST['category_id'] ?? 0 ),
        'min_price'        => isset( $_POST['min_price'] ) && $_POST['min_price'] !== '' ? (float) $_POST['min_price'] : '',
        'max_price'        => isset( $_POST['max_price'] ) && $_POST['max_price'] !== '' ? (float) $_POST['max_price'] : '',
        'orderby'          => sanitize_text_field( $_POST['orderby'] ?? 'menu_order' ),
        'tags'             => isset( $_POST['tags'] )             ? array_map( 'sanitize_text_field', (array) $_POST['tags'] ) : [],
        'attributes'       => isset( $_POST['attributes'] )       ? (array) $_POST['attributes'] : [],
        'local_attributes' => isset( $_POST['local_attributes'] ) ? (array) $_POST['local_attributes'] : [],
        'on_sale'          => ! empty( $_POST['on_sale'] ),
        'in_stock'         => ! empty( $_POST['in_stock'] ),
        'out_of_stock'     => ! empty( $_POST['out_of_stock'] ),
        'paged'            => max( 1, (int) ( $_POST['paged'] ?? 1 ) ),
    ];

    $query = new WP_Query( mica_build_query_args( $params ) );

    ob_start();
    if ( $query->have_posts() ) {
        echo '<div class="products-grid" id="products-grid">';
        while ( $query->have_posts() ) {
            $query->the_post();
            $product = wc_get_product( get_the_ID() );
            if ( $product ) mica_part( 'content/product-card', [ 'product' => $product ] );
        }
        echo '</div>';
    } else {
        mica_part( 'shop/no-products' );
    }
    wp_reset_postdata();
    $html = ob_get_clean();

    ob_start();
    echo paginate_links( [
        'base'    => add_query_arg( 'paged', '%#%' ),
        'format'  => '',
        'total'   => $query->max_num_pages,
        'current' => $params['paged'],
        'type'    => 'list',
    ] );
    $pagination = ob_get_clean();

    wp_send_json_success( [
        'html'       => $html,
        'pagination' => $pagination,
        'found'      => $query->found_posts,
        'pages'      => $query->max_num_pages,
    ] );
}

// ---------------------------------------------------------------------------
// AJAX: get filter options for a category (used when category nav changes)
// ---------------------------------------------------------------------------
add_action( 'wp_ajax_mica_get_filter_options',        'mica_ajax_get_filter_options' );
add_action( 'wp_ajax_nopriv_mica_get_filter_options', 'mica_ajax_get_filter_options' );

function mica_ajax_get_filter_options(): void {
    check_ajax_referer( 'mica_filter_nonce', 'nonce' );

    $category_id = (int) ( $_POST['category_id'] ?? 0 );
    $product_ids = mica_get_product_ids_in_category( $category_id );

    wp_send_json_success( [
        'min_price'  => mica_get_price_range( $product_ids )['min'],
        'max_price'  => mica_get_price_range( $product_ids )['max'],
        'attributes' => mica_get_available_attributes( $product_ids ),
    ] );
}

// ---------------------------------------------------------------------------
// Data helpers
// ---------------------------------------------------------------------------

/**
 * Product IDs within a category (and its children).
 */
function mica_get_product_ids_in_category( int $category_id ): array {
    if ( ! $category_id ) {
        return (array) get_posts( [
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids',
        ] );
    }

    $term_ids = [ $category_id ];
    $children = get_term_children( $category_id, 'product_cat' );
    if ( ! is_wp_error( $children ) ) {
        $term_ids = array_merge( $term_ids, $children );
    }

    return (array) get_posts( [
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'tax_query'      => [ [
            'taxonomy'         => 'product_cat',
            'field'            => 'term_id',
            'terms'            => $term_ids,
            'include_children' => false,
        ] ],
    ] );
}

/**
 * Min/max price for a set of product IDs.
 */
function mica_get_price_range( array $product_ids ): array {
    if ( empty( $product_ids ) ) return [ 'min' => 0, 'max' => 1000 ];

    global $wpdb;
    $ids_list = implode( ',', array_map( 'intval', $product_ids ) );

    $row = $wpdb->get_row( $wpdb->prepare(
        "SELECT MIN(CAST(meta_value AS DECIMAL(10,2))) AS min_price,
                MAX(CAST(meta_value AS DECIMAL(10,2))) AS max_price
        FROM {$wpdb->postmeta}
        WHERE post_id IN ({$ids_list})
        AND meta_key = %s AND meta_value != ''",
        '_price'
    ) );

    return [
        'min' => $row ? (int) floor( (float) $row->min_price ) : 0,
        'max' => $row ? (int) ceil(  (float) $row->max_price ) : 1000,
    ];
}

/**
 * Brand tags for a set of product IDs.
 * Brands are stored as WooCommerce product tags (product_tag taxonomy).
 * Returns array of [ 'name', 'slug', 'count' ] sorted by count DESC.
 */
function mica_get_brand_tags( array $product_ids ): array {
    if ( empty( $product_ids ) ) return [];

    $terms = wp_get_object_terms( $product_ids, 'product_tag', [
        'orderby' => 'count',
        'order'   => 'DESC',
        'fields'  => 'all',
    ] );

    if ( is_wp_error( $terms ) || empty( $terms ) ) return [];

    $counts = [];
    foreach ( $terms as $term ) {
        if ( ! isset( $counts[ $term->slug ] ) ) {
            $counts[ $term->slug ] = [ 'name' => $term->name, 'slug' => $term->slug, 'count' => 0 ];
        }
        $counts[ $term->slug ]['count']++;
    }

    usort( $counts, fn( $a, $b ) => $b['count'] - $a['count'] );
    return array_values( $counts );
}

/**
 * Available product attributes for a set of product IDs.
 * Covers both global WC taxonomy attributes (pa_*) and local product attributes
 * stored in _product_attributes postmeta.
 * Returns [ 'pa_colour' => [ 'label' => 'Colour', 'terms' => [...] ], ... ]
 */
function mica_get_available_attributes( array $product_ids ): array {
    if ( empty( $product_ids ) ) return [];

    $result = [];

    // Global taxonomy attributes
    foreach ( (array) wc_get_attribute_taxonomies() as $tax ) {
        $taxonomy = wc_attribute_taxonomy_name( $tax->attribute_name );
        if ( ! taxonomy_exists( $taxonomy ) ) continue;

        $terms = wp_get_object_terms( $product_ids, $taxonomy, [
            'orderby' => 'name',
            'order'   => 'ASC',
            'fields'  => 'all',
        ] );

        if ( is_wp_error( $terms ) || empty( $terms ) ) continue;

        $counts = [];
        foreach ( $terms as $term ) {
            if ( ! isset( $counts[ $term->slug ] ) ) {
                $counts[ $term->slug ] = [ 'name' => $term->name, 'slug' => $term->slug, 'count' => 0 ];
            }
            $counts[ $term->slug ]['count']++;
        }

        usort( $counts, fn( $a, $b ) => $b['count'] - $a['count'] );
        $result[ $taxonomy ] = [ 'label' => $tax->attribute_label, 'terms' => array_values( $counts ) ];
    }

    // Local (non-taxonomy) attributes from _product_attributes postmeta
    global $wpdb;
    $ids_list = implode( ',', array_map( 'intval', $product_ids ) );

    $rows = $wpdb->get_results(
        "SELECT post_id, meta_value FROM {$wpdb->postmeta}
         WHERE post_id IN ({$ids_list}) AND meta_key = '_product_attributes'"
    );

    $local = [];
    foreach ( $rows as $row ) {
        $attrs = maybe_unserialize( $row->meta_value );
        if ( ! is_array( $attrs ) ) continue;
        foreach ( $attrs as $attr ) {
            if ( ! empty( $attr['is_taxonomy'] ) ) continue; // already handled above
            $key    = sanitize_title( $attr['name'] );
            $values = array_filter( array_map( 'trim', explode( '|', $attr['value'] ) ) );
            foreach ( $values as $val ) {
                $slug = sanitize_title( $val );
                if ( ! isset( $local[ $key ] ) ) {
                    $local[ $key ] = [ 'label' => $attr['name'], 'terms' => [] ];
                }
                if ( ! isset( $local[ $key ]['terms'][ $slug ] ) ) {
                    $local[ $key ]['terms'][ $slug ] = [ 'name' => $val, 'slug' => $slug, 'count' => 0, 'is_local' => true ];
                }
                $local[ $key ]['terms'][ $slug ]['count']++;
            }
        }
    }

    foreach ( $local as $key => $data ) {
        $terms = array_values( $data['terms'] );
        usort( $terms, fn( $a, $b ) => $b['count'] - $a['count'] );
        $result[ $key ] = [ 'label' => $data['label'], 'terms' => $terms ];
    }

    return $result;
}
