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

function credityup_platform_chrome() {
    if (!credityup_home_enabled()) {
        return false;
    }
    return is_front_page() || is_category();
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

add_filter('template_include', function ($template) {
    if (!credityup_platform_chrome()) {
        return $template;
    }
    if (is_category()) {
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
