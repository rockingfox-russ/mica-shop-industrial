<?php
/**
 * Thankyou page — micaonline Industrial theme override
 *
 * @see https://woocommerce.com/document/template-structure/
 * @version 8.1.0
 * @var WC_Order $order
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="woocommerce-order thankyou-b">

<?php if ( $order ) :

    do_action( 'woocommerce_before_thankyou', $order->get_id() );

    if ( $order->has_status( 'failed' ) ) : ?>

        <!-- ── Order failed ── -->
        <div class="thankyou-failed-b">
            <div class="thankyou-failed-icon">✕</div>
            <h1 class="thankyou-failed-title">Payment not completed</h1>
            <p class="thankyou-failed-sub">Your bank or payment provider declined this transaction. Please try again or use a different payment method.</p>
            <div class="thankyou-failed-actions">
                <a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="thankyou-btn-primary">
                    Try again →
                </a>
                <?php if ( is_user_logged_in() ) : ?>
                <a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="thankyou-btn-ghost">
                    My account
                </a>
                <?php endif; ?>
            </div>
        </div>

    <?php else : ?>

        <!-- ── Success banner ── -->
        <div class="thankyou-hero-b">
            <div class="thankyou-hero-inner">
                <div class="thankyou-check">✓</div>
                <div class="thankyou-eyebrow">Order confirmed</div>
                <h1 class="thankyou-headline">
                    Order received.<br>
                    <em class="serif-italic">We're on it.</em>
                </h1>
                <p class="thankyou-sub">
                    A confirmation email is on its way to <strong><?php echo esc_html( $order->get_billing_email() ); ?></strong>.<br>
                    Your order will be dispatched within 1–2 business days and delivered in 5–7 working days.
                </p>
            </div>
        </div>

        <!-- ── Order meta strip ── -->
        <div class="thankyou-meta-strip">
            <div class="thankyou-meta-item">
                <span class="thankyou-meta-label">Order number</span>
                <strong class="thankyou-meta-value">#<?php echo esc_html( $order->get_order_number() ); ?></strong>
            </div>
            <div class="thankyou-meta-item">
                <span class="thankyou-meta-label">Date</span>
                <strong class="thankyou-meta-value"><?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></strong>
            </div>
            <div class="thankyou-meta-item">
                <span class="thankyou-meta-label">Order total</span>
                <strong class="thankyou-meta-value thankyou-total"><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></strong>
            </div>
            <?php if ( $order->get_payment_method_title() ) : ?>
            <div class="thankyou-meta-item">
                <span class="thankyou-meta-label">Payment</span>
                <strong class="thankyou-meta-value"><?php echo wp_kses_post( $order->get_payment_method_title() ); ?></strong>
            </div>
            <?php endif; ?>
        </div>

        <!-- ── Order details grid ── -->
        <div class="thankyou-grid">

            <!-- Items ordered -->
            <div class="thankyou-items-col">
                <div class="thankyou-section-head">
                    <span class="section-eyebrow">Items ordered</span>
                </div>
                <div class="thankyou-items-list">
                    <?php foreach ( $order->get_items() as $item ) :
                        /** @var WC_Order_Item_Product $item */
                        $product    = $item->get_product();
                        $thumb_id   = $product ? $product->get_image_id() : 0;
                        $thumb_url  = $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'thumbnail' ) : wc_placeholder_img_src( 'thumbnail' );
                    ?>
                    <div class="thankyou-item">
                        <div class="thankyou-item-img">
                            <img src="<?php echo esc_url( $thumb_url ); ?>"
                                 alt="<?php echo esc_attr( $item->get_name() ); ?>">
                        </div>
                        <div class="thankyou-item-info">
                            <span class="thankyou-item-name"><?php echo esc_html( $item->get_name() ); ?></span>
                            <?php if ( $product && $product->get_sku() ) : ?>
                            <span class="thankyou-item-sku"><?php echo esc_html( $product->get_sku() ); ?></span>
                            <?php endif; ?>
                            <span class="thankyou-item-qty">Qty: <?php echo esc_html( $item->get_quantity() ); ?></span>
                        </div>
                        <div class="thankyou-item-price">
                            <?php echo wp_kses_post( $order->get_formatted_line_subtotal( $item ) ); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Totals -->
                <div class="thankyou-totals">
                    <div class="thankyou-total-row">
                        <span>Subtotal</span>
                        <span><?php echo wp_kses_post( wc_price( $order->get_subtotal() ) ); ?></span>
                    </div>
                    <?php if ( $order->get_total_shipping() > 0 ) : ?>
                    <div class="thankyou-total-row">
                        <span>Shipping</span>
                        <span><?php echo wp_kses_post( wc_price( $order->get_shipping_total() ) ); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ( $order->get_discount_total() > 0 ) : ?>
                    <div class="thankyou-total-row thankyou-discount">
                        <span>Discount</span>
                        <span>−<?php echo wp_kses_post( wc_price( $order->get_discount_total() ) ); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="thankyou-total-row thankyou-grand-total">
                        <span>Total</span>
                        <span><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></span>
                    </div>
                </div>
            </div>

            <!-- Delivery + billing -->
            <div class="thankyou-sidebar-col">

                <!-- Delivery -->
                <div class="thankyou-card">
                    <div class="thankyou-section-head">
                        <span class="section-eyebrow">Delivery address</span>
                    </div>
                    <address class="thankyou-address">
                        <?php echo wp_kses_post( $order->get_formatted_shipping_address() ?: $order->get_formatted_billing_address() ); ?>
                    </address>
                    <div class="thankyou-delivery-note">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                        Nationwide delivery 5–7 working days
                    </div>
                </div>

                <!-- Billing -->
                <div class="thankyou-card" style="margin-top:var(--space-4);">
                    <div class="thankyou-section-head">
                        <span class="section-eyebrow">Billing details</span>
                    </div>
                    <address class="thankyou-address">
                        <?php echo wp_kses_post( $order->get_formatted_billing_address() ); ?>
                    </address>
                    <?php if ( $order->get_billing_phone() ) : ?>
                    <div class="thankyou-contact-line">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.3 1.8.6 2.6a2 2 0 0 1-.5 2.1L7.9 9.7a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.5c.8.3 1.7.5 2.6.6a2 2 0 0 1 1.7 2z"/></svg>
                        <?php echo esc_html( $order->get_billing_phone() ); ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Actions -->
                <div class="thankyou-actions">
                    <?php if ( is_user_logged_in() ) : ?>
                    <a href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>" class="thankyou-btn-primary" style="display:block;text-align:center;margin-bottom:var(--space-3);">
                        View my orders →
                    </a>
                    <?php endif; ?>
                    <a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>" class="thankyou-btn-ghost" style="display:block;text-align:center;">
                        Continue shopping
                    </a>
                </div>

            </div>
        </div><!-- .thankyou-grid -->

    <?php endif; ?>

    <?php do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() ); ?>
    <?php do_action( 'woocommerce_thankyou', $order->get_id() ); ?>

<?php else : ?>

    <div class="thankyou-hero-b" style="text-align:center;">
        <div class="thankyou-eyebrow">Thank you for shopping</div>
        <h1 class="thankyou-headline">Your order<br><em class="serif-italic">has been received.</em></h1>
        <a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>" class="thankyou-btn-primary" style="display:inline-flex;margin-top:var(--space-6);">
            Continue shopping →
        </a>
    </div>

<?php endif; ?>

</div>
