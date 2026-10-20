<?php
/**
 * Community layer:
 *  - CPTs: mosquee, temoignage, message (viewer wall), question (ask a
 *    scholar), fatwa (Q&A archive), galerie (photo gallery)
 *  - Newsletter: wp_bh_newsletter table + admin list/CSV export
 *  - Form handlers (admin-post): question, message wall
 *  - View counter + "trending"
 *  - Facebook auto-post when the channel goes live
 */

defined('ABSPATH') || exit;

/* ---------------------------------------------------------------------
 * CPTs
 * ------------------------------------------------------------------- */

add_action('init', function () {
    $base = [
        'public'       => true,
        'has_archive'  => false,
        'show_in_rest' => true,
        'supports'     => ['title', 'editor', 'thumbnail'],
    ];
    register_post_type('mosquee', array_merge($base, [
        'labels'      => ['name' => 'Mosquées', 'singular_name' => 'Mosquée', 'add_new_item' => 'Ajouter une mosquée'],
        'menu_icon'   => 'dashicons-lightbulb',
        'supports'    => ['title'],
        'rewrite'     => ['slug' => 'mosquee', 'with_front' => false],
    ]));
    register_post_type('temoignage', array_merge($base, [
        'labels'      => ['name' => 'Témoignages', 'singular_name' => 'Témoignage'],
        'menu_icon'   => 'dashicons-format-quote',
        'rewrite'     => ['slug' => 'temoignage', 'with_front' => false],
    ]));
    register_post_type('message', array_merge($base, [
        'labels'      => ['name' => 'Messages des fidèles', 'singular_name' => 'Message'],
        'menu_icon'   => 'dashicons-format-chat',
        'rewrite'     => ['slug' => 'message-fidele', 'with_front' => false],
    ]));
    register_post_type('question', array_merge($base, [
        'labels'      => ['name' => 'Questions (posez une question)', 'singular_name' => 'Question'],
        'menu_icon'   => 'dashicons-editor-help',
        'rewrite'     => ['slug' => 'question', 'with_front' => false],
    ]));
    register_post_type('fatwa', array_merge($base, [
        'labels'      => ['name' => 'Fatwas (questions-réponses)', 'singular_name' => 'Fatwa', 'add_new_item' => 'Ajouter une fatwa'],
        'menu_icon'   => 'dashicons-yes',
        'rewrite'     => ['slug' => 'fatwa', 'with_front' => false],
    ]));
    register_post_type('galerie', array_merge($base, [
        'labels'      => ['name' => 'Galleries photo', 'singular_name' => 'Galerie', 'add_new_item' => 'Ajouter une galerie'],
        'menu_icon'   => 'dashicons-format-image',
        'supports'    => ['title', 'editor'],
        'rewrite'     => ['slug' => 'galerie', 'with_front' => false],
    ]));

    register_taxonomy('sujet_fatwa', 'fatwa', [
        'labels'       => ['name' => 'Sujets', 'singular_name' => 'Sujet'],
        'hierarchical' => true,
        'public'       => true,
        'rewrite'      => ['slug' => 'sujet-fatwa', 'with_front' => false],
    ]);
    register_taxonomy('sujet_episod', 'episode', [
        'labels'       => ['name' => 'Sujets (épisodes)', 'singular_name' => 'Sujet'],
        'hierarchical' => true,
        'public'       => true,
        'rewrite'      => ['slug' => 'sujet-episodes', 'with_front' => false],
    ]);
});

/* ---------------------------------------------------------------------
 * Meta boxes
 * ------------------------------------------------------------------- */

add_action('add_meta_boxes', function () {
    // AR title on the NEW content types (existing types already have the full
    // "Version arabe" box from admin-ui.php).
    foreach (['mosquee', 'temoignage', 'message', 'question', 'fatwa', 'galerie'] as $pt) {
        add_meta_box('bh-ar-title', 'العنوان (العربية)', 'bh_meta_ar_title', $pt, 'side');
    }
    add_meta_box('bh-mosque-details', 'Détails de la mosquée', 'bh_meta_mosque', 'mosquee', 'normal');
    add_meta_box('bh-gallery-images', 'Images de la galerie', 'bh_meta_gallery', 'galerie', 'normal');
    add_meta_box('bh-episode-presenter', 'Animateur / invitée de l’épisode', 'bh_meta_episode_presenter', 'episode', 'side');
    add_meta_box('bh-episode-transcript', 'Transcript de l’épisode (FR)', 'bh_meta_episode_transcript', 'episode', 'normal');
    add_meta_box('bh-fatwa-answer', 'Réponse (le titre de la fatwa = la question)', 'bh_meta_noop', 'fatwa', 'normal', 'high');
});

function bh_meta_ar_title(\WP_Post $post): void {
    wp_nonce_field('bh_ar_meta', 'bh_ar_nonce');
    $v = get_post_meta($post->ID, '_bh_title_ar', true);
    echo '<input type="text" name="_bh_title_ar" class="widefat" dir="rtl" style="text-align:right" value="' . esc_attr($v) . '">';
}

function bh_meta_mosque(\WP_Post $post): void {
    wp_nonce_field('bh_mosque_meta', 'bh_mosque_nonce');
    $g = static fn ($k) => esc_attr((string) get_post_meta($post->ID, $k, true));
    echo '<p><label>Adresse (pour la carte)<br><input type="text" name="_bh_address" class="widefat" value="' . $g('_bh_address') . '"></label></p>';
    echo '<p><label>Imam / contact<br><input type="text" name="_bh_imam" class="widefat" value="' . $g('_bh_imam') . '"></label></p>';
    echo '<p><label>Téléphone<br><input type="text" name="_bh_phone" class="widefat" value="' . $g('_bh_phone') . '"></label></p>';
    echo '<p><label>URL d’image Google Maps (optionnel)<br><input type="url" name="_bh_map_url" class="widefat" value="' . $g('_bh_map_url') . '"></label></p>';
    echo '<p><label>Notes (horaires de prière, salle femmes…)<br><textarea name="_bh_notes" class="widefat" rows="3">' . $g('_bh_notes') . '</textarea></label></p>';
}

function bh_meta_gallery(\WP_Post $post): void {
    wp_nonce_field('bh_gallery_meta', 'bh_gallery_nonce');
    $ids = (array) get_post_meta($post->ID, '_bh_gallery_ids', true);
    echo '<p>Cliquez sur un média (image) puis « Add to gallery » — l’ordre de la galerie suit cet ordre.</p>';
    wp_media_frame_placeholder();
    echo '<input type="hidden" name="_bh_gallery_ids" id="bh-gallery-ids" value="' . esc_attr(implode(',', $ids)) . '">';
    echo '<div id="bh-gallery-preview" style="display:flex;gap:8px;flex-wrap:wrap;margin-top:10px"></div>';
}

/** Minimal media frame loader (WP core function, safe in admin). */
function wp_media_frame_placeholder(): void {
    echo '<button type="button" class="button" id="bh-open-media">Ajouter des images</button>';
    echo '<script>
    (function () {
        var frame, $input = document.getElementById("bh-gallery-ids"), $prev = document.getElementById("bh-gallery-preview");
        function refresh() {
            $prev.innerHTML = "";
            $input.value.split(",").filter(Boolean).forEach(function (id) {
                var a = document.createElement("a");
                a.href = "#"; a.innerHTML = id;
                $prev.appendChild(a);
            });
        }
        document.getElementById("bh-open-media").onclick = function (e) {
            e.preventDefault();
            if (typeof wp === "undefined" || !wp.media) return;
            frame = wp.media({ title: "Images de la galerie", library: { type: "image" }, multiple: "add" });
            frame.on("update", function (sel) {
                var ids = [];
                sel.forEach(function (att) { ids.push(att.id); });
                $input.value = ids.join(",");
                refresh();
            });
            frame.open();
        };
        refresh();
    })();
    </script>';
}

function bh_meta_episode_presenter(\WP_Post $post): void {
    wp_nonce_field('bh_presenter_meta', 'bh_presenter_nonce');
    $sel = (int) get_post_meta($post->ID, '_bh_presenter_id', true);
    echo '<select name="_bh_presenter_id" class="widefat"><option value="0">— Aucun —</option>';
    $q = new \WP_Query(['post_type' => 'animateur', 'posts_per_page' => -1, 'no_found_rows' => true, 'orderby' => 'title']);
    foreach ($q->posts as $p) {
        echo '<option value="' . (int) $p->ID . '"' . selected($sel, $p->ID, false) . '>' . esc_html(get_the_title($p)) . '</option>';
    }
    echo '</select>';
}

function bh_meta_episode_transcript(\WP_Post $post): void {
    wp_nonce_field('bh_transcript_meta', 'bh_transcript_nonce');
    $v = get_post_meta($post->ID, '_bh_transcript', true);
    echo '<textarea name="_bh_transcript" class="widefat" rows="8" placeholder="Transcript (texte intégral ou résumé détaillé de l’émission)">' . esc_textarea($v) . '</textarea>';
}

function bh_meta_noop(\WP_Post $post): void {
    echo '<p class="description">Le titre de la fatwa est la QUESTION ; la zone de contenu est la RÉPONSE.</p>';
}

add_action('save_post', function (int $post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    $map = [
        '_bh_title_ar'        => ['_bh_title_ar', 'bh_ar_nonce'],
        '_bh_address'         => ['_bh_address', 'bh_mosque_nonce'],
        '_bh_imam'            => ['_bh_imam', 'bh_mosque_nonce'],
        '_bh_phone'           => ['_bh_phone', 'bh_mosque_nonce'],
        '_bh_notes'           => ['_bh_notes', 'bh_mosque_nonce'],
        '_bh_map_url'         => ['_bh_map_url', 'bh_mosque_nonce'],
        '_bh_gallery_ids'     => ['_bh_gallery_ids', 'bh_gallery_nonce'],
        '_bh_presenter_id'    => ['_bh_presenter_id', 'bh_presenter_nonce'],
        '_bh_transcript'      => ['_bh_transcript', 'bh_transcript_nonce'],
    ];
    foreach ($map as $key => [$field, $nonce]) {
        if (!isset($_POST[$nonce]) || !wp_verify_nonce(sanitize_key($_POST[$nonce]), $nonce)) {
            continue;
        }
        if (isset($_POST[$field])) {
            $value = $_POST[$field];
            if ('_bh_presenter_id' === $field) {
                $value = (int) $value;
            } else {
                $value = sanitize_textarea_field(wp_unslash($value));
            }
            update_post_meta($post_id, $key, $value);
        }
    }
});

/* ---------------------------------------------------------------------
 * Newsletter: table + admin
 * ------------------------------------------------------------------- */

function bh_newsletter_install(): void {
    global $wpdb;
    $table  = bh_newsletter_table();
    $char   = $wpdb->get_charset_collate();
    $sql    = "CREATE TABLE {$table} (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        email VARCHAR(190) NOT NULL,
        created_at DATETIME NOT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY email (email)
    ) {$char};";
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}

add_action('admin_menu', function () {
    add_submenu_page('baaboul-houda', 'Newsletter', 'Newsletter', 'manage_options', 'bh-newsletter', 'bh_render_newsletter_page');
});

function bh_render_newsletter_page(): void {
    if (!current_user_can('manage_options')) {
        return;
    }
    global $wpdb;
    $table = bh_newsletter_table();
    $count = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table}");

    if (isset($_POST['bh_export']) && check_admin_referer('bh_export_newsletter', 'bh_export_nonce')) {
        $rows = $wpdb->get_results("SELECT email, created_at FROM {$table} ORDER BY created_at DESC");
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename=bh-newsletter.csv');
        echo "email;created_at\n";
        foreach ($rows as $r) {
            echo $r->email . ';' . $r->created_at . "\n";
        }
        exit;
    }

    $recent = $wpdb->get_results("SELECT email, created_at FROM {$table} ORDER BY created_at DESC LIMIT 20");
    ?>
    <div class="wrap">
        <h1>Newsletter — <?php echo (int) $count; ?> inscrit(s)</h1>
        <form method="post">
            <?php wp_nonce_field('bh_export_newsletter', 'bh_export_nonce'); ?>
            <button class="button button-primary" name="bh_export" value="1">Exporter CSV</button>
        </form>
        <table class="widefat" style="margin-top:12px;max-width:600px">
            <thead><tr><th>Email</th><th>Inscrit le</th></tr></thead>
            <tbody>
            <?php foreach ($recent as $r) : ?>
                <tr><td><?php echo esc_html($r->email); ?></td><td><?php echo esc_html($r->created_at); ?></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}

/* ---------------------------------------------------------------------
 * Form handlers: "ask a scholar" + viewer wall (admin-post, nonce'd).
 * ------------------------------------------------------------------- */

add_action('admin_post_bh_question', 'bh_handle_question_form');
add_action('admin_post_nopriv_bh_question', 'bh_handle_question_form');

function bh_handle_question_form(): void {
    $redirect = wp_get_referer() ?: home_url('/');
    if (!isset($_POST['bh_question_nonce']) || !wp_verify_nonce(sanitize_key($_POST['bh_question_nonce']), 'bh_question')) {
        wp_safe_redirect(add_query_arg('bh_err', 'nonce', $redirect));
        exit;
    }
    $name  = sanitize_text_field(wp_unslash($_POST['name'] ?? ''));
    $email = sanitize_email(wp_unslash($_POST['email'] ?? ''));
    $text  = sanitize_textarea_field(wp_unslash($_POST['question'] ?? ''));
    if ('' === $text) {
        wp_safe_redirect(add_query_arg('bh_err', 'empty', $redirect));
        exit;
    }
    $id = wp_insert_post([
        'post_type'    => 'question',
        'post_status'  => 'pending',
        'post_title'   => mb_substr($text, 0, 90),
        'post_content' => $text,
    ]);
    if ($id && !is_wp_error($id)) {
        update_post_meta($id, '_bh_question_name', $name);
        update_post_meta($id, '_bh_question_email', $email);
        $to = bh_settings()['contact_to'] ?: get_option('admin_email');
        if ($to) {
            wp_mail($to, '[TV Baaboul Houda] Nouvelle question d’un fidèle', $text . "\n\n— " . $name . ($email ? " ($email)" : ''));
        }
    }
    wp_safe_redirect(add_query_arg('bh_ok', 'question', $redirect));
    exit;
}

add_action('admin_post_bh_message_wall', 'bh_handle_message_wall_form');
add_action('admin_post_nopriv_bh_message_wall', 'bh_handle_message_wall_form');

function bh_handle_message_wall_form(): void {
    $redirect = wp_get_referer() ?: home_url('/');
    if (!isset($_POST['bh_wall_nonce']) || !wp_verify_nonce(sanitize_key($_POST['bh_wall_nonce']), 'bh_wall')) {
        wp_safe_redirect(add_query_arg('bh_err', 'nonce', $redirect));
        exit;
    }
    $name = sanitize_text_field(wp_unslash($_POST['name'] ?? ''));
    $text = sanitize_textarea_field(wp_unslash($_POST['message'] ?? ''));
    if ('' === $text) {
        wp_safe_redirect(add_query_arg('bh_err', 'empty', $redirect));
        exit;
    }
    $id = wp_insert_post([
        'post_type'    => 'message',
        'post_status'  => 'pending',
        'post_title'   => mb_substr($text, 0, 90),
        'post_content' => $text,
    ]);
    if ($id && !is_wp_error($id)) {
        update_post_meta($id, '_bh_question_name', $name);
    }
    wp_safe_redirect(add_query_arg('bh_ok', 'wall', $redirect));
    exit;
}

/* ---------------------------------------------------------------------
 * View counter + trending
 * ------------------------------------------------------------------- */

add_action('template_redirect', function () {
    if (!is_singular() || ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' || is_admin()) {
        return;
    }
    $s = function_exists('bh_settings') ? bh_settings() : [];
    if (empty($s['views_enabled'])) {
        return;
    }
    $id  = get_queried_object_id();
    $key = 'bh_views_seen_' . $id;
    if (!isset($_COOKIE[$key])) {
        $views = (int) get_post_meta($id, '_bh_views', true);
        update_post_meta($id, '_bh_views', $views + 1);
        setcookie($key, '1', time() + DAY_IN_SECONDS, '/');
    }
});

function bh_view_count(int $post_id): int {
    return (int) get_post_meta($post_id, '_bh_views', true);
}

/** Top N posts by views (trending sidebar). */
function bh_trending(int $limit = 5): array {
    global $wpdb;
    $rows = $wpdb->get_results("SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_bh_views' ORDER BY meta_value+0 DESC LIMIT " . (int) $limit);
    $out = [];
    foreach ($rows as $r) {
        $p = get_post($r->post_id);
        if ($p && 'publish' === $p->post_status && in_array($p->post_type, ['post', 'episode', 'programme'], true)) {
            $out[] = ['post' => $p, 'views' => (int) $r->meta_value];
        }
    }
    return $out;
}

/* ---------------------------------------------------------------------
 * Facebook auto-post when the channel goes live.
 * ------------------------------------------------------------------- */

add_action('updated_option', function (string $option, $old, $new) {
    if ('bh_settings' !== $option || !is_array($new) || !is_array($old)) {
        return;
    }
    $wasLive = !empty($old['live_enabled']) && !empty($old['live_platform']) && 'off' !== $old['live_platform'];
    $isLive  = !empty($new['live_enabled']) && !empty($new['live_platform']) && 'off' !== $new['live_platform'];
    if (!$isLive || $wasLive) {
        return;
    }
    if (empty($new['fb_autopost']) || empty($new['fb_page_id']) || empty($new['fb_token'])) {
        return;
    }
    $lastUrl = (string) get_option('bh_last_live_post_url', '');
    if ($lastUrl === (string) ($new['live_url'] ?? '')) {
        return;
    }
    $url  = home_url('/direct/');
    $body = wp_remote_post('https://graph.facebook.com/v19.0/' . rawurlencode((string) $new['fb_page_id']) . '/feed', [
        'timeout' => 15,
        'body'    => [
            'message' => ($new['live_title_fr'] ?: 'En direct') . ' — ' . $url,
            'access_token' => $new['fb_token'],
        ],
    ]);
    if (!is_wp_error($body)) {
        update_option('bh_last_live_post_url', (string) ($new['live_url'] ?? ''), false);
    }
}, 10, 3);
