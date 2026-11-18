<?php
/**
 * Front page markup. One in-page ad slot (content1). Interstitial and Rewarded
 * stay with the plugins that print them from wp_head / wp_footer.
 */

if (!defined('ABSPATH')) {
    exit;
}

$slots = credityup_home_slots();
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="Plain-language dating guides for adults in the United States. Credityup does not run a dating app and does not guarantee a match.">
<?php wp_head(); ?>
</head>
<body <?php body_class('cy-home'); ?>>
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
        <p class="cy-kicker">United States · Dating guides</p>
        <h1>A quieter place to compare dating apps.</h1>
        <p class="cy-lede">Credityup publishes plain-language dating guides, and keeps a separate place for reward credits you unlock by watching an ad. We don't run a dating app, sell matches, or offer loans or cash.</p>
        <div class="cy-actions">
            <a class="cy-btn cy-btn-primary" href="<?php echo esc_url(home_url('/best-easy-dating-apps-lp-01/')); ?>">Easy-to-use apps</a>
            <a class="cy-btn cy-btn-ghost" href="<?php echo esc_url(home_url('/instant-matchmaking-apps-lp-02/')); ?>">Instant matchmaking</a>
        </div>
    </section>

    <?php foreach ($slots as $slot) :
        $tag = isset($slot['tag']) ? preg_replace('/[^a-z0-9_-]/i', '', (string) $slot['tag']) : '';
        if ($tag === '' || $tag === 'interstitial' || $tag === 'rewarded') {
            continue;
        }
        $min = isset($slot['min_height']) ? (int) $slot['min_height'] : 300;
        $label = isset($slot['label']) ? (string) $slot['label'] : 'Advertisement';
        ?>
    <aside class="cy-slot winup-ad-block winup-ad-block-1" style="min-height:<?php echo (int) $min; ?>px" aria-label="<?php echo esc_attr($label); ?>">
        <span class="cy-slot-label"><?php echo esc_html($label); ?></span>
        <winupads tag="<?php echo esc_attr($tag); ?>" label="<?php echo esc_attr($label); ?>"></winupads>
    </aside>
    <?php endforeach; ?>

    <section class="cy-section" aria-label="Dating guides and rewards">
        <div class="cy-poles">
            <div id="guides">
                <h2>Dating guides</h2>
                <div class="cy-guides-stack">
                    <article class="cy-guide">
                        <p class="cy-kicker">Guide</p>
                        <h3><a href="<?php echo esc_url(home_url('/best-easy-dating-apps-lp-01/')); ?>">Easy dating apps, without the extra steps</a></h3>
                        <p>What to check in the first ten minutes: how long the profile is, who can see your photo, and where the message field sits.</p>
                    </article>
                    <article class="cy-guide">
                        <p class="cy-kicker">Guide</p>
                        <h3><a href="<?php echo esc_url(home_url('/instant-matchmaking-apps-lp-02/')); ?>">How instant matchmaking apps actually work</a></h3>
                        <p>Quick matching usually means location, a short profile, and an alert. It does not mean a person is already nearby.</p>
                    </article>
                </div>
            </div>
            <div id="rewards">
                <h2>Free credits</h2>
                <article class="cy-reward-panel">
                    <p class="cy-kicker">Rewards</p>
                    <h3>A credit for watching an ad. Not a loan.</h3>
                    <p>Rewards here are credits you choose to unlock by watching a short advertisement. They are not cash, a loan, a credit card, or a spending limit. This block is ready for that unlock. It is not switched on, so watching an ad on this page does not grant a credit yet.</p>
                    <p><a href="<?php echo esc_url(credityup_free_credits_url()); ?>">Open the Free Credits topic</a></p>
                    <div class="cy-reward-mount" id="cy-reward-mount">
                        <p>The Rewarded and Offerwall unlock will sit in this space. The control stays on this page.</p>
                        <a id="cy-reward-unlock" href="#rewards">Reward unlock</a>
                        <?php /* Inert until the ad-slots manifest has web_credits_rewarded / mobile_credits_rewarded. See PLAN.md. */ ?>
                        <winupads tag="credits_rewarded" rewarded trigger="#cy-reward-unlock" prompt="Watch a short ad to unlock a reward credit. This is not cash or a loan." prompt-yes="Watch the ad" prompt-no="Not now"></winupads>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <?php
    $archive = new WP_Query(array(
        'post_type' => 'post',
        'posts_per_page' => 4,
        'ignore_sticky_posts' => true,
        'no_found_rows' => true,
    ));
    if ($archive->have_posts()) :
        ?>
    <section class="cy-section" aria-labelledby="cy-archive-title">
        <h2 id="cy-archive-title">From the archive</h2>
        <ul class="cy-list">
            <?php while ($archive->have_posts()) : $archive->the_post(); ?>
            <li>
                <a href="<?php the_permalink(); ?>">
                    <h3><?php the_title(); ?></h3>
                    <p class="cy-meta"><?php echo esc_html(get_the_date('M j, Y')); ?><?php
                        $cats = get_the_category();
                        if ($cats) {
                            echo ' · ' . esc_html($cats[0]->name);
                        }
                    ?></p>
                </a>
            </li>
            <?php endwhile; ?>
        </ul>
    </section>
        <?php
        wp_reset_postdata();
    endif;
    ?>

    <section class="cy-section" id="safety">
        <h2>Before you meet someone</h2>
        <div class="cy-safety">
            <article>
                <h3>Don't send money</h3>
                <p>Gift cards, crypto, and “travel emergencies” are the usual romance-scam ask. The FTC writes this up plainly. Stop the conversation there.</p>
            </article>
            <article>
                <h3>First meeting in public</h3>
                <p>Tell a friend where you are going and use your own way home. You can leave. You don't owe a second location to someone you just met.</p>
            </article>
            <article>
                <h3>Keep the photo small</h3>
                <p>Check who can see your profile before you upload a face. Skip home address, workplace, and daily route until you have a reason.</p>
            </article>
        </div>
    </section>
</main>
<footer class="cy-foot">
    <div class="cy-wrap">
        <nav aria-label="Legal">
            <a href="<?php echo esc_url(home_url('/terms-of-use/')); ?>">Terms of Service</a>
            <a href="<?php echo esc_url(home_url('/privacy-policy/')); ?>">Privacy Policy</a>
            <a href="<?php echo esc_url(home_url('/cookie-policy/')); ?>">Cookie Policy</a>
        </nav>
        <p>Credityup publishes guides. Advertisements on this site are labeled.</p>
    </div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
