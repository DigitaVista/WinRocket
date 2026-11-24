<?php
/**
 * Competition data helpers (read from Giveaway Lottery / WinRocket product data).
 *
 * @package winrocket
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Is WooCommerce active?
 *
 * @return bool
 */
function winrocket_has_wc() {
	return class_exists( 'WooCommerce' ) && function_exists( 'wc_get_product' );
}

/**
 * Is the product a competition (giveaway)?
 *
 * @param int $product_id Product ID.
 * @return bool
 */
function winrocket_is_competition( $product_id ) {
	return 'yes' === get_post_meta( $product_id, '_enable_giveaway', true );
}

/**
 * Parse a plugin date (stored as "Y-m-d H:i" in site time) to a timestamp in site time.
 *
 * @param string $value Date string.
 * @return int 0 when empty.
 */
function winrocket_parse_date( $value ) {
	$value = trim( (string) $value );
	if ( '' === $value ) {
		return 0;
	}
	try {
		$date = new DateTime( $value, wp_timezone() );
		return $date->getTimestamp();
	} catch ( Exception $e ) {
		return 0;
	}
}

/**
 * Total tickets from the "1 - 4200" ticket range.
 *
 * @param int $product_id Product ID.
 * @return int
 */
function winrocket_total_tickets( $product_id ) {
	if ( function_exists( 'wxgiveaway_get_total_number_of_tickets' ) ) {
		return max( 0, (int) wxgiveaway_get_total_number_of_tickets( $product_id ) );
	}
	$range = explode( '-', (string) get_post_meta( $product_id, '_ticket_range', true ) );
	if ( count( $range ) < 2 ) {
		return 0;
	}
	return max( 0, (int) trim( $range[1] ) - (int) trim( $range[0] ) + 1 );
}

/**
 * Sold tickets (rows in the plugin's ticket table with an order).
 *
 * @param int $product_id Product ID.
 * @return int
 */
function winrocket_sold_tickets( $product_id ) {
	global $wpdb;
	static $cache = array();
	if ( isset( $cache[ $product_id ] ) ) {
		return $cache[ $product_id ];
	}
	$table = $wpdb->prefix . 'wxgiveaway';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
	if ( $exists !== $table ) {
		$cache[ $product_id ] = 0;
		return 0;
	}
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$count                = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM {$table} WHERE giveaways_id = %d AND order_id != 0", $product_id ) );
	$cache[ $product_id ] = $count;
	return $count;
}

/**
 * Instant prizes saved on the product (Pro).
 *
 * @param int $product_id Product ID.
 * @return array
 */
function winrocket_instant_prizes( $product_id ) {
	$prizes = get_post_meta( $product_id, '_wxgiveaway_instant_prizes', true );
	return is_array( $prizes ) ? array_values( array_filter( $prizes, 'is_array' ) ) : array();
}

/**
 * Number of instant win tickets on the product.
 *
 * @param int $product_id Product ID.
 * @return int
 */
function winrocket_instant_ticket_count( $product_id ) {
	$count = 0;
	foreach ( winrocket_instant_prizes( $product_id ) as $prize ) {
		$tickets = array_filter( array_map( 'trim', explode( ',', (string) ( $prize['ticket_no'] ?? '' ) ) ) );
		$count  += count( $tickets );
	}
	return $count;
}

/**
 * All the competition data the theme shows.
 *
 * @param WC_Product|int $product Product.
 * @return array
 */
function winrocket_competition( $product ) {
	$product = is_numeric( $product ) ? wc_get_product( $product ) : $product;
	if ( ! $product ) {
		return array();
	}

	$id     = $product->get_id();
	$is     = winrocket_is_competition( $id );
	$now    = current_time( 'timestamp' );
	$start  = $is ? winrocket_parse_date( get_post_meta( $id, '_start_date', true ) ) : 0;
	$close  = $is ? winrocket_parse_date( get_post_meta( $id, '_close_date', true ) ) : 0;
	$draw   = $is ? winrocket_parse_date( get_post_meta( $id, '_draw_date', true ) ) : 0;
	$total  = $is ? winrocket_total_tickets( $id ) : 0;
	$sold   = $is ? min( $total ? $total : PHP_INT_MAX, winrocket_sold_tickets( $id ) ) : 0;
	$left   = max( 0, $total - $sold );

	// current_time( 'timestamp' ) is site-local; compare with site-local timestamps.
	$offset = (int) ( wp_timezone()->getOffset( new DateTime( 'now', wp_timezone() ) ) );
	$start  = $start ? $start + $offset : 0;
	$close  = $close ? $close + $offset : 0;
	$draw   = $draw ? $draw + $offset : 0;

	if ( ! $is ) {
		$status = 'product';
	} elseif ( $close && $now > $close ) {
		$status = 'closed';
	} elseif ( $start && $now < $start ) {
		$status = 'future';
	} elseif ( $total && 0 === $left ) {
		$status = 'soldout';
	} else {
		$status = 'live';
	}

	$winners = $is ? max( 0, (int) get_post_meta( $id, '_total_winners', true ) ) : 0;
	$instant = $is ? winrocket_instant_ticket_count( $id ) : 0;

	$terms    = get_the_terms( $id, 'product_cat' );
	$category = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0] : null;
	foreach ( (array) $terms as $term ) {
		if ( $term && ! is_wp_error( $term ) && 'uncategorized' !== $term->slug ) {
			$category = $term;
			break;
		}
	}

	return array(
		'id'          => $id,
		'is'          => $is,
		'status'      => $status,
		'start'       => $start,
		'close'       => $close,
		'draw'        => $draw,
		'total'       => $total,
		'sold'        => $sold,
		'left'        => $left,
		'pct'         => $total ? min( 100, round( $sold / $total * 100, 2 ) ) : 0,
		'winners'     => $winners,
		'instant'     => $instant,
		'has_instant' => $instant > 0,
		'prizes'      => $winners + $instant,
		'category'    => $category,
	);
}

/**
 * Format a site-local timestamp.
 *
 * @param int    $ts     Site-local timestamp (from winrocket_competition()).
 * @param string $format PHP date format.
 * @return string
 */
function winrocket_date( $ts, $format = 'j M, H:i' ) {
	return $ts ? date_i18n( $format, $ts ) : '';
}

/**
 * Shop / competitions URL.
 *
 * @return string
 */
function winrocket_shop_url() {
	if ( function_exists( 'wc_get_page_permalink' ) ) {
		return wc_get_page_permalink( 'shop' );
	}
	return home_url( '/' );
}

/**
 * Draw results page URL.
 *
 * @return string
 */
function winrocket_results_url() {
	$page = (int) winrocket_setting( 'results_page_id' );
	if ( $page && 'publish' === get_post_status( $page ) ) {
		return get_permalink( $page );
	}
	return winrocket_shop_url();
}

/**
 * Status badge label.
 *
 * @param string $status Status.
 * @return string
 */
function winrocket_status_label( $status ) {
	$labels = array(
		'live'    => __( 'Live', 'winrocket' ),
		'future'  => __( 'Coming soon', 'winrocket' ),
		'closed'  => __( 'Closed', 'winrocket' ),
		'soldout' => __( 'Sold out', 'winrocket' ),
	);
	return $labels[ $status ] ?? '';
}

/**
 * Competition card HTML.
 *
 * @param WC_Product|int $product Product.
 * @param array          $args    Options: heading tag.
 * @return string
 */
function winrocket_card( $product, $args = array() ) {
	$product = is_numeric( $product ) ? wc_get_product( $product ) : $product;
	if ( ! $product ) {
		return '';
	}
	$c   = winrocket_competition( $product );
	$s   = winrocket_settings();
	$url = get_permalink( $product->get_id() );
	$tag = isset( $args['tag'] ) ? tag_escape( $args['tag'] ) : 'h3';

	$image = $product->get_image( 'winrocket-card', array( 'class' => 'wr-card__img', 'loading' => 'lazy' ) );

	$html  = '<article class="wr-card wr-card--' . esc_attr( $c['status'] ) . '">';
	$html .= '<a class="wr-card__media" href="' . esc_url( $url ) . '" tabindex="-1" aria-hidden="true">' . $image;

	if ( ! empty( $s['card_show_badges'] ) && $c['is'] ) {
		$label = winrocket_status_label( $c['status'] );
		if ( $label ) {
			$html .= '<span class="wr-badge wr-badge--status wr-badge--' . esc_attr( $c['status'] ) . '"><span class="wr-dot" aria-hidden="true"></span>' . esc_html( $label ) . '</span>';
		}
		$html .= '<span class="wr-badge wr-badge--type">' . esc_html( $c['has_instant'] ? __( 'Instant wins', 'winrocket' ) : __( 'Competition', 'winrocket' ) ) . '</span>';
	}
	$html .= '</a>';

	$html .= '<div class="wr-card__body">';
	if ( ! empty( $s['card_show_category'] ) && $c['category'] ) {
		$html .= '<p class="wr-eyebrow wr-card__cat">' . esc_html( $c['category']->name ) . '</p>';
	}
	$html .= '<' . $tag . ' class="wr-card__title"><a href="' . esc_url( $url ) . '">' . esc_html( $product->get_name() ) . '</a></' . $tag . '>';

	if ( ! empty( $s['card_show_date'] ) && $c['is'] ) {
		if ( 'future' === $c['status'] && $c['start'] ) {
			/* translators: %s: date. */
			$html .= '<p class="wr-card__date">' . esc_html( sprintf( __( 'Opens %s', 'winrocket' ), winrocket_date( $c['start'] ) ) ) . '</p>';
		} elseif ( 'closed' === $c['status'] && $c['close'] ) {
			/* translators: %s: date. */
			$html .= '<p class="wr-card__date">' . esc_html( sprintf( __( 'Closed %s', 'winrocket' ), winrocket_date( $c['close'] ) ) ) . '</p>';
		} elseif ( $c['close'] ) {
			/* translators: %s: date. */
			$html .= '<p class="wr-card__date">' . esc_html( sprintf( __( 'Closes %s', 'winrocket' ), winrocket_date( $c['close'] ) ) ) . '</p>';
		}
	}

	if ( ! empty( $s['card_show_bar'] ) && $c['is'] && $c['total'] && in_array( $c['status'], array( 'live', 'soldout' ), true ) ) {
		$html .= winrocket_bar( $c );
	}

	$html .= '<div class="wr-card__foot">';
	$html .= '<span class="wr-card__price">' . wp_kses_post( $product->get_price_html() ) . '</span>';

	if ( 'future' === $c['status'] || 'product' === $c['status'] ) {
		$button = __( 'View details', 'winrocket' );
	} elseif ( 'closed' === $c['status'] || 'soldout' === $c['status'] ) {
		$button = __( 'View results', 'winrocket' );
	} else {
		$button = $s['card_button_text'] ? $s['card_button_text'] : __( 'Enter now', 'winrocket' );
	}
	$html .= '<a class="wr-btn wr-btn--sm" href="' . esc_url( $url ) . '">' . esc_html( $button ) . '<span class="screen-reader-text"> ' . esc_html( $product->get_name() ) . '</span></a>';
	$html .= '</div></div></article>';

	return apply_filters( 'winrocket_card_html', $html, $product, $c );
}

/**
 * "Tickets available" bar.
 *
 * @param array $c Competition data.
 * @return string
 */
function winrocket_bar( $c ) {
	$width = $c['pct'] > 0 && $c['pct'] < 2 ? 2 : $c['pct'];
	return '<div class="wr-tickets">'
		/* translators: %s: tickets left. */
		. '<p class="wr-tickets__label">' . esc_html( sprintf( __( 'Tickets available: %s', 'winrocket' ), number_format_i18n( $c['left'] ) ) ) . '</p>'
		. '<div class="wr-tickets__bar" role="progressbar" aria-valuemin="0" aria-valuemax="' . esc_attr( $c['total'] ) . '" aria-valuenow="' . esc_attr( $c['sold'] ) . '"><span style="width:' . esc_attr( $width ) . '%"></span></div>'
		. '<div class="wr-tickets__scale"><span>0</span><span>' . esc_html( number_format_i18n( $c['total'] ) ) . '</span></div>'
		. '</div>';
}

/**
 * Inline SVG icons.
 *
 * @param string $name Icon.
 * @return string
 */
function winrocket_icon( $name ) {
	$paths = array(
		'clock'  => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
		'ticket' => '<path d="M3 8a2 2 0 0 0 2-2h14a2 2 0 0 0 2 2v2a2 2 0 0 0 0 4v2a2 2 0 0 0-2 2H5a2 2 0 0 0-2-2v-2a2 2 0 0 0 0-4V8z"/><path d="M9 6v12" stroke-dasharray="2 2"/>',
		'users'  => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0"/><path d="M16 4.5a3.5 3.5 0 0 1 0 7M18 14a6 6 0 0 1 3.5 6"/>',
		'gift'   => '<path d="M20 12v9H4v-9M2 7h20v5H2zM12 22V7M12 7H7.5a2.5 2.5 0 1 1 0-5C11 2 12 7 12 7zM12 7h4.5a2.5 2.5 0 1 0 0-5C13 2 12 7 12 7z"/>',
		'arrow'  => '<path d="M5 12h14M13 6l6 6-6 6"/>',
		'trophy' => '<path d="M8 21h8M12 17v4M7 4h10v5a5 5 0 0 1-10 0V4zM7 6H4v1a3 3 0 0 0 3 3M17 6h3v1a3 3 0 0 1-3 3"/>',
		'close'  => '<path d="M6 6l12 12M18 6 6 18"/>',
		'bolt'   => '<path d="M13 2 4 14h7l-1 8 9-12h-7l1-8z"/>',
		'wheel'  => '<circle cx="12" cy="12" r="9"/><path d="M12 3v18M3 12h18M5.6 5.6l12.8 12.8M18.4 5.6 5.6 18.4"/>',
	);
	if ( ! isset( $paths[ $name ] ) ) {
		return '';
	}
	return '<svg class="wr-icon" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false"><g fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' . $paths[ $name ] . '</g></svg>';
}
