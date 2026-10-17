<?php
/**
 * Code Snippets: CY offerwall pixel event (us + de credityup). Settings: "Only run on site front-end".
 * Read-only GPT listeners for slots whose ad unit path contains "offerwall":
 *   rewardedSlotReady   -> fbq trackCustom Offerwall_Ready
 *   slotRenderEnded (!isEmpty) / impressionViewable -> Offerwall_Shown {value:0.33,currency:USD,ad_unit} (once/page)
 *   rewardedSlotGranted -> Offerwall_Completed {value:0.33,currency:USD} (once/page)
 * Also sends offerwall_shown / offerwall_completed to GA4 via gtag (if present).
 * Does NOT touch ad requests, targeting, slot order, wads.js or the auction. fbq calls are queued
 * (retry every 500ms up to 60s) so they survive the speed v4 pixel delay.
 */
if (!is_admin()) {
add_action('wp_footer', function () {
?>
<script id="cy-offerwall-event">
(function (w) {
  if (w.cyOfferwallEv) return; w.cyOfferwallEv = 1;
  var VAL = 0.33, CUR = 'USD', fired = {}, q = [], tries = 0, timer = null;
  function isOW(slot) {
    try { return /offerwall/i.test(slot.getAdUnitPath()); } catch (e) { return false; }
  }
  function unitOf(slot) {
    try { var p = slot.getAdUnitPath(); return p.substring(p.lastIndexOf('/') + 1); } catch (e) { return ''; }
  }
  function flush() {
    if (typeof w.fbq === 'function') {
      while (q.length) { var a = q.shift(); try { w.fbq('trackCustom', a[0], a[1]); } catch (e) {} }
      clearInterval(timer); timer = null; return;
    }
    if (++tries > 120) { clearInterval(timer); timer = null; q = []; }
  }
  function send(fbName, gaName, params, onceKey) {
    if (onceKey) { if (fired[onceKey]) return; fired[onceKey] = 1; }
    q.push([fbName, params || {}]);
    flush();
    if (q.length && !timer) { tries = 0; timer = setInterval(flush, 500); }
    try {
      if (gaName && typeof w.gtag === 'function') {
        var g = { ad_unit: (params && params.ad_unit) || '' };
        if (params && params.value) { g.value = params.value; g.currency = params.currency; }
        w.gtag('event', gaName, g);
      }
    } catch (e) {}
  }
  w.googletag = w.googletag || { cmd: [] };
  w.googletag.cmd = w.googletag.cmd || [];
  w.googletag.cmd.push(function () {
    try {
      var pa = w.googletag.pubads();
      pa.addEventListener('rewardedSlotReady', function (ev) {
        if (isOW(ev.slot)) send('Offerwall_Ready', null, { ad_unit: unitOf(ev.slot) }, 'ready');
      });
      function shown(ev) {
        if (isOW(ev.slot)) send('Offerwall_Shown', 'offerwall_shown', { value: VAL, currency: CUR, ad_unit: unitOf(ev.slot) }, 'shown');
      }
      pa.addEventListener('slotRenderEnded', function (ev) { if (!ev.isEmpty) shown(ev); });
      pa.addEventListener('impressionViewable', shown);
      pa.addEventListener('rewardedSlotGranted', function (ev) {
        if (isOW(ev.slot)) send('Offerwall_Completed', 'offerwall_completed', { value: VAL, currency: CUR, ad_unit: unitOf(ev.slot) }, 'done');
      });
      pa.addEventListener('rewardedSlotClosed', function (ev) { /* read-only; no event needed */ });
    } catch (e) {}
  });
})(window);
</script>
<?php
}, 99);
}
