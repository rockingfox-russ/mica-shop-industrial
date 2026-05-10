<?php
/**
 * archive-product.php — Shop & Category archive page
 * Handles: /shop, /product-category/*, filtered results
 */

defined( 'ABSPATH' ) || exit;

get_header();

$current_cat   = mica_current_category();
$scope_id      = $current_cat ? $current_cat->term_id : 0;

// Active filters from URL params
$active_filters = [
    'min_price'        => isset( $_GET['min_price'] ) && $_GET['min_price'] !== '' ? (float) $_GET['min_price'] : '',
    'max_price'        => isset( $_GET['max_price'] ) && $_GET['max_price'] !== '' ? (float) $_GET['max_price'] : '',
    'orderby'          => sanitize_text_field( $_GET['orderby'] ?? 'menu_order' ),
    'on_sale'          => ! empty( $_GET['on_sale'] ),
    'in_stock'         => ! empty( $_GET['in_stock'] ),
    'out_of_stock'     => ! empty( $_GET['out_of_stock'] ),
    'tags'             => [],
    'attributes'       => [],
    'local_attributes' => [],
];

if ( ! empty( $_GET['filter_tag'] ) ) {
    $active_filters['tags'] = array_map( 'sanitize_text_field', (array) $_GET['filter_tag'] );
}

if ( ! empty( $_GET['filter_attr'] ) && is_array( $_GET['filter_attr'] ) ) {
    foreach ( $_GET['filter_attr'] as $tax => $values ) {
        $active_filters['attributes'][ sanitize_key( $tax ) ] = array_map( 'sanitize_text_field', (array) $values );
    }
}

if ( ! empty( $_GET['filter_local_attr'] ) && is_array( $_GET['filter_local_attr'] ) ) {
    foreach ( $_GET['filter_local_attr'] as $tax => $values ) {
        $active_filters['local_attributes'][ sanitize_key( $tax ) ] = array_map( 'sanitize_text_field', (array) $values );
    }
}
?>

<!-- B's editorial category banner (outside container, full bleed) -->
<div class="listing-banner-b">
    <div class="container">
        <?php mica_breadcrumbs(); ?>
        <div class="listing-banner-inner">
            <h1 class="listing-title-b">
                <?php
                if ( $current_cat ) {
                    $parts = preg_split('/\b(and|&)\b/i', $current_cat->name, 2);
                    if ( count( $parts ) === 2 ) {
                        echo esc_html( trim( $parts[0] ) ) . ' <em class="serif-italic">&amp; ' . esc_html( trim( $parts[1] ) ) . '</em>';
                    } else {
                        echo esc_html( $current_cat->name );
                    }
                } else {
                    echo 'All <em class="serif-italic">Products.</em>';
                }
                ?>
            </h1>
            <?php if ( $current_cat && $current_cat->description ) : ?>
            <p class="listing-desc-b"><?php echo esc_html( $current_cat->description ); ?></p>
            <?php else : ?>
            <p class="listing-desc-b">Pick a category first — the rest gets simpler from there. All stocked locally, dispatched daily.</p>
            <?php endif; ?>
        </div>

        <!-- Subcategory tabs -->
        <?php
        $subcats = get_terms( [
            'taxonomy'   => 'product_cat',
            'parent'     => $scope_id,
            'hide_empty' => true,
            'number'     => 10,
        ] );
        if ( ! empty( $subcats ) && ! is_wp_error( $subcats ) ) : ?>
        <div class="subcategory-tabs">
            <a href="<?php echo esc_url( $current_cat ? get_term_link( $current_cat ) : get_permalink( wc_get_page_id('shop') ) ); ?>"
               class="subcategory-tab active">
                All <span class="subcategory-tab-count">· <?php global $wp_query; echo (int)$wp_query->found_posts; ?></span>
            </a>
            <?php foreach ( $subcats as $sub ) : ?>
            <a href="<?php echo esc_url( get_term_link( $sub ) ); ?>" class="subcategory-tab">
                <?php echo esc_html( $sub->name ); ?>
                <span class="subcategory-tab-count">· <?php echo mica_cat_product_count( $sub->term_id ); ?></span>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="container">

    <div class="shop-layout">

        <!-- Sidebar (desktop) -->
        <?php mica_part( 'shop/filter-sidebar', [
            'scope_category_id' => $scope_id,
            'active_filters'    => $active_filters,
        ] ); ?>

        <!-- Main content -->
        <div class="shop-main" id="shop-main">

            <!-- B's horizontal filter strip -->
            <div class="filter-strip-b">
                <span class="filter-strip-label">Filter:</span>

                <?php mica_part( 'shop/active-filters', [ 'active_filters' => $active_filters ] ); ?>

                <!-- Mobile filter button -->
                <button class="filter-mobile-btn" id="filter-mobile-btn" aria-expanded="false">
                    <?php echo mica_icon( 'filter' ); ?>
                    <?php esc_html_e( 'Filter', 'micaonline' ); ?>
                </button>

                <div class="filter-results-b">
                    <span class="filter-results-count" id="result-count">
                        <?php global $wp_query; ?>
                        <strong><?php echo (int) $wp_query->found_posts; ?></strong> results
                    </span>
                </div>
            </div>

            <!-- Toolbar: result count + sort + view toggle -->
            <div class="shop-toolbar">
                <span class="shop-result-count" id="result-count">
                    <?php
                    global $wp_query;
                    printf(
                        _n( '<strong>%d</strong> product', '<strong>%d</strong> products', $wp_query->found_posts, 'micaonline' ),
                        $wp_query->found_posts
                    );
                    ?>
                </span>

                <div class="shop-sort-wrap">
                    <label for="shop-sort" class="shop-sort-label sr-only"><?php esc_html_e( 'Sort by', 'micaonline' ); ?></label>
                    <select class="shop-sort-select" id="shop-sort" aria-label="<?php esc_attr_e( 'Sort by', 'micaonline' ); ?>">
                        <option value="menu_order"  <?php selected( $active_filters['orderby'], 'menu_order' ); ?>><?php esc_html_e( 'Default', 'micaonline' ); ?></option>
                        <option value="date"        <?php selected( $active_filters['orderby'], 'date' ); ?>><?php esc_html_e( 'Newest', 'micaonline' ); ?></option>
                        <option value="price"       <?php selected( $active_filters['orderby'], 'price' ); ?>><?php esc_html_e( 'Price: Low to High', 'micaonline' ); ?></option>
                        <option value="price-desc"  <?php selected( $active_filters['orderby'], 'price-desc' ); ?>><?php esc_html_e( 'Price: High to Low', 'micaonline' ); ?></option>
                        <option value="popularity"  <?php selected( $active_filters['orderby'], 'popularity' ); ?>><?php esc_html_e( 'Popularity', 'micaonline' ); ?></option>
                        <option value="rating"      <?php selected( $active_filters['orderby'], 'rating' ); ?>><?php esc_html_e( 'Rating', 'micaonline' ); ?></option>
                    </select>

                    <!-- View toggle -->
                    <div class="view-toggle" role="group" aria-label="<?php esc_attr_e( 'View mode', 'micaonline' ); ?>">
                        <button class="view-btn active" id="view-grid" aria-label="<?php esc_attr_e( 'Grid view', 'micaonline' ); ?>">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                                <rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>
                            </svg>
                        </button>
                        <button class="view-btn" id="view-list" aria-label="<?php esc_attr_e( 'List view', 'micaonline' ); ?>">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/>
                                <line x1="8" y1="18" x2="21" y2="18"/>
                                <line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/>
                                <line x1="3" y1="18" x2="3.01" y2="18"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Products grid — WC renders into here -->
            <div id="products-container">
                <?php if ( have_posts() ) : ?>
                    <div class="products-grid" id="products-grid">
                        <?php
                        while ( have_posts() ) {
                            the_post();
                            $product = wc_get_product( get_the_ID() );
                            if ( $product ) {
                                mica_part( 'content/product-card', [ 'product' => $product ] );
                            }
                        }
                        ?>
                    </div>

                    <!-- Pagination -->
                    <div class="pagination-wrap" id="pagination-wrap">
                        <?php
                        global $wp_query;
                        echo paginate_links( [
                            'base'    => add_query_arg( 'paged', '%#%' ),
                            'format'  => '',
                            'total'   => $wp_query->max_num_pages,
                            'current' => max( 1, get_query_var( 'paged' ) ),
                            'type'    => 'list',
                        ] );
                        ?>
                    </div>

                <?php else : ?>
                    <?php mica_part( 'shop/no-products' ); ?>
                <?php endif; ?>
            </div><!-- #products-container -->

        </div><!-- .shop-main -->
    </div><!-- .shop-layout -->
</div><!-- .container -->

<!-- Mobile filter drawer -->
<div class="filter-drawer-overlay" id="filter-drawer-overlay"></div>
<div class="filter-drawer" id="filter-drawer" aria-label="<?php esc_attr_e( 'Filters', 'micaonline' ); ?>">
    <div class="filter-drawer-header">
        <span class="font-bold"><?php esc_html_e( 'Filter Products', 'micaonline' ); ?></span>
        <button class="btn btn-ghost btn-sm" id="filter-drawer-close">✕</button>
    </div>
    <!-- sidebar moved here by JS when drawer opens -->
</div>

<?php
// Expose filter state to JS
$filter_data = [
    'scopeCategoryId' => $scope_id,
    'activeFilters'   => $active_filters,
    'currentUrl'      => get_pagenum_link( 1, false ),
];
?>
<script>
window.micaFilterState = <?php echo wp_json_encode( $filter_data ); ?>;
</script>

<?php get_footer(); ?>
