<?php
/**
 * Theme settings (Appearance > WinRocket).
 *
 * @package winrocket
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Single product sections the theme knows about (WooCommerce tab keys).
 *
 * @return array key => default title.
 */
function winrocket_known_sections() {
	return array(
		'description'                => __( 'Description', 'winrocket' ),
		'wxgiveaway_instant_win_tab' => __( 'Instant wins', 'winrocket' ),
		'wxgiveaway_participant_tab' => __( 'Competition history', 'winrocket' ),
		'wxgiveaway_winner_tab'      => __( 'Winners', 'winrocket' ),
		'gew_lucky_wheel'            => __( 'Lucky wheel', 'winrocket' ),
		'additional_information'     => __( 'Additional information', 'winrocket' ),
		'reviews'                    => __( 'Reviews', 'winrocket' ),
	);
}

/**
 * Default settings.
 *
 * @return array
 */
function winrocket_default_settings() {
	$sections = array();
	foreach ( winrocket_known_sections() as $key => $title ) {
		$sections[ $key ] = array(
			'show'  => 'reviews' === $key ? 0 : 1,
			'title' => '',
		);
	}

	return array(
		// Header.
		'announcement_enable'    => 1,
		'announcement_text'      => __( 'New competitions every week. Enter today for your chance to win.', 'winrocket' ),
		'announcement_link_text' => __( 'See draw results', 'winrocket' ),
		'announcement_link_url'  => '',
		'header_cta_text'        => __( 'Enter now', 'winrocket' ),
		'header_cta_url'         => '',

		// Competition cards.
		'card_show_category'     => 1,
		'card_show_date'         => 1,
		'card_show_bar'          => 1,
		'card_show_badges'       => 1,
		'card_button_text'       => __( 'Enter now', 'winrocket' ),

		// Single competition page.
		'single_info_cards'      => 1,
		'single_card_ends'       => 1,
		'single_card_sold'       => 1,
		'single_card_winners'    => 1,
		'single_card_prizes'     => 1,
		'single_layout'          => 'stacked',
		'single_show_meta'       => 0,
		'single_other_sections'  => 1,
		'single_sections'        => $sections,
		'single_related'         => 1,
		'single_related_count'   => 4,
		'single_related_title'   => __( 'Related competitions', 'winrocket' ),

		// Pages.
		'results_page_id'        => 0,
	);
}

/**
 * All settings merged with defaults.
 *
 * @return array
 */
function winrocket_settings() {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}
	$saved = get_option( 'winrocket_settings', array() );
	$saved = is_array( $saved ) ? $saved : array();
	$cache = array_replace( winrocket_default_settings(), $saved );

	// Merge section rows so new section keys get defaults.
	$defaults_sections        = winrocket_default_settings()['single_sections'];
	$saved_sections           = isset( $saved['single_sections'] ) && is_array( $saved['single_sections'] ) ? $saved['single_sections'] : array();
	$cache['single_sections'] = array_replace( $defaults_sections, $saved_sections );

	return $cache;
}

/**
 * One setting.
 *
 * @param string $key     Key.
 * @param mixed  $default Fallback.
 * @return mixed
 */
function winrocket_setting( $key, $default = null ) {
	$settings = winrocket_settings();
	return array_key_exists( $key, $settings ) ? $settings[ $key ] : $default;
}

/**
 * Admin menu.
 */
function winrocket_settings_menu() {
	add_theme_page(
		__( 'WinRocket settings', 'winrocket' ),
		__( 'WinRocket settings', 'winrocket' ),
		'manage_options',
		'winrocket-settings',
		'winrocket_render_settings_page'
	);
}
add_action( 'admin_menu', 'winrocket_settings_menu' );

/**
 * Register the option.
 */
function winrocket_register_settings() {
	register_setting(
		'winrocket_settings_group',
		'winrocket_settings',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'winrocket_sanitize_settings',
			'default'           => array(),
		)
	);
}
add_action( 'admin_init', 'winrocket_register_settings' );

/**
 * Sanitize settings.
 *
 * @param array $input Raw input.
 * @return array
 */
function winrocket_sanitize_settings( $input ) {
	$input    = is_array( $input ) ? $input : array();
	$defaults = winrocket_default_settings();
	$out      = array();

	$checkboxes = array(
		'announcement_enable',
		'card_show_category',
		'card_show_date',
		'card_show_bar',
		'card_show_badges',
		'single_info_cards',
		'single_card_ends',
		'single_card_sold',
		'single_card_winners',
		'single_card_prizes',
		'single_show_meta',
		'single_other_sections',
		'single_related',
	);
	foreach ( $checkboxes as $key ) {
		$out[ $key ] = empty( $input[ $key ] ) ? 0 : 1;
	}

	$texts = array(
		'announcement_text',
		'announcement_link_text',
		'header_cta_text',
		'card_button_text',
		'single_related_title',
	);
	foreach ( $texts as $key ) {
		$out[ $key ] = isset( $input[ $key ] ) ? sanitize_text_field( wp_unslash( $input[ $key ] ) ) : $defaults[ $key ];
	}

	foreach ( array( 'announcement_link_url', 'header_cta_url' ) as $key ) {
		$out[ $key ] = isset( $input[ $key ] ) ? esc_url_raw( wp_unslash( $input[ $key ] ) ) : '';
	}

	$out['single_layout']        = ( isset( $input['single_layout'] ) && 'tabs' === $input['single_layout'] ) ? 'tabs' : 'stacked';
	$out['single_related_count'] = isset( $input['single_related_count'] ) ? max( 1, min( 12, absint( $input['single_related_count'] ) ) ) : 4;
	$out['results_page_id']      = isset( $input['results_page_id'] ) ? absint( $input['results_page_id'] ) : 0;

	$out['single_sections'] = array();
	foreach ( winrocket_known_sections() as $key => $title ) {
		$row                            = isset( $input['single_sections'][ $key ] ) && is_array( $input['single_sections'][ $key ] ) ? $input['single_sections'][ $key ] : array();
		$out['single_sections'][ $key ] = array(
			'show'  => empty( $row['show'] ) ? 0 : 1,
			'title' => isset( $row['title'] ) ? sanitize_text_field( wp_unslash( $row['title'] ) ) : '',
		);
	}

	return $out;
}

/**
 * Settings page.
 */
function winrocket_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$s    = winrocket_settings();
	$name = 'winrocket_settings';
	$tab  = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'general'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$tabs = array(
		'general' => __( 'Header & cards', 'winrocket' ),
		'single'  => __( 'Single competition', 'winrocket' ),
		'demo'    => __( 'Demo content', 'winrocket' ),
	);

	$checkbox = function ( $key, $label, $desc = '' ) use ( $s, $name ) {
		printf(
			'<label><input type="checkbox" name="%1$s[%2$s]" value="1" %3$s> %4$s</label>%5$s',
			esc_attr( $name ),
			esc_attr( $key ),
			checked( ! empty( $s[ $key ] ), true, false ),
			esc_html( $label ),
			$desc ? '<p class="description">' . esc_html( $desc ) . '</p>' : ''
		);
	};
	$text = function ( $key, $type = 'text', $placeholder = '' ) use ( $s, $name ) {
		printf(
			'<input type="%1$s" class="regular-text" name="%2$s[%3$s]" value="%4$s" placeholder="%5$s">',
			esc_attr( $type ),
			esc_attr( $name ),
			esc_attr( $key ),
			esc_attr( (string) $s[ $key ] ),
			esc_attr( $placeholder )
		);
	};
	?>
	<div class="wrap winrocket-settings">
		<h1><?php esc_html_e( 'WinRocket settings', 'winrocket' ); ?></h1>
		<p><?php esc_html_e( 'Colors, fonts and page layouts are edited in Appearance > Editor. These settings control the competition parts of the theme.', 'winrocket' ); ?></p>

		<nav class="nav-tab-wrapper">
			<?php foreach ( $tabs as $key => $label ) : ?>
				<a href="<?php echo esc_url( admin_url( 'themes.php?page=winrocket-settings&tab=' . $key ) ); ?>" class="nav-tab <?php echo $tab === $key ? 'nav-tab-active' : ''; ?>"><?php echo esc_html( $label ); ?></a>
			<?php endforeach; ?>
		</nav>

		<?php if ( 'demo' === $tab ) : ?>
			<?php do_action( 'winrocket_settings_demo_tab' ); ?>
		<?php else : ?>
		<form method="post" action="options.php">
			<?php settings_fields( 'winrocket_settings_group' ); ?>
			<?php
			// Keep the fields of the other tab when saving this one.
			$other = 'general' === $tab ? 'single' : 'general';
			winrocket_print_hidden_settings( $other, $s, $name );
			?>

			<?php if ( 'general' === $tab ) : ?>
				<h2><?php esc_html_e( 'Announcement bar', 'winrocket' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr><th><?php esc_html_e( 'Show bar', 'winrocket' ); ?></th><td><?php $checkbox( 'announcement_enable', __( 'Show the announcement bar above the header', 'winrocket' ) ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Text', 'winrocket' ); ?></th><td><?php $text( 'announcement_text' ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Link text', 'winrocket' ); ?></th><td><?php $text( 'announcement_link_text' ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Link URL', 'winrocket' ); ?></th><td><?php $text( 'announcement_link_url', 'url', __( 'Empty = draw results page', 'winrocket' ) ); ?></td></tr>
				</table>

				<h2><?php esc_html_e( 'Header button', 'winrocket' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr><th><?php esc_html_e( 'Button text', 'winrocket' ); ?></th><td><?php $text( 'header_cta_text' ); ?><p class="description"><?php esc_html_e( 'Leave empty to hide the button.', 'winrocket' ); ?></p></td></tr>
					<tr><th><?php esc_html_e( 'Button URL', 'winrocket' ); ?></th><td><?php $text( 'header_cta_url', 'url', __( 'Empty = competitions page', 'winrocket' ) ); ?></td></tr>
				</table>

				<h2><?php esc_html_e( 'Competition cards', 'winrocket' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr><th><?php esc_html_e( 'Show on cards', 'winrocket' ); ?></th><td>
						<?php $checkbox( 'card_show_badges', __( 'Status badges (Live, Coming soon, Closed, Instant wins)', 'winrocket' ) ); ?><br>
						<?php $checkbox( 'card_show_category', __( 'Category', 'winrocket' ) ); ?><br>
						<?php $checkbox( 'card_show_date', __( 'Closing / opening date', 'winrocket' ) ); ?><br>
						<?php $checkbox( 'card_show_bar', __( 'Tickets available bar', 'winrocket' ) ); ?>
					</td></tr>
					<tr><th><?php esc_html_e( 'Button text', 'winrocket' ); ?></th><td><?php $text( 'card_button_text' ); ?></td></tr>
				</table>

				<h2><?php esc_html_e( 'Pages', 'winrocket' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr><th><?php esc_html_e( 'Draw results page', 'winrocket' ); ?></th><td>
						<?php
						wp_dropdown_pages(
							array(
								'name'              => esc_attr( $name ) . '[results_page_id]',
								'selected'          => (int) $s['results_page_id'],
								'show_option_none'  => esc_html__( '— Select —', 'winrocket' ),
								'option_none_value' => 0,
							)
						);
						?>
						<p class="description"><?php esc_html_e( 'Add the "Draw results" block to this page. Used by the announcement bar and the results banner.', 'winrocket' ); ?></p>
					</td></tr>
				</table>
			<?php else : ?>
				<h2><?php esc_html_e( 'Info cards under the image', 'winrocket' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr><th><?php esc_html_e( 'Info cards', 'winrocket' ); ?></th><td>
						<?php $checkbox( 'single_info_cards', __( 'Show info cards', 'winrocket' ) ); ?><br><br>
						<?php $checkbox( 'single_card_ends', __( 'Competition ends', 'winrocket' ) ); ?><br>
						<?php $checkbox( 'single_card_sold', __( 'Tickets sold', 'winrocket' ) ); ?><br>
						<?php $checkbox( 'single_card_winners', __( 'Winners', 'winrocket' ) ); ?><br>
						<?php $checkbox( 'single_card_prizes', __( 'Total prizes', 'winrocket' ) ); ?>
					</td></tr>
					<tr><th><?php esc_html_e( 'Product meta', 'winrocket' ); ?></th><td><?php $checkbox( 'single_show_meta', __( 'Show SKU, category and tags under the Enter button', 'winrocket' ) ); ?></td></tr>
				</table>

				<h2><?php esc_html_e( 'Sections below the competition', 'winrocket' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr><th><?php esc_html_e( 'Layout', 'winrocket' ); ?></th><td>
						<select name="<?php echo esc_attr( $name ); ?>[single_layout]">
							<option value="stacked" <?php selected( $s['single_layout'], 'stacked' ); ?>><?php esc_html_e( 'Stacked cards (one under another)', 'winrocket' ); ?></option>
							<option value="tabs" <?php selected( $s['single_layout'], 'tabs' ); ?>><?php esc_html_e( 'Tabs', 'winrocket' ); ?></option>
						</select>
					</td></tr>
					<tr><th><?php esc_html_e( 'Sections', 'winrocket' ); ?></th><td>
						<table class="widefat striped" style="max-width:640px">
							<thead><tr><th><?php esc_html_e( 'Show', 'winrocket' ); ?></th><th><?php esc_html_e( 'Section', 'winrocket' ); ?></th><th><?php esc_html_e( 'Custom title', 'winrocket' ); ?></th></tr></thead>
							<tbody>
							<?php foreach ( winrocket_known_sections() as $key => $title ) : ?>
								<?php $row = $s['single_sections'][ $key ]; ?>
								<tr>
									<td><input type="checkbox" name="<?php echo esc_attr( $name ); ?>[single_sections][<?php echo esc_attr( $key ); ?>][show]" value="1" <?php checked( ! empty( $row['show'] ) ); ?>></td>
									<td><?php echo esc_html( $title ); ?></td>
									<td><input type="text" name="<?php echo esc_attr( $name ); ?>[single_sections][<?php echo esc_attr( $key ); ?>][title]" value="<?php echo esc_attr( $row['title'] ); ?>" placeholder="<?php echo esc_attr( $title ); ?>"></td>
								</tr>
							<?php endforeach; ?>
							</tbody>
						</table>
						<p class="description"><?php esc_html_e( 'A section only appears when the plugin has content for it (for example Instant wins only on competitions with instant prizes).', 'winrocket' ); ?></p>
						<p><?php $checkbox( 'single_other_sections', __( 'Also show sections added by other plugins', 'winrocket' ) ); ?></p>
					</td></tr>
					<tr><th><?php esc_html_e( 'Related competitions', 'winrocket' ); ?></th><td>
						<?php $checkbox( 'single_related', __( 'Show related competitions', 'winrocket' ) ); ?><br><br>
						<label><?php esc_html_e( 'Title', 'winrocket' ); ?> <?php $text( 'single_related_title' ); ?></label><br><br>
						<label><?php esc_html_e( 'How many', 'winrocket' ); ?> <input type="number" min="1" max="12" name="<?php echo esc_attr( $name ); ?>[single_related_count]" value="<?php echo esc_attr( (int) $s['single_related_count'] ); ?>" style="width:70px"></label>
					</td></tr>
				</table>
			<?php endif; ?>

			<?php submit_button(); ?>
		</form>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Hidden inputs for the settings of the tab that is not shown.
 *
 * @param string $tab  Tab.
 * @param array  $s    Settings.
 * @param string $name Option name.
 */
function winrocket_print_hidden_settings( $tab, $s, $name ) {
	$general = array( 'announcement_enable', 'announcement_text', 'announcement_link_text', 'announcement_link_url', 'header_cta_text', 'header_cta_url', 'card_show_badges', 'card_show_category', 'card_show_date', 'card_show_bar', 'card_button_text', 'results_page_id' );
	$single  = array( 'single_info_cards', 'single_card_ends', 'single_card_sold', 'single_card_winners', 'single_card_prizes', 'single_show_meta', 'single_layout', 'single_other_sections', 'single_related', 'single_related_title', 'single_related_count' );

	foreach ( 'general' === $tab ? $general : $single as $key ) {
		printf( '<input type="hidden" name="%1$s[%2$s]" value="%3$s">', esc_attr( $name ), esc_attr( $key ), esc_attr( (string) $s[ $key ] ) );
	}

	if ( 'single' === $tab ) {
		foreach ( $s['single_sections'] as $key => $row ) {
			printf( '<input type="hidden" name="%1$s[single_sections][%2$s][show]" value="%3$s">', esc_attr( $name ), esc_attr( $key ), esc_attr( (string) (int) $row['show'] ) );
			printf( '<input type="hidden" name="%1$s[single_sections][%2$s][title]" value="%3$s">', esc_attr( $name ), esc_attr( $key ), esc_attr( (string) $row['title'] ) );
		}
	}
}
