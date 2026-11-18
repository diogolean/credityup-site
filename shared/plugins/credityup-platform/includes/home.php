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

function credityup_free_credits_url() {
    $term = get_term_by('slug', 'free-credits', 'category');
    if ($term && !is_wp_error($term)) {
        $link = get_term_link($term);
        if (!is_wp_error($link)) {
            return $link;
        }
    }
    return home_url('/category/free-credits/');
}

function credityup_ensure_free_credits_category() {
    if (!credityup_home_enabled()) {
        return;
    }
    if (get_term_by('slug', 'free-credits', 'category')) {
        return;
    }
    wp_insert_term('Free Credits', 'category', array(
        'slug' => 'free-credits',
        'description' => 'Reward credits unlocked by watching an ad. Not cash, loans, or credit products.',
    ));
}
add_action('init', 'credityup_ensure_free_credits_category');

function credityup_platform_chrome() {
    if (!credityup_home_enabled()) {
        return false;
    }
    return is_front_page() || is_category('free-credits');
}

function credityup_primary_nav() {
    $credits = credityup_free_credits_url();
    $links = array(
        array(home_url('/'), 'Home'),
        array(home_url('/category/apps/'), 'Apps'),
        array(home_url('/category/flirting/'), 'Flirting'),
        array(home_url('/category/love/'), 'Love'),
        array(home_url('/category/dating/'), 'Dating'),
        array(home_url('/category/relationships/'), 'Relationships'),
        array($credits, 'Free Credits'),
        array(home_url('/#safety'), 'Safety'),
    );
    echo '<nav class="cy-nav" aria-label="Primary">';
    foreach ($links as $link) {
        echo '<a href="' . esc_url($link[0]) . '">' . esc_html($link[1]) . '</a>';
    }
    echo '</nav>';
}

function credityup_nav_free_credits($items, $args) {
    unset($args);
    if (!credityup_home_enabled() || !is_array($items)) {
        return $items;
    }
    $dating = array('apps', 'flirting', 'love', 'dating', 'relationships');
    $is_header = false;
    $after = null;
    foreach ($items as $index => $item) {
        if (!isset($item->object) || $item->object !== 'category') {
            continue;
        }
        $cat = get_category($item->object_id);
        if (!$cat || is_wp_error($cat)) {
            continue;
        }
        if ($cat->slug === 'free-credits') {
            return $items;
        }
        if (in_array($cat->slug, $dating, true)) {
            $is_header = true;
            if ($cat->slug === 'relationships') {
                $after = $index;
            }
        }
    }
    if (!$is_header) {
        return $items;
    }
    $term = get_term_by('slug', 'free-credits', 'category');
    if (!$term || is_wp_error($term)) {
        return $items;
    }
    $link = get_term_link($term);
    if (is_wp_error($link)) {
        return $items;
    }
    $new = new stdClass();
    $new->ID = 900000 + (int) $term->term_id;
    $new->db_id = $new->ID;
    $new->menu_item_parent = 0;
    $new->object_id = (int) $term->term_id;
    $new->object = 'category';
    $new->type = 'taxonomy';
    $new->title = 'Free Credits';
    $new->url = $link;
    $new->target = '';
    $new->attr_title = '';
    $new->description = '';
    $new->classes = array('menu-item', 'menu-item-type-taxonomy', 'menu-item-object-category');
    $new->xfn = '';
    $new->status = 'publish';
    $new->current = is_category($term->term_id);
    $new->current_item_ancestor = false;
    $new->current_item_parent = false;
    if ($after === null) {
        $items[] = $new;
        return $items;
    }
    array_splice($items, $after + 1, 0, array($new));
    return $items;
}
add_filter('wp_nav_menu_objects', 'credityup_nav_free_credits', 10, 2);

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
    if (!credityup_platform_chrome()) {
        return $template;
    }
    if (is_category('free-credits')) {
        return CREDITYUP_PLATFORM_DIR . 'templates/free-credits.php';
    }
    if (is_front_page()) {
        return CREDITYUP_PLATFORM_DIR . 'templates/front-page.php';
    }
    return $template;
});

add_action('wp_enqueue_scripts', function () {
    if (!credityup_platform_chrome()) {
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
