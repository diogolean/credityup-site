<?php
/**
 * Email capture at the end of the English quiz (#qwu-quiz-en).
 * Stores rows in wp-admin and exports CSV. The address never goes into a URL or analytics.
 */

if (!defined('ABSPATH')) {
    exit;
}

function credityup_leads_table() {
    global $wpdb;
    return $wpdb->prefix . 'credityup_leads';
}

function credityup_leads_install() {
    global $wpdb;
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    $table = credityup_leads_table();
    $charset = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE {$table} (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        email varchar(190) NOT NULL,
        consent tinyint(1) NOT NULL DEFAULT 0,
        page_path varchar(200) NOT NULL DEFAULT '',
        created_gmt datetime NOT NULL,
        ip_hash char(64) NOT NULL DEFAULT '',
        PRIMARY KEY  (id),
        KEY created_gmt (created_gmt)
    ) {$charset};";
    dbDelta($sql);
}

add_action('rest_api_init', function () {
    register_rest_route('credityup/v1', '/lead', array(
        'methods' => 'POST',
        'callback' => 'credityup_leads_rest',
        'permission_callback' => '__return_true',
    ));
});

function credityup_leads_rest(WP_REST_Request $request) {
    $token = (string) $request->get_header('x-credityup-token');
    if (!credityup_platform_hour_token_ok($token, 'lead')) {
        return new WP_REST_Response(array('ok' => false, 'error' => 'token'), 403);
    }

    $honeypot = (string) $request->get_param('company');
    if ($honeypot !== '') {
        return new WP_REST_Response(array('ok' => true), 200);
    }

    $hash = credityup_platform_ip_hash();
    $rate_key = 'cy_lead_' . substr($hash, 0, 16);
    $hits = (int) get_transient($rate_key);
    if ($hits >= 5) {
        return new WP_REST_Response(array('ok' => false, 'error' => 'rate'), 429);
    }
    set_transient($rate_key, $hits + 1, HOUR_IN_SECONDS);

    $consent = (string) $request->get_param('consent');
    if ($consent !== '1') {
        return new WP_REST_Response(array('ok' => false, 'error' => 'consent'), 400);
    }

    $email = sanitize_email((string) $request->get_param('email'));
    if (!is_email($email)) {
        return new WP_REST_Response(array('ok' => false, 'error' => 'email'), 400);
    }

    $path = credityup_leads_path((string) $request->get_param('page'));

    global $wpdb;
    $stored = $wpdb->insert(
        credityup_leads_table(),
        array(
            'email' => $email,
            'consent' => 1,
            'page_path' => $path,
            'created_gmt' => current_time('mysql', true),
            'ip_hash' => $hash,
        ),
        array('%s', '%d', '%s', '%s', '%s')
    );
    if (!$stored) {
        return new WP_REST_Response(array('ok' => false, 'error' => 'store'), 500);
    }

    $response = new WP_REST_Response(array('ok' => true), 200);
    $response->header('Cache-Control', 'no-store');
    return $response;
}

function credityup_leads_path($raw) {
    $raw = trim($raw);
    $parts = wp_parse_url($raw);
    $path = '';
    if (is_array($parts) && isset($parts['path'])) {
        $path = $parts['path'];
    } elseif ($raw !== '' && $raw[0] === '/') {
        $path = explode('?', $raw, 2)[0];
    }
    $path = '/' . ltrim((string) $path, '/');
    $path = substr($path, 0, 200);
    if (!preg_match('#^/[A-Za-z0-9_./-]*$#', $path)) {
        return '/';
    }
    return $path;
}

add_action('wp_enqueue_scripts', function () {
    if (is_admin() || !is_singular()) {
        return;
    }
    wp_enqueue_script(
        'credityup-leads',
        plugins_url('assets/leads.js', CREDITYUP_PLATFORM_FILE),
        array(),
        CREDITYUP_PLATFORM_VERSION,
        true
    );
    wp_localize_script('credityup-leads', 'credityupLeads', array(
        'restUrl' => esc_url_raw(rest_url('credityup/v1/lead')),
        'token' => credityup_platform_hour_token('lead'),
        'privacyUrl' => esc_url_raw(home_url('/privacy-policy/')),
    ));
});

add_action('admin_menu', function () {
    add_menu_page(
        'Credityup leads',
        'Credityup',
        'manage_options',
        'credityup-leads',
        'credityup_leads_admin',
        'dashicons-email',
        58
    );
});

add_action('admin_post_credityup_leads_export', function () {
    if (!current_user_can('manage_options')) {
        wp_die('Forbidden', 403);
    }
    check_admin_referer('credityup_leads_export');
    global $wpdb;
    $table = credityup_leads_table();
    $rows = $wpdb->get_results("SELECT email, consent, page_path, created_gmt FROM {$table} ORDER BY id ASC", ARRAY_A);
    nocache_headers();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="credityup-leads.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, array('email', 'consent', 'page_path', 'created_gmt'));
    foreach ($rows as $row) {
        fputcsv($out, array($row['email'], $row['consent'], $row['page_path'], $row['created_gmt']));
    }
    fclose($out);
    exit;
});

function credityup_leads_admin() {
    if (!current_user_can('manage_options')) {
        return;
    }
    global $wpdb;
    $table = credityup_leads_table();
    $total = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table}");
    $rows = $wpdb->get_results("SELECT email, page_path, created_gmt FROM {$table} ORDER BY id DESC LIMIT 100", ARRAY_A);
    $export = wp_nonce_url(admin_url('admin-post.php?action=credityup_leads_export'), 'credityup_leads_export');
    echo '<div class="wrap">';
    echo '<h1>Leads do quiz</h1>';
    echo '<p>E-mails capturados no fim do quiz em inglês, com consentimento. O endereço não entra em URL nem no analytics. Brevo fica para um passo seguinte: exporte o CSV.</p>';
    echo '<p><a class="button button-primary" href="' . esc_url($export) . '">Exportar CSV</a> · ' . (int) $total . ' no total (últimos 100 abaixo).</p>';
    echo '<table class="widefat striped"><thead><tr><th>E-mail</th><th>Página</th><th>GMT</th></tr></thead><tbody>';
    if (!$rows) {
        echo '<tr><td colspan="3">Nenhum lead ainda.</td></tr>';
    }
    foreach ($rows as $row) {
        echo '<tr><td>' . esc_html($row['email']) . '</td><td>' . esc_html($row['page_path']) . '</td><td>' . esc_html($row['created_gmt']) . '</td></tr>';
    }
    echo '</tbody></table></div>';
}
