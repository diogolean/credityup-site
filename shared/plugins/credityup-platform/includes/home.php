<?php
/**
 * Replaces the posts index on us.credityup.com with a text-first front page.
 * wp_head/wp_footer still run, so Winup, HFCM, and the Rewarded plugin keep printing.
 */

if (!defined('ABSPATH')) {
    exit;
}

function credityup_home_enabled() {
    $host = isset($_SERVER['HTTP_HOST']) ? strtolower((string) $_SERVER['HTTP_HOST']) : '';
    $host = preg_replace('/:\d+$/', '', $host);
    $default = ($host === 'us.credityup.com');
    if (defined('CREDITYUP_HOME') && CREDITYUP_HOME) {
        $default = true;
    }
    return (bool) apply_filters('credityup_home_enabled', $default);
}

function credityup_home_slots() {
    $slots = array(
        array(
            'tag' => 'content1',
            'min_height' => 300,
            'label' => 'Advertisement',
        ),
    );
    return apply_filters('credityup_home_slots', $slots);
}

add_filter('template_include', function ($template) {
    if (!credityup_home_enabled() || !is_front_page()) {
        return $template;
    }
    return CREDITYUP_PLATFORM_DIR . 'templates/front-page.php';
});

add_action('wp_enqueue_scripts', function () {
    if (!credityup_home_enabled() || !is_front_page()) {
        return;
    }
    wp_enqueue_style(
        'credityup-home',
        plugins_url('assets/home.css', CREDITYUP_PLATFORM_FILE),
        array(),
        CREDITYUP_PLATFORM_VERSION
    );
    foreach (array('bootstrap', 'hestia-font-sizes', 'hestia_style', 'hestia-fontawesome', 'font-awesome') as $handle) {
        wp_dequeue_style($handle);
        wp_deregister_style($handle);
    }
}, 100);
