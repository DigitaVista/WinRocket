<?php
/**
 * Theme blocks (server rendered, editable in the Site Editor).
 *
 * @package winrocket
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Block definitions: name => attributes + render callback.
 *
 * @return array
 */
function winrocket_block_definitions() {
	$str  = function ( $default = '' ) {
		return array( 'type' => 'string', 'default' => $default );
	};
	$num  = function ( $default ) {
		return array( 'type' => 'number', 'default' => $default );
	};
	$bool = function ( $default ) {
		return array( 'type' => 'boolean', 'default' => $default );
	};

	return array(
		'announcement'     => array(
			'title'      => __( 'Announcement bar', 'winrocket' ),
			'attributes' => array(),
		),
		'header-button'    => array(
			'title'      => __( 'Header button', 'winrocket' ),
			'attributes' => array(),
		),
		'hero'             => array(
			'title'      => __( 'Featured competition hero', 'winrocket' ),
			'attributes' => array(
				'eyebrow'    => $str( __( 'Featured competition', 'winrocket' ) ),
				'productIds' => $str( '' ),
				'slides'     => $num( 3 ),
				'text'       => $str( __( 'Pick your tickets, check the instant prizes and follow the draw from start to finish.', 'winrocket' ) ),
				'button'     => $str( __( 'Enter competition', 'winrocket' ) ),
				'button2'    => $str( __( 'Browse all draws', 'winrocket' ) ),
			),
		),
		'stats'            => array(
			'title'      => __( 'Competition stats strip', 'winrocket' ),
			'attributes' => array(),
		),
		'section-nav'      => array(
			'title'      => __( 'Section links', 'winrocket' ),
			'attributes' => array(
				'links' => $str( "Featured|#featured\nEnding soon|#ending-soon\nInstant wins|#instant-wins\nCars & bikes|#cars\nCash|#cash\nTech & luxury|#tech\nExperiences|#holidays" ),
			),
		),
		'competition-grid' => array(
			'title'      => __( 'Competition cards', 'winrocket' ),
			'attributes' => array(
				'anchorId' => $str( '' ),
				'eyebrow'  => $str( __( 'Explore', 'winrocket' ) ),
				'title'    => $str( __( 'Featured competitions', 'winrocket' ) ),
				'subtitle' => $str( '' ),
				'source'   => $str( 'featured' ),
				'category' => $str( '' ),
				'count'    => $num( 4 ),
				'columns'  => $num( 4 ),
				'showLink' => $bool( true ),
				'linkText' => $str( __( 'View all competitions', 'winrocket' ) ),
			),
		),
		'results-cta'      => array(
			'title'      => __( 'Draw results banner', 'winrocket' ),
			'attributes' => array(
				'eyebrow' => $str( __( 'Draw history', 'winrocket' ) ),
				'title'   => $str( __( 'See the results', 'winrocket' ) ),
				'text'    => $str( __( 'Check finished competitions and their winning tickets.', 'winrocket' ) ),
				'button'  => $str( __( 'View draw results', 'winrocket' ) ),
			),
		),
		'draw-results'     => array(
			'title'      => __( 'Draw results', 'winrocket' ),
			'attributes' => array(
				'perPage' => $num( 10 ),
			),
		),
	);
}

/**
 * Register blocks and the editor script.
 */
function winrocket_register_blocks() {
	wp_register_script(
		'winrocket-blocks',
		WINROCKET_URI . '/assets/js/blocks-editor.js',
		array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-server-side-render', 'wp-i18n' ),
		WINROCKET_VERSION,
		true
	);

	$defs = winrocket_block_definitions();
	wp_add_inline_script(
		'winrocket-blocks',
		'window.winrocketBlocks = ' . wp_json_encode(
			array(
				'blocks'     => array_map(
					function ( $def ) {
						return array(
							'title'      => $def['title'],
							'attributes' => $def['attributes'],
						);
					},
					$defs
				),
				'categories' => winrocket_has_wc() ? winrocket_category_options() : array(),
			)
		) . ';',
		'before'
	);

	foreach ( $defs as $slug => $def ) {
		register_block_type(
			'winrocket/' . $slug,
			array(
				'api_version'     => 3,
				'title'           => $def['title'],
				'category'        => 'theme',
				'attributes'      => $def['attributes'],
				'editor_script'   => 'winrocket-blocks',
				'supports'        => array(
					'html'  => false,
					'align' => array( 'wide', 'full' ),
				),
				'render_callback' => function ( $attributes ) use ( $slug ) {
					if ( ! winrocket_has_wc() && ! in_array( $slug, array( 'announcement', 'header-button', 'results-cta' ), true ) ) {
						return '';
					}
					$fn = 'winrocket_render_' . str_replace( '-', '_', $slug );
					return function_exists( $fn ) ? call_user_func( $fn, (array) $attributes ) : '';
				},
			)
		);
	}
}
add_action( 'init', 'winrocket_register_blocks' );

/**
 * Product categories for the editor dropdown.
 *
 * @return array
 */
function winrocket_category_options() {
	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
			'number'     => 200,
		)
	);
	$out = array();
	if ( ! is_wp_error( $terms ) ) {
		foreach ( $terms as $term ) {
			$out[] = array(
				'label' => $term->name,
				'value' => $term->slug,
			);
		}
	}
	return $out;
}

/**
 * Is this a block editor preview request (ServerSideRender)?
 *
 * @return bool
 */
function winrocket_is_editor_preview() {
	return defined( 'REST_REQUEST' ) && REST_REQUEST;
}

/**
 * Product IDs for a card source.
 *
 * @param string $source   featured|ending|instant|latest|category|related.
 * @param int    $count    How many.
 * @param string $category Category slug.
 * @param int    $exclude  Product to leave out.
 * @return int[]
 */
function winrocket_query_ids( $source, $count, $category = '', $exclude = 0 ) {
	$count = max( 1, min( 24, (int) $count ) );
	$now   = current_time( 'Y-m-d H:i' );

	$args = array(
		'post_type'           => 'product',
		'post_status'         => 'publish',
		'posts_per_page'      => $count,
		'fields'              => 'ids',
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
		'post__not_in'        => $exclude ? array( $exclude ) : array(),
		'meta_query'          => array( 'relation' => 'AND' ),
		'tax_query'           => array(),
	);

	$giveaway = array(
		'key'   => '_enable_giveaway',
		'value' => 'yes',
	);
	$open     = array(
		'relation' => 'OR',
		array(
			'key'     => '_close_date',
			'value'   => $now,
			'compare' => '>=',
		),
		array(
			'key'     => '_close_date',
			'compare' => 'NOT EXISTS',
		),
	);

	// Hidden products stay hidden.
	$args['tax_query'][] = array(
		'taxonomy' => 'product_visibility',
		'field'    => 'name',
		'terms'    => array( 'exclude-from-catalog' ),
		'operator' => 'NOT IN',
	);

	switch ( $source ) {
		case 'featured':
			$args['meta_query'][] = $giveaway;
			$args['meta_query'][] = $open;
			$args['tax_query'][]  = array(
				'taxonomy' => 'product_visibility',
				'field'    => 'name',
				'terms'    => array( 'featured' ),
			);
			$ids = get_posts( $args );
			if ( count( $ids ) < $count ) {
				// Fill up with the newest open competitions.
				$fill = winrocket_query_ids( 'latest', $count, '', $exclude );
				$ids  = array_slice( array_values( array_unique( array_merge( $ids, $fill ) ) ), 0, $count );
			}
			return array_map( 'intval', $ids );

		case 'ending':
			$args['meta_query'][] = $giveaway;
			$args['meta_query'][] = array(
				'key'     => '_close_date',
				'value'   => $now,
				'compare' => '>=',
			);
			$args['meta_query'][] = array(
				'relation' => 'OR',
				array(
					'key'     => '_start_date',
					'value'   => $now,
					'compare' => '<=',
				),
				array(
					'key'     => '_start_date',
					'compare' => 'NOT EXISTS',
				),
			);
			$args['meta_key'] = '_close_date'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			$args['orderby']  = 'meta_value';
			$args['order']    = 'ASC';
			break;

		case 'instant':
			$args['meta_query'][] = $giveaway;
			$args['meta_query'][] = $open;
			$args['meta_query'][] = array(
				'key'     => '_wxgiveaway_instant_prizes',
				'value'   => array( '', 'a:0:{}' ),
				'compare' => 'NOT IN',
			);
			break;

		case 'category':
			if ( '' === $category ) {
				return array();
			}
			$args['meta_query'][] = $open;
			$args['tax_query'][]  = array(
				'taxonomy' => 'product_cat',
				'field'    => 'slug',
				'terms'    => array_map( 'sanitize_title', array_map( 'trim', explode( ',', $category ) ) ),
			);
			break;

		case 'related':
			if ( ! $exclude || ! function_exists( 'wc_get_related_products' ) ) {
				return array();
			}
			return array_map( 'intval', wc_get_related_products( $exclude, $count ) );

		case 'latest':
		default:
			$args['meta_query'][] = $giveaway;
			$args['meta_query'][] = $open;
			break;
	}

	return array_map( 'intval', get_posts( $args ) );
}

/* -------------------------------------------------------------------------
 * Render callbacks
 * ---------------------------------------------------------------------- */

/**
 * Announcement bar.
 *
 * @return string
 */
function winrocket_render_announcement() {
	$s = winrocket_settings();
	if ( empty( $s['announcement_enable'] ) || '' === trim( $s['announcement_text'] ) ) {
		return '';
	}
	$url  = $s['announcement_link_url'] ? $s['announcement_link_url'] : ( function_exists( 'winrocket_results_url' ) ? winrocket_results_url() : '' );
	$link = ( $s['announcement_link_text'] && $url ) ? ' <a href="' . esc_url( $url ) . '">' . esc_html( $s['announcement_link_text'] ) . ' &rarr;</a>' : '';

	return '<div class="wr-announcement" data-wr-announcement>'
		. '<p>' . esc_html( $s['announcement_text'] ) . $link . '</p>'
		. '<button type="button" class="wr-announcement__close" aria-label="' . esc_attr__( 'Close announcement', 'winrocket' ) . '">' . winrocket_icon( 'close' ) . '</button>'
		. '</div>';
}

/**
 * Header button.
 *
 * @return string
 */
function winrocket_render_header_button() {
	$s = winrocket_settings();
	if ( '' === trim( (string) $s['header_cta_text'] ) ) {
		return '';
	}
	$url = $s['header_cta_url'] ? $s['header_cta_url'] : winrocket_shop_url();
	return '<a class="wr-btn wr-header-btn" href="' . esc_url( $url ) . '">' . esc_html( $s['header_cta_text'] ) . '</a>';
}

/**
 * Hero.
 *
 * @param array $a Attributes.
 * @return string
 */
function winrocket_render_hero( $a ) {
	$ids = array_filter( array_map( 'absint', explode( ',', (string) $a['productIds'] ) ) );
	if ( empty( $ids ) ) {
		$ids = winrocket_query_ids( 'featured', max( 1, min( 5, (int) $a['slides'] ) ) );
	}
	if ( empty( $ids ) ) {
		return winrocket_is_editor_preview() ? '<p class="wr-empty">' . esc_html__( 'Add a competition to show it here.', 'winrocket' ) . '</p>' : '';
	}

	$slides = '';
	foreach ( array_values( $ids ) as $i => $id ) {
		$product = wc_get_product( $id );
		if ( ! $product ) {
			continue;
		}
		$image_id = $product->get_image_id();
		$img      = $image_id ? wp_get_attachment_image( $image_id, 'winrocket-hero', false, array( 'class' => 'wr-hero__img', 'loading' => 0 === $i ? 'eager' : 'lazy', 'fetchpriority' => 0 === $i ? 'high' : 'auto', 'alt' => '' ) ) : '';
		$excerpt  = wp_strip_all_tags( $product->get_short_description() );
		$text     = $a['text'] ? $a['text'] : wp_trim_words( $excerpt, 22 );

		$slides .= '<div class="wr-hero__slide' . ( 0 === $i ? ' is-active' : '' ) . '" data-slide="' . esc_attr( $i ) . '"' . ( 0 === $i ? '' : ' aria-hidden="true"' ) . '>'
			. $img
			. '<div class="wr-hero__inner">'
			. ( $a['eyebrow'] ? '<p class="wr-eyebrow wr-eyebrow--light">' . esc_html( $a['eyebrow'] ) . '</p>' : '' )
			. '<h2 class="wr-hero__title">' . esc_html( $product->get_name() ) . '</h2>'
			. ( $text ? '<p class="wr-hero__text">' . esc_html( $text ) . '</p>' : '' )
			. '<div class="wr-hero__actions">'
			. '<a class="wr-btn" href="' . esc_url( get_permalink( $id ) ) . '">' . esc_html( $a['button'] ) . ' ' . winrocket_icon( 'arrow' ) . '</a>'
			. ( $a['button2'] ? '<a class="wr-btn wr-btn--ghost" href="' . esc_url( winrocket_shop_url() ) . '">' . esc_html( $a['button2'] ) . '</a>' : '' )
			. '</div></div></div>';
	}

	$dots = '';
	if ( count( $ids ) > 1 ) {
		$dots = '<div class="wr-hero__dots" role="tablist">';
		foreach ( array_values( $ids ) as $i => $id ) {
			/* translators: %d: slide number. */
			$dots .= '<button type="button" class="wr-hero__dot' . ( 0 === $i ? ' is-active' : '' ) . '" data-go="' . esc_attr( $i ) . '" aria-label="' . esc_attr( sprintf( __( 'Show slide %d', 'winrocket' ), $i + 1 ) ) . '"></button>';
		}
		$dots .= '</div>';
	}

	return '<section class="wr-hero alignfull" data-wr-hero>' . $slides . $dots . '</section>';
}

/**
 * Stats strip.
 *
 * @return string
 */
function winrocket_render_stats() {
	$stats = get_transient( 'winrocket_stats' );
	if ( false === $stats ) {
		global $wpdb;
		$live    = winrocket_query_ids( 'ending', 200 );
		$instant = 0;
		foreach ( $live as $id ) {
			if ( winrocket_instant_ticket_count( $id ) > 0 ) {
				++$instant;
			}
		}
		$entries = 0;
		$table   = $wpdb->prefix . 'wxgiveaway';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$entries = (int) $wpdb->get_var( "SELECT COUNT(id) FROM {$table} WHERE order_id != 0" );
		}
		$stats = array(
			'live'    => count( $live ),
			'entries' => $entries,
			'instant' => $instant,
		);
		set_transient( 'winrocket_stats', $stats, 10 * MINUTE_IN_SECONDS );
	}

	$items = array(
		array( $stats['live'], __( 'Competitions live now', 'winrocket' ) ),
		array( $stats['entries'], __( 'Entries already taken', 'winrocket' ) ),
		array( $stats['instant'], __( 'Competitions with instant wins', 'winrocket' ) ),
	);

	$html = '<section class="wr-stats alignfull"><div class="wr-stats__inner">';
	foreach ( $items as $item ) {
		$html .= '<div class="wr-stats__item"><strong>' . esc_html( number_format_i18n( $item[0] ) ) . '</strong><span>' . esc_html( $item[1] ) . '</span></div>';
	}
	return $html . '</div></section>';
}

/**
 * Section links.
 *
 * @param array $a Attributes.
 * @return string
 */
function winrocket_render_section_nav( $a ) {
	$lines = array_filter( array_map( 'trim', explode( "\n", (string) $a['links'] ) ) );
	if ( empty( $lines ) ) {
		return '';
	}
	$html = '<nav class="wr-section-nav alignfull" aria-label="' . esc_attr__( 'Competition sections', 'winrocket' ) . '"><div class="wr-section-nav__inner">';
	foreach ( $lines as $line ) {
		$parts = array_map( 'trim', explode( '|', $line, 2 ) );
		$label = $parts[0];
		$href  = $parts[1] ?? '#';
		$html .= '<a href="' . esc_url( $href ) . '">' . esc_html( $label ) . '</a>';
	}
	return $html . '</div></nav>';
}

/**
 * Competition cards row.
 *
 * @param array $a Attributes.
 * @return string
 */
function winrocket_render_competition_grid( $a ) {
	$ids = winrocket_query_ids( $a['source'], $a['count'], $a['category'] );
	if ( empty( $ids ) ) {
		if ( winrocket_is_editor_preview() ) {
			return '<p class="wr-empty">' . esc_html__( 'No competitions match this section yet.', 'winrocket' ) . '</p>';
		}
		return '';
	}

	$link = '';
	if ( ! empty( $a['showLink'] ) && $a['linkText'] ) {
		$url = winrocket_shop_url();
		if ( 'category' === $a['source'] && $a['category'] ) {
			$term = get_term_by( 'slug', strtok( $a['category'], ',' ), 'product_cat' );
			if ( $term ) {
				$url = get_term_link( $term );
			}
		}
		$link = '<a class="wr-section__link" href="' . esc_url( $url ) . '">' . esc_html( $a['linkText'] ) . ' ' . winrocket_icon( 'arrow' ) . '</a>';
	}

	$cols = max( 1, min( 6, (int) $a['columns'] ) );
	$html = '<section class="wr-section"' . ( $a['anchorId'] ? ' id="' . esc_attr( sanitize_title( $a['anchorId'] ) ) . '"' : '' ) . '>';
	$html .= '<header class="wr-section__head"><div>';
	if ( $a['eyebrow'] ) {
		$html .= '<p class="wr-eyebrow">' . esc_html( $a['eyebrow'] ) . '</p>';
	}
	if ( $a['title'] ) {
		$html .= '<h2 class="wr-section__title">' . esc_html( $a['title'] ) . '</h2>';
	}
	if ( $a['subtitle'] ) {
		$html .= '<p class="wr-section__sub">' . esc_html( $a['subtitle'] ) . '</p>';
	}
	$html .= '</div>' . $link . '</header>';
	$html .= '<div class="wr-grid" style="--wr-cols:' . esc_attr( $cols ) . '">';
	foreach ( $ids as $id ) {
		$html .= winrocket_card( $id );
	}
	$html .= '</div></section>';

	return $html;
}

/**
 * Draw results banner.
 *
 * @param array $a Attributes.
 * @return string
 */
function winrocket_render_results_cta( $a ) {
	return '<section class="wr-results-cta">'
		. '<div>'
		. ( $a['eyebrow'] ? '<p class="wr-eyebrow wr-eyebrow--light">' . esc_html( $a['eyebrow'] ) . '</p>' : '' )
		. '<h2>' . esc_html( $a['title'] ) . '</h2>'
		. ( $a['text'] ? '<p>' . esc_html( $a['text'] ) . '</p>' : '' )
		. '</div>'
		. '<a class="wr-btn" href="' . esc_url( function_exists( 'winrocket_results_url' ) ? winrocket_results_url() : home_url( '/' ) ) . '">' . esc_html( $a['button'] ) . ' ' . winrocket_icon( 'arrow' ) . '</a>'
		. '</section>';
}

/**
 * Draw results list.
 *
 * @param array $a Attributes.
 * @return string
 */
function winrocket_render_draw_results( $a ) {
	global $wpdb;
	$table = $wpdb->prefix . 'wxgiveaway_winner_list';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery
	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
		return '<p class="wr-empty">' . esc_html__( 'No draws have been made yet.', 'winrocket' ) . '</p>';
	}

	$per   = max( 1, min( 50, (int) $a['perPage'] ) );
	$page  = isset( $_GET['wr-results'] ) ? max( 1, absint( $_GET['wr-results'] ) ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$off   = ( $page - 1 ) * $per;

	// One row per drawn competition (main draw), newest first.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$total = (int) $wpdb->get_var( "SELECT COUNT(DISTINCT product_id) FROM {$table} WHERE wining_type = 'final' OR wining_type IS NULL" );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$products = $wpdb->get_col( $wpdb->prepare( "SELECT product_id FROM {$table} WHERE wining_type = 'final' OR wining_type IS NULL GROUP BY product_id ORDER BY MAX(created_at) DESC LIMIT %d OFFSET %d", $per, $off ) );

	if ( empty( $products ) ) {
		return '<p class="wr-empty">' . esc_html__( 'No draws have been made yet. Check back after the first competition closes.', 'winrocket' ) . '</p>';
	}

	$html = '<div class="wr-results">';
	foreach ( $products as $product_id ) {
		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			continue;
		}
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} WHERE product_id = %d AND (wining_type = 'final' OR wining_type IS NULL) ORDER BY created_at ASC", $product_id ), ARRAY_A );
		if ( empty( $rows ) ) {
			continue;
		}
		$last = end( $rows );
		$date = mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $last['created_at'] );

		$html .= '<article class="wr-result">';
		$html .= '<a class="wr-result__media" href="' . esc_url( get_permalink( $product_id ) ) . '">' . $product->get_image( 'winrocket-card' ) . '</a>';
		$html .= '<div class="wr-result__body">';
		/* translators: %s: draw date. */
		$html .= '<p class="wr-eyebrow">' . esc_html( sprintf( __( 'Drawn %s', 'winrocket' ), $date ) ) . '</p>';
		$html .= '<h3><a href="' . esc_url( get_permalink( $product_id ) ) . '">' . esc_html( $product->get_name() ) . '</a></h3>';
		$html .= '<ol class="wr-result__winners">';
		foreach ( $rows as $i => $row ) {
			$name  = $row['winner_name'];
			$order = ! empty( $row['order_id'] ) ? wc_get_order( $row['order_id'] ) : false;
			if ( $order && function_exists( 'wxgiveaway_format_winner_display_name' ) ) {
				$name = wxgiveaway_format_winner_display_name( $order );
			}
			$prize = $row['prize_label'] ? $row['prize_label'] : ( 0 === $i ? __( 'Main prize', 'winrocket' ) : sprintf( /* translators: %d: prize number. */ __( 'Prize %d', 'winrocket' ), $i + 1 ) );
			$html .= '<li><span class="wr-result__prize">' . winrocket_icon( 'trophy' ) . esc_html( $prize ) . '</span>'
				. '<span class="wr-result__name">' . esc_html( $name ? $name : __( 'Winner', 'winrocket' ) ) . '</span>'
				/* translators: %s: ticket number. */
				. '<span class="wr-result__ticket">' . esc_html( sprintf( __( 'Ticket %s', 'winrocket' ), $row['ticket_no'] ) ) . '</span></li>';
		}
		$html .= '</ol></div></article>';
	}
	$html .= '</div>';

	$pages = (int) ceil( $total / $per );
	if ( $pages > 1 ) {
		$html .= '<nav class="wr-pagination">' . paginate_links(
			array(
				'base'      => add_query_arg( 'wr-results', '%#%' ),
				'format'    => '',
				'current'   => $page,
				'total'     => $pages,
				'prev_text' => '&larr;',
				'next_text' => '&rarr;',
			)
		) . '</nav>';
	}

	return $html;
}
