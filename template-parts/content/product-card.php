<?php
/**
 * template-parts/content/product-card.php
 *
 * @var WC_Product $product  Passed via mica_part()
 */

defined( 'ABSPATH' ) || exit;

/** @var WC_Product $product */
$product = $args['product'] ?? null;
if ( ! $product instanceof WC_Product ) return;

// Product basics
$pid        = $product->get_id();
$permalink  = get_permalink( $pid );
$thumb_id   = $product->get_image_id('medium');
$stock      = mica_stock_label( $product );
$price_html = $product->get_price_html();

// Paint data (single source of truth)
$paint_colour = get_post_meta( $pid, 'wpcf-paint-colour', true );
$paint_name   = get_post_meta( $pid, 'wpcf-paint-colour-name', true );
$paint_code   = get_post_meta( $pid, 'wpcf-paint-colour-code', true );
$is_paint     = ! empty( $paint_colour );

// Paint overlay styles (only calculated if needed)
$overlay_style = '';

if ( $is_paint ) {
    // Determine lightness from the actual hex value so any colour works correctly
    $hex      = ltrim( $paint_colour, '#' );
    $is_light = false;
    if ( ctype_xdigit( $hex ) && in_array( strlen( $hex ), [ 3, 6 ], true ) ) {
        if ( strlen( $hex ) === 3 ) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        $r = hexdec( substr( $hex, 0, 2 ) );
        $g = hexdec( substr( $hex, 2, 2 ) );
        $b = hexdec( substr( $hex, 4, 2 ) );
        $is_light = ( 0.299 * $r + 0.587 * $g + 0.114 * $b ) / 255 > 0.55;
    }

    if ( $is_light ) {
        $text_color = '#1a1a1a';
        $shadow     = '0 1px 2px rgba(0,0,0,.2)';
        $border     = '1px solid rgba(0,0,0,.15)';
        $box        = '0 2px 12px rgba(0,0,0,.1)';
    } else {
        $text_color = '#ffffff';
        $shadow     = '0 1px 4px rgba(0,0,0,.45)';
        $border     = '1px solid rgba(255,255,255,.25)';
        $box        = '0 2px 12px rgba(0,0,0,.2)';
    }

    $overlay_style = sprintf(
        'background:%s; color:%s; text-shadow:%s; border:%s; box-shadow:%s;',
        esc_attr( $paint_colour ),
        esc_attr( $text_color ),
        esc_attr( $shadow ),
        esc_attr( $border ),
        esc_attr( $box )
    );
}
?>
<article class="product-card" data-product-id="<?php echo esc_attr( $pid ); ?>" <?php echo $is_paint ? sprintf(
    'data-paint-colour="%s" data-paint-name="%s" data-paint-code="%s"',
    esc_attr( $paint_colour ),
    esc_attr( $paint_name ),
    esc_attr( $paint_code )
) : ''; ?>>

    <!-- Image -->
    <div class="product-card-img">
        <a href="<?php echo esc_url( $permalink ); ?>" tabindex="-1" aria-hidden="true">
            <?php if ( $thumb_id ) : ?>
                <?php echo wp_get_attachment_image( $thumb_id, 'mica-product-card', false, [
                    'alt'     => esc_attr( $product->get_name() ),
                    'loading' => 'lazy',
                ] ); ?>
            <?php else : ?>
                <?php echo wc_placeholder_img( 'mica-product-card' ); ?>
            <?php endif; ?>
            
        </a>

        <?php echo mica_product_badges( $product ); ?>

        <?php if ( $is_paint ) : ?>
            <div class="paint-card-swatch" style="background:<?php echo esc_attr( $paint_colour ); ?>;color:<?php echo $is_light ? '#1a1a1a' : 'rgba(255,255,255,.95)'; ?>;text-shadow:<?php echo $is_light ? '0 1px 2px rgba(0,0,0,.2)' : '0 1px 3px rgba(0,0,0,.4)'; ?>;border-top:<?php echo $is_light ? '1px solid rgba(0,0,0,.12)' : '1px solid rgba(255,255,255,.15)'; ?>;">
                <span class="paint-card-swatch-dot" style="background:<?php echo esc_attr( $paint_colour ); ?>;border-color:<?php echo $is_light ? 'rgba(0,0,0,.25)' : 'rgba(255,255,255,.7)'; ?>;"></span>
                <span><?php echo esc_html( $paint_code ?: $paint_name ?: $paint_colour ); ?></span>
            </div>
        <?php endif; ?>

        <?php if ( $is_paint ) : ?>
            <span class="paint-swatch-thumb" 
                  style="background:<?php echo esc_attr( $paint_colour ); ?>;" 
                  data-colour-name="<?php echo esc_attr( $paint_name ?: $paint_colour ); ?>"
                  aria-label="Colour: <?php echo esc_attr( $paint_name ?: $paint_colour ); ?>">
            </span>
        <?php endif; ?>

        <button class="product-card-wishlist" aria-label="<?php esc_attr_e( 'Add to wishlist', 'micaonline' ); ?>">
            <?php echo mica_icon( 'heart' ); ?>
        </button>
    </div>

    <!-- Body -->
    <div class="product-card-body">
        <?php
        $cats = get_the_terms( $pid, 'product_cat' );
        $cat  = $cats && ! is_wp_error( $cats ) ? array_shift( $cats ) : null;
        ?>
        <?php if ( $cat ) : ?>
            <span class="product-card-cat"><?php echo esc_html( $cat->name ); ?></span>
        <?php endif; ?>

        <h3 class="product-card-title">
            <a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
        </h3>

        <span class="product-card-stock <?php echo esc_attr( $stock['class'] ); ?>">
            <?php echo esc_html( $stock['label'] ); ?>
        </span>

        <!-- <?php if ( $is_paint ) : ?>
            <div class="paint-colour-badge">
                <span class="paint-colour-dot" style="background:<?php echo esc_attr( $paint_colour ); ?>;"></span>
                <span><?php echo esc_html( $paint_name ?: $paint_colour ); ?></span>
            </div>
        <?php endif; ?> -->
    </div>

    <!-- Footer: price + ATC -->
    <div class="product-card-footer">
        <div class="product-card-price">
            <?php if ( $product->is_on_sale() ) : ?>
                <span class="price-current"><?php echo wc_price( $product->get_sale_price() ); ?></span>
                <span class="price-original"><?php echo wc_price( $product->get_regular_price() ); ?></span>
            <?php else : ?>
                <span class="price-current"><?php echo $price_html; ?></span>
            <?php endif; ?>
        </div>

        <?php if ( $product->is_in_stock() ) : ?>
            <button class="btn-add-to-cart"
                    data-product-id="<?php echo esc_attr( $pid ); ?>"
                    data-nonce="<?php echo esc_attr( wp_create_nonce( 'wc-add-to-cart-' . $pid ) ); ?>"
                    aria-label="<?php echo esc_attr( sprintf( __( 'Add %s to cart', 'micaonline' ), $product->get_name() ) ); ?>">
                <?php echo mica_icon( 'cart' ); ?>
                <span><?php esc_html_e( 'Add', 'micaonline' ); ?></span>
            </button>
        <?php else : ?>
            <a href="<?php echo esc_url( $permalink ); ?>" class="btn btn-ghost btn-sm">
                <?php esc_html_e( 'View', 'micaonline' ); ?>
            </a>
        <?php endif; ?>
    </div>

</article>