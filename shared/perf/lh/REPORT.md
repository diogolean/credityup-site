# Lighthouse mobile, 15/10/2026, from the box (ads always blocked)
See summary.txt and the raw lpX-A/B.json and cX-A.json files.
A = ads blocked. B = A plus api.winup.network, winup-ad-engine and *rewarded* blocked. Run C (Playwright inline strip) was not run.
Key finding: wads.js is a synchronous script in the <head> (line 327). ad-engine.js then calls erp.winup.network/api/public/ad-slots (about 1.7s, finishing around 4.4s), and FCP waits on this chain. Blocking it moves FCP from ~7s to ~2.8s.
