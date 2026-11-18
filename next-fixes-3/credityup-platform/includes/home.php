<?php
/**
 * Replaces the posts index on us.credityup.com with a text-first front page,
 * and category archives with the same chrome. wp_head/wp_footer still run,
 * so Winup, HFCM, and the Rewarded plugin keep printing on other templates.
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

function credityup_archive_template_path() {
    if (!defined('CREDITYUP_PLATFORM_DIR')) {
        return '';
    }
    return CREDITYUP_PLATFORM_DIR . 'templates/free-credits.php';
}

function credityup_archive_template_ready($path = '') {
    if ($path === '') {
        $path = credityup_archive_template_path();
    }
    if ($path === '' || !is_readable($path)) {
        return false;
    }
    $head = @file_get_contents($path, false, null, 0, 1600);
    return is_string($head) && strpos($head, 'credityup_print_slot') !== false;
}

function credityup_legal_slugs() {
    return array('terms-of-use', 'privacy-policy', 'cookie-policy');
}

function credityup_is_legal_page() {
    return function_exists('is_page') && is_page(credityup_legal_slugs());
}

function credityup_legal_template_ready($path = '') {
    if ($path === '') {
        $path = credityup_archive_template_path();
    }
    if ($path === '' || !is_readable($path)) {
        return false;
    }
    $src = @file_get_contents($path);
    return is_string($src) && strpos($src, 'cy-legal') !== false && strpos($src, 'credityup_is_legal_page') !== false;
}

function credityup_home_card_slugs() {
    // Current monetizing LP articles. The LP-01 and LP-02 hubs are already
    // featured in the Dating guides section, so the grid lists their six articles.
    return array(
        'best-easy-dating-apps-lp-01-art01',
        'best-easy-dating-apps-lp-01-art02',
        'best-easy-dating-apps-lp-01-art03',
        'instant-matchmaking-apps-lp-02-art01',
        'instant-matchmaking-apps-lp-02-art02',
        'instant-matchmaking-apps-lp-02-art03',
    );
}

function credityup_home_card_query() {
    $slugs = credityup_home_card_slugs();
    return new WP_Query(array(
        'post_type' => 'post',
        'post_status' => 'publish',
        'post_name__in' => $slugs,
        'orderby' => 'post_name__in',
        'posts_per_page' => count($slugs),
        'ignore_sticky_posts' => true,
        'no_found_rows' => true,
    ));
}

function credityup_platform_chrome() {
    if (!credityup_home_enabled()) {
        return false;
    }
    if (is_front_page() || is_category('free-credits')) {
        return true;
    }
    if (is_category() && credityup_archive_template_ready()) {
        return true;
    }
    return credityup_is_legal_page() && credityup_legal_template_ready();
}

function credityup_primary_nav() {
    $credits = credityup_free_credits_url();
    $links = array(
        array('home', home_url('/'), 'Home'),
        array('apps', home_url('/category/apps/'), 'Apps'),
        array('flirting', home_url('/category/flirting/'), 'Flirting'),
        array('love', home_url('/category/love/'), 'Love'),
        array('dating', home_url('/category/dating/'), 'Dating'),
        array('relationships', home_url('/category/relationships/'), 'Relationships'),
        array('free-credits', $credits, 'Free Credits'),
        array('safety', home_url('/#safety'), 'Safety'),
    );
    echo '<nav class="cy-nav" aria-label="Primary">';
    foreach ($links as $link) {
        $current = false;
        if ($link[0] === 'home') {
            $current = is_front_page();
        } elseif ($link[0] !== 'safety') {
            $current = is_category($link[0]);
        }
        echo '<a href="' . esc_url($link[1]) . '"';
        if ($current) {
            echo ' class="is-current" aria-current="page"';
        }
        echo '>' . esc_html($link[2]) . '</a>';
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

function credityup_archive_slots() {
    $slots = array(
        array(
            'tag' => 'content1',
            'min_height' => 300,
            'label' => 'Advertisement',
        ),
        array(
            'tag' => 'content2',
            'min_height' => 300,
            'label' => 'Advertisement',
        ),
    );
    return apply_filters('credityup_archive_slots', $slots);
}

function credityup_print_slot($slot) {
    $tag = isset($slot['tag']) ? preg_replace('/[^a-z0-9_-]/i', '', (string) $slot['tag']) : '';
    if ($tag === '' || $tag === 'interstitial' || $tag === 'rewarded') {
        return;
    }
    $min = isset($slot['min_height']) ? (int) $slot['min_height'] : 300;
    $label = isset($slot['label']) ? (string) $slot['label'] : 'Advertisement';
    echo '<aside class="cy-slot winup-ad-block winup-ad-block-1" style="min-height:' . (int) $min . 'px" aria-label="' . esc_attr($label) . '">';
    echo '<span class="cy-slot-label">' . esc_html($label) . '</span>';
    echo '<winupads tag="' . esc_attr($tag) . '" label="' . esc_attr($label) . '"></winupads>';
    echo '</aside>';
}

function credityup_english_date($post = null) {
    if (function_exists('get_post_datetime')) {
        $dt = get_post_datetime($post);
        if ($dt instanceof DateTimeInterface) {
            return $dt->format('M j, Y');
        }
    }
    if (function_exists('get_post_timestamp')) {
        $ts = get_post_timestamp($post);
        if ($ts) {
            $tz = function_exists('wp_timezone') ? wp_timezone() : new DateTimeZone('UTC');
            return (new DateTimeImmutable('@' . (int) $ts))->setTimezone($tz)->format('M j, Y');
        }
    }
    return '';
}

function credityup_print_archive_card() {
    $cats = get_the_category();
    $cat = ($cats && !is_wp_error($cats)) ? $cats[0] : null;
    $slug = ($cat && !empty($cat->slug)) ? sanitize_title($cat->slug) : 'topic';
    $name = ($cat && !empty($cat->name)) ? (string) $cat->name : '';
    $excerpt = wp_trim_words(wp_strip_all_tags((string) get_the_excerpt()), 22);
    echo '<article class="cy-archive-card">';
    echo '<a href="' . esc_url(get_permalink()) . '">';
    echo '<div class="cy-archive-media cy-archive-media--' . esc_attr($slug) . '">';
    if (has_post_thumbnail()) {
        the_post_thumbnail('medium', array(
            'class' => 'cy-archive-thumb',
            'alt' => esc_attr(wp_strip_all_tags(get_the_title())),
        ));
    }
    echo '</div><div class="cy-archive-body">';
    if ($name !== '') {
        echo '<p class="cy-archive-chip">' . esc_html($name) . '</p>';
    }
    echo '<h3>' . esc_html(get_the_title()) . '</h3>';
    if ($excerpt !== '') {
        echo '<p class="cy-card-excerpt">' . esc_html($excerpt) . '</p>';
    }
    echo '</div></a></article>';
}

/**
 * Keep a single Rewarded controller on Credityup chrome pages.
 * wads.js only intercepts a click when rewardedSlotReady has fired
 * (L.mostrar is set). Until then, #cy-reward-unlock keeps href="#rewards".
 * A second <winupads rewarded> (for example trigger=".btn-winupads-trigger")
 * would steal that controller, so stray rewarded tags are disarmed here
 * before wads.js mounts them. Dating LPs are not chrome pages.
 */
function credityup_rewarded_controller_script() {
    if (!credityup_platform_chrome()) {
        return;
    }
    echo "<script id=\"cy-rewarded-controller\">\n";
    echo <<<'JS'
(function () {
  var OUR = '#cy-reward-unlock';

  function ours(el) {
    return !!(el && document.getElementById('cy-reward-unlock') && (el.getAttribute('trigger') || '').trim() === OUR);
  }

  function disarm(root) {
    if (!root || !root.querySelectorAll) {
      return;
    }
    var nodes = root.querySelectorAll('winupads[rewarded], winup-ads[rewarded]');
    for (var i = 0; i < nodes.length; i++) {
      if (ours(nodes[i])) {
        continue;
      }
      nodes[i].removeAttribute('rewarded');
      nodes[i].setAttribute('data-cy-disarmed', '1');
    }
    var link = document.getElementById('cy-reward-unlock');
    if (link && link.classList) {
      link.classList.remove('btn-winupads-trigger');
    }
  }

  function start() {
    disarm(document);
    if (!window.MutationObserver || !document.documentElement) {
      return;
    }
    var mo = new MutationObserver(function () {
      disarm(document);
    });
    mo.observe(document.documentElement, { childList: true, subtree: true });
    window.addEventListener('load', function () {
      setTimeout(function () {
        disarm(document);
        mo.disconnect();
      }, 2000);
    });
  }

  function mark(state) {
    var link = document.getElementById('cy-reward-unlock');
    var hint = document.getElementById('cy-reward-hint');
    if (!link) {
      return;
    }
    link.setAttribute('data-cy-reward', state);
    if (!hint) {
      return;
    }
    if (state === 'ready') {
      hint.textContent = 'Watch a short ad to unlock a reward credit. The control stays on this page.';
    } else if (state === 'empty') {
      hint.textContent = 'No ad is ready right now. You can stay on this page or open the Free Credits topic.';
    }
  }

  function hookPubads() {
    if (!window.googletag || !window.googletag.cmd) {
      return false;
    }
    window.googletag.cmd.push(function () {
      var pa = window.googletag.pubads && window.googletag.pubads();
      if (!pa || pa.cyRewardHooked) {
        return;
      }
      pa.cyRewardHooked = 1;
      pa.addEventListener('rewardedSlotReady', function () {
        mark('ready');
      });
      pa.addEventListener('slotRenderEnded', function (ev) {
        if (!ev || !ev.isEmpty || !ev.slot || !ev.slot.getAdUnitPath) {
          return;
        }
        var path = ev.slot.getAdUnitPath() || '';
        if (path.indexOf('_rewarded_') === -1) {
          return;
        }
        var link = document.getElementById('cy-reward-unlock');
        if (link && link.getAttribute('data-cy-reward') !== 'ready') {
          mark('empty');
        }
      });
    });
    return true;
  }

  start();
  var tries = 0;
  (function wait() {
    if (hookPubads() || ++tries > 40) {
      return;
    }
    setTimeout(wait, 250);
  }());
}());
JS;
    echo "\n</script>\n";
}
add_action('wp_head', 'credityup_rewarded_controller_script', 1);

function credityup_slot_collapse_script() {
    if (!credityup_platform_chrome()) {
        return;
    }
    echo "<script id=\"cy-slot-collapse\">\n";
    echo <<<'JS'
(function () {
  function boxFor(ev) {
    if (!ev || !ev.slot || !ev.slot.getSlotElementId) {
      return null;
    }
    var id = ev.slot.getSlotElementId();
    if (!id || !document.getElementById) {
      return null;
    }
    var el = document.getElementById(id);
    return (el && el.closest) ? el.closest('.cy-slot') : null;
  }

  function apply(box, empty) {
    if (!box || !box.classList) {
      return;
    }
    if (empty) {
      box.classList.add('cy-slot-empty');
      box.classList.remove('cy-slot-filled');
      box.setAttribute('aria-hidden', 'true');
      if (box.style && box.style.setProperty) {
        box.style.setProperty('display', 'none', 'important');
      }
    } else {
      box.classList.add('cy-slot-filled');
      box.classList.remove('cy-slot-empty');
      box.removeAttribute('aria-hidden');
      if (box.style && box.style.removeProperty) {
        box.style.removeProperty('display');
      }
    }
  }

  function hook() {
    if (!window.googletag || !window.googletag.cmd) {
      return false;
    }
    window.googletag.cmd.push(function () {
      var pa = window.googletag.pubads && window.googletag.pubads();
      if (!pa || pa.cySlotCollapseHooked) {
        return;
      }
      pa.cySlotCollapseHooked = 1;
      pa.addEventListener('slotRenderEnded', function (ev) {
        var box = boxFor(ev);
        if (!box) {
          return;
        }
        apply(box, !!ev.isEmpty);
      });
    });
    return true;
  }

  var tries = 0;
  (function wait() {
    if (hook() || ++tries > 40) {
      return;
    }
    setTimeout(wait, 250);
  }());
}());
JS;
    echo "\n</script>\n";
}
add_action('wp_head', 'credityup_slot_collapse_script', 1);

add_filter('template_include', function ($template) {
    if (!credityup_home_enabled()) {
        return $template;
    }
    if (is_category('free-credits')) {
        $path = credityup_archive_template_path();
        return ($path !== '' && is_readable($path)) ? $path : $template;
    }
    if (is_category() && credityup_archive_template_ready()) {
        return credityup_archive_template_path();
    }
    if (is_front_page()) {
        $home = CREDITYUP_PLATFORM_DIR . 'templates/front-page.php';
        return is_readable($home) ? $home : $template;
    }
    if (credityup_is_legal_page() && credityup_legal_template_ready()) {
        return credityup_archive_template_path();
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
