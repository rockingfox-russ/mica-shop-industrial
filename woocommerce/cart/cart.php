<?php
/**
 * woocommerce/cart/cart.php
 * Override of WC cart template — uses theme classes.
 */
defined( 'ABSPATH' ) || exit;
do_action( 'woocommerce_before_cart' );
?>

<div class="container">
    <?php mica_breadcrumbs(); ?>
    <h1 class="section-title mb-8"><?php esc_html_e( 'Your Cart', 'micaonline' ); ?></h1>

    <?php wc_print_notices(); ?>

    <form class="woocommerce-cart-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
    <?php do_action( 'woocommerce_before_cart_table' ); ?>

    <div class="cart-layout">

        <!-- Cart items -->
        <div>
            <div class="cart-table">
                <div class="cart-table-header">
                    <span><?php esc_html_e( 'Product', 'micaonline' ); ?></span>
                    <span><?php esc_html_e( 'Price', 'micaonline' ); ?></span>
                    <span><?php esc_html_e( 'Quantity', 'micaonline' ); ?></span>
                    <span><?php esc_html_e( 'Total', 'micaonline' ); ?></span>
                    <span></span>
                </div>

                <?php do_action( 'woocommerce_before_cart_contents' ); ?>

                <?php foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) :
                    $_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
                    $product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );
                    if ( ! $_product || ! $_product->exists() || $cart_item['quantity'] === 0 ) continue;

                    $thumb_id = $_product->get_image_id();
                ?>
                <div class="cart-item <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">

                    <!-- Product info -->
                    <div class="cart-item-product">
                        <div class="cart-item-img">
                            <?php if ( $thumb_id ) : ?>
                                <?php echo wp_get_attachment_image( $thumb_id, 'thumbnail', false, [ 'alt' => esc_attr( $_product->get_name() ) ] ); ?>
                            <?php else : ?>
                                <?php echo wc_placeholder_img( 'thumbnail' ); ?>
                            <?php endif; ?>
                        </div>
                        <div>
                            <div class="cart-item-name">
                                <a href="<?php echo esc_url( get_permalink( $product_id ) ); ?>">
                                    <?php echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) ); ?>
                                </a>
                            </div>
                            <?php if ( $_product->get_sku() ) : ?>
                                <div class="cart-item-sku">SKU: <?php echo esc_html( $_product->get_sku() ); ?></div>
                            <?php endif; ?>
                            <?php echo wc_get_formatted_cart_item_data( $cart_item ); ?>
                        </div>
                    </div>

                    <!-- Price -->
                    <div class="cart-item-price">
                        <?php echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key ); ?>
                    </div>

                    <!-- Quantity -->
                    <div>
                        <?php
                        $min_qty = $_product->get_min_purchase_quantity();
                        $max_qty = $_product->get_max_purchase_quantity();
                        echo woocommerce_quantity_input( [
                            'input_name'   => "cart[{$cart_item_key}][qty]",
                            'input_value'  => $cart_item['quantity'],
                            'max_value'    => $max_qty,
                            'min_value'    => $min_qty,
                            'product_name' => $_product->get_name(),
                        ], $_product, false );
                        ?>
                    </div>

                    <!-- Line total -->
                    <div class="cart-item-total">
                        <?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); ?>
                    </div>

                    <!-- Remove -->
                    <div>
                        <?php
                        $remove_url = apply_filters( 'woocommerce_cart_item_remove_link',
                            sprintf( '<a href="%s" class="cart-item-remove" aria-label="%s" data-product_id="%s" data-cart_item_key="%s">%s</a>',
                                esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
                                esc_html__( 'Remove item', 'micaonline' ),
                                esc_attr( $product_id ),
                                esc_attr( $cart_item_key ),
                                mica_icon( 'x' )
                            ),
                            $cart_item_key
                        );
                        echo $remove_url;
                        ?>
                    </div>
                </div>
                <?php endforeach; ?>

                <?php do_action( 'woocommerce_cart_contents' ); ?>
            </div>

            <!-- Coupon + update -->
            <div style="display:flex;align-items:center;gap:1rem;margin-top:1rem;flex-wrap:wrap;">
                <?php if ( wc_coupons_enabled() ) : ?>
                <div class="coupon-form" style="flex:1;">
                    <input type="text" name="coupon_code" class="coupon-input"
                           id="coupon_code" value=""
                           placeholder="<?php esc_attr_e( 'Coupon code', 'micaonline' ); ?>">
                    <button type="submit" class="btn btn-ghost" name="apply_coupon" value="<?php esc_attr_e( 'Apply', 'micaonline' ); ?>">
                        <?php esc_html_e( 'Apply coupon', 'micaonline' ); ?>
                    </button>
                </div>
                <?php endif; ?>
                <button type="submit" class="btn btn-ghost" name="update_cart" value="<?php esc_attr_e( 'Update cart', 'micaonline' ); ?>">
                    <?php esc_html_e( 'Update cart', 'micaonline' ); ?>
                </button>
                <?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
                <?php do_action( 'woocommerce_cart_actions' ); ?>
            </div>
        </div>

        <!-- Cart totals -->
        <div class="cart-summary">
            <?php do_action( 'woocommerce_cart_collaterals' ); ?>
        </div>

    </div><!-- .cart-layout -->

    <?php do_action( 'woocommerce_after_cart_table' ); ?>
    </form>

    <?php do_action( 'woocommerce_after_cart' ); ?>
</div>
