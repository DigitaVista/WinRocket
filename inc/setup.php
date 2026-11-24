<?php
/**
 * Theme setup and assets.
 *
 * @package winrocket
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme supports.
 */
function winrocket_setup() {
	load_theme_textdomain( 'winrocket', WINROCKET_DIR . '/languages' );

	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	add_editor_style( 'assets/css/theme.css' );

	add_image_size( 'winrocket-card', 640, 480, true );
	add_image_size( 'winrocket-hero', 1920, 900, true );

	register_nav_menus(
		array(
			'primary' => __( 'Primary menu', 'winrocket' ),
			'footer'  => __( 'Footer menu', 'winrocket' ),
		)
	);
}
add_action( 'after_setup_theme', 'winrocket_setup' );

/**
 * Front-end assets.
 */
function winrocket_assets() {
	wp_enqueue_style( 'winrocket', WINROCKET_URI . '/assets/css/theme.css', array(), WINROCKET_VERSION );
	wp_enqueue_script( 'winrocket', WINROCKET_URI . '/assets/js/theme.js', array(), WINROCKET_VERSION, true );
}
add_action( 'wp_enqueue_scripts', 'winrocket_assets', 20 );

/**
 * Keep WooCommerce's own stylesheets but let ours load after them.
 */
add_filter(
	'woocommerce_enqueue_styles',
	function ( $styles ) {
		// The small-screen sheet fights the card grid; our CSS covers small screens.
		unset( $styles['woocommerce-smallscreen'] );
		return $styles;
	}
);

/**
 * Body classes.
 *
 * @param array $classes Classes.
 * @return array
 */
function winrocket_body_class( $classes ) {
	$classes[] = 'winrocket';
	if ( winrocket_setting( 'announcement_enable' ) ) {
		$classes[] = 'wr-has-announcement';
	}
	return $classes;
}
add_filter( 'body_class', 'winrocket_body_class' );

/**
 * Block patterns category.
 */
function winrocket_pattern_category() {
	register_block_pattern_category( 'winrocket', array( 'label' => __( 'WinRocket', 'winrocket' ) ) );
}
add_action( 'init', 'winrocket_pattern_category' );
