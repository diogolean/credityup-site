# Home Rewarded fix (us.credityup.com)

Status: **patch prepared, not deployed.** No live wp-admin change was made.

## Root cause — confirmed

The published home (Credityup redesign, 18/11, plugin `credityup-platform`, template `front-page.php`) prints:

```html
<winupads tag="credits_rewarded" rewarded trigger="#cy-reward-unlock" …>
```

`wads.js` (https://api.winup.network/api/v1/public/kvp/wads.js) maps a `<winupads>` `tag` to a manifest key as `{web|mobile}_` + tag. Device prefix comes from viewport width (`<768` → `mobile`, else `web`), or from a `platform` attribute.

- `tag="credits_rewarded"` looks up `web_credits_rewarded` / `mobile_credits_rewarded`.
- The live manifest (`https://erp.winup.network/api/public/ad-slots?domain=us.credityup.com`) has **neither** key. It only has `web_rewarded` and `mobile_rewarded`.
- If the lookup misses, wads.js `continue`s and never mounts the tag, never defines a GPT Rewarded slot, and never binds the click. `#cy-reward-unlock` is then just an in-page `#rewards` link.

This was intentional in PLAN.md / DEPLOY.md: the home hook was left inert until Winup created a separate credits cascade. Diogo now wants Rewarded on the home to use the **same existing units as the dating LPs**, without waiting for new manifest keys and without touching the LPs.

## How the LP Rewarded path works (unchanged)

On `/best-easy-dating-apps-lp-01/` there is **no** `<winupads rewarded>` tag and **no** `.btn-winupads-trigger` button.

Rewarded fires through the Rewarded Ad Modal plugin (RAM) in `wp_footer`:

- Units: `/23340104016/us_credityup_com/us_credityup_com_web_rewarded_20260930` and `…_mobile_rewarded_20260930` (same paths as manifest `web_rewarded` / `mobile_rewarded`).
- `displayMode: "auto"` — the `#masterRewarded` prompt is shown on page load, then `makeRewardedVisible()`.
- `COOLDOWN_MS: 60000` in `localStorage` (`ram_reward_cd_…`).
- HFCM #4 (`MODE=reload`) only rewrites `viralnetwork.com.br` button redirects to the current URL. It does not create the slot. The `LP_CLICK` list in that snippet is the gifmoney-style insurance/certificate slugs, not the dating LPs.

The home has RAM CSS (via the sitewide skin) but **no** `#masterRewarded` element and **no** RAM script, so the auto-popup does not run there. That is good: this fix must not add a load-time popup on the home.

The string `<winupads rewarded trigger=".btn-winupads-trigger">` appears on the home only inside an HFCM #4 **comment**. It is a real tag on some gifmoney-format posts (see the 18/11 WP export), not a sitewide injection from winup-ad-engine v1.3.0. The plugin's `ad-engine.js` only lazy-loads blocks; it does not invent Rewarded tags. Confirmed against the live home HTML: the only Rewarded `<winupads>` on `/` is the inert `credits_rewarded` tag.

## Tag mapping in wads.js (relevant bits)

- Empty `tag` + `rewarded` attribute → position defaults to `rewarded` → `web_rewarded` / `mobile_rewarded`.
- `tag="rewarded"` does the same, explicitly.
- Only **one** Rewarded controller is allowed (`if (L) return null`). First successfully mapped Rewarded tag wins.
- `trigger` is a CSS selector; click is captured on `document` in the capture phase.
- `cooldown` is seconds, in-memory (`L.ate`). After close/grant, the next click within that window is not intercepted. This is **not** RAM's `localStorage` 60s, but it is the hook wads.js exposes.

## Exact before / after (home only)

File: `shared/plugins/credityup-platform/templates/front-page.php` (live: `wp-content/plugins/credityup-platform/templates/front-page.php`).

**Before**

```html
<p>Rewards here are credits you choose to unlock by watching a short advertisement. They are not cash, a loan, a credit card, or a spending limit. This block is ready for that unlock. It is not switched on, so watching an ad on this page does not grant a credit yet.</p>
<p><a href="<?php echo esc_url(credityup_free_credits_url()); ?>">Open the Free Credits topic</a></p>
<div class="cy-reward-mount" id="cy-reward-mount">
    <p>The Rewarded and Offerwall unlock will sit in this space. The control stays on this page.</p>
    <a id="cy-reward-unlock" href="#rewards">Reward unlock</a>
    <?php /* Inert until the ad-slots manifest has web_credits_rewarded / mobile_credits_rewarded. See PLAN.md. */ ?>
    <winupads tag="credits_rewarded" rewarded trigger="#cy-reward-unlock" prompt="Watch a short ad to unlock a reward credit. This is not cash or a loan." prompt-yes="Watch the ad" prompt-no="Not now"></winupads>
</div>
```

**After**

```html
<p>Rewards here are credits you choose to unlock by watching a short advertisement. They are not cash, a loan, a credit card, or a spending limit.</p>
<p><a href="<?php echo esc_url(credityup_free_credits_url()); ?>">Open the Free Credits topic</a></p>
<div class="cy-reward-mount" id="cy-reward-mount">
    <p>Watch a short ad to unlock a reward credit. The control stays on this page.</p>
    <a id="cy-reward-unlock" class="btn-winupads-trigger" href="#rewards">Reward unlock</a>
    <?php /* Maps to manifest web_rewarded / mobile_rewarded, the same units as the dating LPs. cooldown=60 matches the RAM 60s pause. */ ?>
    <winupads tag="rewarded" rewarded trigger="#cy-reward-unlock" cooldown="60" prompt="Watch a short ad to unlock a reward credit. This is not cash or a loan." prompt-yes="Watch the ad" prompt-no="Not now"></winupads>
</div>
```

Also, one CSS line in `assets/home.css`: keep hiding the custom element (wads.js also sets `display:none`):

```css
winupads[tag="credits_rewarded"],
winupads[tag="rewarded"] { display: none; }
```

The unlock control stays an `<a>` (not `<button>`), so HFCM #1 still does not fire `InitiateCheckout` on this click. The trigger remains `#cy-reward-unlock`. The extra class is only compatibility with the gifmoney button selector; it does not add a second slot.

Copy change is only the “not switched on” wording, which would be false once the slot fires. Legal “not a loan / not cash” lines stay.

## What was not changed

- Dating LPs, articles, HFCM #1–#5, winup-ad-engine, RAM modal, theme, menu.
- `templates/free-credits.php` still has the inert `tag="credits_rewarded"` hook. Same bug, left for a later one-by-one pass.
- No new GAM / Winup manifest keys. No credit ledger (watching the ad is still the only “unlock”; nothing grants a stored credit).

## Deploy steps (wp-admin) — do this only after approval

Admin: https://us.credityup.com/winup/

Preferred path (smallest surface): edit the two plugin files in place. Do **not** re-upload the whole `credityup-platform` zip unless you have confirmed the live plugin has no newer changes (push, leads, Firebase).

1. Backup the live files first. Plugin File Editor → `credityup-platform` → copy out:
   - `templates/front-page.php`
   - `assets/home.css`
   Save them locally (this folder can hold the copies).
2. Apply the after-snippet above to `templates/front-page.php`, and the one CSS selector change to `assets/home.css`.
   Direct URLs (when logged in):
   - `https://us.credityup.com/winup/plugin-editor.php?file=credityup-platform/templates/front-page.php&plugin=credityup-platform/credityup-platform.php`
   - `https://us.credityup.com/winup/plugin-editor.php?file=credityup-platform/assets/home.css&plugin=credityup-platform/credityup-platform.php`
3. Do not open or save HFCM snippets, Winup Ad Engine blocks, or LP post content.
4. LiteSpeed Cache: purge the homepage. Live `/` is currently a LiteSpeed HIT (`x-litespeed-cache: hit`). Cloudflare is `DYNAMIC`, so LS is the one that matters. Purge `/` or Purge All if `/` still shows `tag="credits_rewarded"`.
5. Verify on a fresh incognito window, desktop and a width &lt; 768:
   - View-source of `/` shows `tag="rewarded"` and `trigger="#cy-reward-unlock"`.
   - Click **Reward unlock** → confirm prompt (“Watch a short ad…”) → Yes → GPT Rewarded for `us_credityup_com_web_rewarded_20260930` or `…_mobile_rewarded_20260930`.
   - DevTools: only one Rewarded `defineOutOfPageSlot` on the home (no `#masterRewarded`).
   - Reload `/best-easy-dating-apps-lp-01/` and confirm the RAM auto modal still appears (LPs untouched).
   - Quiz, `content1`, Offerwall, interstitial unchanged.

## Risks

- Home now shares the dating LPs’ Rewarded inventory. Reporting mixes home-click and LP-auto traffic on the same units. Fill on the home depends on existing demand for those units (better than a missing key, but not a dedicated credits cascade).
- wads.js `cooldown="60"` is per pageview, in memory. RAM’s 60s is `localStorage` and is not on the home. A reload can request Rewarded again immediately.
- GPT allows one Rewarded slot at a time. The home has no RAM, so this tag is the only claimant on `/`. If someone later injects a sitewide `<winupads rewarded trigger=".btn-winupads-trigger">` *before* this tag in the DOM, wads.js will keep only the first one; the class on the unlock link is the fallback so the button would still fire.
- There is still no credit-granting backend. The prompt language should not be read as a cash/loan product; the legal lines remain.
- `free-credits.php` stays inert until a follow-up.
- The empty `content1` on the home (request goes out, often no fill) is a separate issue and is not addressed here.

## Repo

Unified diff: `home-rewarded.patch`. Tests: `python3 shared/tests/check_platform.py` (updated to expect `tag="rewarded"` and `cooldown="60"` on the home only).

Branch from the published redesign (`cursor/home-lp-push-leads-4837`, PR #4), not from `main` (PR #4 is still unmerged) and not from the A/B branch (PR #5).
