<?php
/**
 * Contact form — dependency-free (no plugin needed), honeypot protected,
 * delivered by email (WP_Mail) with WhatsApp fallback guidance.
 *
 * Usage in templates: do_shortcode('[bh_contact_form]')
 */

defined('ABSPATH') || exit;

add_shortcode('bh_contact_form', function (): string {
    $s = bh_settings();
    if (empty($s['contact_enabled'])) {
        return '<p class="bh-note">' . esc_html(bh_l('Le formulaire de contact est momentanément désactivé.', 'نموذج الاتصال غير مفعّل حاليًا.')) . '</p>';
    }

    $status = isset($_GET['bh_contact']) ? sanitize_key(wp_unslash($_GET['bh_contact'])) : '';
    $whatsapp = preg_replace('/[^0-9+]/', '', (string) $s['whatsapp']);

    $html  = '<div class="bh-contact">';
    if ('ok' === $status) {
        $html .= '<div class="bh-alert bh-alert-ok" role="status">' . esc_html(bh_l('Merci ! Votre message a bien été envoyé. Nous vous répondrons dès que possible.', 'شكرًا لكم! تم إرسال رسالتكم بنجاح وسنرد عليكم في أقرب وقت.')) . '</div>';
    } elseif ('err' === $status) {
        $html .= '<div class="bh-alert bh-alert-err" role="alert">' . esc_html(bh_l('Désolé, votre message n’a pas pu être envoyé. Réessayez ou écrivez-nous sur WhatsApp.', 'عذرًا، تعذّر إرسال رسالتكم. حاولوا مرة أخرى أو راسلونا على واتساب.')) . '</div>';
    }
    $html .= '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" class="bh-contact-form">';
    $html .= '<input type="hidden" name="action" value="bh_contact">';
    wp_nonce_field('bh_contact', 'bh_contact_nonce');
    $html .= '<p><label for="bh-cf-name">' . esc_html(bh_l('Nom complet', 'الاسم الكامل')) . ' <span class="bh-req">*</span></label>'
        . '<input type="text" id="bh-cf-name" name="bh_name" required maxlength="120"></p>';
    $html .= '<p><label for="bh-cf-email">' . esc_html(bh_l('Email', 'البريد الإلكتروني')) . ' <span class="bh-req">*</span></label>'
        . '<input type="email" id="bh-cf-email" name="bh_email" required maxlength="190"></p>';
    $html .= '<p class="bh-hp" aria-hidden="true"><label for="bh-cf-ws">Site web</label>'
        . '<input type="text" id="bh-cf-ws" name="bh_website" tabindex="-1" autocomplete="off"></p>';
    $html .= '<p><label for="bh-cf-subject">' . esc_html(bh_l('Sujet', 'الموضوع')) . '</label>'
        . '<input type="text" id="bh-cf-subject" name="bh_subject" maxlength="190"></p>';
    $html .= '<p><label for="bh-cf-msg">' . esc_html(bh_l('Message', 'الرسالة')) . ' <span class="bh-req">*</span></label>'
        . '<textarea id="bh-cf-msg" name="bh_message" rows="5" required maxlength="5000"></textarea></p>';
    $html .= '<p><button type="submit" class="bh-btn bh-btn-primary">' . esc_html(bh_l('Envoyer le message', 'إرسال الرسالة')) . '</button>';
    if ($whatsapp) {
        $html .= ' <a class="bh-btn bh-btn-ghost" href="https://wa.me/' . esc_attr($whatsapp) . '" target="_blank" rel="noopener">' . esc_html(bh_l('Ou écrivez-nous sur WhatsApp', 'أو راسلونا عبر واتساب')) . '</a>';
    }
    $html .= '</p></form></div>';

    return $html;
});

add_action('admin_post_nopriv_bh_contact', 'bh_handle_contact');
add_action('admin_post_bh_contact', 'bh_handle_contact');

function bh_handle_contact(): void {
    $redirect_to = wp_get_referer() ? wp_get_referer() : home_url('/');
    $fail = static function () use ($redirect_to) {
        wp_safe_redirect(add_query_arg('bh_contact', 'err', $redirect_to));
        exit;
    };

    if (!isset($_POST['bh_contact_nonce']) || !wp_verify_nonce(wp_unslash($_POST['bh_contact_nonce']), 'bh_contact')) {
        $fail();
    }
    // Honeypot: bots fill the hidden field.
    if (!empty($_POST['bh_website'])) {
        wp_safe_redirect(add_query_arg('bh_contact', 'ok', $redirect_to)); // silent success for bots
        exit;
    }

    $name    = sanitize_text_field(wp_unslash($_POST['bh_name'] ?? ''));
    $email   = sanitize_email(wp_unslash($_POST['bh_email'] ?? ''));
    $subject = sanitize_text_field(wp_unslash($_POST['bh_subject'] ?? ''));
    $message = sanitize_textarea_field(wp_unslash($_POST['bh_message'] ?? ''));

    if (!$name || !is_email($email) || !$message) {
        $fail();
    }

    $s     = bh_settings();
    $to    = !empty($s['contact_to']) ? $s['contact_to'] : get_option('admin_email');
    $site  = get_bloginfo('name');
    $body  = "Nom: {$name}\nEmail: {$email}\nSujet: {$subject}\n\n{$message}\n\n— Formulaire de contact, " . home_url('/');
    $ok    = wp_mail(
        $to,
        '[Site ' . $site . '] ' . ($subject ? $subject : 'Nouveau message'),
        $body,
        ['Reply-To: ' . $name . ' <' . $email . '>'],
    );

    if (!$ok) {
        // Do not lose the message when the mail transport is unavailable (dev, misconfigured SMTP).
        error_log('[bh-contact] wp_mail failed; message kept here: ' . str_replace("
", ' | ', $body));
    }

    wp_safe_redirect(add_query_arg('bh_contact', $ok ? 'ok' : 'err', $redirect_to));
    exit;
}

// PHPMailer rejects "wordpress@localhost" (no dotted domain): fall back to the admin address.
add_filter('wp_mail_from', function ($from) {
    $host = substr((string) strrchr((string) $from, '@'), 1);
    return (false === strpos($host, '.')) ? (string) get_option('admin_email') : $from;
});
