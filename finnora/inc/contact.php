<?php
defined('ABSPATH') || exit;
function fin_handle_contact() {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') wp_die('Invalid request.', '', ['response' => 405]);
    foreach (['fin_nonce', 'fin_name', 'fin_email', 'fin_message', 'fin_topic', 'fin_website', 'fin_consent'] as $field) if (isset($_POST[$field]) && !is_string($_POST[$field])) wp_die('Invalid form data.', '', ['response' => 400]);
    if (!isset($_POST['fin_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['fin_nonce'])), 'fin_contact')) wp_die('This form has expired. Please reload the page and try again.', '', ['response' => 403]);
    $status = 'invalid';
    $recipient = sanitize_email(get_theme_mod('fin_contact_email', ''));
    $name = sanitize_text_field(wp_unslash($_POST['fin_name'] ?? ''));
    $email = sanitize_email(wp_unslash($_POST['fin_email'] ?? ''));
    $message = sanitize_textarea_field(wp_unslash($_POST['fin_message'] ?? ''));
    $topic = sanitize_key($_POST['fin_topic'] ?? 'feedback');
    if (!$recipient) $status = 'disabled';
    elseif (!empty($_POST['fin_website'])) $status = 'sent';
    elseif ($name && strlen($name) <= 300 && is_email($email) && strlen($message) >= 10 && strlen($message) <= 15000 && ($_POST['fin_consent'] ?? '') === '1') {
        $key = 'fin_contact_' . substr(wp_hash($_SERVER['REMOTE_ADDR'] ?? ''), 0, 24);
        if (get_transient($key)) $status = 'limited';
        else {
            set_transient($key, 1, 3 * MINUTE_IN_SECONDS);
            $status = wp_mail($recipient, 'FINNORA — ' . (['design' => 'Design enquiry', 'product' => 'Products', 'feedback' => 'Website feedback'][$topic] ?? 'Contact'), "Name: $name\nEmail: $email\n\n$message", ['Reply-To: ' . $email]) ? 'sent' : 'failed';
        }
    }
    wp_safe_redirect(add_query_arg('fin_contact', $status, fin_page_url('contact'))); exit;
}
add_action('admin_post_fin_contact', 'fin_handle_contact');
add_action('admin_post_nopriv_fin_contact', 'fin_handle_contact');
