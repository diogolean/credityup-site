# Snippet 14: CY Tracking (rewarded and offerwall revenue events)

File: `shared/snippets/14_cy_tracking.php`. Tests: `php shared/tests/test_14_tracking.php` (needs node).
Install: Code Snippets, PHP, "Run everywhere", paste without `<?php`. **Not installed yet.**

## Events (all `fbq('trackCustom', …, {eventID})` on the pixel the page already initialises)

| Event | Fires on | Once per |
|---|---|---|
| `CY_Rewarded_View` | GPT `rewardedSlotReady`, non-offerwall rewarded slot | ad unit / page |
| `CY_Rewarded_Complete` | GPT `rewardedSlotGranted`, non-offerwall rewarded slot (reward granted) | ad unit / page |
| `CY_Offerwall_Paid` | GPT `slotRenderEnded` with `isEmpty=false` on an ad unit whose path contains `offerwall` | page |

Params: `block` (last ad-unit segment), `ad_unit`, `page_path`, `utm_source`, `utm_medium`,
`utm_campaign`, `utm_content`, `fbclid`, `event_id`. Rewarded adds `reward_type`/`reward_amount`.
Offerwall adds `price_key`, `line_item_id`, `advertiser_id`, `creative_id`, `backfill` (1 when no line item id, i.e. AdX/backfill).

UTMs and fbclid are stored 30 days in the first-party cookie `cy_attr` (last non-empty value wins per key).

## How the offerwall price rule is detected

From static reading of the Winup loader `wads.js` (`api.winup.network/api/v1/public/kvp/wads.js`):
- The manifest per slot has `cascade` / `cascadesUtm` (conditions on UTMs, `post_id`, `pais`). The first
  matching cascade picks the ad unit path (`cascadePath`/`base`).
- For each step it sets slot targeting `price=<tier>` (key name = manifest `priceKey`, default `price`);
  on no-fill it retries with the next, lower tier. GAM price rules / line items match on that key-value.
- So when the offerwall renders a paid ad, `slot.getTargeting('price')` is the tier that actually filled.
  That is sent as `price_key`, with the GAM `lineItemId` for joining against GAM reports.

Limits:
- The real winning price/eCPM is not exposed client-side. `price_key` is the floor tier that filled, which is a proxy.
- If the manifest renames `priceKey`, update `tgt(ev.slot, 'price')`.
- AdX/backfill fills have `lineItemId=null` (`backfill=1`). For those the floor tier is the only signal.
- I did not query GAM network 23340104016 to map `price` values to unified pricing rule names. Join on `price_key` / `line_item_id` in a GAM report when needed.

## Existing events: what stays
- `Lead` "Rewarded_Ad_Completed(_EN)" (inline pixel block in the LPs) stays. It feeds the current LAL 1% US and campaign optimization. Note that it also fires for offerwall rewarded grants, so it is not a clean "rewarded" signal. That is why the new events filter out offerwall.
- `InitiateCheckout` "Quiz_Option_Clicked" stays (it fires on any button click, so it is noisy).
- `Offerwall_Ready/Shown/Completed` (inline `cy-offerwall-event`) stay. `Offerwall_Shown` fires on any render or viewable event. `CY_Offerwall_Paid` is the stricter, once-per-page paid-fill signal with price context.
- Later, after the new events have data: build a LAL on `CY_Rewarded_Complete` + `CY_Offerwall_Paid` and compare it with the current one. Retire the noisy ones only after that.
