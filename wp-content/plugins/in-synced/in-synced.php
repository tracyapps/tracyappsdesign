<?php
/**
 * Plugin Name: In Synced
 * Description: Securely push selected database rows, uploads, and wp-content files between paired WordPress sites.
 * Version: 0.1.0
 * Author: Tapps
 * Requires PHP: 7.2
 */

if (!defined('ABSPATH')) {
    exit;
}

define('IN_SYNCED_VERSION', '0.1.0');
define('IN_SYNCED_FILE', __FILE__);
define('IN_SYNCED_DIR', plugin_dir_path(__FILE__));

require_once IN_SYNCED_DIR . 'includes/class-in-synced.php';

add_action('plugins_loaded', static function () {
    In_Synced::instance();
});

if (defined('WP_CLI') && WP_CLI) {
    require_once IN_SYNCED_DIR . 'includes/class-in-synced-cli.php';
}
