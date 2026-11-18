<?php
/**
 * Category archive chrome for us.credityup.com.
 * Free Credits also mounts the same Rewarded hook as the home.
 * Dating landing pages are not modified by this template.
 * URL stays /category/<slug>/.
 */

if (!defined('ABSPATH')) {
    exit;
}

$term = get_queried_object();
$slug = ($term && isset($term->slug)) ? (string) $term->slug : '';
$is_credits = ($slug === 'free-credits');
$title = $is_credits ? 'Free credits' : single_cat_title('', false);
$raw_desc = ($term && isset($term->description)) ? trim(wp_strip_all_tags((string) $term->description)) : '';
if ($is_credits) {
    $description = 'Free credits on Credityup are rewards you unlock by watching an ad. They are not cash, loans, or credit products.';
    $lede = 'A free credit here is a reward you choose to unlock by watching a short advertisement. It is not cash, a loan, a credit card, or a spending limit. Credityup does not lend money and does not sell credit products.';
    $kicker = 'Rewards · Not a financial product';
} else {
    $description = $raw_desc !== '' ? $raw_desc : ($title . ' guides, in plain language, from Credityup.');
    $lede = $raw_desc !== '' ? $raw_desc : 'Plain-language dating guides in this topic. Credityup does not run a dating app and does not guarantee a match.';
    $kicker = 'United States · Dating guides';
}

if (!function_exists('credityup_print_slot')) {
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
}
if (!function_exists('credityup_archive_slots')) {
    function credityup_archive_slots() {
        return array(
            array('tag' => 'content1', 'min_height' => 300, 'label' => 'Advertisement'),
            array('tag' => 'content2', 'min_height' => 300, 'label' => 'Advertisement'),
        );
    }
}
if (!function_exists('credityup_english_date')) {
    function credityup_english_date($post = null) {
        if (function_exists('get_post_datetime')) {
            $dt = get_post_datetime($post);
            if ($dt instanceof DateTimeInterface) {
                return $dt->format('M j, Y');
            }
        }
        return '';
    }
}
if (!function_exists('credityup_primary_nav')) {
    function credityup_primary_nav() {
        echo '<nav class="cy-nav" aria-label="Primary"></nav>';
    }
}

$slots = credityup_archive_slots();
$content1 = isset($slots[0]) ? $slots[0] : array('tag' => 'content1', 'min_height' => 300, 'label' => 'Advertisement');
$content2 = isset($slots[1]) ? $slots[1] : array('tag' => 'content2', 'min_height' => 300, 'label' => 'Advertisement');
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="<?php echo esc_attr($description); ?>">
<?php wp_head(); ?>
</head>
<body <?php body_class('cy-home cy-archive' . ($is_credits ? ' cy-topic' : '')); ?>>
<?php wp_body_open(); ?>
<a class="cy-skip" href="#content">Skip to content</a>
<header class="cy-bar">
    <a class="cy-logo" href="<?php echo esc_url(home_url('/')); ?>">
        <svg width="22" height="22" viewBox="0 0 32 32" aria-hidden="true"><path d="M16 4 L28 26 H4 Z" fill="none" stroke="#ddd6fe" stroke-width="2"/><path d="M16 12 L23 26 H9 Z" fill="#a78bfa"/></svg>
        <span>credityup</span>
    </a>
    <?php credityup_primary_nav(); ?>
</header>
<main id="content" class="cy-wrap">
    <section class="cy-hero">
        <p class="cy-kicker"><?php echo esc_html($kicker); ?></p>
        <h1><?php echo esc_html($title); ?></h1>
        <p class="cy-lede"><?php echo esc_html($lede); ?></p>
    </section>

    <?php credityup_print_slot($content1); ?>

    <?php if ($is_credits) : ?>
    <section class="cy-section" id="rewards" aria-labelledby="cy-unlock-title">
        <h2 id="cy-unlock-title">Unlock</h2>
        <article class="cy-reward-panel">
            <p class="cy-kicker">Watch an ad</p>
            <h3>A credit for watching an ad. Not a loan.</h3>
            <p>Rewards here are credits you choose to unlock by watching a short advertisement. They are not cash, a loan, a credit card, or a spending limit.</p>
            <div class="cy-reward-mount" id="cy-reward-mount">
                <p id="cy-reward-hint">Watch a short ad to unlock a reward credit. The control stays on this page.</p>
                <a id="cy-reward-unlock" href="#rewards">Reward unlock</a>
                <winupads tag="rewarded" rewarded trigger="#cy-reward-unlock" cooldown="60" prompt="Watch a short ad to unlock a reward credit. This is not cash or a loan." prompt-yes="Watch the ad" prompt-no="Not now"></winupads>
            </div>
        </article>
    </section>
    <?php endif; ?>

    <section class="cy-section" aria-labelledby="cy-articles-title">
        <h2 id="cy-articles-title"><?php echo $is_credits ? 'Articles' : 'In this topic'; ?></h2>
        <?php if (have_posts()) : ?>
        <div class="cy-archive-grid">
            <?php while (have_posts()) : the_post(); ?>
                <?php
                if (function_exists('credityup_print_archive_card')) {
                    credityup_print_archive_card();
                } else {
                    $date = function_exists('credityup_english_date') ? credityup_english_date() : '';
                    $excerpt = wp_trim_words(wp_strip_all_tags((string) get_the_excerpt()), 22);
                    echo '<article class="cy-archive-card"><a href="' . esc_url(get_permalink()) . '">';
                    echo '<div class="cy-archive-media"></div><div class="cy-archive-body">';
                    echo '<h3>' . esc_html(get_the_title()) . '</h3>';
                    if ($date !== '') {
                        echo '<p class="cy-meta">' . esc_html($date) . '</p>';
                    }
                    if ($excerpt !== '') {
                        echo '<p class="cy-card-excerpt">' . esc_html($excerpt) . '</p>';
                    }
                    echo '</div></a></article>';
                }
                ?>
            <?php endwhile; ?>
        </div>
        <?php else : ?>
        <p class="cy-empty"><?php echo $is_credits ? 'No articles in this topic yet. New Free Credits pieces will be listed here.' : 'No articles in this topic yet.'; ?></p>
        <?php endif; ?>
    </section>

    <?php credityup_print_slot($content2); ?>

    <?php
    the_posts_pagination(array(
        'mid_size' => 1,
        'prev_text' => 'Previous',
        'next_text' => 'Next',
        'class' => 'cy-pager',
    ));
    ?>
</main>
<footer class="cy-foot">
    <div class="cy-wrap">
        <nav aria-label="Legal">
            <a href="<?php echo esc_url(home_url('/terms-of-use/')); ?>">Terms of Service</a>
            <a href="<?php echo esc_url(home_url('/privacy-policy/')); ?>">Privacy Policy</a>
            <a href="<?php echo esc_url(home_url('/cookie-policy/')); ?>">Cookie Policy</a>
        </nav>
        <p><?php echo $is_credits ? 'Credits on this page are rewards for watching an ad. They are not cash or a loan.' : 'Credityup publishes guides. Advertisements on this site are labeled.'; ?></p>
    </div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
