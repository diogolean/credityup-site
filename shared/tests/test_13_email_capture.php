<?php
// Local test harness for snippet 13 (no WordPress needed). Run: php shared/tests/test_13_email_capture.php
define( 'ABSPATH', __DIR__ . '/' );
$GLOBALS['_t'] = array(); $GLOBALS['_actions'] = array(); $GLOBALS['_fired'] = array();
function add_action( $h, $cb, $p = 10 ) { $GLOBALS['_actions'][ $h ][] = $cb; }
function add_filter() {}
function apply_filters( $h, $v ) { return $v; }
function do_action( $h, ...$a ) { $GLOBALS['_fired'][] = array( $h, $a ); }
function sanitize_email( $e ) { return preg_replace( '/[^a-z0-9@._+\-]/i', '', $e ); }
function is_email( $e ) { return (bool) filter_var( $e, FILTER_VALIDATE_EMAIL ); }
function absint( $v ) { return abs( (int) $v ); }
function sanitize_text_field( $s ) { return trim( strip_tags( $s ) ); }
function esc_url_raw( $u ) { return filter_var( $u, FILTER_SANITIZE_URL ); }
function wp_salt() { return 'testsalt'; }
function get_transient( $k ) { return $GLOBALS['_t'][ $k ] ?? false; }
function set_transient( $k, $v ) { $GLOBALS['_t'][ $k ] = $v; }
function current_time() { return gmdate( 'Y-m-d H:i:s' ); }
class FakeWpdb {
	public $prefix = 'wp_'; public $insert_id = 0; public $pdo;
	function __construct() {
		$this->pdo = new PDO( 'sqlite::memory:' );
		$this->pdo->exec( "CREATE TABLE wp_cy_leads (id INTEGER PRIMARY KEY AUTOINCREMENT, email TEXT UNIQUE, consent INT, consent_text TEXT, page_id INT, page_url TEXT, utm_source TEXT, utm_medium TEXT, utm_campaign TEXT, utm_content TEXT, utm_term TEXT, ip_hash TEXT, user_agent TEXT, submissions INT DEFAULT 1, created_at TEXT, updated_at TEXT)" );
	}
	function prepare( $q, ...$a ) { if ( count( $a ) === 1 && is_array( $a[0] ) ) { $a = $a[0]; } $i = 0;
		return preg_replace_callback( '/%[sd]/', function ( $m ) use ( &$i, $a ) { $v = $a[ $i++ ]; return $m[0] === '%d' ? (int) $v : $this->pdo->quote( $v ); }, $q ); }
	function get_var( $q ) { $r = $this->pdo->query( $q )->fetchColumn(); return $r === false ? null : $r; }
	function query( $q ) { return $this->pdo->exec( $q ); }
	function insert( $t, $d ) { $st = $this->pdo->prepare( "INSERT INTO $t (" . implode( ',', array_keys( $d ) ) . ') VALUES (' . rtrim( str_repeat( '?,', count( $d ) ), ',' ) . ')' );
		$ok = @$st->execute( array_values( $d ) ); if ( ! $ok ) { return false; } $this->insert_id = (int) $this->pdo->lastInsertId(); return 1; }
}
$wpdb = new FakeWpdb();
require dirname( __DIR__ ) . '/snippets/13_cy_email_capture.php';

$pass = 0; $fail = 0;
function t( $name, $cond ) { global $pass, $fail; echo ( $cond ? 'PASS ' : 'FAIL ' ) . $name . "\n"; $cond ? $pass++ : $fail++; }
$ctx = array( 'ip' => '1.2.3.4', 'ua' => 'UA', 'cookies' => array( 'utm_campaign' => 'from_cookie' ) );
$ok  = array( 'email' => ' Foo@Example.com ', 'consent' => '1', 'page_id' => '100', 'utm_source' => 'fb', 'utm_medium' => 'paid', 'page_url' => 'https://us.credityup.com/x/' );

$r = cy_ec_process( $ok, $ctx );
t( 'valid lead stored', $r['ok'] && $r['id'] === 1 );
$row = $wpdb->pdo->query( 'SELECT * FROM wp_cy_leads' )->fetch( PDO::FETCH_ASSOC );
t( 'email normalized', $row['email'] === 'foo@example.com' );
t( 'utm from params', $row['utm_source'] === 'fb' && $row['utm_medium'] === 'paid' );
t( 'utm fallback from cookie', $row['utm_campaign'] === 'from_cookie' );
t( 'ip hashed (not raw)', strlen( $row['ip_hash'] ) === 64 && strpos( $row['ip_hash'], '1.2.3.4' ) === false );
t( 'hook fired is_new=true', end( $GLOBALS['_fired'] )[0] === 'cy_lead_captured' && end( $GLOBALS['_fired'] )[1][2] === true );
$r = cy_ec_process( array_merge( $ok, array( 'utm_source' => 'tiktok' ) ), array( 'ip' => '9.9.9.9' ) + $ctx );
t( 'dedupe same id', $r['ok'] && $r['id'] === 1 && (int) $wpdb->get_var( 'SELECT COUNT(*) FROM wp_cy_leads' ) === 1 );
t( 'dedupe keeps first-touch utm, counts submits', $wpdb->get_var( 'SELECT utm_source||submissions FROM wp_cy_leads' ) === 'fb2' );
t( 'hook fired is_new=false', end( $GLOBALS['_fired'] )[1][2] === false );
t( 'honeypot silently dropped', cy_ec_process( array_merge( $ok, array( 'email' => 'bot@x.com', 'website' => 'spam' ) ), $ctx )['id'] === 0 && (int) $wpdb->get_var( "SELECT COUNT(*) FROM wp_cy_leads WHERE email='bot@x.com'" ) === 0 );
t( 'invalid email rejected', cy_ec_process( array_merge( $ok, array( 'email' => 'nope' ) ), $ctx )['code'] === 'invalid_email' );
t( 'consent required', cy_ec_process( array_merge( $ok, array( 'consent' => '' ) ), $ctx )['code'] === 'consent_required' );
t( 'other page rejected', cy_ec_process( array_merge( $ok, array( 'page_id' => 5 ) ), $ctx )['code'] === 'bad_page' );
t( 'SQLi-ish utm stored literally', cy_ec_process( array_merge( $ok, array( 'email' => 'q@x.com', 'utm_source' => "a'); DROP TABLE wp_cy_leads;--" ) ), array( 'ip' => '7.7.7.7' ) + $ctx )['ok'] && (int) $wpdb->get_var( 'SELECT COUNT(*) FROM wp_cy_leads' ) === 2 );
$codes = array(); for ( $i = 0; $i < 7; $i++ ) { $codes[] = cy_ec_process( array_merge( $ok, array( 'email' => "r$i@x.com" ) ), array( 'ip' => '5.5.5.5' ) + $ctx )['code']; }
t( 'rate limit after 5/IP', array_count_values( $codes )['ok'] === 5 && end( $codes ) === 'rate_limited' );
t( 'csv injection escaped', cy_ec_csv_cell( '=HYPERLINK(1)' ) === "'=HYPERLINK(1)" && cy_ec_csv_cell( 'a@b.com' ) === 'a@b.com' );
echo "\n$pass passed, $fail failed\n"; exit( $fail ? 1 : 0 );
