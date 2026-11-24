<?php
/**
 * Product loop item: competition card.
 *
 * Overrides woocommerce/templates/content-product.php.
 *
 * @package winrocket
 * @version 9.4.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}
?>
<li <?php wc_product_class( 'wr-loop-item', $product ); ?>>
	<?php echo winrocket_card( $product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside. ?>
</li>
