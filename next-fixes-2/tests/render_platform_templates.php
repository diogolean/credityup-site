<?php
/**
 * Stub harness: render Credityup templates without a full WordPress install.
 * Usage: php shared/tests/render_platform_templates.php
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');

define('ABSPATH', '/tmp/cy-wp-stub/');
define('CREDITYUP_PLATFORM_VERSION', '1.0.2-test');
define('CREDITYUP_PLATFORM_FILE', dirname(__DIR__) . '/plugins/credityup-platform/credityup-platform.php');

$ROOT = dirname(__DIR__, 2);
$PLUGIN = $ROOT . '/shared/plugins/credityup-platform';
$BACKUP = $ROOT . '/next-fixes/backup-live';

class CY_Stub_Term {
    public $slug;
    public $name;
    public $description;
    public $term_id = 4;
    public function __construct($slug, $name, $description = '') {
        $this->slug = $slug;
        $this->name = $name;
        $this->description = $description;
    }
}

class CY_Stub_Post {
    public $ID;
    public $post_title;
    public $post_excerpt;
    public $post_date;
    public $permalink;
    public $thumb;
    public $category;
    public function __construct($id, $title, $excerpt, $date, $permalink, $thumb, $category) {
        $this->ID = $id;
        $this->post_title = $title;
        $this->post_excerpt = $excerpt;
        $this->post_date = $date;
        $this->permalink = $permalink;
        $this->thumb = $thumb;
        $this->category = $category;
    }
}

class WP_Query {
    public $posts = array();
    public $post_count = 0;
    public $current_post = -1;
    public $post;
    public function __construct($args = array()) {
        global $cy_stub_posts;
        $this->posts = $cy_stub_posts;
        $this->post_count = count($this->posts);
    }
    public function have_posts() {
        return $this->current_post + 1 < $this->post_count;
    }
    public function the_post() {
        global $cy_stub_current, $post;
        $this->current_post++;
        $this->post = $this->posts[$this->current_post];
        $cy_stub_current = $this->post;
        $post = $this->post;
        return $this->post;
    }
}

$cy_stub_is_front = false;
$cy_stub_category = null;
$cy_stub_current = null;
$post = null;
$cy_stub_posts = array(
    new CY_Stub_Post(1, 'Recognizing Interest: Subtle Signals Decoded', 'A short excerpt about interest cues.', '2026-09-30 12:00:00', 'https://us.credityup.com/subtle-signs-interested-lp-04-art03/', 'https://us.credityup.com/wp-content/uploads/2026/09/subtle-signs-interested-lp-04-art03-360x240.png', new CY_Stub_Term('apps', 'Apps')),
    new CY_Stub_Post(2, 'First Date Conversations: Finding Your Spark', 'Talking points for a first date.', '2026-09-30 12:00:00', 'https://us.credityup.com/first-date-conversation-starters-lp-03-art02/', '', new CY_Stub_Term('flirting', 'Flirting')),
    new CY_Stub_Post(3, 'Decoding Cues: Recognizing When Someone’s Interested', 'How to read a signal.', '2026-09-30 12:00:00', 'https://us.credityup.com/subtle-signs-interested-lp-04-art01/', '', new CY_Stub_Term('dating', 'Dating')),
    new CY_Stub_Post(4, 'Navigating Romantic Cues: Popular Apps vs. Tinder', 'Comparing cues across apps.', '2026-09-30 12:00:00', 'https://us.credityup.com/subtle-signs-interested-lp-04-art02/', '', new CY_Stub_Term('relationships', 'Relationships')),
);

function language_attributes() { echo 'lang="en-US"'; }
function bloginfo($show = '') { echo 'Us credityup'; }
function wp_head() {}
function wp_footer() {}
function wp_body_open() {}
function body_class($extra = '') {
    $classes = trim('cy-home ' . $extra);
    echo 'class="' . esc_attr($classes) . '"';
}
function esc_url($u) { return htmlspecialchars((string) $u, ENT_QUOTES, 'UTF-8'); }
function esc_attr($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
function esc_html($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
function home_url($path = '/') { return 'https://us.credityup.com' . $path; }
function is_wp_error($v) { return false; }
function is_front_page() { global $cy_stub_is_front; return $cy_stub_is_front; }
function is_category($slug = null) {
    global $cy_stub_category;
    if ($cy_stub_category === null) {
        return false;
    }
    if ($slug === null || $slug === '') {
        return true;
    }
    return $cy_stub_category === $slug;
}
function is_admin() { return false; }
function get_term_by($field, $value, $taxonomy = 'category') {
    if ($value === 'free-credits') {
        return new CY_Stub_Term('free-credits', 'Free Credits', 'Reward credits unlocked by watching an ad.');
    }
    return false;
}
function get_term_link($term, $taxonomy = '') {
    $slug = is_object($term) && isset($term->slug) ? $term->slug : 'free-credits';
    return home_url('/category/' . $slug . '/');
}
function get_category($id) { return false; }
function wp_insert_term($name, $taxonomy, $args = array()) { return array(); }
function get_option($k, $default = false) { return $default; }
function update_option($k, $v, $autoload = true) { return true; }
function wp_enqueue_style($h, $src = '', $deps = array(), $ver = false) {}
function wp_dequeue_style($h) {}
function wp_deregister_style($h) {}
function plugins_url($path, $file = '') { return 'https://us.credityup.com/wp-content/plugins/credityup-platform/' . ltrim($path, '/'); }
function get_queried_object() {
    global $cy_stub_category;
    if ($cy_stub_category === 'free-credits') {
        return new CY_Stub_Term('free-credits', 'Free Credits', 'Reward credits unlocked by watching an ad.');
    }
    if ($cy_stub_category) {
        return new CY_Stub_Term($cy_stub_category, ucfirst($cy_stub_category), '');
    }
    return null;
}
function single_cat_title($before = '', $display = true) {
    $term = get_queried_object();
    $title = $term ? $term->name : '';
    if ($display) {
        echo esc_html($title);
        return '';
    }
    return $title;
}
function wp_strip_all_tags($s) { return trim(strip_tags((string) $s)); }
function wp_trim_words($text, $num_words = 55, $more = null) {
    $words = preg_split('/\s+/', trim((string) $text));
    if (count($words) <= $num_words) {
        return implode(' ', $words);
    }
    return implode(' ', array_slice($words, 0, $num_words)) . '…';
}
function have_posts() {
    global $wp_query;
    if (!isset($wp_query) || !($wp_query instanceof WP_Query)) {
        $wp_query = new WP_Query();
    }
    return $wp_query->have_posts();
}
function the_post() {
    global $wp_query;
    $wp_query->the_post();
}
function the_permalink() { echo esc_url(get_permalink()); }
function get_permalink($post = null) {
    $p = cy_stub_post($post);
    return $p ? $p->permalink : home_url('/');
}
function the_title() { echo esc_html(get_the_title()); }
function get_the_title($post = null) {
    $p = cy_stub_post($post);
    return $p ? $p->post_title : '';
}
function get_the_excerpt($post = null) {
    $p = cy_stub_post($post);
    return $p ? $p->post_excerpt : '';
}
function get_the_date($format = 'F j, Y', $post = null) {
    // Localized on purpose in this stub, to prove templates avoid it.
    $p = cy_stub_post($post);
    if (!$p) {
        return '';
    }
    $ts = strtotime($p->post_date);
    $months = array(1=>'jan',2=>'fev',3=>'mar',4=>'abr',5=>'mai',6=>'jun',7=>'jul',8=>'ago',9=>'set',10=>'out',11=>'nov',12=>'dez');
    if ($format === 'M j, Y') {
        return $months[(int) date('n', $ts)] . ' ' . date('j, Y', $ts);
    }
    return date($format, $ts);
}
function get_post_datetime($post = null) {
    $p = cy_stub_post($post);
    if (!$p) {
        return false;
    }
    return new DateTimeImmutable($p->post_date, new DateTimeZone('America/Sao_Paulo'));
}
function get_the_category($post = null) {
    $p = cy_stub_post($post);
    return ($p && $p->category) ? array($p->category) : array();
}
function has_post_thumbnail($post = null) {
    $p = cy_stub_post($post);
    return $p && $p->thumb !== '';
}
function the_post_thumbnail($size = 'medium', $attr = array()) {
    $p = cy_stub_post();
    if (!$p || $p->thumb === '') {
        return;
    }
    $class = isset($attr['class']) ? $attr['class'] : '';
    $alt = isset($attr['alt']) ? $attr['alt'] : '';
    echo '<img src="' . esc_url($p->thumb) . '" class="' . esc_attr($class) . '" alt="' . esc_attr($alt) . '">';
}
function get_the_posts_pagination($args = array()) { return ''; }
function the_posts_pagination($args = array()) {}
function wp_reset_postdata() {
    global $cy_stub_current, $post, $wp_query;
    $cy_stub_current = null;
    $post = null;
    if (isset($wp_query) && $wp_query instanceof WP_Query) {
        $wp_query->current_post = -1;
    }
}
function apply_filters($tag, $value) { return $value; }
function add_filter($tag, $cb, $pri = 10, $args = 1) { return true; }
function add_action($tag, $cb, $pri = 10, $args = 1) { return true; }
function sanitize_title($s) { return strtolower(preg_replace('/[^a-z0-9-]+/i', '-', (string) $s)); }
function cy_stub_post($post = null) {
    global $cy_stub_current;
    if ($post instanceof CY_Stub_Post) {
        return $post;
    }
    return $cy_stub_current;
}

function cy_load($file) {
    if (!is_readable($file)) {
        throw new RuntimeException('Missing file: ' . $file);
    }
    require $file;
}

function cy_render($template, $label) {
    global $wp_query;
    $wp_query = new WP_Query();
    ob_start();
    try {
        include $template;
        $html = ob_get_clean();
    } catch (Throwable $e) {
        ob_end_clean();
        throw $e;
    }
    if (strpos($html, '<html') === false) {
        throw new RuntimeException($label . ': no html');
    }
    return $html;
}

$fails = 0;
function cy_ok($msg) { echo "ok  $msg\n"; }
function cy_fail($msg) { global $fails; $fails++; echo "FAIL  $msg\n"; }

// Case A: new home.php + new templates
function cy_case_a($plugin) {
    global $cy_stub_is_front, $cy_stub_category, $wp_query;
    if (!defined('CREDITYUP_PLATFORM_DIR')) {
        define('CREDITYUP_PLATFORM_DIR', $plugin . '/');
    }
    cy_load($plugin . '/includes/home.php');

    $cy_stub_is_front = true;
    $cy_stub_category = null;
    $html = cy_render($plugin . '/templates/front-page.php', 'A front-page');
    if (strpos($html, 'cy-archive-grid') === false) {
        cy_fail('A home missing archive grid');
    } elseif (strpos($html, 'set 30, 2026') !== false) {
        cy_fail('A home still has Portuguese date');
    } elseif (strpos($html, 'Sep 30, 2026') === false) {
        cy_fail('A home missing English date');
    } else {
        cy_ok('A front-page renders, English date, archive cards');
    }
    if (strpos($html, 'cy-archive-thumb') === false) {
        cy_fail('A home missing featured image on a post that has one');
    } else {
        cy_ok('A home shows a featured thumbnail');
    }

    $cy_stub_is_front = false;
    $cy_stub_category = 'free-credits';
    $html = cy_render($plugin . '/templates/free-credits.php', 'A free-credits');
    if (strpos($html, 'tag="rewarded"') === false) {
        cy_fail('A free-credits missing rewarded tag');
    } elseif (strpos($html, 'credits_rewarded') !== false) {
        cy_fail('A free-credits still has credits_rewarded');
    } else {
        cy_ok('A free-credits renders with rewarded hook');
    }

    $cy_stub_category = 'apps';
    $html = cy_render($plugin . '/templates/free-credits.php', 'A category/apps');
    if (strpos($html, 'tag="rewarded"') !== false) {
        cy_fail('A apps archive printed rewarded');
    } elseif (strpos($html, 'cy-archive-grid') === false) {
        cy_fail('A apps archive missing cards');
    } else {
        cy_ok('A category/apps renders without rewarded');
    }

    $path = credityup_archive_template_path();
    if (!credityup_archive_template_ready($path)) {
        cy_fail('A archive_template_ready should be true for the new template');
    } else {
        cy_ok('A archive_template_ready detects the new template');
    }
}

// Case B: new home.php + old templates (already loaded home.php in this process if A ran in same PHP).
// Isolated in a subprocess from the runner below.

// Case C similarly isolated.

if (php_sapi_name() === 'cli' && realpath($_SERVER['SCRIPT_FILENAME']) === __FILE__) {
    try {
        cy_case_a($PLUGIN);
    } catch (Throwable $e) {
        cy_fail('A fatal: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    }
    if ($fails) {
        echo "FAILED ($fails)\n";
        exit(1);
    }
    echo "ok render harness A in-process\n";
    exit(0);
}
