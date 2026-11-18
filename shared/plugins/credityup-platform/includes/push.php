<?php
/**
 * Firebase Cloud Messaging web push.
 *
 * Public web keys live in options or wp-config constants and are written to
 * /credityup-firebase-config.js (that file is public by design).
 * The WegoChat URL and Authorization header stay on the server.
 * If the URL is unknown, tokens are queued and credityup_fcm_token still fires.
 *
 * Required from Winup before a campaign can send:
 *   CREDITYUP_WEGOCHAT_PUSH_URL
 *   CREDITYUP_WEGOCHAT_PUSH_AUTHORIZATION   (optional; never printed to the page)
 * Filters if their JSON shape differs:
 *   credityup_wegochat_push_url
 *   credityup_wegochat_push_body
 *   credityup_wegochat_push_authorization
 */

if (!defined('ABSPATH')) {
    exit;
}

function credityup_push_table() {
    global $wpdb;
    return $wpdb->prefix . 'credityup_push_tokens';
}

function credityup_push_install() {
    global $wpdb;
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    $table = credityup_push_table();
    $charset = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE {$table} (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        token_hash char(64) NOT NULL,
        token text NOT NULL,
        created_gmt datetime NOT NULL,
        delivered_gmt datetime DEFAULT NULL,
        attempts smallint unsigned NOT NULL DEFAULT 0,
        last_error varchar(180) NOT NULL DEFAULT '',
        PRIMARY KEY  (id),
        UNIQUE KEY token_hash (token_hash)
    ) {$charset};";
    dbDelta($sql);
}

function credityup_push_defaults() {
    $defaults = array(
        'apiKey' => 'YOUR_FIREBASE_API_KEY',
        'authDomain' => 'YOUR_FIREBASE_PROJECT_ID.firebaseapp.com',
        'projectId' => 'YOUR_FIREBASE_PROJECT_ID',
        'messagingSenderId' => 'YOUR_MESSAGING_SENDER_ID',
        'appId' => 'YOUR_FIREBASE_APP_ID',
        'vapidKey' => 'YOUR_VAPID_PUBLIC_KEY',
        'endpoint' => '',
    );
    $constants = array(
        'apiKey' => 'CREDITYUP_FCM_API_KEY',
        'authDomain' => 'CREDITYUP_FCM_AUTH_DOMAIN',
        'projectId' => 'CREDITYUP_FCM_PROJECT_ID',
        'messagingSenderId' => 'CREDITYUP_FCM_SENDER_ID',
        'appId' => 'CREDITYUP_FCM_APP_ID',
        'vapidKey' => 'CREDITYUP_FCM_VAPID_KEY',
        'endpoint' => 'CREDITYUP_WEGOCHAT_PUSH_URL',
    );
    $stored = get_option('credityup_push_settings', array());
    if (!is_array($stored)) {
        $stored = array();
    }
    $out = $defaults;
    foreach ($defaults as $key => $placeholder) {
        if (!empty($stored[$key])) {
            $out[$key] = (string) $stored[$key];
        }
        $constant = $constants[$key];
        if (defined($constant) && constant($constant) !== '') {
            $out[$key] = (string) constant($constant);
        }
    }
    return $out;
}

function credityup_push_config_ready($settings) {
    foreach (array('apiKey', 'authDomain', 'projectId', 'messagingSenderId', 'appId', 'vapidKey') as $key) {
        $value = isset($settings[$key]) ? (string) $settings[$key] : '';
        if ($value === '' || strpos($value, 'YOUR_') === 0) {
            return false;
        }
    }
    return true;
}

function credityup_push_authorization() {
    $auth = '';
    if (defined('CREDITYUP_WEGOCHAT_PUSH_AUTHORIZATION')) {
        $auth = (string) CREDITYUP_WEGOCHAT_PUSH_AUTHORIZATION;
    }
    $stored = get_option('credityup_push_authorization', '');
    if ($auth === '' && is_string($stored)) {
        $auth = $stored;
    }
    return (string) apply_filters('credityup_wegochat_push_authorization', $auth);
}

function credityup_push_root_config_js($settings) {
    $public = array(
        'apiKey' => $settings['apiKey'],
        'authDomain' => $settings['authDomain'],
        'projectId' => $settings['projectId'],
        'messagingSenderId' => $settings['messagingSenderId'],
        'appId' => $settings['appId'],
        'vapidKey' => $settings['vapidKey'],
    );
    $json = wp_json_encode($public, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP);
    return "(function(root){root.CREDITYUP_FIREBASE_CONFIG={$json};})(typeof self!=='undefined'?self:this);\n";
}

function credityup_push_install_root_files() {
    $dir = ABSPATH;
    if (!is_writable($dir)) {
        return new WP_Error('credityup_push_unwritable', $dir);
    }
    $sw_dest = $dir . 'firebase-messaging-sw.js';
    if (!file_exists($sw_dest)) {
        $copied = copy(CREDITYUP_PLATFORM_DIR . 'assets/firebase-messaging-sw.js', $sw_dest);
        if (!$copied) {
            return new WP_Error('credityup_push_copy', $sw_dest);
        }
    }
    $settings = credityup_push_defaults();
    $config_path = $dir . 'credityup-firebase-config.js';
    if (!file_exists($config_path) || credityup_push_config_ready($settings)) {
        file_put_contents($config_path, credityup_push_root_config_js($settings));
    }
    return true;
}

function credityup_push_deliver_token($token) {
    $settings = credityup_push_defaults();
    $url = apply_filters('credityup_wegochat_push_url', $settings['endpoint']);
    if (!$url) {
        return new WP_Error('credityup_push_no_endpoint', 'WegoChat endpoint is not set');
    }
    $body = apply_filters('credityup_wegochat_push_body', array(
        'token' => $token,
        'provider' => 'fcm',
        'domain' => (string) wp_parse_url(home_url(), PHP_URL_HOST),
    ), $token);
    $headers = array('Content-Type' => 'application/json');
    $auth = credityup_push_authorization();
    if ($auth !== '') {
        $headers['Authorization'] = $auth;
    }
    $response = wp_remote_post($url, array(
        'timeout' => 8,
        'headers' => $headers,
        'body' => wp_json_encode($body),
    ));
    if (is_wp_error($response)) {
        return $response;
    }
    $code = (int) wp_remote_retrieve_response_code($response);
    if ($code < 200 || $code >= 300) {
        return new WP_Error('credityup_push_http', 'HTTP ' . $code);
    }
    return true;
}

function credityup_push_token_hash($token) {
    return hash('sha256', $token);
}

function credityup_push_store_and_send($token) {
    global $wpdb;
    $table = credityup_push_table();
    $hash = credityup_push_token_hash($token);
    $existing = $wpdb->get_row($wpdb->prepare("SELECT id FROM {$table} WHERE token_hash = %s", $hash));
    if (!$existing) {
        $wpdb->insert(
            $table,
            array(
                'token_hash' => $hash,
                'token' => $token,
                'created_gmt' => current_time('mysql', true),
                'attempts' => 0,
                'last_error' => '',
            ),
            array('%s', '%s', '%s', '%d', '%s')
        );
        $id = (int) $wpdb->insert_id;
    } else {
        $id = (int) $existing->id;
    }

    do_action('credityup_fcm_token', $token, array('id' => $id));

    $result = credityup_push_deliver_token($token);
    if (is_wp_error($result)) {
        $wpdb->query($wpdb->prepare(
            "UPDATE {$table} SET attempts = attempts + 1, last_error = %s WHERE id = %d",
            substr($result->get_error_message(), 0, 180),
            $id
        ));
        return $result;
    }
    $wpdb->update(
        $table,
        array(
            'delivered_gmt' => current_time('mysql', true),
            'last_error' => '',
        ),
        array('id' => $id),
        array('%s', '%s'),
        array('%d')
    );
    return true;
}

function credityup_push_retry_pending() {
    global $wpdb;
    $table = credityup_push_table();
    $tokens = $wpdb->get_col("SELECT token FROM {$table} WHERE delivered_gmt IS NULL ORDER BY id ASC LIMIT 200");
    $sent = 0;
    foreach ($tokens as $token) {
        $result = credityup_push_store_and_send($token);
        if (!is_wp_error($result)) {
            $sent++;
        }
    }
    return $sent;
}

add_action('rest_api_init', function () {
    register_rest_route('credityup/v1', '/push-token', array(
        'methods' => 'POST',
        'callback' => 'credityup_push_rest',
        'permission_callback' => '__return_true',
    ));
});

function credityup_push_rest(WP_REST_Request $request) {
    $header = (string) $request->get_header('x-credityup-token');
    if (!credityup_platform_hour_token_ok($header, 'push')) {
        return new WP_REST_Response(array('ok' => false), 403);
    }
    $hash = credityup_platform_ip_hash();
    $rate_key = 'cy_push_' . substr($hash, 0, 16);
    $hits = (int) get_transient($rate_key);
    if ($hits >= 10) {
        return new WP_REST_Response(array('ok' => false), 429);
    }
    set_transient($rate_key, $hits + 1, HOUR_IN_SECONDS);

    $token = trim((string) $request->get_param('token'));
    if (strlen($token) < 20 || strlen($token) > 4096 || preg_match('/[\s<>]/', $token)) {
        return new WP_REST_Response(array('ok' => false), 400);
    }
    $result = credityup_push_store_and_send($token);
    $delivered = !is_wp_error($result);
    $response = new WP_REST_Response(array(
        'ok' => true,
        'delivered' => $delivered,
    ), 200);
    $response->header('Cache-Control', 'no-store');
    return $response;
}

add_action('wp_enqueue_scripts', function () {
    if (is_admin()) {
        return;
    }
    wp_enqueue_script(
        'credityup-push',
        plugins_url('assets/push.js', CREDITYUP_PLATFORM_FILE),
        array(),
        CREDITYUP_PLATFORM_VERSION,
        true
    );
    $settings = credityup_push_defaults();
    wp_localize_script('credityup-push', 'credityupPush', array(
        'restUrl' => esc_url_raw(rest_url('credityup/v1/push-token')),
        'token' => credityup_platform_hour_token('push'),
        'config' => array(
            'apiKey' => $settings['apiKey'],
            'authDomain' => $settings['authDomain'],
            'projectId' => $settings['projectId'],
            'messagingSenderId' => $settings['messagingSenderId'],
            'appId' => $settings['appId'],
            'vapidKey' => $settings['vapidKey'],
        ),
    ));
});

add_action('admin_menu', function () {
    add_submenu_page(
        'credityup-leads',
        'Push',
        'Push',
        'manage_options',
        'credityup-push',
        'credityup_push_admin'
    );
});

add_action('admin_post_credityup_push_save', function () {
    if (!current_user_can('manage_options')) {
        wp_die('Forbidden', 403);
    }
    check_admin_referer('credityup_push_save');
    $keys = array('apiKey', 'authDomain', 'projectId', 'messagingSenderId', 'appId', 'vapidKey', 'endpoint');
    $clean = array();
    foreach ($keys as $key) {
        $clean[$key] = sanitize_text_field(wp_unslash($_POST[$key] ?? ''));
    }
    update_option('credityup_push_settings', $clean, false);
    $auth = sanitize_text_field(wp_unslash($_POST['authorization'] ?? ''));
    if ($auth !== '') {
        update_option('credityup_push_authorization', $auth, false);
    }
    credityup_push_install_root_files();
    wp_safe_redirect(admin_url('admin.php?page=credityup-push&updated=1'));
    exit;
});

add_action('admin_post_credityup_push_retry', function () {
    if (!current_user_can('manage_options')) {
        wp_die('Forbidden', 403);
    }
    check_admin_referer('credityup_push_retry');
    $sent = credityup_push_retry_pending();
    wp_safe_redirect(admin_url('admin.php?page=credityup-push&resent=' . (int) $sent));
    exit;
});

add_action('admin_notices', function () {
    if (!current_user_can('manage_options')) {
        return;
    }
    $sw = ABSPATH . 'firebase-messaging-sw.js';
    if (file_exists($sw)) {
        return;
    }
    echo '<div class="notice notice-warning"><p>Credityup push: falta <code>firebase-messaging-sw.js</code> na raiz do WordPress (' . esc_html($sw) . '). A raiz não estava gravável na ativação. Copie o arquivo de <code>wp-content/plugins/credityup-platform/assets/</code> para junto do <code>wp-config.php</code> e salve Credityup → Push de novo.</p></div>';
});

function credityup_push_admin() {
    if (!current_user_can('manage_options')) {
        return;
    }
    $settings = credityup_push_defaults();
    global $wpdb;
    $table = credityup_push_table();
    $pending = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table} WHERE delivered_gmt IS NULL");
    $done = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table} WHERE delivered_gmt IS NOT NULL");
    $fields = array(
        'apiKey' => 'apiKey',
        'authDomain' => 'authDomain',
        'projectId' => 'projectId',
        'messagingSenderId' => 'messagingSenderId',
        'appId' => 'appId',
        'vapidKey' => 'VAPID public key',
        'endpoint' => 'URL do endpoint WegoChat (server-side)',
    );
    echo '<div class="wrap"><h1>Web push</h1>';
    if (!empty($_GET['updated'])) {
        echo '<div class="notice notice-success"><p>Configuração salva. O arquivo público <code>credityup-firebase-config.js</code> na raiz foi reescrito com a config web (sem segredo de servidor).</p></div>';
    }
    if (isset($_GET['resent'])) {
        echo '<div class="notice notice-success"><p>Reenvio: ' . (int) $_GET['resent'] . ' tokens aceitos pelo endpoint.</p></div>';
    }
    echo '<p>O visitante vê um pedido nosso antes do prompt do browser. O token FCM entra em <code>' . esc_html($table) . '</code> e, se a URL existir, o WordPress faz o POST. O header Authorization não vai para o JavaScript.</p>';
    echo '<p>Pendentes: ' . (int) $pending . ' · Entregues: ' . (int) $done . '</p>';
    echo '<p>Enquanto a Winup não passar a URL, o método e o nome do campo, os tokens ficam pendentes. O filtro <code>credityup_wegochat_push_body</code> troca o JSON se o painel não aceitar <code>{"token","provider":"fcm","domain"}</code>. A action <code>credityup_fcm_token</code> recebe o token no servidor. No browser, <code>window.credityupPushHooks</code> é uma lista de funções. Não mandar o token para o dataLayer, GA4 ou pixel.</p>';
    echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
    echo '<input type="hidden" name="action" value="credityup_push_save">';
    wp_nonce_field('credityup_push_save');
    echo '<table class="form-table">';
    foreach ($fields as $key => $label) {
        $value = $settings[$key];
        $is_placeholder = ($value !== '' && strpos($value, 'YOUR_') === 0);
        echo '<tr><th scope="row"><label for="cy_' . esc_attr($key) . '">' . esc_html($label) . '</label></th><td>';
        echo '<input class="regular-text" id="cy_' . esc_attr($key) . '" name="' . esc_attr($key) . '" value="' . esc_attr($is_placeholder ? '' : $value) . '" placeholder="' . esc_attr($is_placeholder ? $value : '') . '" autocomplete="off">';
        echo '</td></tr>';
    }
    echo '<tr><th scope="row"><label for="cy_authorization">Authorization (opcional, segredo)</label></th><td>';
    echo '<input class="regular-text" type="password" id="cy_authorization" name="authorization" value="" placeholder="' . (credityup_push_authorization() !== '' ? 'já salvo — preencha só para trocar' : 'Bearer …') . '" autocomplete="new-password">';
    echo '<p class="description">Constante <code>CREDITYUP_WEGOCHAT_PUSH_AUTHORIZATION</code> tem prioridade. Não commitar este valor.</p>';
    echo '</td></tr></table>';
    submit_button('Salvar');
    echo '</form>';
    $retry = wp_nonce_url(admin_url('admin-post.php?action=credityup_push_retry'), 'credityup_push_retry');
    echo '<p><a class="button" href="' . esc_url($retry) . '">Reenviar tokens pendentes</a></p>';
    echo '<p>Arquivos na raiz: <code>firebase-messaging-sw.js</code> ' . (file_exists(ABSPATH . 'firebase-messaging-sw.js') ? '(ok)' : '(ausente)') . ', <code>credityup-firebase-config.js</code> ' . (file_exists(ABSPATH . 'credityup-firebase-config.js') ? '(ok)' : '(ausente)') . '.</p>';
    echo '</div>';
}
