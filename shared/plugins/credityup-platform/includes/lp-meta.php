<?php
/**
 * Front-end titles and descriptions for the two US landing pages.
 * Post body stays whatever is in the editor until the HTML in us/content/ is pasted.
 */

if (!defined('ABSPATH')) {
    exit;
}

function credityup_lp_copy_map() {
    return array(
        'best-easy-dating-apps-lp-01' => array(
            'title' => 'Easy dating apps: free plans vs Tinder Gold, Bumble Premium, and Hinge',
            'description' => 'What the free plan includes, and what Tinder Gold, Bumble Premium, and Hinge paid plans usually add. Prices are on the store listing. Guides stay on Credityup.',
        ),
        'instant-matchmaking-apps-lp-02' => array(
            'title' => 'Instant matchmaking apps: free use, paid boosts, and what to check',
            'description' => 'How quick matching works before you pay, and how boosts and premium subscriptions differ from verification. You stay on Credityup.',
        ),
    );
}

function credityup_lp_requested_slug() {
    if (!is_singular('page')) {
        return '';
    }
    $post = get_queried_object();
    if (!$post || empty($post->post_name)) {
        return '';
    }
    $map = credityup_lp_copy_map();
    return isset($map[$post->post_name]) ? $post->post_name : '';
}

add_filter('document_title_parts', function ($parts) {
    $slug = credityup_lp_requested_slug();
    if ($slug === '') {
        return $parts;
    }
    $map = credityup_lp_copy_map();
    $parts['title'] = $map[$slug]['title'];
    return $parts;
});

add_filter('the_title', function ($title, $post_id) {
    if (is_admin() || !in_the_loop() || !is_singular('page')) {
        return $title;
    }
    $slug = get_post_field('post_name', $post_id);
    $map = credityup_lp_copy_map();
    if (!isset($map[$slug]) || (int) $post_id !== (int) get_queried_object_id()) {
        return $title;
    }
    return $map[$slug]['title'];
}, 10, 2);

add_action('wp_head', function () {
    if (is_admin()) {
        return;
    }
    $slug = credityup_lp_requested_slug();
    if ($slug === '') {
        return;
    }
    $map = credityup_lp_copy_map();
    echo '<meta name="description" content="' . esc_attr($map[$slug]['description']) . '">' . "\n";
}, 1);
