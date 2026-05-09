<?php
/**
 * single-product.php — Single Product Page
 */
defined( 'ABSPATH' ) || exit;
get_header();

while ( have_posts() ) :
    the_post();
    $product = wc_get_product( get_the_ID() );
    if ( ! $product ) { get_footer(); exit; }

    $product_id  = $product->get_id();
    $gallery_ids = $product->get_gallery_image_ids();
    $thumb_id    = $product->get_image_id();
    $all_images  = $thumb_id ? array_merge( [ $thumb_id ], $gallery_ids ) : $gallery_ids;
    $stock       = mica_stock_label( $product );
    $stores      = mica_get_stores();
    $barcode     = $product->get_sku();
    $sku         = get_post_meta( $product_id, 'wpcf-barcode', true );

    // Paint colour — custom fields
    $paint_colour = get_post_meta( $product_id, 'wpcf-paint-colour', true );   // hex e.g. #4A90D9
    $paint_name   = get_post_meta( $product_id, 'wpcf-paint-colour-name', true );
    $paint_code   = get_post_meta( $product_id, 'wpcf-paint-colour-code', true );
    $is_paint     = ! empty( $paint_colour );
?>


<div class="container">
    <?php mica_breadcrumbs(); ?>
<?php do_action( 'woocommerce_before_single_product' ); ?>

    <div class="single-product-layout">
        <!-- ═══ Gallery ═══ -->
        <div class="product-gallery">
            <div class="product-gallery-main">
                <?php if ( $thumb_id ) : ?>
                    <?php echo wp_get_attachment_image( $thumb_id, 'mico-product-single', false, [
                        'id'  => 'gallery-main-img',
                        'alt' => esc_attr( $product->get_name() ),
                    ] ); ?>
                <?php else : ?>
                    <?php echo wc_placeholder_img( 'mico-product-single' ); ?>
                <?php endif; ?>
<!-- 
                <?php if ( $is_paint ) : ?>
                <div class="paint-swatch-overlay"
                     style="background:<?php echo esc_attr( $paint_colour ); ?>;"
                     title="<?php echo esc_attr( $paint_name ?: $paint_colour ); ?>">
                    <span class="paint-swatch-label">
                        <?php echo esc_html( $paint_name ?: '' ); ?>
                        <?php if ( $paint_code ) echo '<small>' . esc_html( $paint_code ) . '</small>'; ?>
                    </span>
                </div>
                <?php endif; ?> -->

                <?php echo mica_product_badges( $product ); ?>
            </div>

            <?php if ( count( $all_images ) > 1 ) : ?>
            <div class="product-gallery-thumbs">
                <?php foreach ( $all_images as $i => $img_id ) :
                    $full   = wp_get_attachment_image_url( $img_id, 'mico-product-single' );
                    $srcset = wp_get_attachment_image_srcset( $img_id, 'mico-product-single' );
                ?>
                <div class="gallery-thumb <?php echo $i === 0 ? 'active' : ''; ?>"
                     data-full="<?php echo esc_url( $full ); ?>"
                     data-srcset="<?php echo esc_attr( $srcset ); ?>"
                     role="button" tabindex="0"
                     aria-label="Image <?php echo $i + 1; ?>">
                    <?php echo wp_get_attachment_image( $img_id, 'thumbnail', false, [ 'alt' => '' ] ); ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div><!-- .product-gallery -->

        <!-- ═══ Product Info ═══ -->
        <div class="product-info">

            <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;">
                <?php
                $brand = wp_get_post_terms( $product_id, 'pa_brand', [ 'fields' => 'names' ] );
                $brand = ( ! is_wp_error( $brand ) && ! empty( $brand ) ) ? $brand[0] : get_post_meta( $product_id, '_product_brand', true );
                ?>
                <?php if ( $brand ) : ?>
                    <span class="product-brand"><?php echo esc_html( $brand ); ?></span>
                <?php endif; ?>
                <span id="pdp-barcode-wrap" style="font-size:.75rem;color:var(--clr-text-muted);<?php echo $barcode ? '' : 'display:none;'; ?>">Barcode: <code id="pdp-barcode-val"><?php echo esc_html( $barcode ); ?></code></span>
                <span id="pdp-sku-wrap" style="font-size:.75rem;color:var(--clr-text-muted);<?php echo $sku ? '' : 'display:none;'; ?>">SKU: <code id="pdp-sku-val"><?php echo esc_html( $sku ); ?></code></span>
            </div>

            <h1 class="product-title"><?php the_title(); ?></h1>

            <?php if ( $product->get_review_count() > 0 ) : ?>
            <div class="product-rating">
                <?php echo wc_get_rating_html( $product->get_average_rating() ); ?>
                <span class="rating-count">(<?php printf( _n( '%d review', '%d reviews', $product->get_review_count(), 'micaonline' ), $product->get_review_count() ); ?>)</span>
            </div>
            <?php endif; ?>

            <!-- Price -->
            <div class="product-price-block">
                <?php if ( $product->is_on_sale() ) :
                    $pct = $product->get_regular_price() > 0
                        ? round( ( ( $product->get_regular_price() - $product->get_sale_price() ) / $product->get_regular_price() ) * 100 )
                        : 0;
                ?>
                    <span class="product-price-main"><?php echo wc_price( $product->get_sale_price() ); ?></span>
                    <span class="product-price-old"><?php echo wc_price( $product->get_regular_price() ); ?></span>
                    <span class="product-saving">Save <?php echo $pct; ?>%</span>
                <?php else : ?>
                    <span class="product-price-main"><?php echo $product->get_price_html(); ?></span>
                <?php endif; ?>
            </div>

            <!-- Paint colour chip -->
            <?php if ( $is_paint ) : ?>
            <div class="paint-colour-block">
                <span class="paint-colour-block-label"><?php esc_html_e( 'Colour', 'micaonline' ); ?></span>
                <div class="paint-colour-chip-wrap">
                    <span class="paint-colour-chip"
                          style="background:<?php echo esc_attr( $paint_colour ); ?>;"
                          aria-label="<?php echo esc_attr( $paint_name ?: $paint_colour ); ?>">
                    </span>
                    <div>
                        <span class="paint-colour-chip-name"><?php echo esc_html( $paint_name ?: $paint_colour ); ?></span>
                        <?php if ( $paint_code ) : ?>
                            <small class="paint-colour-chip-code"><?php echo esc_html( $paint_code ); ?></small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Short desc -->
            <?php if ( $product->get_short_description() ) : ?>
            <div style="font-size:.875rem;color:var(--clr-text-muted);line-height:1.65;">
                <?php echo wpautop( wp_kses_post( $product->get_short_description() ) ); ?>
            </div>
            <?php endif; ?>

            <!-- ATC -->
            <?php if ( $product->is_in_stock() ) : ?>
            <div class="product-add-form">
                <div class="quantity-wrap">
                    <span class="qty-label"><?php esc_html_e( 'Qty', 'micaonline' ); ?></span>
                    <div class="qty-control">
                        <button class="qty-btn" data-action="minus" aria-label="Decrease">−</button>
                        <?php $max_qty = esc_attr( $product->get_stock_quantity() ?: 9999 ); ?>
                        <input class="qty-input" type="number" value="1" min="1"
                               max="<?php echo $max_qty; ?>"
                               data-original-max="<?php echo $max_qty; ?>">
                        <button class="qty-btn" data-action="plus" aria-label="Increase">+</button>
                    </div>
                    <span class="product-card-stock <?php echo esc_attr( $stock['class'] ); ?>">
                        <?php echo mica_icon( 'check' ); ?> <?php echo esc_html( $stock['label'] ); ?>
                    </span>
                </div>
                <?php woocommerce_template_single_add_to_cart(); ?>
            </div>
            <?php else : ?>
                <div class="woocommerce-info" style="margin-top:0;">
                    <?php esc_html_e( 'Currently out of stock.', 'micaonline' ); ?>
                </div>
            <?php endif; ?>

            <!-- Hidden inputs used by stock checker + variation sync JS -->
            <input type="hidden" id="mico-product-id"      value="<?php echo esc_attr( $product_id ); ?>">
            <input type="hidden" id="mica-product-sku"     value="<?php echo esc_attr( $barcode ); ?>">
            <input type="hidden" id="mica-product-barcode" value="<?php echo esc_attr( $sku ); ?>">

            <!-- ═══ In-Store Stock Checker ═══ -->
            <?php if ( $barcode ) : ?>
            <div class="store-checker-box">
                <div class="store-checker-header">
                    <?php echo mica_icon( 'store' ); ?>
                    <div>
                        <strong><?php esc_html_e( 'In-Store Availability', 'micaonline' ); ?></strong>
                        <span class="store-checker-hint"><?php esc_html_e( 'Check stock at your nearest Mica store', 'micaonline' ); ?></span>
                    </div>
                    <button class="btn btn-secondary btn-sm" id="btn-check-stock"
                            data-product-id="<?php echo esc_attr( $product_id ); ?>"
                            data-sku="<?php echo esc_attr( $barcode ); ?>"
                            data-nonce="<?php echo esc_attr( wp_create_nonce( 'mica_stock_check' ) ); ?>">
                        <?php esc_html_e( 'Check Stock', 'micaonline' ); ?>
                    </button>
                </div>
            </div>
            <?php endif; ?>

            <!-- Meta list -->
            <ul class="product-meta-list">
                <li class="product-meta-item">
                    <?php echo mica_icon( 'store' ); ?>
                    <span><?php esc_html_e( 'Standard Delivery:', 'micaonline' ); ?>
                    <strong><?php esc_html_e( '5-7 Working Days', 'micaonline' ); ?></strong></span>
                </li>
                <li class="product-meta-item">
                    <?php echo mica_icon( 'truck' ); ?>
                    <span><?php esc_html_e( 'Nationwide delivery available', 'micaonline' ); ?></span>
                </li>
            </ul>

        </div><!-- .product-info -->
    </div><!-- .single-product-layout -->

    <!-- ═══ Tabs ═══ -->
    <div class="product-tabs">
        <div class="tabs-nav" role="tablist">
            <button class="tab-btn active" data-tab="description" role="tab">
                <?php esc_html_e( 'Description', 'micaonline' ); ?>
            </button>
            <?php if ( $product->get_attributes() ) : ?>
            <button class="tab-btn" data-tab="specs" role="tab">
                <?php esc_html_e( 'Specifications', 'micaonline' ); ?>
            </button>
            <?php endif; ?>
            <?php if ( comments_open() ) : ?>
            <button class="tab-btn" data-tab="reviews" role="tab">
                <?php printf( __( 'Reviews (%d)', 'micaonline' ), $product->get_review_count() ); ?>
            </button>
            <?php endif; ?>
            <?php if ( $is_paint ) : ?>
            <button class="tab-btn" data-tab="paint" role="tab">🎨 Paint Info</button>
            <?php endif; ?>
        </div>

        <div id="tab-description" class="tab-panel active" role="tabpanel">
            <?php echo wpautop( wp_kses_post( $product->get_description() ) ); ?>
        </div>

        <?php if ( $product->get_attributes() ) : ?>
        <div id="tab-specs" class="tab-panel" role="tabpanel">
            <table style="width:100%;border-collapse:collapse;font-size:.875rem;">
                <?php foreach ( $product->get_attributes() as $attribute ) :
                    if ( ! $attribute->get_visible() ) continue;
                    $values = $attribute->is_taxonomy()
                        ? wp_get_post_terms( $product_id, $attribute->get_name(), [ 'fields' => 'names' ] )
                        : $attribute->get_options();
                ?>
                <tr style="border-bottom:1px solid var(--clr-border);">
                    <td style="padding:.6rem .75rem;font-weight:600;width:40%;background:var(--clr-surface);">
                        <?php echo esc_html( wc_attribute_label( $attribute->get_name() ) ); ?>
                    </td>
                    <td style="padding:.6rem .75rem;">
                        <?php echo esc_html( is_array( $values ) ? implode( ', ', $values ) : $values ); ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <?php endif; ?>

        <?php if ( comments_open() ) : ?>
        <div id="tab-reviews" class="tab-panel" role="tabpanel">
            <?php comments_template(); ?>
        </div>
        <?php endif; ?>

        <?php if ( $is_paint ) : ?>
        <div id="tab-paint" class="tab-panel" role="tabpanel">
            <div style="display:flex;gap:2rem;align-items:flex-start;flex-wrap:wrap;">
                <div style="width:140px;height:140px;border-radius:var(--radius-xl);
                            background:<?php echo esc_attr( $paint_colour ); ?>;
                            border:1px solid var(--clr-border);box-shadow:var(--shadow-md);flex-shrink:0;">
                </div>
                <div>
                    <?php if ( $paint_name ) : ?><h3 style="font-size:1.25rem;margin-bottom:.25rem;"><?php echo esc_html( $paint_name ); ?></h3><?php endif; ?>
                    <?php if ( $paint_code ) : ?><p style="font-size:.875rem;color:var(--clr-text-muted);margin-bottom:.75rem;">Code: <strong><?php echo esc_html( $paint_code ); ?></strong></p><?php endif; ?>
                    <p style="font-size:.875rem;color:var(--clr-text-muted);">Hex: <code><?php echo esc_html( $paint_colour ); ?></code></p>
                    <p style="font-size:.75rem;color:var(--clr-text-muted);margin-top:1rem;max-width:360px;line-height:1.6;">
                        <?php esc_html_e( 'Screen colours are approximations. Request a physical swatch at your nearest Mica store for accurate colour matching.', 'micaonline' ); ?>
                    </p>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Related -->
    <?php
    $related_ids = wc_get_related_products( $product_id, 4 );
    $related     = array_filter( array_map( 'wc_get_product', $related_ids ) );
    if ( ! empty( $related ) ) :
    ?>
    <div style="margin-top:var(--space-10);padding-top:var(--space-8);border-top:1px solid var(--clr-border);">
        <div class="section-header mb-6">
            <h2 class="section-title"><?php esc_html_e( 'You might also like', 'micaonline' ); ?></h2>
        </div>
        <div class="products-grid">
            <?php foreach ( $related as $rp ) mica_part( 'content/product-card', [ 'product' => $rp ] ); ?>
        </div>
    </div>
    <?php endif; ?>

</div><!-- .container -->

<!-- ════════════════════════════════
     IN-STORE STOCK MODAL
════════════════════════════════ -->
<div id="stock-modal-overlay" class="stock-modal-overlay">
    <div class="stock-modal" role="dialog" aria-modal="true"
         aria-label="<?php esc_attr_e( 'In-store stock', 'micaonline' ); ?>">

        <div class="stock-modal-header">
            <div>
                <h2 class="stock-modal-title"><?php esc_html_e( 'In-Store Stock', 'micaonline' ); ?></h2>
                <p class="stock-modal-subtitle"><?php echo esc_html( $product->get_name() ); ?></p>
            </div>
            <button class="stock-modal-close" id="stock-modal-close" aria-label="Close">
                <?php echo mica_icon( 'x' ); ?>
            </button>
        </div>

        <?php if ( $barcode ) : ?>
        <div class="stock-modal-sku">
            <?php esc_html_e( 'Barcode / SKU:', 'micaonline' ); ?>
            <strong><?php echo esc_html( $barcode ); ?></strong>
        </div>
        <?php endif; ?>

        <div id="stock-modal-loading" class="stock-modal-loading">
            <div class="stock-spinner"></div>
            <span><?php esc_html_e( 'Checking live stock levels…', 'micaonline' ); ?></span>
        </div>

        <div id="stock-modal-results" style="display:none;">
            <div class="stock-results-legend">
                <span class="stock-dot stock-high"></span><?php esc_html_e( 'In stock', 'micaonline' ); ?>
                <span class="stock-dot stock-low"></span><?php esc_html_e( 'Low stock (≤5)', 'micaonline' ); ?>
                <span class="stock-dot stock-none"></span><?php esc_html_e( 'Out of stock', 'micaonline' ); ?>
            </div>
            <div id="stock-store-list" class="stock-store-list"></div>
        </div>

        <div id="stock-modal-error" class="stock-modal-error" style="display:none;">
            <?php echo mica_icon( 'x' ); ?>
            <span id="stock-error-msg"></span>
        </div>

        <!-- <div class="stock-modal-footer">
            <p class="stock-modal-note">
                <?php esc_html_e( 'Levels refresh every 15 min. Reserve online — collect same day before 2pm.', 'micaonline' ); ?>
            </p>
            <?php if ( $product->is_in_stock() ) : ?>
            <a href="<?php echo esc_url( add_query_arg( 'add-to-cart', $product_id, wc_get_checkout_url() ) ); ?>"
               class="btn btn-primary btn-full">
                <?php esc_html_e( 'Order for Click & Collect', 'micaonline' ); ?>
            </a>
            <?php endif; ?>
        </div> -->

    </div>
</div>

<input type="hidden" id="mica-product-id"  value="<?php echo esc_attr( $product_id ); ?>">
<input type="hidden" id="mica-product-barcode" value="<?php echo esc_attr( $barcode ); ?>">
<input type="hidden" id="mica-product-sku" value="<?php echo esc_attr( $sku ); ?>">

<?php endwhile; ?>
<?php get_footer(); ?>
