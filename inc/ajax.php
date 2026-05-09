<?php
/**
 * inc/ajax.php — Ajax handlers not covered by WC or filters.php
 */
defined( 'ABSPATH' ) || exit;

/**
 * Ajax: Add to cart (used by product cards in Ajax-filtered grid).
 * WC has its own handler but we supplement with fragment updates.
 */
add_action( 'wp_ajax_woocommerce_ajax_add_to_cart',        'mica_ajax_add_to_cart' );
add_action( 'wp_ajax_nopriv_woocommerce_ajax_add_to_cart', 'mica_ajax_add_to_cart' );

function mica_ajax_add_to_cart(): void {
    $product_id   = (int) ( $_POST['product_id'] ?? 0 );
    $quantity     = max( 1, (int) ( $_POST['quantity'] ?? 1 ) );
    $variation_id = (int) ( $_POST['variation_id'] ?? 0 );

    if ( ! $product_id ) {
        wp_send_json_error( [ 'message' => 'Invalid product' ] );
    }

    $added = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id );

    if ( ! $added ) {
        wp_send_json_error( [ 'message' => 'Could not add to cart' ] );
    }

    // Return cart fragments for updating mini-cart count
    WC_AJAX::get_refreshed_fragments();
}

/**
 * Ajax: Get product quick-view HTML.
 */
add_action( 'wp_ajax_mica_quick_view',        'mica_ajax_quick_view' );
add_action( 'wp_ajax_nopriv_mica_quick_view', 'mica_ajax_quick_view' );

function mica_ajax_quick_view(): void {
    check_ajax_referer( 'mica_filter_nonce', 'nonce' );

    $product_id = (int) ( $_POST['product_id'] ?? 0 );
    $product    = wc_get_product( $product_id );

    if ( ! $product ) {
        wp_send_json_error( 'Product not found' );
    }

    ob_start();
    // Basic quick view — product name, price, ATC button
    ?>
    <div style="display:flex;gap:1.5rem;padding:1.5rem;">
        <div style="width:220px;flex-shrink:0;">
            <?php echo $product->get_image( 'mica-product-single' ); ?>
        </div>
        <div style="flex:1;">
            <p style="font-size:.75rem;text-transform:uppercase;letter-spacing:.6px;color:var(--clr-text-muted);margin-bottom:.5rem;">
                <?php $cats = get_the_terms( $product_id, 'product_cat' );
                echo $cats ? esc_html( $cats[0]->name ) : ''; ?>
            </p>
            <h2 style="font-size:1.5rem;font-weight:800;margin-bottom:.75rem;">
                <?php echo esc_html( $product->get_name() ); ?>
            </h2>
            <div style="font-size:1.75rem;font-weight:800;color:var(--clr-orange);margin-bottom:1rem;">
                <?php echo $product->get_price_html(); ?>
            </div>
            <p style="font-size:.875rem;color:var(--clr-text-muted);margin-bottom:1.5rem;line-height:1.6;">
                <?php echo wp_trim_words( $product->get_description() ?: $product->get_short_description(), 30 ); ?>
            </p>
            <div style="display:flex;gap:.75rem;">
                <button class="btn-add-to-cart"
                    data-product-id="<?php echo esc_attr( $product_id ); ?>"
                    data-nonce="<?php echo esc_attr( wp_create_nonce( 'wc-add-to-cart-' . $product_id ) ); ?>"
                    style="flex:1;justify-content:center;">
                    Add to cart
                </button>
                <a href="<?php echo esc_url( get_permalink( $product_id ) ); ?>"
                   class="btn btn-secondary">
                    View Product
                </a>
            </div>
        </div>
    </div>
    <?php
    $html = ob_get_clean();
    wp_send_json_success( [ 'html' => $html ] );
}
