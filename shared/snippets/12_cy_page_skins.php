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
/* Purple header */
body.cy-skin-romance .navbar,body.cy-skin-romance .navbar.navbar-default{background:linear-gradient(90deg,#2c0e34,#5b1a6e)!important}
body.cy-skin-romance .navbar a,body.cy-skin-romance .navbar .navbar-brand{color:#f3d9ff!important}
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
