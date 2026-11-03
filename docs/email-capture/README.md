# Email capture (snippet 13) — post-quiz lead form

File: `shared/snippets/13_cy_email_capture.php` · Tests: `shared/tests/test_13_email_capture.php`

## What it does
- **Where:** only page **100** (`/instant-matchmaking-apps-lp-02/`). The form is printed hidden in `wp_footer`, then moved directly **below `#qwu-quiz-en`** and shown only when the quiz sets `data-qwu-phase="result"` ("We found 3 highly compatible platforms!"). Before that it is `hidden`, so it adds nothing to LCP/CLS on load and never covers content (no popup). Pages list is filterable via `cy_ec_pages`.
- **Fields:** email + required consent checkbox "I agree to receive offers by email" + hidden honeypot `website`.
- **Submit:** `fetch` POST `/wp-json/cy/v1/lead`. A fresh nonce is pulled from `GET /wp-json/cy/v1/token` when the form appears (cached HTML can't hold a valid nonce). Both routes send `no-cache` + `X-LiteSpeed-Cache-Control: no-cache`.
- **Protection:** honeypot (silent drop), WP nonce, rate limit 5 submits / 10 min per IP hash, server-side email + consent validation, CSV-injection escaping on export.
- **Storage:** `wp_cy_leads` (created by dbDelta on first wp-admin load): email (unique → dedupe), consent, consent_text, page_id, page_url, utm_source/medium/campaign/content/term (URL first, then `utm_*` cookies; first-touch kept on duplicates), ip_hash (HMAC-SHA256 with WP salt — raw IP never stored), user_agent, submissions, created_at/updated_at (UTC).
- **Admin:** wp-admin → **CY Leads** (`manage_options`): search, filter by page + date range, total count, CSV export (respects filters), bulk delete.
- On success fires Meta Pixel `Lead` (content_name `cy_email_capture`) if `fbq` exists.

## Size / performance
- Inline JS ≈ 1.8 KB raw (~0.9 KB gzip), CSS+HTML ≈ 1.4 KB, only on page 100. No external requests on load; 1 tiny REST GET only after quiz completion. Script is at the end of `<body>` (inline scripts can't use `defer`; footer placement gives the same effect).
- Does not touch quiz code: it only observes the `data-qwu-phase` attribute.

## Install (when approved)
1. Code Snippets → Add New → type PHP, "Run everywhere". Paste the file content **without** the first `<?php` line. Title: `13 CY Email Capture`.
2. Activate, then open any wp-admin page once (creates the table). Check **CY Leads** appears.
3. LiteSpeed Cache → Purge page 100. (REST routes are already no-cache.)
4. Test in incognito: finish the quiz, submit an email, see it in CY Leads. Delete the test row.

## Rollback
Deactivate the snippet → form, routes and admin page disappear immediately; purge LiteSpeed for page 100. Data is kept. Full wipe: `DROP TABLE wp_cy_leads; DELETE FROM wp_options WHERE option_name='cy_ec_db_ver';`

## Connecting an email provider later
Hook: `do_action( 'cy_lead_captured', int $lead_id, array $lead, bool $is_new )`. Add a second snippet, e.g. Brevo:
```php
add_action( 'cy_lead_captured', function ( $id, $lead, $is_new ) {
	if ( ! $is_new || ! defined( 'CY_BREVO_KEY' ) ) return; // key in wp-config.php, never in the repo
	wp_remote_post( 'https://api.brevo.com/v3/contacts', array(
		'blocking' => false, 'timeout' => 3,
		'headers'  => array( 'api-key' => CY_BREVO_KEY, 'content-type' => 'application/json' ),
		'body'     => wp_json_encode( array( 'email' => $lead['email'], 'listIds' => array( 2 ), 'updateEnabled' => true,
			'attributes' => array( 'UTM_SOURCE' => $lead['utm_source'], 'UTM_CAMPAIGN' => $lead['utm_campaign'] ) ) ),
	) );
}, 10, 3 );
```
Mailchimp: `PUT https://<dc>.api.mailchimp.com/3.0/lists/<list_id>/members/<md5(lowercase email)>` with `status_if_new: "subscribed"` (or `"pending"` for double opt-in). For volume, push via WP-Cron/Action Scheduler instead of in-request. Or export CSV from CY Leads and import manually.

## Consent / compliance (not legal advice)
- **GDPR (DE/EU) & LGPD (BR):** consent must be free, specific, unticked by default (it is), recorded with text + timestamp (stored: `consent_text`, `created_at`). Link a privacy policy (form links WP's privacy page — make sure it exists and mentions email marketing + the ESP). Honor deletion requests (CY Leads → delete) and unsubscribe. EU: prefer double opt-in in the ESP.
- **CAN-SPAM (US):** every email needs a working unsubscribe honored within 10 business days, a physical postal address, non-deceptive subject/from.
- Don't send to leads without `consent=1`; don't buy/merge lists into this table.
