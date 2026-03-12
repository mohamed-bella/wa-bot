<?php
/**
 * Plugin Name: Basma WhatsApp Bot
 * Description: WhatsApp bot connection, test panel, and order notifications.
 * Version:     2.0.0
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* ── CONFIG ── */
if ( ! defined( 'BASMA_BOT_URL' ) )     define( 'BASMA_BOT_URL',     'http://77.42.43.52:3000' );
if ( ! defined( 'BASMA_BOT_API_KEY' ) ) define( 'BASMA_BOT_API_KEY', 'basma_api_secret_2024' );
if ( ! defined( 'BASMA_BOT_PHONE' ) )   define( 'BASMA_BOT_PHONE',   '212704969534' );

/* ══════════════════════════════════════════════════
   UTILITY: format phone to international format
══════════════════════════════════════════════════ */
if ( ! function_exists( 'basma_bot_format_phone' ) ) :
function basma_bot_format_phone( $phone ) {
    $phone = preg_replace( '/\D/', '', $phone );
    if ( strpos( $phone, '0' ) === 0 ) {
        $phone = '212' . substr( $phone, 1 );
    }
    return $phone;
}
endif;

/* ══════════════════════════════════════════════════
   UTILITY: send rich order confirmation (Client & Admin)
   Uses /api/send-message to allow links and images
══════════════════════════════════════════════════ */
if ( ! function_exists( 'basma_bot_send_rich_order_messages' ) ) :
function basma_bot_send_rich_order_messages( $client_phone, $name, $order_id, $total, $product_id ) {

    $client_phone = basma_bot_format_phone( $client_phone );
    $admin_phone  = basma_bot_format_phone( BASMA_BOT_PHONE ); // From settings

    // Details fallback
    $product_name = 'Produit de la commande';
    $product_url  = home_url();
    $product_img  = '';

    if ( $product_id > 0 ) {
        if ( function_exists( 'cso_get_product_data' ) ) {
            $data = cso_get_product_data( $product_id );
            if ( ! empty( $data['name'] ) ) $product_name = $data['name'];
            if ( ! empty( $data['images'][0] ) ) $product_img = $data['images'][0];
        }
        $post_url = get_permalink( $product_id );
        if ( $post_url ) $product_url = $post_url;
    }

    $price_str = number_format( floatval( $total ), 2 ) . ' MAD';

    // 1. Construct CLIENT Message
    $client_msg  = "[ *Nouvelle Commande Confirmée!* ]\n\n";
    $client_msg .= "Bonjour *" . $name . "*,\n";
    $client_msg .= "Merci pour votre commande *" . $order_id . "*\n";
    $client_msg .= "Total: *" . $price_str . "*\n\n";
    $client_msg .= "*Produit:* " . $product_name . "\n";
    $client_msg .= "*Lien:* " . $product_url . "\n\n";
    $client_msg .= "\nNous vous contacterons bientôt pour la livraison.";

    // 2. Construct ADMIN Message
    $admin_msg  = "[ *NOUVELLE COMMANDE REÇUE* ]\n\n";
    $admin_msg  .= "*ID:* " . $order_id . "\n";
    $admin_msg  .= "*Client:* " . $name . "\n";
    $admin_msg  .= "*Téléphone:* +" . $client_phone . "\n";
    $admin_msg  .= "*Total:* " . $price_str . "\n\n";
    $admin_msg  .= "*Produit:* " . $product_name . "\n";
    $admin_msg  .= "*Lien:* " . $product_url . "\n\n";

    // Send to Client (with image)
    $client_success = basma_bot_send_message( $client_phone, $client_msg, $product_img );

    // Send to Admin (with image) (only if it's not the same number testing)
    $admin_success = true;
    if ( $client_phone !== $admin_phone ) {
        $admin_success = basma_bot_send_message( $admin_phone, $admin_msg, $product_img );
    }

    return ( $client_success || $admin_success );
}
endif;

/* ══════════════════════════════════════════════════
   UTILITY: send a plain text message via /api/send-message
══════════════════════════════════════════════════ */
if ( ! function_exists( 'basma_bot_send_message' ) ) :
function basma_bot_send_message( $phone, $message_text, $image_url = '' ) {

    $phone = basma_bot_format_phone( $phone );

    $payload = array(
        'phone'   => $phone,
        'message' => $message_text,
    );

    if ( ! empty( $image_url ) ) {
        $payload['image_url'] = $image_url;
    }

    $resp = wp_remote_post( BASMA_BOT_URL . '/api/send-message', array(
        'timeout'  => 5,
        'blocking' => true,
        'headers'  => array(
            'Content-Type' => 'application/json',
            'x-api-key'    => BASMA_BOT_API_KEY,
        ),
        'body' => wp_json_encode( $payload ),
    ) );

    if ( is_wp_error( $resp ) ) {
        error_log( 'Basma Bot Error: ' . $resp->get_error_message() );
        return false;
    }

    $body = json_decode( wp_remote_retrieve_body( $resp ), true );
    return ! empty( $body['success'] );
}
endif;

/* ══════════════════════════════════════════════════
   HOOK: fires when plugin.php places a new order
══════════════════════════════════════════════════ */
if ( ! function_exists( 'basma_bot_on_order_placed' ) ) :
function basma_bot_on_order_placed( $order_id, $full_name, $phone, $total, $product_id ) {
    basma_bot_send_rich_order_messages( $phone, $full_name, $order_id, $total, $product_id );
}
endif;
add_action( 'basma_order_placed', 'basma_bot_on_order_placed', 10, 5 );

/* ══════════════════════════════════════════════════
   ADMIN AJAX: test button handler (server-side)
══════════════════════════════════════════════════ */
if ( ! function_exists( 'basma_bot_ajax_test' ) ) :
function basma_bot_ajax_test() {
    check_ajax_referer( 'basma_bot_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Permission denied.' ); wp_die();
    }

    $phone = sanitize_text_field( $_POST['phone'] ?? BASMA_BOT_PHONE );

    // Use the rich order confirmation endpoint for testing
    $success = basma_bot_send_rich_order_messages(
        $phone,
        'Test Admin',
        '#TEST-' . rand(1000, 9999),
        '1.00',
        0
    );

    if ( $success ) {
        wp_send_json_success( "Message de test (Client + Admin) envoy\xc3\xa9 avec succ\xc3\xa8s au " . basma_bot_format_phone($phone) . '!' );
    } else {
        wp_send_json_error( "Erreur. V\xc3\xa9rifiez que " . BASMA_BOT_URL . " est accessible et WhatsApp connect\xc3\xa9." );
    }
    wp_die();
}
endif;
add_action( 'wp_ajax_basma_bot_test', 'basma_bot_ajax_test' );

/* ══════════════════════════════════════════════════
   ADMIN MENU
══════════════════════════════════════════════════ */
if ( ! function_exists( 'basma_bot_admin_menu' ) ) :
function basma_bot_admin_menu() {
    add_menu_page(
        'WhatsApp Bot',
        'WhatsApp Bot',
        'manage_options',
        'basma-bot',
        'basma_bot_admin_page',
        'dashicons-whatsapp',
        57
    );
}
endif;
add_action( 'admin_menu', 'basma_bot_admin_menu' );

/* ══════════════════════════════════════════════════
   ADMIN PAGE RENDER
══════════════════════════════════════════════════ */
if ( ! function_exists( 'basma_bot_admin_page' ) ) :
function basma_bot_admin_page() {
    $nonce = wp_create_nonce( 'basma_bot_nonce' );
    $ajax  = admin_url( 'admin-ajax.php' );
    $url   = BASMA_BOT_URL;
    $key   = BASMA_BOT_API_KEY;
    $phone = BASMA_BOT_PHONE;
    ?>
    <div class="wrap">

        <h1 style="display:flex;align-items:center;gap:10px;margin-bottom:5px;">
            <span style="color:#25d366;font-size:28px;">&#x229B;</span> WhatsApp Bot
        </h1>
        <p class="description" style="margin-bottom:25px;">
            Testez la connexion de votre bot WhatsApp. Server: <code><?php echo esc_html($url); ?></code>
        </p>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;max-width:860px;">

            <!-- STATUS CARD -->
            <div style="background:#fff;padding:25px;border-radius:10px;border:1px solid #ddd;">
                <h2 style="margin-top:0;font-size:15px;padding-bottom:10px;border-bottom:1px solid #eee;">Statut du serveur</h2>
                <div id="wab-status" style="text-align:center;padding:20px;background:#f8fafc;border-radius:8px;min-height:80px;display:flex;align-items:center;justify-content:center;">
                    <em style="color:#94a3b8;">Verification...</em>
                </div>
                <button id="wab-check" class="button" style="margin-top:12px;width:100%;">
                    &#8635; Verifier connexion
                </button>
            </div>

            <!-- TEST SEND CARD -->
            <div style="background:#fff;padding:25px;border-radius:10px;border:1px solid #ddd;">
                <h2 style="margin-top:0;font-size:15px;padding-bottom:10px;border-bottom:1px solid #eee;">Tester l'envoi</h2>
                <label style="font-weight:700;font-size:13px;display:block;margin-bottom:6px;">Telephone (avec code pays)</label>
                <input type="text" id="wab-phone" value="<?php echo esc_attr($phone); ?>" style="width:100%;margin-bottom:12px;" class="regular-text" placeholder="212XXXXXXXXX">
                <button id="wab-test" class="button button-primary" style="width:100%;height:38px;background:#25d366;border-color:#25d366;font-weight:700;">
                    Envoyer message de test
                </button>
                <div id="wab-result" style="display:none;margin-top:10px;padding:10px;border-radius:6px;font-weight:600;font-size:13px;"></div>
            </div>

        </div>

        <!-- INFO TABLE -->
        <div style="margin-top:20px;max-width:860px;background:#fff;padding:20px;border-radius:10px;border:1px solid #ddd;">
            <h2 style="margin-top:0;font-size:15px;">Configuration</h2>
            <table class="widefat striped" style="border:none;">
                <tr><td style="font-weight:700;width:200px;">URL Serveur</td><td><code><?php echo esc_html($url); ?></code></td></tr>
                <tr><td style="font-weight:700;">API Key</td><td><code><?php echo esc_html($key); ?></code></td></tr>
                <tr><td style="font-weight:700;">Hook commandes</td>
                    <td><code>do_action('basma_order_placed', $order_id, $name, $phone, $total, $product_id)</code><br>
                    <small>Appele automatiquement dans plugin.php apres la sauvegarde de la commande.</small></td></tr>
            </table>
        </div>

    </div>

    <script>
    (function(){
        var ajaxUrl  = '<?php echo esc_js($ajax); ?>';
        var nonce    = '<?php echo esc_js($nonce); ?>';
        var botUrl   = '<?php echo esc_js($url); ?>';
        var apiKey   = '<?php echo esc_js($key); ?>';

        var statusEl  = document.getElementById('wab-status');
        var checkBtn  = document.getElementById('wab-check');
        var testBtn   = document.getElementById('wab-test');
        var phoneEl   = document.getElementById('wab-phone');
        var resultEl  = document.getElementById('wab-result');

        function checkStatus() {
            checkBtn.disabled = true;
            statusEl.innerHTML = '<em style="color:#94a3b8;">Connexion...</em>';
            fetch(botUrl + '/api/health')
            .then(function(r){ return r.json(); })
            .then(function(d){
                statusEl.innerHTML = '<div style="color:#10b981;font-weight:800;font-size:18px;">&#10003; Serveur en ligne</div>';
            })
            .catch(function(){
                statusEl.innerHTML = '<div style="color:#ef4444;font-weight:700;">&#9888; Serveur inaccessible depuis le navigateur<br><small style="color:#64748b;font-weight:400;">Utilisez le bouton Test (via PHP serveur) ci-contre.</small></div>';
            })
            .finally(function(){ checkBtn.disabled = false; });
        }

        checkBtn.addEventListener('click', checkStatus);
        checkStatus();

        testBtn.addEventListener('click', function(){
            var phone = phoneEl.value.trim();
            if(!phone){ alert('Entrez un numero.'); return; }
            testBtn.disabled = true;
            testBtn.textContent = 'Envoi...';
            resultEl.style.display = 'none';

            var fd = new FormData();
            fd.append('action', 'basma_bot_test');
            fd.append('nonce', nonce);
            fd.append('phone', phone);

            fetch(ajaxUrl, { method: 'POST', body: fd })
            .then(function(r){ return r.json(); })
            .then(function(d){
                resultEl.style.display = 'block';
                if (d.success) {
                    resultEl.style.background = '#dcfce7';
                    resultEl.style.color = '#166534';
                    resultEl.textContent = 'OK: ' + d.data;
                } else {
                    resultEl.style.background = '#fee2e2';
                    resultEl.style.color = '#991b1b';
                    resultEl.textContent = 'Erreur: ' + d.data;
                }
            })
            .catch(function(){
                resultEl.style.display = 'block';
                resultEl.style.background = '#fef3c7';
                resultEl.style.color = '#92400e';
                resultEl.textContent = 'Erreur reseau.';
            })
            .finally(function(){
                testBtn.disabled = false;
                testBtn.textContent = 'Envoyer message de test';
            });
        });
    })();
    </script>
    <?php
}
endif;
