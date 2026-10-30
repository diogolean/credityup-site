<?php
/**
 * Code Snippets: "CY page skins (US)" — one theme (Hestia Pro), one visual skin per page.
 * Settings: "Only run on site front-end". Activate AFTER "CY loader v3.2 (US)".
 *
 *   page 100 instant-matchmaking-apps-lp-02 ............ skin "romance" (old purple look)
 *   page 187 digital-marketing-certificate-top-business-schools  skin "invest" (current navy/blue standard)
 *
 * Every byte below is printed ONLY on the page that owns the skin. No extra HTTP request,
 * no plugin, no builder. Does NOT touch: Winup wads.js / ad slots, the quiz, GA4, Meta pixel, HFCM snippets.
 * Rollback: deactivate this snippet + LiteSpeed "Purge all".
 * To add a page: put its ID in $CY_SKINS.
 */
if (!function_exists('cy_skin_for_current_page')) {
    function cy_skin_for_current_page() {
        $CY_SKINS = array(100 => 'romance', 187 => 'invest');
        if (is_admin() || !is_singular()) return '';
        $id = (int) get_queried_object_id();
        return isset($CY_SKINS[$id]) ? $CY_SKINS[$id] : '';
    }
}
/* Header color for the romance skin: 'pink' (same gradient as the title band, .header-filter-gradient
   #a81d84 -> #ea396f) or 'black'. Change this one word to switch. */
if (!defined('CY_ROMANCE_HEADER')) define('CY_ROMANCE_HEADER', 'black');

add_filter('body_class', function ($c) {
    $s = cy_skin_for_current_page();
    if ($s) $c[] = 'cy-skin-' . $s;
    return $c;
});

/* Priority 2: right after the loader (priority 1), still before any stylesheet/script in <head>. */
add_action('wp_head', function () {
    if (wp_doing_ajax() || is_feed() || (defined('REST_REQUEST') && REST_REQUEST)) return;
    if (cy_skin_for_current_page() !== 'romance') return;
    ?>
<style id="cy-skin-romance">
/* Loader: back to the old v3.1 purple/neon (same DOM as v3.2, colors only) */
#cy-loader{background:radial-gradient(110% 80% at 50% 42%,#2c0e34,#09050d)!important}
#cy-loader .n svg{filter:drop-shadow(0 0 5px #d14cff) drop-shadow(0 0 12px rgba(255,79,216,.5))!important}
#cy-loader circle{stroke:#ff4fd8!important}
#cy-loader .r2 circle{stroke:#b07cff!important}
#cy-loader .c{background:#ff7be6!important;box-shadow:0 0 10px #ff4fd8!important}
#cy-loader:after{content:"Curating matches";margin-top:18px;font:300 9.5px/1 -apple-system,"Helvetica Neue",Helvetica,Arial,sans-serif;letter-spacing:.38em;text-transform:uppercase;color:#f3d9ff;text-shadow:0 0 8px rgba(214,90,255,.7)}
/* Header progress bar: CSS-only (no JS), runs from navigation start (first paint), above the loader, gone by 3s */
html:before{content:"";position:fixed;top:0;left:0;height:4px;width:100%;z-index:2147483001;pointer-events:none;transform-origin:0 50%;background:linear-gradient(90deg,#ff4fd8,#b07cff);box-shadow:0 0 8px rgba(255,79,216,.7);animation:cyBar 2.6s cubic-bezier(.1,.7,.2,1) forwards,cyBarOut .4s ease 2.6s forwards}
@keyframes cyBar{from{transform:scaleX(.08)}to{transform:scaleX(1)}}
@keyframes cyBarOut{to{opacity:0;visibility:hidden}}
/* Header: pink (title-band gradient) or black, chosen by CY_ROMANCE_HEADER */
<?php if (CY_ROMANCE_HEADER === 'black') : ?>
body.cy-skin-romance .navbar,body.cy-skin-romance .navbar.navbar-default{background:#0a0a0a!important;box-shadow:0 1px 0 rgba(255,255,255,.06)!important}
<?php else : ?>
body.cy-skin-romance .navbar,body.cy-skin-romance .navbar.navbar-default{background:linear-gradient(45deg,#a81d84 0,#ea396f 100%)!important;box-shadow:0 2px 10px rgba(120,10,70,.25)!important}
<?php endif; ?>
body.cy-skin-romance .navbar a,body.cy-skin-romance .navbar .navbar-brand{color:#fff!important}
body.cy-skin-romance .navbar .navbar-toggle .icon-bar{background:#fff!important}
/* Relationship (heart) logo, white-text variant (shared/assets/logo_romance_white.webp, 6.6 KB) inline: no extra request.
   The original <img> printed by "CY speed v4" is only hidden, that snippet is untouched. */
body.cy-skin-romance .navbar-brand img{visibility:hidden!important}
body.cy-skin-romance .navbar-brand{background:url(data:image/webp;base64,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) left center/contain no-repeat!important}
/* Rewarded popup: old pink/purple + relationship hands image (relacio4.jpg) */
html body.cy-skin-romance #masterRewarded{background:rgba(44,14,52,.62)!important}
html body.cy-skin-romance #masterRewarded .ramx-header{background:#f6c6d6 url(https://us.credityup.com/wp-content/uploads/2026/10/relacio4.jpg) center/cover no-repeat!important;min-height:140px}
html body.cy-skin-romance #masterRewarded .ramx-header img{display:none!important}
html body.cy-skin-romance #masterRewarded .ramx-badge{color:#cc3876!important;background:#FCE4EC!important}
html body.cy-skin-romance #masterRewarded .ramx-title{color:#0f172a!important}
html body.cy-skin-romance #masterRewarded .ramx-btn{border-color:#e2e8f0!important}
html body.cy-skin-romance #masterRewarded .ramx-btn:hover{border-color:#cc3876!important}
html body.cy-skin-romance #masterRewarded .ramx-btn-arrow{background:#cc3876!important;color:#fff!important}
@media (prefers-reduced-motion:reduce){html:before{animation:cyBarOut .4s ease 2.6s forwards}}
</style>
    <?php
}, 2);

/* Invest skin: page 187 already uses the site-wide navy/blue standard (loader v3.2, navy popup,
   LP187 Wide Layout HFCM). Only a body class is added, so future blue-only rules can target
   body.cy-skin-invest without affecting other pages. No extra CSS is printed. */
