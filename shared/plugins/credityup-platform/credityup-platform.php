<?php
/**
 * Plugin Name: Credityup Platform
 * Description: Front page for us.credityup.com, quiz email capture with CSV export, and Firebase web push with a soft opt-in. Does not change Winup ad tags, the Rewarded modal, Offerwall redirects, or Meta pixel snippets.
 * Version: 1.0.3
 * Author: Credityup
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Text Domain: credityup-platform
 */

if (!defined('ABSPATH')) {
    exit;
}

define('CREDITYUP_PLATFORM_VERSION', '1.0.3');
define('CREDITYUP_PLATFORM_FILE', __FILE__);
define('CREDITYUP_PLATFORM_DIR', plugin_dir_path(__FILE__));

require_once CREDITYUP_PLATFORM_DIR . 'includes/lp-meta.php';
require_once CREDITYUP_PLATFORM_DIR . 'includes/home.php';
require_once CREDITYUP_PLATFORM_DIR . 'includes/leads.php';
require_once CREDITYUP_PLATFORM_DIR . 'includes/push.php';

register_activation_hook(__FILE__, 'credityup_platform_activate');

add_action('admin_init', function () {
    if (get_option('credityup_platform_db') === CREDITYUP_PLATFORM_VERSION) {
        return;
    }
    credityup_leads_install();
    credityup_push_install();
    update_option('credityup_platform_db', CREDITYUP_PLATFORM_VERSION, false);
});

add_action('init', function () {
    if (is_admin()) {
        return;
    }
    $settings = credityup_push_defaults();
    if (!credityup_push_config_ready($settings)) {
        return;
    }
    $hash = md5(credityup_push_root_config_js($settings));
    $config_path = ABSPATH . 'credityup-firebase-config.js';
    if (get_option('credityup_push_config_hash') === $hash && file_exists($config_path)) {
        return;
    }
    $written = credityup_push_install_root_files();
    if (!is_wp_error($written)) {
        update_option('credityup_push_config_hash', $hash, false);
    }
});

function credityup_platform_activate() {
    credityup_leads_install();
    credityup_push_install();
    credityup_push_install_root_files();
}

function credityup_platform_hour_token($purpose) {
    $hour = (string) floor(time() / 3600);
    return hash_hmac('sha256', $hour . '|' . $purpose, wp_salt('auth'));
}

function credityup_platform_hour_token_ok($given, $purpose) {
    $given = (string) $given;
    if ($given === '') {
        return false;
    }
    $now = (int) floor(time() / 3600);
    for ($i = 0; $i <= 1; $i++) {
        $expected = hash_hmac('sha256', (string) ($now - $i) . '|' . $purpose, wp_salt('auth'));
        if (hash_equals($expected, $given)) {
            return true;
        }
    }
    return false;
}

function credityup_platform_ip_hash() {
    $ip = isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : '';
    return hash('sha256', $ip . '|' . wp_salt('auth'));
}
