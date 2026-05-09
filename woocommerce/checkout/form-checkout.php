<?php
/**
 * woocommerce/checkout/form-checkout.php
 * Clean checkout with delivery vs click & collect toggle.
 */
defined( 'ABSPATH' ) || exit;

if ( WC()->cart->is_empty() ) {
    wc_print_notice( sprintf(
        __( 'Your cart is empty. %sShop now &rarr;%s', 'micaonline' ),
        '<a href="' . esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ) . '">',
        '</a>'
    ), 'notice' );
    return;
}

do_action( 'woocommerce_before_checkout_form', $checkout );

$checkout = WC()->checkout();
?>

<div class="container">
    <?php mica_breadcrumbs(); ?>
    <h1 class="section-title mb-8"><?php esc_html_e( 'Checkout', 'micaonline' ); ?></h1>

    <?php wc_print_notices(); ?>

    <form name="checkout" method="post" class="checkout woocommerce-checkout"
          action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data">

    <div class="checkout-layout">

        <!-- Left: billing + shipping + payment -->
        <div>

            <!-- ── Delivery method ── -->
            <!-- <div class="checkout-section">
                <h2 class="checkout-section-title">
                    <span class="checkout-step-num">1</span>
                    <?php esc_html_e( 'How would you like to receive your order?', 'micaonline' ); ?>
                </h2>

                <div class="delivery-toggle" id="delivery-toggle">
                    <label class="delivery-option" id="opt-shipping">
                        <input type="radio" name="mica_delivery_method" value="shipping" checked>
                        <div>
                            <div class="delivery-option-label"><?php esc_html_e( 'Home Delivery', 'micaonline' ); ?></div>
                            <div class="delivery-option-sub"><?php esc_html_e( 'Delivered to your address', 'micaonline' ); ?></div>
                        </div>
                    </label>
                    <label class="delivery-option" id="opt-collect">
                        <input type="radio" name="mica_delivery_method" value="collect">
                        <div>
                            <div class="delivery-option-label">
                                <?php echo mica_icon( 'store' ); ?>
                                <?php esc_html_e( 'Click & Collect', 'micaonline' ); ?>
                            </div>
                            <div class="delivery-option-sub"><?php esc_html_e( 'Pick up at a Mica store — often same day', 'micaonline' ); ?></div>
                        </div>
                    </label>
                </div> -->

                <!-- Store picker (shown when click & collect selected) -->
                <!-- <div id="mica-click-collect-wrap" style="display:none;">
                    <div class="form-field">
                        <label class="form-label" for="mica_collection_store">
                            <?php esc_html_e( 'Select collection store', 'micaonline' ); ?>
                            <span class="required">*</span>
                        </label>
                        <select name="mica_collection_store" id="mica_collection_store" class="form-select">
                            <option value=""><?php esc_html_e( '— Choose a store —', 'micaonline' ); ?></option>
                            <?php foreach ( mica_get_stores() as $store ) : ?>
                            <option value="<?php echo esc_attr( $store['id'] ); ?>">
                                <?php echo esc_html( $store['name'] . ' — ' . $store['city'] ); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <div id="store-hours-display" style="margin-top:.5rem;font-size:.8rem;color:var(--clr-text-muted);display:none;"></div>
                    </div>
                </div>
            </div> -->

            <!-- ── Billing details ── -->
            <div class="checkout-section" id="billing-section">
                <h2 class="checkout-section-title">
                    <span class="checkout-step-num">1</span>
                    <?php esc_html_e( 'Your details', 'micaonline' ); ?>
                </h2>
                <?php do_action( 'woocommerce_checkout_billing' ); ?>
            </div>

            <!-- ── Shipping address — WC outputs "Ship to different address?" checkbox; its JS handles show/hide ── -->
            <div class="checkout-section" id="shipping-section">
                <?php do_action( 'woocommerce_checkout_shipping' ); ?>
            </div>

            <!-- ── Order notes ── -->
            <!-- <?php if ( apply_filters( 'woocommerce_enable_order_notes_field', 'yes' === get_option( 'woocommerce_enable_order_comments', 'yes' ) ) ) : ?>
            <div class="checkout-section">
                <div class="form-field">
                    <label class="form-label" for="order_comments">
                        <?php esc_html_e( 'Order notes (optional)', 'micaonline' ); ?>
                    </label>
                    <textarea name="order_comments" id="order_comments" class="form-textarea"
                              rows="3" placeholder="<?php esc_attr_e( 'Notes about your order, e.g. special delivery instructions.', 'micaonline' ); ?>"></textarea>
                </div>
            </div>
            <?php endif; ?> -->

            <!-- ── Payment ── -->
            <div class="checkout-section">
                <h2 class="checkout-section-title">
                    <span class="checkout-step-num">2</span>
                    <?php esc_html_e( 'Payment', 'micaonline' ); ?>
                </h2>
                <?php
                do_action( 'woocommerce_review_order_before_payment' );

                $available_gateways = WC()->cart->needs_payment()
                    ? WC()->payment_gateways()->get_available_payment_gateways()
                    : [];

                if ( $available_gateways ) {
                    WC()->payment_gateways()->set_current_gateway( $available_gateways );
                }

                wc_get_template(
                    'checkout/payment.php',
                    [
                        'checkout'           => $checkout,
                        'available_gateways' => $available_gateways,
                        'order_button_text'  => apply_filters( 'woocommerce_order_button_text', __( 'Place order', 'woocommerce' ) ),
                    ]
                );
                ?>
            </div>

        </div><!-- left column -->

        <!-- Right: order summary -->
        <div class="oder-summary-block">
            <div class="order-summary">
                <div class="order-summary-header">
                    <?php esc_html_e( 'Order Summary', 'micaonline' ); ?>
                </div>
                <div class="order-summary-items">
                    <?php foreach ( WC()->cart->get_cart() as $item ) :
                        $p = $item['data'];
                        if ( ! $p ) continue;
                    ?>
                    <div class="order-summary-item">
                        <div class="order-summary-item-img">
                            <?php echo $p->get_image( 'thumbnail' ); ?>
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:.8rem;font-weight:600;line-height:1.3;color:var(--clr-text);">
                                <?php echo esc_html( $p->get_name() ); ?>
                                <span style="color:var(--clr-text-muted);font-weight:400;"> &times; <?php echo $item['quantity']; ?></span>
                            </div>
                        </div>
                        <div style="font-size:.875rem;font-weight:700;color:var(--clr-orange);flex-shrink:0;">
                            <?php echo WC()->cart->get_product_subtotal( $p, $item['quantity'] ); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="order-summary-totals">
                    <?php do_action( 'woocommerce_review_order_before_cart_contents' ); ?>

                    <div class="summary-row">
                        <span><?php esc_html_e( 'Subtotal', 'micaonline' ); ?></span>
                        <span><?php echo WC()->cart->get_cart_subtotal(); ?></span>
                    </div>

                    <?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
                    <div class="summary-row" style="color:var(--clr-success);">
                        <span><?php esc_html_e( 'Coupon:', 'micaonline' ); ?> <?php echo esc_html( $code ); ?></span>
                        <span>-<?php wc_cart_totals_coupon_html( $coupon ); ?></span>
                    </div>
                    <?php endforeach; ?>

                    <?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
                    <div class="summary-row">
                        <span><?php echo esc_html( $fee->name ); ?></span>
                        <span><?php wc_cart_totals_fee_html( $fee ); ?></span>
                    </div>
                    <?php endforeach; ?>

                    <?php if ( WC()->cart->needs_shipping() ) : ?>
                    <div class="summary-row">
                        <span><?php esc_html_e( 'Shipping', 'micaonline' ); ?></span>
                        <span><?php wc_cart_totals_shipping_html(); ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) : ?>
                    <div class="summary-row">
                        <span><?php esc_html_e( 'Tax', 'micaonline' ); ?></span>
                        <span><?php wc_cart_totals_taxes_total_html(); ?></span>
                    </div>
                    <?php endif; ?>

                    <div class="summary-row total">
                        <span><?php esc_html_e( 'Total', 'micaonline' ); ?></span>
                        <span class="amount"><?php wc_cart_totals_order_total_html(); ?></span>
                    </div>
                </div>
            </div><!-- .order-summary -->
        </div><!-- right column -->

    </div><!-- .checkout-layout -->

    </form>

    <?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
</div>

<script>
( function () {
    const storeData = <?php echo wp_json_encode( mica_get_stores() ); ?>;

    const deliveryOpts = document.querySelectorAll( 'input[name="mica_delivery_method"]' );
    const collectWrap  = document.getElementById( 'mica-click-collect-wrap' );
    const shippingSection = document.getElementById( 'shipping-section' );
    const storeSelect  = document.getElementById( 'mica_collection_store' );
    const hoursDisplay = document.getElementById( 'store-hours-display' );

    function onDeliveryChange( val ) {
        const isCollect = val === 'collect';

        // Show/hide store picker
        if ( collectWrap ) collectWrap.style.display = isCollect ? '' : 'none';
        if ( storeSelect ) storeSelect.required = isCollect;

        // Toggle delivery options highlight
        document.getElementById( 'opt-shipping' )?.classList.toggle( 'selected', ! isCollect );
        document.getElementById( 'opt-collect'  )?.classList.toggle( 'selected',   isCollect );

        // Hide shipping address when collecting
        if ( shippingSection ) shippingSection.style.display = isCollect ? 'none' : '';
    }

    deliveryOpts.forEach( r => {
        r.addEventListener( 'change', () => onDeliveryChange( r.value ) );
    } );

    // Show store hours on store selection
    storeSelect?.addEventListener( 'change', function () {
        const store = storeData.find( s => s.id === this.value );
        if ( store && hoursDisplay ) {
            hoursDisplay.textContent = store.hours;
            hoursDisplay.style.display = '';
        } else if ( hoursDisplay ) {
            hoursDisplay.style.display = 'none';
        }
    } );

    // Init
    const checked = document.querySelector( 'input[name="mica_delivery_method"]:checked' );
    if ( checked ) onDeliveryChange( checked.value );
} )();
</script>
