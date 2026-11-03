<?php
/**
 * Snippet 13 — CY Email Capture (lightweight, post-quiz)
 *
 * Code Snippets plugin: type "PHP", scope "Run everywhere". Paste WITHOUT the opening "<?php" line.
 *
 * What it does
 *  - Front: on page 100 (instant-matchmaking-apps-lp-02) prints a tiny hidden form in the footer.
 *    A deferred inline script moves it right below #qwu-quiz-en and reveals it ONLY when the quiz
 *    reaches data-qwu-phase="result". Nothing is visible before that -> no LCP / CLS impact on load,
 *    no popup, never blocks content, no external libraries.
 *  - Submit: fetch() POST to REST /wp-json/cy/v1/lead. Nonce is fetched fresh from
 *    /wp-json/cy/v1/token only when the form is revealed (LiteSpeed-cached HTML would hold stale nonces).
 *    Honeypot + nonce + per-IP rate limit (5 / 10 min). Dedupe by email (unique key).
 *  - Storage: custom table {$wpdb->prefix}cy_leads.
 *  - Admin: "CY Leads" menu (manage_options): search, page/date filters, count, CSV export, delete.
 *  - Hook for future ESP integration: do_action( 'cy_lead_captured', $lead_id, $lead, $is_new ).
 *
 * Rollback: deactivate the snippet (table + data stay). To wipe data: DROP TABLE wp_cy_leads;
 *           DELETE FROM wp_options WHERE option_name = 'cy_ec_db_ver';
 */

if ( ! defined( 'ABSPATH' ) ) { return; }

if ( ! defined( 'CY_EC_DB_VER' ) ) {
	define( 'CY_EC_DB_VER', '1' );
	define( 'CY_EC_CONSENT_TEXT', 'I agree to receive offers by email' );
	define( 'CY_EC_RATE_MAX', 5 );        // submissions…
	define( 'CY_EC_RATE_WINDOW', 600 );   // …per 10 minutes per IP hash
}

/** Pages where the form is shown. Filterable: add_filter( 'cy_ec_pages', fn( $p ) => array_merge( $p, [ 123 ] ) ); */
function cy_ec_pages() {
	return array_map( 'intval', (array) apply_filters( 'cy_ec_pages', array( 100 ) ) );
}

function cy_ec_table() {
	global $wpdb;
	return $wpdb->prefix . 'cy_leads';
}

/* ------------------------------------------------------------------ DB */
function cy_ec_install() {
	if ( get_option( 'cy_ec_db_ver' ) === CY_EC_DB_VER ) { return; }
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	$t  = cy_ec_table();
	$cs = $wpdb->get_charset_collate();
	dbDelta( "CREATE TABLE $t (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  email varchar(190) NOT NULL,
  consent tinyint(1) NOT NULL DEFAULT 0,
  consent_text varchar(255) NOT NULL DEFAULT '',
  page_id bigint(20) unsigned NOT NULL DEFAULT 0,
  page_url varchar(255) NOT NULL DEFAULT '',
  utm_source varchar(100) NOT NULL DEFAULT '',
  utm_medium varchar(100) NOT NULL DEFAULT '',
  utm_campaign varchar(150) NOT NULL DEFAULT '',
  utm_content varchar(150) NOT NULL DEFAULT '',
  utm_term varchar(150) NOT NULL DEFAULT '',
  ip_hash char(64) NOT NULL DEFAULT '',
  user_agent varchar(255) NOT NULL DEFAULT '',
  submissions int(10) unsigned NOT NULL DEFAULT 1,
  created_at datetime NOT NULL,
  updated_at datetime NOT NULL,
  PRIMARY KEY  (id),
  UNIQUE KEY email (email),
  KEY page_id (page_id),
  KEY created_at (created_at)
) $cs;" );
	update_option( 'cy_ec_db_ver', CY_EC_DB_VER, false );
}
add_action( 'admin_init', 'cy_ec_install' );

/* ------------------------------------------------------------------ Core (pure-ish, unit-tested) */
function cy_ec_ip_hash( $ip ) {
	return hash_hmac( 'sha256', (string) $ip, wp_salt( 'auth' ) );
}

/**
 * Validate + store one lead. Returns array( 'ok' => bool, 'code' => string, 'id' => int ).
 * $in = request params, $ctx = array( 'ip' => ..., 'ua' => ..., 'cookies' => $_COOKIE ).
 */
function cy_ec_process( array $in, array $ctx ) {
	global $wpdb;

	// Honeypot: bots fill "website". Pretend success, store nothing.
	if ( ! empty( $in['website'] ) ) { return array( 'ok' => true, 'code' => 'ok', 'id' => 0 ); }

	$email = strtolower( trim( sanitize_email( (string) ( $in['email'] ?? '' ) ) ) );
	if ( ! $email || ! is_email( $email ) || strlen( $email ) > 190 ) {
		return array( 'ok' => false, 'code' => 'invalid_email', 'id' => 0 );
	}
	if ( empty( $in['consent'] ) ) {
		return array( 'ok' => false, 'code' => 'consent_required', 'id' => 0 );
	}
	$page_id = absint( $in['page_id'] ?? 0 );
	if ( ! in_array( $page_id, cy_ec_pages(), true ) ) {
		return array( 'ok' => false, 'code' => 'bad_page', 'id' => 0 );
	}

	$ip_hash = cy_ec_ip_hash( $ctx['ip'] ?? '' );
	$rk      = 'cy_ec_rl_' . substr( $ip_hash, 0, 32 );
	$hits    = (int) get_transient( $rk );
	if ( $hits >= CY_EC_RATE_MAX ) {
		return array( 'ok' => false, 'code' => 'rate_limited', 'id' => 0 );
	}
	set_transient( $rk, $hits + 1, CY_EC_RATE_WINDOW );

	$cookies = (array) ( $ctx['cookies'] ?? array() );
	$utm     = array();
	foreach ( array( 'source' => 100, 'medium' => 100, 'campaign' => 150, 'content' => 150, 'term' => 150 ) as $k => $max ) {
		$v = $in[ 'utm_' . $k ] ?? ( $cookies[ 'utm_' . $k ] ?? '' );
		$utm[ 'utm_' . $k ] = substr( sanitize_text_field( (string) $v ), 0, $max );
	}

	$now  = current_time( 'mysql', true ); // UTC
	$lead = array_merge( array(
		'email'        => $email,
		'consent'      => 1,
		'consent_text' => CY_EC_CONSENT_TEXT,
		'page_id'      => $page_id,
		'page_url'     => substr( esc_url_raw( (string) ( $in['page_url'] ?? '' ) ), 0, 255 ),
		'ip_hash'      => $ip_hash,
		'user_agent'   => substr( sanitize_text_field( (string) ( $ctx['ua'] ?? '' ) ), 0, 255 ),
		'updated_at'   => $now,
	), $utm );

	$t        = cy_ec_table();
	$existing = (int) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $t WHERE email = %s", $email ) );
	if ( $existing ) {
		// Dedupe: keep first-touch UTMs/created_at, refresh consent + counters.
		$wpdb->query( $wpdb->prepare(
			"UPDATE $t SET consent = 1, consent_text = %s, updated_at = %s, submissions = submissions + 1 WHERE id = %d",
			CY_EC_CONSENT_TEXT, $now, $existing
		) );
		$id = $existing; $is_new = false;
	} else {
		$lead['created_at'] = $now;
		if ( false === $wpdb->insert( $t, $lead ) ) {
			// Race on the unique key -> treat as duplicate.
			$id = (int) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $t WHERE email = %s", $email ) );
			if ( ! $id ) { return array( 'ok' => false, 'code' => 'db_error', 'id' => 0 ); }
			$is_new = false;
		} else {
			$id = (int) $wpdb->insert_id; $is_new = true;
		}
	}

	/** Future ESP hook (Brevo/Mailchimp): see docs/email-capture/README.md */
	do_action( 'cy_lead_captured', $id, $lead, $is_new );

	return array( 'ok' => true, 'code' => 'ok', 'id' => $id );
}

/* ------------------------------------------------------------------ REST */
function cy_ec_nocache() {
	if ( ! headers_sent() ) {
		nocache_headers();
		header( 'X-LiteSpeed-Cache-Control: no-cache' );
	}
}

add_action( 'rest_api_init', function () {
	register_rest_route( 'cy/v1', '/token', array(
		'methods'             => 'GET',
		'permission_callback' => '__return_true',
		'callback'            => function () {
			cy_ec_nocache();
			return array( 't' => wp_create_nonce( 'cy_ec_lead' ) );
		},
	) );
	register_rest_route( 'cy/v1', '/lead', array(
		'methods'             => 'POST',
		'permission_callback' => '__return_true',
		'callback'            => function ( WP_REST_Request $r ) {
			cy_ec_nocache();
			if ( ! wp_verify_nonce( (string) $r->get_param( 't' ), 'cy_ec_lead' ) ) {
				return new WP_REST_Response( array( 'ok' => false, 'code' => 'bad_nonce' ), 403 );
			}
			$res = cy_ec_process( $r->get_params(), array(
				'ip'      => $_SERVER['REMOTE_ADDR'] ?? '',
				'ua'      => $_SERVER['HTTP_USER_AGENT'] ?? '',
				'cookies' => $_COOKIE,
			) );
			$status = $res['ok'] ? 200 : ( 'rate_limited' === $res['code'] ? 429 : 400 );
			unset( $res['id'] );
			return new WP_REST_Response( $res, $status );
		},
	) );
} );

/* ------------------------------------------------------------------ Front */
add_action( 'wp_footer', function () {
	if ( ! is_page( cy_ec_pages() ) ) { return; }
	$pid = (int) get_queried_object_id();
	?>
<style id="cy-ec-css">#cy-ec{margin:16px 0;padding:16px;border:2px solid #d81b60;border-radius:12px;background:#111;color:#fff;font-size:15px}#cy-ec[hidden]{display:none!important}#cy-ec b{display:block;color:#ff4f8b;margin-bottom:8px}#cy-ec .r{display:flex;gap:8px}#cy-ec input[type=email]{flex:1;min-width:0;padding:11px;border:1px solid #444;border-radius:8px;background:#fff;color:#111;font-size:16px}#cy-ec button{padding:11px 16px;border:0;border-radius:8px;background:#d81b60;color:#fff;font-weight:800;cursor:pointer}#cy-ec label{display:block;margin-top:8px;font-size:13px;color:#ddd}#cy-ec .hp{position:absolute;left:-9999px}#cy-ec .m{margin-top:8px;font-size:13px;color:#ff9fc0}</style>
<form id="cy-ec" hidden novalidate data-pid="<?php echo esc_attr( $pid ); ?>" data-api="<?php echo esc_url( rest_url( 'cy/v1/' ) ); ?>">
<b>Get new matches &amp; offers by email</b>
<div class="r"><input type="email" name="email" required autocomplete="email" placeholder="your@email.com" aria-label="Email"><button type="submit">Send</button></div>
<label><input type="checkbox" name="consent" value="1" required> <?php echo esc_html( CY_EC_CONSENT_TEXT ); ?>. Unsubscribe anytime. <a href="<?php echo esc_url( get_privacy_policy_url() ); ?>" style="color:#ff9fc0">Privacy</a></label>
<input class="hp" type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">
<div class="m" role="status" aria-live="polite"></div>
</form>
<script id="cy-ec-js">
(function(){var f=document.getElementById('cy-ec'),q=document.getElementById('qwu-quiz-en');if(!f||!q||!window.fetch)return;var A=f.dataset.api,T,m=f.querySelector('.m'),b=f.querySelector('button');q.parentNode.insertBefore(f,q.nextSibling);function show(){if(q.getAttribute('data-qwu-phase')!=='result'||!f.hidden)return;f.hidden=false;fetch(A+'token',{credentials:'same-origin',cache:'no-store'}).then(function(r){return r.json()}).then(function(j){T=j.t}).catch(function(){})}show();new MutationObserver(show).observe(q,{attributes:true,attributeFilter:['data-qwu-phase']});
f.addEventListener('click',function(e){e.stopPropagation()});
f.addEventListener('submit',function(e){e.preventDefault();e.stopPropagation();var d=new FormData(f),u=new URLSearchParams(location.search),c=document.cookie;if(!f.email.checkValidity()){m.textContent='Please enter a valid email.';return}if(!f.consent.checked){m.textContent='Please tick the consent box.';return}['source','medium','campaign','content','term'].forEach(function(k){var v=u.get('utm_'+k),x;if(!v){x=c.match('(?:^|; )utm_'+k+'=([^;]*)');v=x?decodeURIComponent(x[1]):''}d.append('utm_'+k,v)});d.append('page_id',f.dataset.pid);d.append('page_url',location.origin+location.pathname);d.append('t',T||'');b.disabled=true;fetch(A+'lead',{method:'POST',body:d,credentials:'same-origin'}).then(function(r){return r.json()}).then(function(j){if(j.ok){f.innerHTML='<b>Thanks! You\'re on the list.</b>';try{window.fbq&&fbq('track','Lead',{content_name:'cy_email_capture'})}catch(_){}}else{m.textContent=j.code==='rate_limited'?'Too many tries, please wait a few minutes.':'Please check your email and try again.';b.disabled=false}}).catch(function(){m.textContent='Network error, please try again.';b.disabled=false})})})();
</script>
	<?php
}, 99 );

/* ------------------------------------------------------------------ Admin */
function cy_ec_admin_where( array $g ) {
	global $wpdb;
	$w = array( '1=1' ); $a = array();
	if ( ! empty( $g['s'] ) ) { $w[] = 'email LIKE %s'; $a[] = '%' . $wpdb->esc_like( sanitize_text_field( $g['s'] ) ) . '%'; }
	if ( ! empty( $g['page_id'] ) ) { $w[] = 'page_id = %d'; $a[] = absint( $g['page_id'] ); }
	if ( ! empty( $g['from'] ) && preg_match( '/^\d{4}-\d{2}-\d{2}$/', $g['from'] ) ) { $w[] = 'created_at >= %s'; $a[] = $g['from'] . ' 00:00:00'; }
	if ( ! empty( $g['to'] ) && preg_match( '/^\d{4}-\d{2}-\d{2}$/', $g['to'] ) ) { $w[] = 'created_at <= %s'; $a[] = $g['to'] . ' 23:59:59'; }
	$sql = implode( ' AND ', $w );
	return $a ? $wpdb->prepare( $sql, $a ) : $sql;
}

add_action( 'admin_menu', function () {
	add_menu_page( 'CY Leads', 'CY Leads', 'manage_options', 'cy-leads', 'cy_ec_admin_page', 'dashicons-email-alt', 58 );
} );

function cy_ec_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) { wp_die( 'Forbidden' ); }
	global $wpdb;
	$t = cy_ec_table();

	if ( ! empty( $_POST['cy_ec_del'] ) && check_admin_referer( 'cy_ec_del' ) ) {
		$ids = array_filter( array_map( 'absint', (array) $_POST['cy_ec_del'] ) );
		if ( $ids ) {
			$wpdb->query( "DELETE FROM $t WHERE id IN (" . implode( ',', $ids ) . ')' ); // ints only
			echo '<div class="notice notice-success"><p>' . count( $ids ) . ' lead(s) deleted.</p></div>';
		}
	}

	$g     = wp_unslash( $_GET );
	$where = cy_ec_admin_where( $g );
	$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $t WHERE $where" );
	$per   = 50;
	$pg    = max( 1, absint( $g['paged'] ?? 1 ) );
	$rows  = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $t WHERE $where ORDER BY id DESC LIMIT %d OFFSET %d", $per, ( $pg - 1 ) * $per ) );
	$pages = $wpdb->get_col( "SELECT DISTINCT page_id FROM $t ORDER BY page_id" );
	$f     = array( 's' => $g['s'] ?? '', 'page_id' => $g['page_id'] ?? '', 'from' => $g['from'] ?? '', 'to' => $g['to'] ?? '' );
	$exp   = wp_nonce_url( add_query_arg( array_merge( array( 'action' => 'cy_ec_export' ), array_filter( $f ) ), admin_url( 'admin-post.php' ) ), 'cy_ec_export' );
	?>
	<div class="wrap"><h1>CY Leads <span class="title-count"><?php echo esc_html( $total ); ?></span></h1>
	<form method="get"><input type="hidden" name="page" value="cy-leads">
		<input type="search" name="s" value="<?php echo esc_attr( $f['s'] ); ?>" placeholder="Search email">
		<select name="page_id"><option value="">All pages</option><?php foreach ( $pages as $p ) { printf( '<option value="%1$d"%2$s>%1$d — %3$s</option>', $p, selected( $f['page_id'], $p, false ), esc_html( get_the_title( $p ) ) ); } ?></select>
		From <input type="date" name="from" value="<?php echo esc_attr( $f['from'] ); ?>"> To <input type="date" name="to" value="<?php echo esc_attr( $f['to'] ); ?>">
		<button class="button">Filter</button> <a class="button button-primary" href="<?php echo esc_url( $exp ); ?>">Export CSV (<?php echo esc_html( $total ); ?>)</a>
	</form>
	<form method="post"><?php wp_nonce_field( 'cy_ec_del' ); ?>
	<table class="widefat striped" style="margin-top:12px"><thead><tr><td><input type="checkbox" onclick="document.querySelectorAll('.cy-ck').forEach(c=>c.checked=this.checked)"></td><th>Email</th><th>Consent</th><th>Page</th><th>UTM source / medium / campaign / content</th><th>Submits</th><th>Created (UTC)</th></tr></thead><tbody>
	<?php if ( ! $rows ) { echo '<tr><td colspan="7">No leads yet.</td></tr>'; }
	foreach ( $rows as $r ) {
		printf( '<tr><td><input class="cy-ck" type="checkbox" name="cy_ec_del[]" value="%d"></td><td>%s</td><td>%s</td><td>%d</td><td>%s</td><td>%d</td><td>%s</td></tr>',
			$r->id, esc_html( $r->email ), $r->consent ? 'yes' : 'no', $r->page_id,
			esc_html( implode( ' / ', array( $r->utm_source, $r->utm_medium, $r->utm_campaign, $r->utm_content ) ) ),
			$r->submissions, esc_html( $r->created_at ) );
	} ?>
	</tbody></table>
	<p><button class="button" onclick="return confirm('Delete selected leads permanently?')">Delete selected</button>
	<?php
	$last = max( 1, (int) ceil( $total / $per ) );
	if ( $last > 1 ) {
		echo ' Page ' . $pg . ' / ' . $last . ' ';
		if ( $pg > 1 ) { echo '<a class="button" href="' . esc_url( add_query_arg( 'paged', $pg - 1 ) ) . '">‹</a> '; }
		if ( $pg < $last ) { echo '<a class="button" href="' . esc_url( add_query_arg( 'paged', $pg + 1 ) ) . '">›</a>'; }
	}
	?></p></form></div>
	<?php
}

/** CSV-injection safe cell. */
function cy_ec_csv_cell( $v ) {
	$v = (string) $v;
	return ( '' !== $v && strpbrk( $v[0], "=+-@\t\r" ) ) ? "'" . $v : $v;
}

add_action( 'admin_post_cy_ec_export', function () {
	if ( ! current_user_can( 'manage_options' ) ) { wp_die( 'Forbidden' ); }
	check_admin_referer( 'cy_ec_export' );
	global $wpdb;
	$t     = cy_ec_table();
	$where = cy_ec_admin_where( wp_unslash( $_GET ) );
	$cols  = array( 'id', 'email', 'consent', 'consent_text', 'page_id', 'page_url', 'utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'submissions', 'created_at', 'updated_at' );
	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=cy-leads-' . gmdate( 'Ymd-His' ) . '.csv' );
	$out = fopen( 'php://output', 'w' );
	fputcsv( $out, $cols );
	$off = 0;
	do {
		$rows = $wpdb->get_results( "SELECT " . implode( ',', $cols ) . " FROM $t WHERE $where ORDER BY id ASC LIMIT 1000 OFFSET $off", ARRAY_A );
		foreach ( $rows as $r ) { fputcsv( $out, array_map( 'cy_ec_csv_cell', $r ) ); }
		$off += 1000;
	} while ( count( $rows ) === 1000 );
	fclose( $out );
	exit;
} );
