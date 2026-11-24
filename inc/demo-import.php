<?php
/**
 * One-click demo content: competitions, categories, pages, menu and
 * optional sample entries. Everything it creates is tagged so it can be
 * removed again.
 *
 * @package winrocket
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Demo tab on the settings page.
 */
function winrocket_demo_tab() {
	$imported = (int) get_option( 'winrocket_demo_imported', 0 );
	$has_wc   = winrocket_has_wc();
	$has_gl   = function_exists( 'wxgiveaway_is_giveaway' );
	$count    = count( winrocket_demo_products() );
	?>
	<div class="wr-demo" style="max-width:760px;margin-top:20px">
		<h2><?php esc_html_e( 'Demo competitions', 'winrocket' ); ?></h2>
		<p>
			<?php
			/* translators: %d: number of demo competitions. */
			echo esc_html( sprintf( __( 'Adds %d sample competitions in four categories (cars & bikes, cash, tech & luxury, holidays), a Draw results page and a menu, so the site looks like a working competition store.', 'winrocket' ), $count ) );
			?>
		</p>
		<p><?php esc_html_e( 'Photos are downloaded from Unsplash (free to use under the Unsplash License). Each photo keeps the photographer credit in its caption. The import needs your server to reach images.unsplash.com.', 'winrocket' ); ?></p>

		<?php if ( ! $has_wc ) : ?>
			<div class="notice notice-error inline"><p><?php esc_html_e( 'Please install and activate WooCommerce first.', 'winrocket' ); ?></p></div>
		<?php elseif ( ! $has_gl ) : ?>
			<div class="notice notice-warning inline"><p><?php esc_html_e( 'Giveaway Lottery is not active. Products will be created, but they only work as competitions with the plugin active.', 'winrocket' ); ?></p></div>
		<?php endif; ?>

		<?php if ( $imported ) : ?>
			<div class="notice notice-info inline"><p>
				<?php
				/* translators: %s: date. */
				echo esc_html( sprintf( __( 'Demo content was imported on %s. Importing again only adds what is missing.', 'winrocket' ), wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $imported ) ) );
				?>
			</p></div>
		<?php endif; ?>

		<p><label><input type="checkbox" id="wr-demo-entries" checked> <?php esc_html_e( 'Also add sample entries (demo orders from fictional customers) and two finished draws with winners', 'winrocket' ); ?></label></p>
		<p class="description"><?php esc_html_e( 'Sample orders are marked as demo, use example.com email addresses and no emails are sent while importing. They are deleted when you remove the demo content.', 'winrocket' ); ?></p>

		<p>
			<button type="button" class="button button-primary" id="wr-demo-import" <?php disabled( ! $has_wc ); ?>><?php esc_html_e( 'Import demo content', 'winrocket' ); ?></button>
			<button type="button" class="button" id="wr-demo-remove" <?php disabled( ! $has_wc ); ?>><?php esc_html_e( 'Remove demo content', 'winrocket' ); ?></button>
		</p>

		<div id="wr-demo-progress" style="display:none;margin-top:16px">
			<div style="height:8px;background:#e5e7eb;border-radius:99px;overflow:hidden;max-width:520px"><span id="wr-demo-bar" style="display:block;height:100%;width:0;background:#0a84c9;transition:width .3s"></span></div>
			<ol id="wr-demo-log" style="margin-top:12px;max-height:260px;overflow:auto;font-size:13px"></ol>
		</div>
	</div>
	<script>
	( function () {
		var nonce = <?php echo wp_json_encode( wp_create_nonce( 'winrocket_demo' ) ); ?>;
		var ajax = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;
		var t = <?php echo wp_json_encode( array( 'confirm' => __( 'Remove all demo competitions, demo orders, pages and the demo menu? This cannot be undone.', 'winrocket' ), 'done' => __( 'All done.', 'winrocket' ), 'error' => __( 'Something went wrong:', 'winrocket' ) ) ); ?>;
		var log = document.getElementById( 'wr-demo-log' );
		var bar = document.getElementById( 'wr-demo-bar' );
		var buttons = [ document.getElementById( 'wr-demo-import' ), document.getElementById( 'wr-demo-remove' ) ];

		function add( text ) {
			var li = document.createElement( 'li' );
			li.textContent = text;
			log.appendChild( li );
			log.scrollTop = log.scrollHeight;
		}
		function call( step, extra ) {
			var body = new URLSearchParams( Object.assign( { action: 'winrocket_demo', nonce: nonce, step: step }, extra || {} ) );
			return fetch( ajax, { method: 'POST', credentials: 'same-origin', body: body } ).then( function ( r ) { return r.json(); } ).then( function ( r ) {
				if ( ! r || ! r.success ) {
					throw new Error( r && r.data ? r.data : 'error' );
				}
				return r.data;
			} );
		}
		function lock( on ) {
			buttons.forEach( function ( b ) { b.disabled = on; } );
			document.getElementById( 'wr-demo-progress' ).style.display = 'block';
		}
		function run( steps, i ) {
			if ( i >= steps.length ) {
				bar.style.width = '100%';
				add( t.done );
				lock( false );
				return;
			}
			call( steps[ i ] ).then( function ( d ) {
				add( d.message );
				bar.style.width = Math.round( ( i + 1 ) / steps.length * 100 ) + '%';
				run( steps, i + 1 );
			} ).catch( function ( e ) {
				add( t.error + ' ' + e.message );
				bar.style.width = Math.round( ( i + 1 ) / steps.length * 100 ) + '%';
				run( steps, i + 1 );
			} );
		}

		buttons[0].addEventListener( 'click', function () {
			lock( true );
			log.innerHTML = '';
			bar.style.width = '0';
			var entries = document.getElementById( 'wr-demo-entries' ).checked ? '1' : '0';
			call( 'prepare', { entries: entries } ).then( function ( d ) {
				add( d.message );
				run( d.steps, 0 );
			} ).catch( function ( e ) {
				add( t.error + ' ' + e.message );
				lock( false );
			} );
		} );

		buttons[1].addEventListener( 'click', function () {
			if ( ! window.confirm( t.confirm ) ) {
				return;
			}
			lock( true );
			log.innerHTML = '';
			bar.style.width = '30%';
			call( 'remove' ).then( function ( d ) {
				bar.style.width = '100%';
				add( d.message );
				lock( false );
			} ).catch( function ( e ) {
				add( t.error + ' ' + e.message );
				lock( false );
			} );
		} );
	} )();
	</script>
	<?php
}
add_action( 'winrocket_settings_demo_tab', 'winrocket_demo_tab' );

/**
 * AJAX router.
 */
function winrocket_demo_ajax() {
	check_ajax_referer( 'winrocket_demo', 'nonce' );
	if ( ! current_user_can( 'manage_options' ) || ! winrocket_has_wc() ) {
		wp_send_json_error( __( 'You are not allowed to do this.', 'winrocket' ) );
	}

	if ( function_exists( 'set_time_limit' ) ) {
		@set_time_limit( 300 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
	}

	// Never send emails while creating demo content.
	add_filter( 'pre_wp_mail', '__return_false', 999 );

	$step = isset( $_POST['step'] ) ? sanitize_text_field( wp_unslash( $_POST['step'] ) ) : '';

	try {
		if ( 'prepare' === $step ) {
			$entries = ! empty( $_POST['entries'] );
			wp_send_json_success( winrocket_demo_prepare( $entries ) );
		}
		if ( 'remove' === $step ) {
			wp_send_json_success( array( 'message' => winrocket_demo_remove() ) );
		}
		if ( 'finish' === $step ) {
			wp_send_json_success( array( 'message' => winrocket_demo_finish() ) );
		}
		if ( 0 === strpos( $step, 'product:' ) ) {
			wp_send_json_success( array( 'message' => winrocket_demo_product( substr( $step, 8 ) ) ) );
		}
		if ( 0 === strpos( $step, 'entries:' ) ) {
			wp_send_json_success( array( 'message' => winrocket_demo_entries( substr( $step, 8 ) ) ) );
		}
	} catch ( Throwable $e ) {
		wp_send_json_error( $e->getMessage() );
	}

	wp_send_json_error( __( 'Unknown step.', 'winrocket' ) );
}
add_action( 'wp_ajax_winrocket_demo', 'winrocket_demo_ajax' );

/**
 * Site-time date string, N days from now at a fixed time.
 *
 * @param float  $days Days from now.
 * @param string $time H:i.
 * @return string Y-m-d H:i.
 */
function winrocket_demo_date( $days, $time = '20:00' ) {
	return wp_date( 'Y-m-d', time() + (int) round( $days * DAY_IN_SECONDS ) ) . ' ' . $time;
}

/**
 * Find a demo product by key.
 *
 * @param string $key Key.
 * @return int
 */
function winrocket_demo_product_id( $key ) {
	$ids = get_posts(
		array(
			'post_type'      => 'product',
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_winrocket_demo_key', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'     => $key, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
		)
	);
	return $ids ? (int) $ids[0] : 0;
}

/**
 * Step 1: categories, pages, menu. Returns the remaining steps.
 *
 * @param bool $entries Add sample entries.
 * @return array
 */
function winrocket_demo_prepare( $entries ) {
	// Categories.
	foreach ( winrocket_demo_categories() as $slug => $name ) {
		$term = get_term_by( 'slug', $slug, 'product_cat' );
		if ( ! $term ) {
			$new = wp_insert_term( $name, 'product_cat', array( 'slug' => $slug ) );
			if ( ! is_wp_error( $new ) ) {
				update_term_meta( $new['term_id'], '_winrocket_demo', 1 );
			}
		}
	}

	// Draw results page.
	$settings = get_option( 'winrocket_settings', array() );
	$settings = is_array( $settings ) ? $settings : array();
	$page_id  = (int) ( $settings['results_page_id'] ?? 0 );
	if ( ! $page_id || 'publish' !== get_post_status( $page_id ) ) {
		$page_id = wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => __( 'Draw results', 'winrocket' ),
				'post_name'    => 'draw-results',
				'post_content' => "<!-- wp:paragraph -->\n<p>" . esc_html__( 'Every finished draw with its winning tickets.', 'winrocket' ) . "</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:winrocket/draw-results /-->",
			)
		);
		if ( $page_id && ! is_wp_error( $page_id ) ) {
			update_post_meta( $page_id, '_winrocket_demo', 1 );
			$settings['results_page_id'] = (int) $page_id;
			update_option( 'winrocket_settings', $settings );
		}
	}

	// Shop page named "Competitions".
	$shop_id = wc_get_page_id( 'shop' );
	if ( $shop_id > 0 && ! get_option( 'winrocket_demo_shop_title' ) ) {
		update_option( 'winrocket_demo_shop_title', get_the_title( $shop_id ) );
		wp_update_post(
			array(
				'ID'         => $shop_id,
				'post_title' => __( 'Competitions', 'winrocket' ),
			)
		);
	}

	// Menu (block navigation, used by the header and footer).
	if ( ! get_posts( array( 'post_type' => 'wp_navigation', 'meta_key' => '_winrocket_demo', 'posts_per_page' => 1, 'fields' => 'ids', 'post_status' => 'publish' ) ) ) { // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		$links = array(
			array( __( 'Home', 'winrocket' ), home_url( '/' ) ),
			array( __( 'Competitions', 'winrocket' ), wc_get_page_permalink( 'shop' ) ),
			array( __( 'Draw results', 'winrocket' ), get_permalink( $page_id ) ),
			array( __( 'My tickets', 'winrocket' ), wc_get_account_endpoint_url( 'my-ticket' ) ),
			array( __( 'My account', 'winrocket' ), wc_get_page_permalink( 'myaccount' ) ),
		);
		$content = '';
		foreach ( $links as $link ) {
			$content .= '<!-- wp:navigation-link ' . wp_json_encode(
				array(
					'label' => $link[0],
					'url'   => $link[1],
					'kind'  => 'custom',
				)
			) . " /-->\n";
		}
		$nav = wp_insert_post(
			array(
				'post_type'    => 'wp_navigation',
				'post_status'  => 'publish',
				'post_title'   => __( 'WinRocket menu', 'winrocket' ),
				'post_content' => $content,
			)
		);
		if ( $nav && ! is_wp_error( $nav ) ) {
			update_post_meta( $nav, '_winrocket_demo', 1 );
		}
	}

	$steps = array();
	foreach ( array_keys( winrocket_demo_products() ) as $key ) {
		$steps[] = 'product:' . $key;
	}
	if ( $entries ) {
		foreach ( winrocket_demo_products() as $key => $p ) {
			if ( ! empty( $p['sold'] ) ) {
				$steps[] = 'entries:' . $key;
			}
		}
	}
	$steps[] = 'finish';

	return array(
		'message' => __( 'Categories, Draw results page and menu are ready.', 'winrocket' ),
		'steps'   => $steps,
	);
}

/**
 * Step: one competition.
 *
 * @param string $key Demo key.
 * @return string
 */
function winrocket_demo_product( $key ) {
	$all = winrocket_demo_products();
	if ( ! isset( $all[ $key ] ) ) {
		throw new Exception( 'Unknown demo product.' );
	}
	$p = $all[ $key ];

	$existing = winrocket_demo_product_id( $key );
	if ( $existing ) {
		/* translators: %s: product name. */
		return sprintf( __( 'Already there: %s', 'winrocket' ), $p['title'] );
	}

	$term = get_term_by( 'slug', $p['cat'], 'product_cat' );

	$product = new WC_Product_Simple();
	$product->set_name( $p['title'] );
	$product->set_status( 'publish' );
	$product->set_catalog_visibility( 'visible' );
	$product->set_virtual( true );
	$product->set_regular_price( wc_format_decimal( $p['price'] ) );
	if ( ! empty( $p['sale'] ) ) {
		$product->set_sale_price( wc_format_decimal( $p['sale'] ) );
	}
	$product->set_featured( ! empty( $p['featured'] ) );
	$product->set_short_description( $p['text'] );
	$product->set_description( winrocket_demo_description( $p ) );
	if ( $term ) {
		$product->set_category_ids( array( (int) $term->term_id ) );
	}
	$product->update_meta_data( '_winrocket_demo', 1 );
	$product->update_meta_data( '_winrocket_demo_key', $key );
	$id = $product->save();

	// Competition settings (Giveaway Lottery).
	$start = isset( $p['start'] ) ? winrocket_demo_date( $p['start'], '10:00' ) : winrocket_demo_date( -3, '10:00' );
	// Finished draws stay open until the sample entries are in; "finish" closes them.
	$close = ! empty( $p['closed'] ) ? winrocket_demo_date( 1 ) : winrocket_demo_date( $p['close'] );

	update_post_meta( $id, '_enable_giveaway', 'yes' );
	update_post_meta( $id, '_no_of_tickets', 1 );
	update_post_meta( $id, '_bonus_tickets', 0 );
	update_post_meta( $id, '_total_winners', isset( $p['winners'] ) ? (int) $p['winners'] : 1 );
	update_post_meta( $id, '_disable_multiple_quantity', 'no' );
	update_post_meta( $id, '_enable_auto_draw', 'no' );
	update_post_meta( $id, '_ticket_range', '1 - ' . (int) $p['tickets'] );
	update_post_meta( $id, '_start_date', $start );
	update_post_meta( $id, '_close_date', $close );
	update_post_meta( $id, '_draw_date', $close );

	// Instant prizes are a Giveaway Lottery Pro feature.
	if ( ! empty( $p['instant'] ) && class_exists( 'Wx_Giveway_Lottry_for_Woocommerce_Pro' ) ) {
		$prizes = array();
		foreach ( $p['instant'] as $row ) {
			$prizes[] = array(
				'image_id'      => '',
				'ticket_no'     => $row[1],
				'prize_label'   => $row[0],
				'prize_type'    => 'other',
				'probabilities' => array(),
				'prize_details' => array(
					/* translators: %s: prize. */
					'description' => sprintf( __( '%s, sent to the winner by email.', 'winrocket' ), $row[0] ),
				),
			);
		}
		update_post_meta( $id, '_wxgiveaway_instant_prizes', $prizes );
	}

	// Photo.
	$note = '';
	$att  = winrocket_demo_image( $p, $id );
	if ( is_wp_error( $att ) ) {
		$note = ' ' . __( '(photo could not be downloaded)', 'winrocket' );
	} else {
		set_post_thumbnail( $id, $att );
	}

	/* translators: %s: product name. */
	return sprintf( __( 'Added %s', 'winrocket' ), $p['title'] ) . $note;
}

/**
 * Long description for a demo product.
 *
 * @param array $p Demo product.
 * @return string
 */
function winrocket_demo_description( $p ) {
	$price = wp_strip_all_tags( wc_price( ! empty( $p['sale'] ) ? $p['sale'] : $p['price'] ) );
	return '<p>' . esc_html( $p['text'] ) . ' ' . esc_html__( 'This is a demo competition: choose your tickets, see your numbers in your account and follow the draw.', 'winrocket' ) . '</p>'
		. '<ul>'
		/* translators: %s: price. */
		. '<li>' . esc_html( sprintf( __( 'Entry price: %s', 'winrocket' ), $price ) ) . '</li>'
		/* translators: %s: number of tickets. */
		. '<li>' . esc_html( sprintf( __( 'Maximum tickets: %s', 'winrocket' ), number_format_i18n( $p['tickets'] ) ) ) . '</li>'
		. '<li>' . esc_html__( 'Live draw when the competition closes', 'winrocket' ) . '</li>'
		. '</ul>';
}

/**
 * Download a demo photo from Unsplash into the media library.
 *
 * @param array $p       Demo product.
 * @param int   $post_id Product ID.
 * @return int|WP_Error Attachment ID.
 */
function winrocket_demo_image( $p, $post_id ) {
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$url = 'https://images.unsplash.com/' . rawurlencode( $p['image'] ) . '?w=1600&h=1200&fit=crop&q=80&fm=jpg';
	$tmp = download_url( $url, 60 );
	if ( is_wp_error( $tmp ) ) {
		return $tmp;
	}

	$file = array(
		'name'     => sanitize_file_name( sanitize_title( $p['title'] ) . '.jpg' ),
		'tmp_name' => $tmp,
	);
	$att  = media_handle_sideload( $file, $post_id, $p['title'] );
	if ( is_wp_error( $att ) ) {
		wp_delete_file( $tmp );
		return $att;
	}

	wp_update_post(
		array(
			'ID'           => $att,
			/* translators: %s: photographer name. */
			'post_excerpt' => sprintf( __( 'Photo by %s on Unsplash', 'winrocket' ), $p['credit'] ),
		)
	);
	update_post_meta( $att, '_wp_attachment_image_alt', $p['title'] );
	update_post_meta( $att, '_winrocket_demo', 1 );

	return $att;
}

/**
 * Step: sample entries for one competition.
 *
 * @param string $key Demo key.
 * @return string
 */
function winrocket_demo_entries( $key ) {
	$all = winrocket_demo_products();
	$id  = winrocket_demo_product_id( $key );
	if ( ! isset( $all[ $key ] ) || ! $id ) {
		throw new Exception( 'Demo product not found.' );
	}
	$p       = $all[ $key ];
	$product = wc_get_product( $id );

	$done = (array) get_option( 'winrocket_demo_entries', array() );
	if ( in_array( $id, array_map( 'intval', $done ), true ) ) {
		/* translators: %s: product name. */
		return sprintf( __( 'Entries already added: %s', 'winrocket' ), $p['title'] );
	}

	$target    = (int) min( 400, round( $p['tickets'] * $p['sold'] / 100 ) );
	$customers = winrocket_demo_customers();
	$made      = 0;
	$orders    = 0;
	$i         = abs( crc32( $key ) ) % count( $customers );

	while ( $made < $target && $orders < 12 ) {
		$qty = min( $target - $made, wp_rand( 5, max( 6, (int) ceil( $target / 4 ) ) ) );
		$who = $customers[ $i % count( $customers ) ];

		$order = wc_create_order( array( 'created_via' => 'winrocket-demo' ) );
		$order->add_product( $product, $qty );
		$order->set_billing_first_name( $who[0] );
		$order->set_billing_last_name( $who[1] );
		$order->set_billing_email( strtolower( $who[0] . '.' . $who[1] ) . '@example.com' );
		$order->set_billing_country( WC()->countries->get_base_country() );
		$order->set_date_created( time() - wp_rand( HOUR_IN_SECONDS, 3 * DAY_IN_SECONDS ) );
		$order->update_meta_data( '_winrocket_demo', 1 );
		$order->update_meta_data( '_winrocket_demo_product', $id );
		$order->calculate_totals( false );
		$order->save();
		// Completing the order lets Giveaway Lottery generate the ticket numbers.
		$order->update_status( 'completed', __( 'Demo entry created by the WinRocket theme.', 'winrocket' ) );

		$made += $qty;
		++$orders;
		++$i;
	}

	$done[] = $id;
	update_option( 'winrocket_demo_entries', array_values( array_unique( array_map( 'intval', $done ) ) ), false );

	/* translators: 1: number of tickets, 2: product name. */
	return sprintf( __( 'Added %1$d sample tickets to %2$s', 'winrocket' ), $made, $p['title'] );
}

/**
 * Last step: close the finished demo draws and pick their winners.
 *
 * @return string
 */
function winrocket_demo_finish() {
	$drawn = 0;
	foreach ( winrocket_demo_products() as $key => $p ) {
		if ( empty( $p['closed'] ) ) {
			continue;
		}
		$id = winrocket_demo_product_id( $key );
		if ( ! $id ) {
			continue;
		}
		$close = winrocket_demo_date( $p['close'] );
		update_post_meta( $id, '_start_date', winrocket_demo_date( $p['close'] - 14, '10:00' ) );
		update_post_meta( $id, '_close_date', $close );
		update_post_meta( $id, '_draw_date', $close );

		if ( function_exists( 'wxgiveaway_winner_selection_cron' ) && function_exists( 'wxgiveaway_get_final_winner_count' ) && 0 === wxgiveaway_get_final_winner_count( $id ) ) {
			wxgiveaway_winner_selection_cron( $id );
			if ( wxgiveaway_get_final_winner_count( $id ) > 0 ) {
				++$drawn;
			}
		}
	}

	delete_transient( 'winrocket_stats' );
	update_option( 'winrocket_demo_imported', time() );

	if ( $drawn ) {
		/* translators: %d: number of draws. */
		return sprintf( _n( 'Demo ready. %d finished draw now has a winner.', 'Demo ready. %d finished draws now have winners.', $drawn, 'winrocket' ), $drawn );
	}
	return __( 'Demo ready.', 'winrocket' );
}

/**
 * Remove everything the demo created.
 *
 * @return string
 */
function winrocket_demo_remove() {
	global $wpdb;

	$orders = wc_get_orders(
		array(
			'limit'       => -1,
			'return'      => 'ids',
			'status'      => array_keys( wc_get_order_statuses() ),
			'created_via' => 'winrocket-demo',
		)
	);
	foreach ( $orders as $order_id ) {
		$order = wc_get_order( $order_id );
		if ( $order ) {
			$order->delete( true );
		}
	}

	$products = get_posts(
		array(
			'post_type'      => 'product',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_key'       => '_winrocket_demo', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		)
	);
	foreach ( $products as $product_id ) {
		$thumb = (int) get_post_thumbnail_id( $product_id );
		if ( $thumb && get_post_meta( $thumb, '_winrocket_demo', true ) ) {
			wp_delete_attachment( $thumb, true );
		}
		// Leftover tickets and winners of the demo competitions.
		foreach ( array( 'wxgiveaway' => 'giveaways_id', 'wxgiveaway_winner_list' => 'product_id' ) as $table => $column ) {
			$name = $wpdb->prefix . $table;
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $name ) ) === $name ) {
				$wpdb->delete( $name, array( $column => $product_id ), array( '%d' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			}
		}
		wp_delete_post( $product_id, true );
	}

	$posts = get_posts(
		array(
			'post_type'      => array( 'page', 'wp_navigation', 'attachment' ),
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_key'       => '_winrocket_demo', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		)
	);
	foreach ( $posts as $post_id ) {
		if ( 'attachment' === get_post_type( $post_id ) ) {
			wp_delete_attachment( $post_id, true );
		} else {
			wp_delete_post( $post_id, true );
		}
	}

	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
			'meta_key'   => '_winrocket_demo', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		)
	);
	if ( ! is_wp_error( $terms ) ) {
		foreach ( $terms as $term ) {
			if ( 0 === (int) $term->count ) {
				wp_delete_term( $term->term_id, 'product_cat' );
			}
		}
	}

	$shop_title = get_option( 'winrocket_demo_shop_title' );
	if ( $shop_title && wc_get_page_id( 'shop' ) > 0 ) {
		wp_update_post(
			array(
				'ID'         => wc_get_page_id( 'shop' ),
				'post_title' => $shop_title,
			)
		);
	}
	delete_option( 'winrocket_demo_shop_title' );
	delete_option( 'winrocket_demo_imported' );
	delete_option( 'winrocket_demo_entries' );
	delete_transient( 'winrocket_stats' );

	$settings = get_option( 'winrocket_settings', array() );
	if ( is_array( $settings ) && ! empty( $settings['results_page_id'] ) && ! get_post( $settings['results_page_id'] ) ) {
		$settings['results_page_id'] = 0;
		update_option( 'winrocket_settings', $settings );
	}

	/* translators: 1: products, 2: orders. */
	return sprintf( __( 'Removed %1$d demo competitions and %2$d demo orders.', 'winrocket' ), count( $products ), count( $orders ) );
}
