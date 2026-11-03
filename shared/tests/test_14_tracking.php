<?php
// Local test harness for snippet 14 (no WordPress, no real ads). Run: php shared/tests/test_14_tracking.php
// Renders the script via PHP, then executes it in node against a fake googletag + fbq.
define( 'ABSPATH', __DIR__ . '/' );
$GLOBALS['_actions'] = array();
function add_action( $h, $cb, $p = 10 ) { $GLOBALS['_actions'][ $h ][] = array( $cb, $p ); }
function is_admin() { return false; }
require dirname( __DIR__ ) . '/snippets/14_cy_tracking.php';

$pass = 0; $fail = 0;
function t( $name, $cond ) { global $pass, $fail; echo ( $cond ? 'PASS ' : 'FAIL ' ) . $name . "\n"; $cond ? $pass++ : $fail++; }

$html = cy_trk_script();
t( 'hooked on wp_head', isset( $GLOBALS['_actions']['wp_head'] ) );
t( 'never inits a pixel', strpos( $html, "'init'" ) === false && strpos( $html, '2574163523103706' ) === false );
t( 'no network requests', ! preg_match( '/fetch\(|sendBeacon|XMLHttpRequest/', $html ) );
t( 'small (< 5KB)', strlen( $html ) < 5120 );
t( 'no Lead/InitiateCheckout dup', ! preg_match( "/'(Lead|InitiateCheckout|Rewarded_Ad_Completed)/", $html ) );

$js = preg_replace( '#</?script[^>]*>#', '', $html );
$harness = <<<'H'
const L = {}, out = [];
const mk = (p, tg) => ({ getAdUnitPath: () => p, getTargeting: k => (tg[k] || []) });
global.window = global; global.location = { search: process.env.QS, pathname: '/lp-02/', protocol: 'https:' };
global.document = { cookie: process.env.CK || '' };
global.fbq = (...a) => out.push(a);
global.googletag = { cmd: { push: f => f() }, pubads: () => ({ addEventListener: (e, f) => (L[e] = L[e] || []).push(f) }) };
global.googletag.cmd = { push: f => f() };
eval(require('fs').readFileSync(0, 'utf8'));
const fire = (e, ev) => (L[e] || []).forEach(f => f(ev));
const rw = mk('/23340104016/us/rewarded_quiz', {}), ow = mk('/23340104016/us/offerwall_c2', { price: ['p250'] });
fire('rewardedSlotReady', { slot: rw }); fire('rewardedSlotReady', { slot: rw });
fire('rewardedSlotGranted', { slot: rw, payload: { type: 'coins', amount: 1 } });
fire('rewardedSlotGranted', { slot: ow });
fire('rewardedSlotReady', { slot: ow });
fire('slotRenderEnded', { slot: ow, isEmpty: true });
fire('slotRenderEnded', { slot: rw, isEmpty: false });
fire('slotRenderEnded', { slot: ow, isEmpty: false, lineItemId: 123, advertiserId: 9, creativeId: 7 });
fire('slotRenderEnded', { slot: ow, isEmpty: false, lineItemId: 124 });
console.log(JSON.stringify({ out, cookie: document.cookie }));
H;
function run( $js, $h, $qs, $ck = '' ) {
	$f = tempnam( sys_get_temp_dir(), 'h' ); file_put_contents( $f, $h );
	$p = proc_open( 'QS=' . escapeshellarg( $qs ) . ' CK=' . escapeshellarg( $ck ) . ' node ' . escapeshellarg( $f ), array( array( 'pipe', 'r' ), array( 'pipe', 'w' ), array( 'pipe', 'w' ) ), $pp );
	fwrite( $pp[0], $js ); fclose( $pp[0] ); $o = stream_get_contents( $pp[1] ); $e = stream_get_contents( $pp[2] ); proc_close( $p ); unlink( $f );
	if ( $e ) { echo $e; } return json_decode( $o, true );
}
$r = run( $js, $harness, '?utm_source=fb&utm_campaign=lal1&fbclid=ABC&x=1' );
$all = $r['out'];
$std = array_values( array_filter( $all, function ( $a ) { return $a[0] === 'track'; } ) );
$r['out'] = array_values( array_filter( $all, function ( $a ) { return $a[0] === 'trackCustom'; } ) );
$names = array_map( function ( $a ) { return $a[1]; }, $r['out'] );
t( 'only trackCustom + track calls', count( $r['out'] ) + count( $std ) === count( $all ) );
t( 'exact event sequence (dedup + offerwall excluded from rewarded)', $names === array( 'CY_Rewarded_View', 'CY_Rewarded_Complete', 'CY_Offerwall_Paid' ) );
t( 'full order: std right after its custom', array_map( function ( $a ) { return $a[1]; }, $all ) === array( 'CY_Rewarded_View', 'CY_Rewarded_Complete', 'Subscribe', 'CY_Offerwall_Paid', 'Purchase' ) );
$rc = $r['out'][1][2]; $ow = $r['out'][2][2];
t( 'Subscribe: value 0 USD, context, eventID _std', $std[0][2]['value'] === 0 && $std[0][2]['currency'] === 'USD' && $std[0][2]['block'] === 'rewarded_quiz' && $std[0][2]['utm_campaign'] === 'lal1' && $std[0][2]['cy_event'] === 'CY_Rewarded_Complete' && $std[0][3]['eventID'] === $rc['event_id'] . '_std' );
t( 'Purchase: value from price_key, context, eventID _std', $std[1][2]['value'] === 250 && $std[1][2]['currency'] === 'USD' && $std[1][2]['line_item_id'] === 123 && $std[1][3]['eventID'] === $ow['event_id'] . '_std' );
t( 'rewarded params: block/page/utm/fbclid', $rc['block'] === 'rewarded_quiz' && $rc['page_path'] === '/lp-02/' && $rc['utm_source'] === 'fb' && $rc['utm_campaign'] === 'lal1' && $rc['fbclid'] === 'ABC' );
t( 'reward payload passed', $rc['reward_type'] === 'coins' && $rc['reward_amount'] === 1 );
t( 'event_id present and = eventID option', strlen( $rc['event_id'] ) > 6 && $r['out'][1][3]['eventID'] === $rc['event_id'] );
t( 'unique event ids', $r['out'][0][2]['event_id'] !== $rc['event_id'] );
t( 'offerwall paid: price_key + line item', $ow['price_key'] === 'p250' && $ow['line_item_id'] === 123 && $ow['advertiser_id'] === 9 && $ow['backfill'] === 0 && $ow['block'] === 'offerwall_c2' );
t( 'cookie written 30d', strpos( $r['cookie'], 'cy_attr=' ) === 0 && strpos( $r['cookie'], 'max-age=2592000' ) !== false );
$ck = explode( ';', $r['cookie'] )[0];
$r2 = run( $js, $harness, '', $ck );
t( 'utms restored from cookie on later page', $r2['out'][1][2]['utm_campaign'] === 'lal1' && $r2['out'][1][2]['fbclid'] === 'ABC' );
t( 'no cookie rewrite when url has no utms', $r2['cookie'] === $ck );
$r3 = run( $js, $harness, '?utm_campaign=new', $ck );
t( 'new utm overrides, keeps others', $r3['out'][1][2]['utm_campaign'] === 'new' && $r3['out'][1][2]['utm_source'] === 'fb' );
$r4 = run( $js, $harness, '', 'cy_attr=%7Bbroken' );
t( 'broken cookie does not break events', count( $r4['out'] ) === 5 );

echo "\n$pass passed, $fail failed\n"; exit( $fail ? 1 : 0 );
