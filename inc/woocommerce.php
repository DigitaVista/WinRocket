<?php
/**
 * WooCommerce: single competition layout, sections and loops.
 *
 * @package winrocket
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hook changes, once WooCommerce is loaded.
 */
function winrocket_wc_hooks() {
	if ( ! winrocket_has_wc() ) {
		return;
	}

	// The templates print the breadcrumbs block instead.
	remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
	remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

	// Info cards under the gallery.
	add_action( 'woocommerce_before_single_product_summary', 'winrocket_info_cards', 30 );

	// Sections instead of tabs.
	remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
	add_action( 'woocommerce_after_single_product_summary', 'winrocket_single_sections', 10 );

	// Related competitions as theme cards.
	remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
	add_action( 'woocommerce_after_single_product_summary', 'winrocket_related', 20 );

	if ( ! winrocket_setting( 'single_show_meta' ) ) {
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
	}

	// The templates print the page title block.
	add_filter( 'woocommerce_show_page_title', '__return_false' );

	// Our section titles replace WooCommerce's inner headings.
	add_filter( 'woocommerce_product_description_heading', '__return_empty_string' );
	add_filter( 'woocommerce_product_additional_information_heading', '__return_empty_string' );
}
add_action( 'wp', 'winrocket_wc_hooks', 20 );

/**
 * Clear cached stats when products or orders change.
 */
function winrocket_flush_stats() {
	delete_transient( 'winrocket_stats' );
}
add_action( 'save_post_product', 'winrocket_flush_stats' );
add_action( 'woocommerce_order_status_changed', 'winrocket_flush_stats' );

/**
 * Info cards: ends, tickets sold, winners, prizes.
 */
function winrocket_info_cards() {
	global $product;
	$s = winrocket_settings();
	if ( empty( $s['single_info_cards'] ) || ! $product || ! winrocket_is_competition( $product->get_id() ) ) {
		return;
	}
	$c     = winrocket_competition( $product );
	$cards = array();

	if ( ! empty( $s['single_card_ends'] ) && $c['close'] ) {
		$label = 'future' === $c['status'] ? __( 'Competition opens', 'winrocket' ) : ( 'closed' === $c['status'] ? __( 'Competition ended', 'winrocket' ) : __( 'Competition ends', 'winrocket' ) );
		$when  = 'future' === $c['status'] && $c['start'] ? $c['start'] : $c['close'];
		$cards[] = array(
			'icon'  => 'clock',
			'label' => $label,
			'value' => esc_html( winrocket_date( $when, get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) ) . ' <small>' . esc_html( winrocket_timezone_label() ) . '</small>',
		);
	}
	if ( ! empty( $s['single_card_sold'] ) && $c['total'] ) {
		$cards[] = array(
			'icon'  => 'ticket',
			'label' => __( 'Tickets sold', 'winrocket' ),
			'value' => esc_html( number_format_i18n( $c['sold'] ) ) . ' <small>/ ' . esc_html( number_format_i18n( $c['total'] ) ) . '</small>',
		);
	}
	if ( ! empty( $s['single_card_winners'] ) && $c['winners'] ) {
		$cards[] = array(
			'icon'  => 'users',
			'label' => __( 'Winners', 'winrocket' ),
			/* translators: %s: number of winners. */
			'value' => esc_html( sprintf( _n( '%s Winner', '%s Winners', $c['winners'], 'winrocket' ), number_format_i18n( $c['winners'] ) ) ),
		);
	}
	if ( ! empty( $s['single_card_prizes'] ) && $c['prizes'] ) {
		$cards[] = array(
			'icon'  => 'gift',
			'label' => __( 'Total prizes', 'winrocket' ),
			/* translators: %s: number of prizes. */
			'value' => esc_html( sprintf( _n( '%s Prize', '%s Prizes', $c['prizes'], 'winrocket' ), number_format_i18n( $c['prizes'] ) ) ),
		);
	}

	$cards = apply_filters( 'winrocket_info_cards', $cards, $product, $c );
	if ( empty( $cards ) ) {
		return;
	}

	echo '<div class="wr-info-cards">';
	foreach ( $cards as $card ) {
		echo '<div class="wr-info-card"><span class="wr-info-card__icon">' . winrocket_icon( $card['icon'] ) . '</span><div><p class="wr-info-card__label">' . esc_html( $card['label'] ) . '</p><p class="wr-info-card__value">' . wp_kses_post( $card['value'] ) . '</p></div></div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	echo '</div>';
}

/**
 * Short timezone label, e.g. "UTC+1" or "Europe/London".
 *
 * @return string
 */
function winrocket_timezone_label() {
	$tz = wp_timezone_string();
	if ( preg_match( '/^[+-]\d{2}:\d{2}$/', $tz ) ) {
		$hours   = (int) substr( $tz, 1, 2 );
		$minutes = substr( $tz, 4, 2 );
		return 'UTC' . $tz[0] . $hours . ( '00' !== $minutes ? ':' . $minutes : '' );
	}
	return $tz;
}

/**
 * Sections below the competition (stacked cards or tabs).
 */
function winrocket_single_sections() {
	global $product;
	if ( ! $product ) {
		return;
	}
	$s     = winrocket_settings();
	$known = winrocket_known_sections();
	$tabs  = apply_filters( 'woocommerce_product_tabs', array() );
	if ( empty( $tabs ) ) {
		return;
	}

	// Known sections first, in the theme's order, then the rest by priority.
	$ordered = array();
	foreach ( $known as $key => $title ) {
		if ( isset( $tabs[ $key ] ) ) {
			$ordered[ $key ] = $tabs[ $key ];
		}
	}
	foreach ( $tabs as $key => $tab ) {
		if ( ! isset( $ordered[ $key ] ) ) {
			$ordered[ $key ] = $tab;
		}
	}

	$panels = array();
	foreach ( $ordered as $key => $tab ) {
		if ( isset( $known[ $key ] ) ) {
			$row = $s['single_sections'][ $key ] ?? array( 'show' => 1, 'title' => '' );
			if ( empty( $row['show'] ) ) {
				continue;
			}
			$title = '' !== $row['title'] ? $row['title'] : $known[ $key ];
		} else {
			if ( empty( $s['single_other_sections'] ) ) {
				continue;
			}
			$title = wp_strip_all_tags( $tab['title'] ?? $key );
		}
		if ( empty( $tab['callback'] ) || ! is_callable( $tab['callback'] ) ) {
			continue;
		}

		ob_start();
		call_user_func( $tab['callback'], $key, $tab );
		$content = ob_get_clean();
		if ( '' === trim( wp_strip_all_tags( $content, true ) ) && false === strpos( $content, '<img' ) && false === strpos( $content, '<table' ) && false === strpos( $content, '<form' ) ) {
			continue;
		}

		$panels[] = array(
			'key'     => sanitize_html_class( $key ),
			'title'   => $title,
			'content' => $content,
		);
	}

	if ( empty( $panels ) ) {
		return;
	}

	if ( 'tabs' === $s['single_layout'] && count( $panels ) > 1 ) {
		echo '<div class="wr-tabs" data-wr-tabs><div class="wr-tabs__list" role="tablist">';
		foreach ( $panels as $i => $panel ) {
			printf(
				'<button type="button" role="tab" id="wr-tab-%1$s" aria-controls="wr-panel-%1$s" aria-selected="%2$s" class="wr-tabs__tab%3$s">%4$s</button>',
				esc_attr( $panel['key'] ),
				0 === $i ? 'true' : 'false',
				0 === $i ? ' is-active' : '',
				esc_html( $panel['title'] )
			);
		}
		echo '</div>';
		foreach ( $panels as $i => $panel ) {
			printf(
				'<section role="tabpanel" id="wr-panel-%1$s" aria-labelledby="wr-tab-%1$s" class="wr-panel wr-panel--%1$s"%2$s>%3$s</section>',
				esc_attr( $panel['key'] ),
				0 === $i ? '' : ' hidden',
				$panel['content'] // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- plugin/WooCommerce output.
			);
		}
		echo '</div>';
		return;
	}

	echo '<div class="wr-sections">';
	foreach ( $panels as $panel ) {
		printf(
			'<section class="wr-panel wr-panel--%1$s" id="section-%1$s"><h2 class="wr-panel__title">%2$s</h2>%3$s</section>',
			esc_attr( $panel['key'] ),
			esc_html( $panel['title'] ),
			$panel['content'] // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- plugin/WooCommerce output.
		);
	}
	echo '</div>';
}

/**
 * Related competitions.
 */
function winrocket_related() {
	global $product;
	$s = winrocket_settings();
	if ( ! $product || empty( $s['single_related'] ) ) {
		return;
	}
	$ids = winrocket_query_ids( 'related', (int) $s['single_related_count'], '', $product->get_id() );
	if ( empty( $ids ) ) {
		return;
	}
	echo '<section class="wr-section wr-related">';
	if ( $s['single_related_title'] ) {
		echo '<header class="wr-section__head"><div><h2 class="wr-section__title">' . esc_html( $s['single_related_title'] ) . '</h2></div></header>';
	}
	echo '<div class="wr-grid" style="--wr-cols:4">';
	foreach ( $ids as $id ) {
		echo winrocket_card( $id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside.
	}
	echo '</div></section>';
}

/**
 * Extra sorting option: ending soonest.
 *
 * @param array $options Options.
 * @return array
 */
function winrocket_catalog_orderby( $options ) {
	$options['wr_ending'] = __( 'Ending soonest', 'winrocket' );
	return $options;
}
add_filter( 'woocommerce_catalog_orderby', 'winrocket_catalog_orderby' );

/**
 * Query args for "Ending soonest".
 *
 * @param array $args Args.
 * @return array
 */
function winrocket_ordering_args( $args ) {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$orderby = isset( $_GET['orderby'] ) ? wc_clean( wp_unslash( $_GET['orderby'] ) ) : '';
	if ( 'wr_ending' === $orderby ) {
		$args['orderby']  = 'meta_value';
		$args['order']    = 'ASC';
		$args['meta_key'] = '_close_date'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
	}
	return $args;
}
add_filter( 'woocommerce_get_catalog_ordering_args', 'winrocket_ordering_args' );

/**
 * Four cards per row in the shop.
 */
add_filter(
	'loop_shop_columns',
	function () {
		return 4;
	}
);
add_filter(
	'loop_shop_per_page',
	function () {
		return 16;
	}
);
