<?php
/**
 * woocommerce/content-product.php
 * Called by WC loop — delegates to our reusable product card.
 */
defined( 'ABSPATH' ) || exit;
global $product;
if ( ! $product || ! $product instanceof WC_Product ) return;
mica_part( 'content/product-card', [ 'product' => $product ] );
