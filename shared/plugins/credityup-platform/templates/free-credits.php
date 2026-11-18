<?php
/**
 * Category archive for free-credits.
 * The credits_rewarded tag is inert until Winup adds the manifest keys.
 * Dating landing pages are not modified by this template.
 */

if (!defined('ABSPATH')) {
    exit;
}
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="Free credits on Credityup are rewards you unlock by watching an ad. They are not cash, loans, or credit products.">
<?php wp_head(); ?>
</head>
<body <?php body_class('cy-home cy-topic'); ?>>
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
        <p class="cy-kicker">Rewards · Not a financial product</p>
        <h1>Free credits</h1>
        <p class="cy-lede">A free credit here is a reward you choose to unlock by watching a short advertisement. It is not cash, a loan, a credit card, or a spending limit. Credityup does not lend money and does not sell credit products.</p>
    </section>

    <section class="cy-section" id="rewards" aria-labelledby="cy-unlock-title">
        <h2 id="cy-unlock-title">Unlock</h2>
        <article class="cy-reward-panel">
            <p class="cy-kicker">Watch an ad</p>
            <h3>The unlock is reserved, not switched on.</h3>
            <p>When this is enabled, the button asks you to watch an ad before a reward credit is granted. Until then, the button stays on this page and does not grant a credit.</p>
            <div class="cy-reward-mount" id="cy-reward-mount">
                <p>Rewarded and Offerwall unlocks use this space. The control does not leave Credityup.</p>
                <a id="cy-reward-unlock" href="#rewards">Reward unlock</a>
                <winupads tag="credits_rewarded" rewarded trigger="#cy-reward-unlock" prompt="Watch a short ad to unlock a reward credit. This is not cash or a loan." prompt-yes="Watch the ad" prompt-no="Not now"></winupads>
            </div>
        </article>
    </section>

    <section class="cy-section" aria-labelledby="cy-articles-title">
        <h2 id="cy-articles-title">Articles</h2>
        <?php if (have_posts()) : ?>
        <ul class="cy-list">
            <?php while (have_posts()) : the_post(); ?>
            <li>
                <a href="<?php the_permalink(); ?>">
                    <h3><?php the_title(); ?></h3>
                    <p class="cy-meta"><?php echo esc_html(get_the_date('M j, Y')); ?></p>
                </a>
            </li>
            <?php endwhile; ?>
        </ul>
        <?php else : ?>
        <p class="cy-empty">No articles in this topic yet. New Free Credits pieces will be listed here.</p>
        <?php endif; ?>
    </section>
</main>
<footer class="cy-foot">
    <div class="cy-wrap">
        <nav aria-label="Legal">
            <a href="<?php echo esc_url(home_url('/terms-of-use/')); ?>">Terms of Service</a>
            <a href="<?php echo esc_url(home_url('/privacy-policy/')); ?>">Privacy Policy</a>
            <a href="<?php echo esc_url(home_url('/cookie-policy/')); ?>">Cookie Policy</a>
        </nav>
        <p>Credits on this page are rewards for watching an ad. They are not cash or a loan.</p>
    </div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
