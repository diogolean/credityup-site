# Combined crash-proof set (next-fixes + empty slots + archive cards)

Status: **prepared, not deployed.** This replaces the mid-deploy of `next-fixes/`. Live right now: the `next-fixes` `assets/home.css` is already on the server; every other plugin file was rolled back to the PR #6 originals (backups in `/workspace/cy-deploy/next-fixes/backup-live/`).

The earlier 500 on `/category/free-credits/` happened because `templates/free-credits.php` was pasted while the live `includes/home.php` still lacked `credityup_print_slot()` and `credityup_archive_slots()`. This set is safe at every intermediate step: **paste `includes/home.php` first**, and the templates have `function_exists` fallbacks if a file is pasted out of order.

Admin: https://us.credityup.com/winup/
Editor: `https://us.credityup.com/winup/plugin-editor.php?plugin=credityup-platform/credityup-platform.php&file=credityup-platform/`

Full contents to paste are under `next-fixes-2/credityup-platform/` (same relative paths as the plugin). No new plugin files need to be created. Do not re-upload a plugin zip (push/Firebase on the server may be newer than git). Do not paste leads.php, push.php, lp-meta.php, or any LP / HFCM snippet.

What this set adds on top of `next-fixes/`:
- Crash-proof deploy: `credityup_archive_template_ready()` keeps other category URLs on Hestia until the new `free-credits.php` is present (it looks for the `credityup_print_slot` sentinel). Templates define `credityup_print_slot` / `credityup_archive_slots` themselves if the new `home.php` is not loaded yet.
- Empty `.cy-slot` boxes collapse after GPT `slotRenderEnded` with `isEmpty`, and reopen if a later render fills. Space stays reserved (`min-height: 300px`) while loading. Ad tags and requests are unchanged.
- Home “From the archive” (and category post lists) become a responsive card grid: thumbnail or category gradient, category chip, title, short excerpt, English date (`Sep 30, 2026` via `DateTime::format`, not the `pt_BR` `set 30, 2026`).

## Paste order — every step must stay up

Backup the five live files first (Plugin File Editor → copy out). Then paste **entire file contents**.

### 1. `includes/home.php`

This is the file that must go first. It adds the missing functions, the Rewarded controller script, the slot-collapse script, English dates, and the archive-card helper. `template_include` still uses the current (PR #6) `free-credits.php` for `/category/free-credits/` only. Other categories stay on Hestia until step 3, because the old template does not contain the `credityup_print_slot` sentinel.

Check:
- `/` returns 200 and still looks like the current new home (PR #6 markup is fine).
- `/category/free-credits/` returns **200**, not 500, and still shows the current Free Credits page (old “reserved, not switched on” copy is OK here).
- `/category/apps/` is still the Hestia archive (expected).
- `/best-easy-dating-apps-lp-01/` is unchanged.

### 2. `assets/home.css`

Superset of the CSS already live, plus `.cy-slot-empty` and `.cy-archive-grid` card styles. Unused classes are harmless on the old templates.

Check:
- `/` and `/category/free-credits/` still look correct.
- If a home `content1` request comes back empty, the grey “Advertisement” box should now collapse (the collapse script was already installed in step 1). If it fills, the reserved height stays and the ad shows.

### 3. `templates/free-credits.php`

New shared archive template. After this paste, `credityup_archive_template_ready()` becomes true and **all** `/category/<slug>/` URLs switch to this chrome. `function_exists` fallbacks are in the file, so this would not 500 even if step 1 had been skipped.

Check:
- `/category/free-credits/` returns **200**. New chrome, unlock panel, `tag="rewarded"` + `cooldown="60"`, no “not switched on” copy, `content1` under the hero and `content2` under the list, card grid (empty state is fine).
- `/category/apps/`, `/category/flirting/`, `/category/love/`, `/category/dating/`, `/category/relationships/` use the new layout, post cards, `content1`/`content2`, and **no** Rewarded tag. URLs stay `/category/<slug>/`.
- Dates on the cards are English (`Sep 30, 2026`), not `set 30, 2026`.

### 4. `templates/front-page.php`

Drops `btn-winupads-trigger`, adds `id="cy-reward-hint"`, and replaces the bare “From the archive” list with the card grid. Has a fallback if `credityup_print_archive_card()` is missing.

Check:
- View-source of `/` has `trigger="#cy-reward-unlock"`, `cooldown="60"`, no `btn-winupads-trigger` on the unlock link.
- “From the archive” is a 1-col (mobile) / 2-col / 3-col card grid, with a thumbnail when the post has one (or a category gradient if not), a category chip, title, excerpt, and `Sep 30, 2026`.
- Reward unlock behavior is unchanged from the click fix: prompt only when the slot is ready; otherwise `#rewards`.
- LP-01 RAM modal still opens.

### 5. `credityup-platform.php`

Version `1.0.2`, so the CSS query string updates. No other behavior change.

Check:
- After LiteSpeed purge of `/` and `/category/*` (or Purge All), view-source shows `home.css?ver=1.0.2`.

## Final checks

Incognito, desktop and width under 768px:

- Home `content1` and category `content1`/`content2`: reserved height while loading; the outer `.cy-slot` (label and min-height included) hides on `isEmpty`; it comes back if a later render has fill. One Rewarded request on the home / Free Credits, none on Apps etc.
- LiteSpeed was serving HIT pages; purge before judging.

## Risks

- Pasting `free-credits.php` before `home.php` will no longer 500, but other categories would stay on Hestia until `home.php` is also updated (the live `template_include` only maps Free Credits). Follow the order above.
- Empty-slot collapse depends on `slotRenderEnded`. If GPT never fires the event, the grey box stays (space remains reserved, same as today).
- English dates use `DateTime::format('M j, Y')` (always English). They do not follow a visitor’s locale, which is what we want on this US site.
- Category archives still dequeue Hestia styles once the new template is active. Single posts, tags, and search stay on the old theme.
- Home/Free Credits still share the dating LPs’ Rewarded units; no-fill is still possible. `cooldown="60"` is in-memory per pageview.
- Do not re-upload a zip of this plugin over the live one.

## Local checks already run

- `php -l` on every PHP file in this set.
- `python3 shared/tests/check_platform.py`
- Stub harness (no full WordPress): `php shared/tests/render_platform_templates.php` (new home + new templates), plus isolated mid-deploy cases `php shared/tests/render_platform_isolated.php b` (new home.php + old templates) and `c` (old home.php + new templates — the previous 500 case, now no fatal).
