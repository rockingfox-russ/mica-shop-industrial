<?php
/**
 * template-parts/shop/filter-sidebar.php
 * Included twice: desktop sidebar + mobile drawer — no function definitions here.
 *
 * @var int   $scope_category_id
 * @var array $active_filters
 */
defined( 'ABSPATH' ) || exit;

$scope_id = (int) ( $args['scope_category_id'] ?? 0 );
$af       = wp_parse_args( $args['active_filters'] ?? [], [
    'min_price'        => '',
    'max_price'        => '',
    'on_sale'          => false,
    'in_stock'         => false,
    'out_of_stock'     => false,
    'tags'             => [],
    'attributes'       => [],
    'local_attributes' => [],
] );

$product_ids = mica_get_product_ids_in_category( $scope_id );
$price_range = mica_get_price_range( $product_ids );
$brands      = mica_get_brand_tags( $product_ids );
$attributes  = mica_get_available_attributes( $product_ids );
$current_cat = mica_current_category();
if ( $current_cat ) {
    // Get top ancestor
    $ancestor_id = $current_cat->term_id;

    while ( $parent = get_term( $ancestor_id )->parent ) {
        $ancestor_id = $parent;
    }

    // Only show that branch
    $top_cats = mica_get_categories( $ancestor_id );
} else {
    $top_cats = mica_get_categories( 0 );
}

$slide_min = $af['min_price'] !== '' ? max( $price_range['min'], (int) $af['min_price'] ) : $price_range['min'];
$slide_max = $af['max_price'] !== '' ? min( $price_range['max'], (int) $af['max_price'] ) : $price_range['max'];
$aside_class = 'filter-sidebar';
if ( wp_is_mobile() ) {
    $aside_class .= ' is-mobile';
}
?>

<aside class="<?php echo esc_attr( $aside_class ); ?>" id="filter-sidebar"
       data-scope-category="<?php echo esc_attr( $scope_id ); ?>">
<div class="filter-panel" id="filter-panel">

    <?php /* ── Header ── */ ?>
    <div class="filter-panel-header">
        <h2 class="filter-panel-title"><?php esc_html_e( 'Filter Products', 'micaonline' ); ?></h2>
        <button class="filter-clear-all" id="filter-clear-all" style="display:none">
            <?php esc_html_e( 'Clear All', 'micaonline' ); ?>
        </button>
    </div>

    <?php /* ── 1. Price — collapsed ── */ ?>
    <div class="filter-section">
        <button class="filter-section-toggle" aria-expanded="true">
            <span class="filter-section-title"><?php esc_html_e( 'Price', 'micaonline' ); ?></span>
            <?php echo mica_icon( 'chevron' ); ?>
        </button>
        <div class="filter-section-body">
            <div class="price-slider-wrap" id="price-slider-wrap"
                 data-min="<?php echo esc_attr( $price_range['min'] ); ?>"
                 data-max="<?php echo esc_attr( $price_range['max'] ); ?>">
                <div class="price-slider-track">
                    <div class="price-slider-fill" id="price-slider-fill"></div>
                </div>
                <input type="range" id="price-slider-min"
                       min="<?php echo esc_attr( $price_range['min'] ); ?>"
                       max="<?php echo esc_attr( $price_range['max'] ); ?>"
                       value="<?php echo esc_attr( $slide_min ); ?>" step="1">
                <input type="range" id="price-slider-max"
                       min="<?php echo esc_attr( $price_range['min'] ); ?>"
                       max="<?php echo esc_attr( $price_range['max'] ); ?>"
                       value="<?php echo esc_attr( $slide_max ); ?>" step="1">
            </div>
            <div class="price-inputs-row">
                <label class="price-input-group">
                    <span class="price-currency">R</span>
                    <input type="number" id="filter-min-price"
                           placeholder="<?php echo esc_attr( $price_range['min'] ); ?>"
                           value="<?php echo esc_attr( $af['min_price'] ); ?>"
                           min="<?php echo esc_attr( $price_range['min'] ); ?>"
                           max="<?php echo esc_attr( $price_range['max'] ); ?>">
                </label>
                <span class="price-sep">–</span>
                <label class="price-input-group">
                    <span class="price-currency">R</span>
                    <input type="number" id="filter-max-price"
                           placeholder="<?php echo esc_attr( $price_range['max'] ); ?>"
                           value="<?php echo esc_attr( $af['max_price'] ); ?>"
                           min="<?php echo esc_attr( $price_range['min'] ); ?>"
                           max="<?php echo esc_attr( $price_range['max'] ); ?>">
                </label>
            </div>
            <button class="btn btn-secondary btn-sm btn-full" id="btn-apply-price">
                <?php esc_html_e( 'Apply', 'micaonline' ); ?>
            </button>
        </div>
    </div>

    <?php /* ── 2. Availability — collapsed ── */ ?>
    <div class="filter-section">
        <button class="filter-section-toggle" aria-expanded="true">
            <span class="filter-section-title"><?php esc_html_e( 'Availability', 'micaonline' ); ?></span>
            <?php echo mica_icon( 'chevron' ); ?>
        </button>
        <div class="filter-section-body">
            <div class="filter-checkbox-list">
                <label class="filter-check-label">
                    <input type="checkbox" id="filter-on-sale" value="1"
                           <?php checked( ! empty( $af['on_sale'] ) ); ?>>
                    <span class="filter-check-name"><?php esc_html_e( 'On Promotion', 'micaonline' ); ?></span>
                </label>
                <label class="filter-check-label">
                    <input type="checkbox" id="filter-in-stock" value="1"
                           <?php checked( ! empty( $af['in_stock'] ) ); ?>>
                    <span class="filter-check-name"><?php esc_html_e( 'In Stock', 'micaonline' ); ?></span>
                </label>
                <label class="filter-check-label">
                    <input type="checkbox" id="filter-out-of-stock" value="1"
                           <?php checked( ! empty( $af['out_of_stock'] ) ); ?>>
                    <span class="filter-check-name"><?php esc_html_e( 'Out of Stock', 'micaonline' ); ?></span>
                </label>
            </div>
        </div>
    </div>

    <?php /* ── 3. Brand — expanded ── */ ?>
    <?php if ( ! empty( $brands ) ) : ?>
    <div class="filter-section">
        <button class="filter-section-toggle" aria-expanded="true">
            <span class="filter-section-title"><?php esc_html_e( 'Brand', 'micaonline' ); ?></span>
            <?php echo mica_icon( 'chevron' ); ?>
        </button>
        <div class="filter-section-body">
            <?php if ( count( $brands ) > 5 ) : ?>
            <input type="text" class="filter-search-input" id="brand-search"
                   placeholder="<?php esc_attr_e( 'Search brands…', 'micaonline' ); ?>"
                   autocomplete="off">
            <?php endif; ?>
            <div class="filter-checkbox-list">
                <?php foreach ( $brands as $i => $brand ) :
                    $active = in_array( $brand['slug'], (array) $af['tags'], true );
                ?>
                <label class="filter-check-label<?php echo ( $i >= 5 && ! $active ) ? ' filter-item-hidden' : ''; ?>">
                    <input type="checkbox" name="filter_tag[]"
                           value="<?php echo esc_attr( $brand['slug'] ); ?>"
                           <?php checked( $active ); ?>>
                    <span class="filter-check-name"><?php echo esc_html( mica_title_case( $brand['name'] ) ); ?></span>
                    <!-- <span class="filter-check-count"><?php echo (int) $brand['count']; ?></span> -->
                </label>
                <?php endforeach; ?>
                <?php if ( count( $brands ) > 5 ) : ?>
                <button type="button" class="filter-show-more"
                        data-show-text="<?php echo esc_attr( sprintf( __( 'Show %d more', 'micaonline' ), count( $brands ) - 5 ) ); ?>"
                        data-hide-text="<?php esc_attr_e( 'Show less', 'micaonline' ); ?>">
                    <?php echo esc_html( sprintf( __( 'Show %d more', 'micaonline' ), count( $brands ) - 5 ) ); ?>
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php /* ── 4. Categories — expanded ── */ ?>
    <div class="filter-section">
        <button class="filter-section-toggle" aria-expanded="true">
            <span class="filter-section-title"><?php esc_html_e( 'Categories', 'micaonline' ); ?></span>
            <?php echo mica_icon( 'chevron' ); ?>
        </button>
        <div class="filter-section-body">
            <ul class="filter-cat-list">
                <?php foreach ( $top_cats as $cat ) :
                    $is_current  = $current_cat && $current_cat->term_id === $cat->term_id;
                    $is_ancestor = $current_cat && mica_is_ancestor( $cat->term_id, $current_cat->term_id, 'product_cat' );
                    $subcats     = mica_get_categories( $cat->term_id );
                    $has_subs    = ! empty( $subcats );
                    $expanded    = $is_current || $is_ancestor;
                    $cls         = 'filter-cat-item';
                    if ( $is_current )  $cls .= ' current-cat';
                    if ( $is_ancestor ) $cls .= ' current-cat-ancestor';
                ?>
                <li class="<?php echo esc_attr( $cls ); ?>">
                    <div class="filter-cat-row">
                        <a href="<?php echo esc_url( get_term_link( $cat ) ); ?>"
                           data-cat-id="<?php echo esc_attr( $cat->term_id ); ?>">
                            <span><?php echo esc_html( mica_title_case( $cat->name ) ); ?></span>
                            <span class="filter-cat-count"><?php echo mica_cat_product_count( $cat->term_id ); ?></span>
                        </a>
                        <?php if ( $has_subs ) : ?>
                        <button type="button" class="filter-cat-toggle"
                                aria-expanded="<?php echo $expanded ? 'true' : 'false'; ?>"
                                aria-label="<?php esc_attr_e( 'Toggle subcategories', 'micaonline' ); ?>">
                            <?php echo mica_icon( 'chevron' ); ?>
                        </button>
                        <?php endif; ?>
                    </div>
                    <?php if ( $has_subs ) : ?>
                    <ul class="filter-subcat-list"<?php echo $expanded ? '' : ' hidden'; ?>>
                        <?php foreach ( $subcats as $sub ) :
                            $sub_active = $current_cat && $current_cat->term_id === $sub->term_id;
                        ?>
                        <li class="filter-subcat-item<?php echo $sub_active ? ' current-cat' : ''; ?>">
                            <a href="<?php echo esc_url( get_term_link( $sub ) ); ?>"
                               data-cat-id="<?php echo esc_attr( $sub->term_id ); ?>">
                                <span><?php echo esc_html( mica_title_case( $sub->name ) ); ?></span>
                                <span class="filter-cat-count"><?php echo mica_cat_product_count( $sub->term_id ); ?></span>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <?php /* ── 5. Attributes — each collapsed ── */ ?>
    <?php foreach ( $attributes as $taxonomy => $attr_data ) :
        $is_local   = ! empty( $attr_data['terms'][0]['is_local'] );
        $term_count = count( $attr_data['terms'] );
    ?>
    <div class="filter-section" data-filter-type="attribute"
         data-taxonomy="<?php echo esc_attr( $taxonomy ); ?>">
        <button class="filter-section-toggle collapsed" aria-expanded="false">
            <span class="filter-section-title"><?php echo esc_html( mica_title_case( $attr_data['label'] ) ); ?></span>
            <?php echo mica_icon( 'chevron' ); ?>
        </button>
        <div class="filter-section-body" hidden>
            <?php if ( $term_count > 5 ) : ?>
            <input type="text" class="filter-search-input"
                   placeholder="<?php echo esc_attr( sprintf( __( 'Search %s…', 'micaonline' ), strtolower( $attr_data['label'] ) ) ); ?>"
                   autocomplete="off">
            <?php endif; ?>
            <div class="filter-checkbox-list">
                <?php foreach ( $attr_data['terms'] as $i => $term ) :
                    if ( $is_local ) {
                        $active     = in_array( $term['slug'], (array) ( $af['local_attributes'][ $taxonomy ] ?? [] ), true );
                        $input_name = 'filter_local_attr[' . esc_attr( $taxonomy ) . '][]';
                    } else {
                        $active     = in_array( $term['slug'], (array) ( $af['attributes'][ $taxonomy ] ?? [] ), true );
                        $input_name = 'filter_attr[' . esc_attr( $taxonomy ) . '][]';
                    }
                ?>
                <label class="filter-check-label<?php echo ( $i >= 5 && ! $active ) ? ' filter-item-hidden' : ''; ?>">
                    <input type="checkbox"
                           name="<?php echo $input_name; ?>"
                           value="<?php echo esc_attr( $term['slug'] ); ?>"
                           <?php checked( $active ); ?>
                           data-taxonomy="<?php echo esc_attr( $taxonomy ); ?>"
                           data-term="<?php echo esc_attr( $term['slug'] ); ?>"
                           <?php if ( $is_local ) echo 'data-local="1"'; ?>>
                    <span class="filter-check-name"><?php echo esc_html( mica_title_case( $term['name'] ) ); ?></span>
                    <!-- <span class="filter-check-count"><?php echo (int) $term['count']; ?></span> -->
                </label>
                <?php endforeach; ?>
                <?php if ( $term_count > 5 ) : ?>
                <button type="button" class="filter-show-more"
                        data-show-text="<?php echo esc_attr( sprintf( __( 'Show %d more', 'micaonline' ), $term_count - 5 ) ); ?>"
                        data-hide-text="<?php esc_attr_e( 'Show less', 'micaonline' ); ?>">
                    <?php echo esc_html( sprintf( __( 'Show %d more', 'micaonline' ), $term_count - 5 ) ); ?>
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

</div><!-- .filter-panel -->
</aside>
