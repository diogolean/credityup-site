# next-fixes-3 — home cards, menu, legal chrome

Status: **prepared, not deployed.** Build this on the live next-fixes-2 / PR #8 files (`home.css?ver=1.0.2`). Do not re-upload a plugin zip. Do not paste leads.php, push.php, lp-meta.php, or any LP / HFCM snippet. Dating LPs are unchanged.

Admin: https://us.credityup.com/winup/
Editor: `https://us.credityup.com/winup/plugin-editor.php?plugin=credityup-platform/credityup-platform.php&file=credityup-platform/`

Full contents to paste are under `next-fixes-3/credityup-platform/` (same relative paths as the plugin). No new plugin files need to be created — legal pages reuse `templates/free-credits.php`, the same way category archives already do. Copies of the five currently live files (PR #8) are in `next-fixes-3/backup-live/`.

This set is safe at every intermediate step: **paste `includes/home.php` first.** Legal pages stay on Hestia until the template contains the `cy-legal` / `credityup_is_legal_page` sentinel. Other categories already use this template live; the new legal branch does not run on them. Templates still have `function_exists` fallbacks.

## What this set changes

- Removes the visible “From the archive” heading from the home, and removes dates from home and category cards.
- Home article cards no longer list the latest posts (currently LP-03 / LP-04 articles). They now query the six current monetizing LP-01 and LP-02 articles, in that order. The two LP hubs stay in the Dating guides section and hero buttons, so they are not duplicated in the grid.
- Category archives still list every post in that topic (including older `*-sec-*` posts). Those URLs return 200 and still monetize; only the dates are removed. Permalinks are unchanged.
- Safety in the menu stays `https://us.credityup.com/#safety`. The home already has `<section id="safety">`. CSS now adds `scroll-margin-top` so the fixed nav does not cover the section.
- Terms, Privacy, and Cookie (`/terms-of-use/`, `/privacy-policy/`, `/cookie-policy/`) switch to the new chrome once the template is pasted. Same URLs, no ad slots in the plugin template. Site-wide HFCM / RAM snippets may still print via `wp_head` / `wp_footer`.
- Empty-slot collapse also sets `display: none !important` on the box, in addition to the `.cy-slot-empty` class.
- Version `1.0.3` so the CSS query string updates.

## Paste order — every step must stay up

Backup the five live files first (Plugin File Editor → copy out, or use `next-fixes-3/backup-live/`). Then paste **entire file contents**. Purge LiteSpeed after the last step (or after any visual check that looks stale). `x-litespeed-cache: hit` is common.

### 1. `includes/home.php`

Must go first. Adds the home-card slug query, drops dates from `credityup_print_archive_card()`, adds the legal-page hooks, and tightens slot collapse. `template_include` will not switch Terms / Privacy / Cookie until the new template is present (`credityup_legal_template_ready()`). Category archives and the home keep using the files already live.

Check:

- `/` returns 200, still the current new home. Cards may still show “From the archive” and dates until step 4, and may still point at LP-03 / LP-04 until then.
- `/category/free-credits/` and `/category/apps/` return **200**, not 500.
- `/terms-of-use/` is still the current Hestia legal page (expected).
- `/best-easy-dating-apps-lp-01/` is unchanged.

### 2. `assets/home.css`

Adds `#safety` / `#rewards` / `#content` scroll margin, hides leftover `.cy-archive-card .cy-meta`, and styles `.cy-legal`. Unused classes are harmless on the current templates.

Check:

- `/` and the category archives still look correct.
- Jumping to `/#safety` from a category page should land with the heading visible below the fixed bar (easier to confirm after a cache purge; the new CSS may still be `?ver=1.0.2` until step 5).

### 3. `templates/free-credits.php`

Shared archive **and** legal template. After this paste, `credityup_legal_template_ready()` becomes true and the three legal URLs switch to this chrome. Category archives lose card dates immediately. `function_exists` fallbacks remain, so this would not 500 even if step 1 had been skipped.

Check:

- `/category/free-credits/` returns **200**. Unlock panel and Rewarded tag unchanged. No dates on cards (the topic is empty today).
- `/category/apps/` (and the other dating categories) still use the new layout, no Rewarded tag, **no dates** on cards, same `/category/<slug>/` URLs.
- `/terms-of-use/`, `/privacy-policy/`, `/cookie-policy/` return **200**, use the new nav/footer, and show the page body in a `.cy-legal` article. No plugin `content1` / Rewarded tags.
- `/best-easy-dating-apps-lp-01/` is unchanged.

### 4. `templates/front-page.php`

Drops the “From the archive” heading and dates, and queries the six LP-01 / LP-02 articles. Has a slug-list fallback if `credityup_home_card_query()` is missing.

Check:

- `/` has no “From the archive” heading and no dates on the cards.
- The grid links to the six articles listed below, not to LP-03 / LP-04 or to `*-sec-*` posts.
- Hero buttons and Dating guides still go to `/best-easy-dating-apps-lp-01/` and `/instant-matchmaking-apps-lp-02/`.
- `#safety` is still on the page. Reward unlock behavior is unchanged: prompt only when the slot is ready; otherwise `#rewards`.
- LP-01 RAM modal still opens.

### 5. `credityup-platform.php`

Version `1.0.3`, so the CSS query string updates. No other behavior change.

Check:

- After a LiteSpeed purge of `/`, `/category/*`, and the three legal URLs (or Purge All), view-source shows `home.css?ver=1.0.3`.

## Destinations (live today, and after this set)

### Menu (already correct; unchanged)

| Item | URL | After this set |
|---|---|---|
| Home | `/` | New chrome (already). |
| Apps | `/category/apps/` | New chrome (already). |
| Flirting | `/category/flirting/` | New chrome (already). |
| Love | `/category/love/` | New chrome (already). |
| Dating | `/category/dating/` | New chrome (already). |
| Relationships | `/category/relationships/` | New chrome (already). |
| Free Credits | `/category/free-credits/` | New chrome (already). |
| Safety | `/#safety` | Still the home section “Before you meet someone”. `/safety/` is a 404 and is **not** linked. |

All eight menu URLs return 200. Category URLs are kept.

### Home cards

Hero / Dating guides (already monetizing LPs, unchanged):

- Easy-to-use apps / first guide → `/best-easy-dating-apps-lp-01/` (200, Hestia LP, ads + RAM + quiz)
- Instant matchmaking / second guide → `/instant-matchmaking-apps-lp-02/` (200, Hestia LP, ads + RAM + quiz)

Home grid **live today** (latest four posts, all 200, Hestia LP-articles, 4 ads + RAM + quiz):

- Recognizing Interest… → `/subtle-signs-interested-lp-04-art03/`
- Navigating Romantic Cues… → `/subtle-signs-interested-lp-04-art02/`
- Decoding Cues… → `/subtle-signs-interested-lp-04-art01/`
- First Date Conversations… → `/first-date-conversation-starters-lp-03-art02/`

Home grid **after this set** (current monetizing LP-01 / LP-02 articles, 200, 6 ads + RAM + quiz):

- `/best-easy-dating-apps-lp-01-art01/`
- `/best-easy-dating-apps-lp-01-art02/`
- `/best-easy-dating-apps-lp-01-art03/`
- `/instant-matchmaking-apps-lp-02-art01/`
- `/instant-matchmaking-apps-lp-02-art02/`
- `/instant-matchmaking-apps-lp-02-art03/`

The LP-03 / LP-04 articles and hubs also return 200 and monetize; they remain reachable from their own pages and from category lists, they are just no longer the home grid.

### Category cards (permalinks unchanged)

These are the posts currently listed in each topic. All return 200.

- Apps: `/subtle-signs-interested-lp-04-art03/`, `/first-date-conversation-starters-lp-03-art01/`, `/profiles-work-united-states-sec-3/`, `/best-easy-dating-apps-lp-01-art03/`
- Flirting: `/first-date-conversation-starters-lp-03-art02/`, `/dating-apps-united-states-sec-4/`, `/best-easy-dating-apps-lp-01-art02/`
- Love: `/first-date-conversation-starters-lp-03-art03/`, `/instant-matchmaking-apps-lp-02-art01/`, `/best-easy-dating-apps-lp-01-art01/`
- Dating: `/subtle-signs-interested-lp-04-art01/`, `/instant-matchmaking-apps-lp-02-art02/`, `/popular-us-apps-sec-1/`
- Relationships: `/subtle-signs-interested-lp-04-art02/`, `/instant-matchmaking-apps-lp-02-art03/`, `/tinder-united-states-sec-2/`
- Free Credits: no posts yet

The `*-sec-*` posts are older Hestia articles (4 ads + RAM + quiz). They are not 404s. Category lists keep them because they belong to those topics.

### Footer

- `/terms-of-use/`, `/privacy-policy/`, `/cookie-policy/` — all 200. Live they are still Hestia; after step 3 they use the new chrome.

## Crawl notes

Crawled home, every menu category, LP-01, LP-02, the six LP-01/02 articles, the LP-03 and LP-04 hubs and articles, the four `*-sec-*` posts, and the three legal pages.

- **No 404s** among links actually present in the home, menu, category cards, LPs, or footer.
- Probed but **not linked** from that chrome: `/category/hookup/` (404) and `/safety/` (404). The menu correctly uses `/#safety`, not `/safety/`.
- Old-theme (Hestia) pages that stay that way on purpose: dating LPs and all LP / `*-sec-*` articles. They are the monetizing article templates.
- Old-theme pages this set moves into the new chrome: the three legal URLs.
- `#safety` exists only on the home, which is where the menu points.

## Rewarded / mobile verification (live next-fixes-2, already done)

Headless Chromium, 12s wait, no ad clicks. Artifacts: `/workspace/cy-deploy/next-fixes-2/verify/` (use the non-`*-error.png` files).

- Home desktop 1366: 1 rewarded GPT request, `/…/us_credityup_com_web_rewarded_20260930`, tag mounted (`data-wads-montado="1"`), not disarmed. Fill was empty (hint: “No ad is ready…”). No console errors.
- Home mobile 390 / iPhone UA: same for `…_mobile_rewarded_20260930`. Tag mounted, not disarmed. No console errors.
- Free Credits desktop and mobile: rewarded requested and mounted, same empty-fill hint.
- `/category/apps/`: no rewarded request (correct).
- LP-01 desktop and mobile: RAM still requests the same rewarded units. No `#cy-reward-unlock` (expected).

The report that home had no rewarded unit after the next-fixes-2 deploy was likely timing: wads.js fetches the manifest asynchronously. No Rewarded fix is included in this set.

Screenshots:

- `/workspace/cy-deploy/next-fixes-2/verify/home-desktop.png`
- `/workspace/cy-deploy/next-fixes-2/verify/home-mobile.png`
- `/workspace/cy-deploy/next-fixes-2/verify/apps-desktop.png`
- `/workspace/cy-deploy/next-fixes-2/verify/apps-mobile.png`
- `/workspace/cy-deploy/next-fixes-2/verify/free-credits-desktop.png`
- `/workspace/cy-deploy/next-fixes-2/verify/free-credits-mobile.png`
- `/workspace/cy-deploy/next-fixes-2/verify/lp01-desktop.png`
- `/workspace/cy-deploy/next-fixes-2/verify/lp01-mobile.png`

Mobile layout: the 8-item nav wraps to two rows (`--cy-bar: 152px`) and the hero is padded below it. Cards, guides, and Safety stack in one column and stay usable. Empty `content1` grey boxes were not visible on the home or Apps desktop shots (collapse is working visually). “From the archive” and card dates are still in those live screenshots; this set removes them.

## Risks

- Pasting `free-credits.php` before `home.php` will not 500 (guards are in the template). Legal pages would stay on Hestia until `home.php` is also updated, because only the new `template_include` maps them.
- Home cards depend on those six slugs remaining published. If one is missing, the grid just shows the ones that exist.
- Legal chrome does not strip site-wide HFCM or RAM output on those pages. It only stops the plugin from printing `content1` / Rewarded there.
- Empty-slot collapse still depends on `slotRenderEnded`. The extra inline `display: none` is a fallback if a theme style wins over `.cy-slot-empty`.
- Do not re-upload a zip of this plugin over the live one.

## Local checks already run

- `php -l` on every PHP file in this set.
- `python3 shared/tests/check_platform.py`
- Stub harness: `php shared/tests/render_platform_templates.php` (new home + new templates, including a legal-page case), plus `php shared/tests/render_platform_isolated.php b` (new home.php + old templates) and `c` (old home.php + new templates — the previous 500 case, still no fatal).
