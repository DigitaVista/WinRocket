<?php
/**
 * WinRocket theme functions.
 *
 * @package winrocket
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WINROCKET_VERSION', '1.0.0' );
define( 'WINROCKET_DIR', get_template_directory() );
define( 'WINROCKET_URI', get_template_directory_uri() );

require WINROCKET_DIR . '/inc/settings.php';
require WINROCKET_DIR . '/inc/helpers.php';
require WINROCKET_DIR . '/inc/setup.php';
require WINROCKET_DIR . '/inc/blocks.php';
require WINROCKET_DIR . '/inc/woocommerce.php';
require WINROCKET_DIR . '/inc/demo-data.php';
require WINROCKET_DIR . '/inc/demo-import.php';
