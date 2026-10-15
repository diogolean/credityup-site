# credityup perf diagnosis 2026-10-15 (read-only)
- Server LiteSpeed, NO page cache (TTFB ~1.8-2.0s, no x-litespeed-cache header), no CDN/Cloudflare. Static cache max-age 12h.
- Theme Hestia Pro; plugins seen: winup-ad-engine, safelayout-cute-preloader 2.3.0 (+Rewarded Ads, HFCM). No cache/optimization plugin.
- Loader = Safelayout Cute Preloader: fullscreen z-index 9999999, closes on window.load (min 500ms, max 9000ms, close button 5s). window.load waits for gpt.js/ad iframes/images -> holds screen up to 9s => LCP ~10s.
- Render-blocking head: gpt.js, winup wads.js, bootstrap.min.css, font-sizes.min.css, style.min.css, Google Fonts Roboto+Roboto Slab (6 weights), jquery+migrate, gtag.
- Images: logo PNG 1254x837 380KB(de)/497KB(us) no width/height on de; thumbs 360x240 PNG 100-140KB each (x12 ~1.5MB); us LP image relacio4.jpg via http:// (data-ram-img-src).
Fix plan: see final report.
