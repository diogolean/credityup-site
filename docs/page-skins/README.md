# Page skins (v4) — DRAFT, not installed

Snippet: `shared/snippets/12_cy_page_skins.php` (Code Snippets, "Only run on site front-end", activate after "CY loader v3.2 (US)").

| Page | ID | Skin | What changes |
|---|---|---|---|
| /instant-matchmaking-apps-lp-02/ | 100 | `cy-skin-romance` | Old purple/neon loader ("Curating matches"), purple header progress bar from first paint (CSS only), purple header, pink rewarded popup with the hands image `relacio4.jpg` |
| /digital-marketing-certificate-top-business-schools/ | 187 | `cy-skin-invest` | Body class only; it already uses the site-wide navy/blue standard |

~3 KB of inline CSS on page 100 only, no JS, no extra requests. Does not touch wads.js/ad slots, quiz, GA4, Meta pixel, HFCM.
Backups: `backups/2026-10-30-before-skins/`. Lighthouse (local, mobile, ads blocked, 10 interleaved runs): `lighthouse_final_summary.txt`.
Rollback: deactivate the snippet + LiteSpeed "Purge all".
