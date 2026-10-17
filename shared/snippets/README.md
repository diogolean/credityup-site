
## 11_offerwall_pixel_event.php (v3-offerwall-event)
Code Snippet, front-end only, prints in wp_footer. Read-only GPT listeners for ad units containing "offerwall"
(`us.credityup.com_mobile_offerwall_20260930`, `de.credityup.com_mobile_offerwall_20261007`; floor $300, eCPM ~$330):
- `Offerwall_Ready` (rewardedSlotReady), `Offerwall_Shown` {value 0.33 USD, ad_unit} once/page (render non-empty or viewable),
  `Offerwall_Completed` {value 0.33 USD} once/page (rewardedSlotGranted). GA4: `offerwall_shown` / `offerwall_completed`.
- fbq calls queued, retried every 500ms up to 60s (compatible with speed v4 delay). Existing Lead on any rewardedSlotGranted stays.
- Does not alter ad requests, targeting or slot order. Rollback: deactivate the snippet (backup tag v4-pre-offerwall).
