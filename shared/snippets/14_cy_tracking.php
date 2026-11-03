<?php
/**
 * Snippet 14 — CY Tracking (rewarded + offerwall revenue events with context)
 *
 * Code Snippets plugin: type "PHP", scope "Run everywhere". Paste WITHOUT the opening "<?php" line.
 *
 * Adds two Meta custom events (trackCustom) with block/page/UTM context, on top of the pixel the
 * page already initialises (Pixel DIOGO 2574163523103706 on us., DE pixel on de.). It never calls
 * fbq('init'), never changes pixel settings and does not edit the existing HFCM snippets.
 *
 *  CY_Rewarded_View      rewardedSlotReady   on a NON-offerwall rewarded slot (ad loaded, modal can open)
 *  CY_Rewarded_Complete  rewardedSlotGranted on a NON-offerwall rewarded slot (Google granted the reward)
 *  CY_Offerwall_Paid     slotRenderEnded with isEmpty=false on an offerwall slot (paid fill), once/page.
 *                        Carries price_key = slot targeting "price" set by wads.js (the cascade /
 *                        unified-pricing-rule tier that filled), plus lineItemId/advertiserId/creativeId.
 *
 * Params on every event: block, ad_unit, page_path, utm_source/medium/campaign/content, fbclid,
 * event_id (also passed as {eventID} for future CAPI dedupe). UTMs + fbclid are persisted 30 days in
 * the first-party cookie "cy_attr" (last non-empty touch wins), so later pages keep attribution.
 *
 * Existing events are KEPT (see docs/tracking/README.md): Lead "Rewarded_Ad_Completed(_EN)",
 * InitiateCheckout, Offerwall_Ready/Shown/Completed. Nothing is duplicated under the same name.
 *
 * Perf: ~2 KB inline, no network requests, no DOM work; listeners attach via googletag.cmd.
 * Rollback: deactivate the snippet.
 */

if ( ! defined( 'ABSPATH' ) ) { return; }

if ( ! function_exists( 'cy_trk_script' ) ) {
	function cy_trk_script() {
		return <<<'JS'
<script id="cy-tracking-14">
(function (w, d) {
  if (w.cyTrk14) return; w.cyTrk14 = 1;
  var CK = 'cy_attr', KEYS = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'fbclid'];
  var fired = {}, q = [], timer = null, tries = 0;
  function readCookie() {
    var m = d.cookie.match(/(?:^|;\s*)cy_attr=([^;]*)/);
    try { return m ? JSON.parse(decodeURIComponent(m[1])) : {}; } catch (e) { return {}; }
  }
  function attr() {
    var a = readCookie(), p, changed = false;
    try { p = new URLSearchParams(w.location.search); } catch (e) { return a; }
    KEYS.forEach(function (k) { var v = p.get(k); if (v) { a[k] = v.slice(0, 100); changed = true; } });
    if (changed) {
      d.cookie = CK + '=' + encodeURIComponent(JSON.stringify(a)) + ';path=/;max-age=2592000;SameSite=Lax' +
        (w.location.protocol === 'https:' ? ';Secure' : '');
    }
    return a;
  }
  var A = attr();
  function eid() { return 'cy' + Date.now().toString(36) + Math.random().toString(36).slice(2, 8); }
  function unit(slot) { try { return slot.getAdUnitPath(); } catch (e) { return ''; } }
  function isOW(slot) { return /offerwall/i.test(unit(slot)); }
  function tgt(slot, k) { try { var v = slot.getTargeting(k); return v && v.length ? String(v[0]) : ''; } catch (e) { return ''; } }
  function flush() {
    if (typeof w.fbq === 'function') {
      while (q.length) { var a = q.shift(); try { w.fbq('trackCustom', a[0], a[1], { eventID: a[1].event_id }); } catch (e) {} }
      clearInterval(timer); timer = null; return;
    }
    if (++tries > 120) { clearInterval(timer); timer = null; q = []; }
  }
  function send(name, slot, extra, once) {
    if (once) { if (fired[once]) return; fired[once] = 1; }
    var u = unit(slot), p = {
      block: u.substring(u.lastIndexOf('/') + 1), ad_unit: u, page_path: w.location.pathname,
      event_id: eid()
    };
    KEYS.forEach(function (k) { if (A[k]) p[k] = A[k]; });
    for (var k in extra) if (extra[k] !== null && extra[k] !== undefined && extra[k] !== '') p[k] = extra[k];
    q.push([name, p]); flush();
    if (q.length && !timer) { tries = 0; timer = setInterval(flush, 500); }
  }
  w.cyTrk = { send: send, attr: function () { return A; } };
  w.googletag = w.googletag || { cmd: [] }; w.googletag.cmd = w.googletag.cmd || [];
  w.googletag.cmd.push(function () {
    try {
      var pa = w.googletag.pubads();
      pa.addEventListener('rewardedSlotReady', function (ev) {
        if (!isOW(ev.slot)) send('CY_Rewarded_View', ev.slot, {}, 'rv:' + unit(ev.slot));
      });
      pa.addEventListener('rewardedSlotGranted', function (ev) {
        if (isOW(ev.slot)) return;
        var r = ev.payload || {};
        send('CY_Rewarded_Complete', ev.slot, { reward_type: r.type, reward_amount: r.amount }, 'rc:' + unit(ev.slot));
      });
      pa.addEventListener('slotRenderEnded', function (ev) {
        if (ev.isEmpty || !isOW(ev.slot)) return;
        send('CY_Offerwall_Paid', ev.slot, {
          price_key: tgt(ev.slot, 'price'),
          line_item_id: ev.lineItemId, advertiser_id: ev.advertiserId, creative_id: ev.creativeId,
          backfill: ev.lineItemId ? 0 : 1
        }, 'owp');
      });
    } catch (e) {}
  });
})(window, document);
</script>
JS;
	}
	function cy_trk_print() {
		if ( is_admin() ) { return; }
		echo cy_trk_script() . "\n";
	}
}
add_action( 'wp_head', 'cy_trk_print', 2 );
