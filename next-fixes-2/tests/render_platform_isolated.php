<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
$case = isset($argv[1]) ? $argv[1] : '';
$ROOT = dirname(__DIR__, 2);
$NEW = $ROOT . '/shared/plugins/credityup-platform';
$OLD = $ROOT . '/next-fixes/backup-live';

$stub = file_get_contents(__DIR__ . '/render_platform_templates.php');
$cut = strpos($stub, 'if (php_sapi_name() === \'cli\'');
if ($cut === false) {
    fwrite(STDERR, "cannot isolate stub\n");
    exit(2);
}
$wrapper = tempnam(sys_get_temp_dir(), 'cy-h');
$code = substr($stub, 0, $cut);
$code .= "\n\$NEW = " . var_export($NEW, true) . ";\n\$OLD = " . var_export($OLD, true) . ";\n";

if ($case === 'b') {
    $code .= <<<'S'
define('CREDITYUP_PLATFORM_DIR', $NEW . '/');
cy_load($NEW . '/includes/home.php');
$cy_stub_is_front = true;
$cy_stub_category = null;
$html = cy_render($OLD . '/templates/front-page.php', 'B front-page');
if (strpos($html, 'From the archive') === false) { cy_fail('B home did not render old archive'); } else { cy_ok('B new home.php + old front-page.php'); }
$cy_stub_is_front = false;
$cy_stub_category = 'free-credits';
$html = cy_render($OLD . '/templates/free-credits.php', 'B free-credits');
if (strpos($html, 'Free credits') === false && strpos($html, 'Free Credits') === false) { cy_fail('B old free-credits did not render'); } else { cy_ok('B new home.php + old free-credits.php'); }
if (function_exists('credityup_archive_template_ready') && credityup_archive_template_ready($OLD . '/templates/free-credits.php')) {
    cy_fail('B must not treat the old template as ready');
} else {
    cy_ok('B archive_template_ready ignores the old template');
}
S;
} elseif ($case === 'c') {
    $code .= <<<'S'
define('CREDITYUP_PLATFORM_DIR', $OLD . '/');
cy_load($OLD . '/includes/home.php');
$cy_stub_is_front = true;
$cy_stub_category = null;
$html = cy_render($NEW . '/templates/front-page.php', 'C front-page');
if (strpos($html, 'From the archive') === false) { cy_fail('C new front-page failed with old home.php'); } else { cy_ok('C old home.php + new front-page.php (no fatal)'); }
$cy_stub_is_front = false;
$cy_stub_category = 'free-credits';
$html = cy_render($NEW . '/templates/free-credits.php', 'C free-credits');
if (strpos($html, 'tag="rewarded"') === false) { cy_fail('C new free-credits failed with old home.php'); } else { cy_ok('C old home.php + new free-credits.php (no fatal, the previous 500 case)'); }
S;
} else {
    fwrite(STDERR, "usage: render_platform_isolated.php b|c\n");
    exit(2);
}

$code .= "\nif (\$fails) { echo \"FAILED (\$fails)\\n\"; exit(1);} echo \"ok isolated \$case\\n\"; exit(0);\n";
file_put_contents($wrapper, $code);
passthru('php ' . escapeshellarg($wrapper), $exit);
unlink($wrapper);
exit($exit);
