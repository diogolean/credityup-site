# Next fixes — home Rewarded click, Free Credits, category archives

Status: **prepared, not deployed.** Paste these files in the WP Plugin File Editor. Do not re-upload the whole plugin zip (push / Firebase on the live plugin may have diverged).

WordPress paths are under `wp-content/plugins/credityup-platform/`. Full contents to paste are in `next-fixes/credityup-platform/` (same relative paths). No new files are required: `templates/free-credits.php` is reused as the archive template so the editor can overwrite it.

Admin: https://us.credityup.com/winup/
Editor base: `https://us.credityup.com/winup/plugin-editor.php?plugin=credityup-platform/credityup-platform.php&file=credityup-platform/`

## Why the home click showed nothing

`wads.js` keeps a single Rewarded controller `L`. On click (capture phase) it only calls `preventDefault` when `L.mostrar` is set. That function is assigned in `rewardedSlotReady` (`makeRewardedVisible`). If the slot has not filled yet, or `slotRenderEnded` comes back empty, `L.mostrar` stays null. The handler then does nothing, and `#cy-reward-unlock` behaves like a normal link (`href="#rewards"`). That is the jump you saw. There is no confirm prompt, because the prompt is only built after a ready slot.

Rewarded on this domain is a single shot: the cascade retry in `wads.js` is skipped when `e.rewarded` is true, and the us.credityup manifest still has no `cascadesUtm` on `web_rewarded` / `mobile_rewarded`. So no-fill is common, and a click before ready looks like a broken button.

The 8s timer (`L.limite`) only starts after the user confirms and `E()` runs. It is not a wait before the first click.

About the global `<winupads rewarded trigger=".btn-winupads-trigger">`: it is not a real element in the logged-out HTML of `/` (only a comment inside HFCM #4, plus the class we had added to the unlock link). If that tag is injected (Winup Ad Engine block, logged-in view, later footer print), `wads.js` gives the controller to the first successfully mapped Rewarded tag (`if (L) return null`). A first global tag would bind `.btn-winupads-trigger`, ignore our `#cy-reward-unlock` trigger, and would have no prompt and no `cooldown="60"`. The previous patch added that class as a fallback, which would also make a late global tag own the home button. This patch drops the class and disarms any Rewarded `<winupads>` that is not `trigger="#cy-reward-unlock"` before `wads.js` mounts, on Credityup chrome pages only (home and category archives — not dating LPs).

The visit to `/category/free-credits/` on mobile is not caused by the unlock `href` (it stays `#rewards`). The “Open the Free Credits topic” link sits just above the button; after a no-fill click the hash jump can make that link easy to hit.

This patch does **not** invent a second Rewarded controller and does **not** `preventDefault` when no ad is ready. Fallback remains `#rewards`. `cooldown="60"` stays on our tag only.

## Files to paste (existing plugin files only)

| # | Plugin File Editor file | Box copy |
|---|-------------------------|----------|
| 1 | `assets/home.css` | `next-fixes/credityup-platform/assets/home.css` |
| 2 | `templates/free-credits.php` | `next-fixes/credityup-platform/templates/free-credits.php` |
| 3 | `includes/home.php` | `next-fixes/credityup-platform/includes/home.php` |
| 4 | `templates/front-page.php` | `next-fixes/credityup-platform/templates/front-page.php` |
| 5 | `credityup-platform.php` | `next-fixes/credityup-platform/credityup-platform.php` |

Do not paste `leads.php`, `push.php`, `lp-meta.php`, or any LP / HFCM snippet.

## Deploy order and what to check after each step

Backup the five live files first (Plugin File Editor → copy out). Then paste **entire file contents**, not a partial snippet.

1. **`assets/home.css`** — extra archive / nav / pager rules only. Safe on the current home.
   - Check: `/` still looks like the new home (nav, cards, reward panel). No unstyled jump.
2. **`templates/free-credits.php`** — still hooked only for `/category/free-credits/` until step 3, so other categories stay on Hestia for this moment. The Free Credits page gains working Rewarded (`tag="rewarded"`, `cooldown="60"`), `content1` / `content2`, and the “not switched on” copy is removed.
   - Check: `/category/free-credits/` uses the new chrome, shows the unlock panel, `content1` under the hero and `content2` under the (empty) article list. View-source has `tag="rewarded"` and no `credits_rewarded`. `/category/apps/` is still the old theme, which is expected here.
3. **`includes/home.php`** — this is the switch: all `is_category()` URLs use the new archive template, home.css is enqueued there, the early `cy-rewarded-controller` script is printed, nav gets `aria-current`.
   - Check: `/category/apps/`, `/category/flirting/`, `/category/love/`, `/category/dating/`, `/category/relationships/` use the new layout, post cards, `content1` and `content2`, and **no** `#cy-reward-unlock` / Rewarded tag. URL stays `/category/<slug>/`. `/category/free-credits/` still has the unlock panel. `/` still loads. Dating LPs (`/best-easy-dating-apps-lp-01/`) still show the RAM modal and are visually unchanged.
4. **`templates/front-page.php`** — removes `class="btn-winupads-trigger"` and adds `id="cy-reward-hint"`.
   - Check: view-source of `/` has `trigger="#cy-reward-unlock"`, `cooldown="60"`, no `btn-winupads-trigger` on the unlock link.
5. **`credityup-platform.php`** — version `1.0.1` so the CSS query string updates. Does not change plugin behavior otherwise.
6. **LiteSpeed:** purge `/` and `/category/*` (or Purge All). Live pages are cache HITs.

### Final Rewarded check (home and Free Credits)

In incognito, desktop and a width under 768px:

- On load, one GPT Rewarded request for `us_credityup_com_web_rewarded_20260930` or `…_mobile_rewarded_20260930` (not two).
- If `rewardedSlotReady` fires, click **Reward unlock** → confirm prompt (“Watch a short ad…”) → the ad. Default navigation is blocked only in this case.
- If the slot is empty or still waiting, the click is **not** blocked and the page stays on `#rewards`. The hint under the button should change to “No ad is ready right now…” after an empty `slotRenderEnded`. It must not go to `/category/free-credits/` by itself.
- `/best-easy-dating-apps-lp-01/` RAM auto modal still opens. Do not edit HFCM, Winup Ad Engine, or LP posts.

## Risks

- Home and Free Credits share the dating LPs’ Rewarded units. No-fill will still happen until Winup adds a cascade; the click will then fall back to `#rewards` instead of appearing broken, but no ad will play.
- `cooldown="60"` is in-memory in `wads.js` (pageview only), not RAM’s `localStorage` 60s.
- The disarm script runs only on `is_front_page()` and `is_category()`. Gifmoney posts and dating LPs are untouched. If a sitewide Rewarded tag is required on a category archive, this script will disarm it there on purpose (no Rewarded on those archives).
- Category archives now dequeue Hestia/Bootstrap like the home. Single posts, tags, and search still use the old theme.
- `content1` on the home (and possibly these archives) can still return empty fill; that is a separate inventory issue.
- Replacing `includes/home.php` in the wrong order (before the new `free-credits.php`) would briefly render every category with the old Free Credits “not switched on” page. Follow the order above.
- Do not re-upload a zip of this plugin over the live one: push/Firebase files on the server may be newer than git.
