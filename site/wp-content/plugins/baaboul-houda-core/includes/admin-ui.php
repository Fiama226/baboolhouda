<?php
/**
 * Admin UI: bilingual meta boxes (version arabe), episode source fields,
 * animateur extras, schedule repeater JS.
 */

defined('ABSPATH') || exit;

add_action('admin_enqueue_scripts', function (string $hook) {
    if ('toplevel_page_baaboul-houda' !== $hook && 'post.php' !== $hook && 'post-new.php' !== $hook) {
        return;
    }
    wp_enqueue_style('bh-admin', BH_CORE_URL . 'assets/dist/css/admin.css', [], BH_VERSION);
    wp_enqueue_script('bh-admin', BH_CORE_URL . 'assets/dist/js/admin.js', [], BH_VERSION, true);

    // Media library modal (upload/choose an audio or video file) on the episode editor.
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if ($screen && 'episode' === $screen->post_type) {
        wp_enqueue_media();
    }
});

/* ---------------------------------------------------------------------
 * Bilingual (AR) meta box — pages, posts, programmes, episodes, events
 * ------------------------------------------------------------------- */
add_action('add_meta_boxes', function () {
    foreach (['page', 'post', 'programme', 'episode', 'evenement', 'animateur'] as $type) {
        add_meta_box('bh-ar', 'Version arabe (النسخة العربية)', 'bh_render_ar_metabox', $type, 'normal', 'high');
    }
    add_meta_box('bh-episode-source', 'Source de la vidéo / audio (replay)', 'bh_render_episode_metabox', 'episode', 'side');
});

function bh_render_ar_metabox(\WP_Post $post): void {
    wp_nonce_field('bh_ar_' . $post->ID, 'bh_ar_nonce');
    $title = get_post_meta($post->ID, '_bh_title_ar', true);
    $body  = get_post_meta($post->ID, '_bh_content_ar', true);
    $ex    = get_post_meta($post->ID, '_bh_excerpt_ar', true);
    echo '<p><label for="bh-title-ar">Titre (العنوان)</label><br>'
        . '<input type="text" id="bh-title-ar" name="bh_title_ar" class="widefat" value="' . esc_attr($title) . '"></p>';
    echo '<p><label for="bh-excerpt-ar">Résumé (اختصار)</label><br>'
        . '<textarea id="bh-excerpt-ar" name="bh_excerpt_ar" class="widefat" rows="2">' . esc_textarea($ex) . '</textarea></p>';
    echo '<p><label for="bh-content-ar">Contenu (المحتوى)</label><br>'
        . '<textarea id="bh-content-ar" name="bh_content_ar" class="widefat" rows="6" dir="rtl" placeholder="الصيغة العربية — تُنشر تلقائيًا على /ar/">' . esc_textarea($body) . '</textarea></p>';
    echo '<p class="description">Laissez vide pour afficher la version française sous /ar/.</p>';
}

function bh_render_episode_metabox(\WP_Post $post): void {
    $type  = get_post_meta($post->ID, '_bh_source_type', true) ?: 'youtube';
    $url   = get_post_meta($post->ID, '_bh_source_url', true);
    $audio = get_post_meta($post->ID, '_bh_audio_url', true);
    $pid   = (int) get_post_meta($post->ID, '_bh_programme_id', true);
    $date  = get_post_meta($post->ID, '_bh_date', true);

    $options = [0 => '— Aucun —'];
    $q = new \WP_Query(['post_type' => 'programme', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC', 'no_found_rows' => true]);
    foreach ($q->posts as $p) {
        $options[$p->ID] = get_the_title($p->ID);
    }

    echo '<p><label>Programme parent</label><br><select name="bh_programme_id">'
        . wp_kses_post(implode('', array_map(static fn ($id, $label) => '<option value="' . (int) $id . '"' . selected($pid, $id, false) . '>' . esc_html($label) . '</option>', array_keys($options), $options)))
        . '</select></p>';
    echo '<p><label>Date de diffusion</label><br><input type="date" name="bh_date" value="' . esc_attr($date) . '"></p>';
    echo '<div class="bh-episode-source">';
    echo '<p><label for="bh-source-type">Type de source</label><br><select id="bh-source-type" name="bh_source_type">'
        . '<option value="youtube"' . selected($type, 'youtube', false) . '>YouTube (URL)</option>'
        . '<option value="facebook"' . selected($type, 'facebook', false) . '>Facebook (URL)</option>'
        . '<option value="file"' . selected($type, 'file', false) . '>Vidéo auto-hébergée (.mp4 / URL)</option>'
        . '<option value="audio"' . selected($type, 'audio', false) . '>Audio seul — replay radio (.mp3 / URL)</option>'
        . '</select></p>';
    echo '<p><label for="bh-source-url">URL</label><br><input type="url" id="bh-source-url" name="bh_source_url" class="widefat" value="' . esc_attr($url) . '" placeholder="https://…">'
        . '<button type="button" class="button bh-media-pick" data-target="bh-source-url" data-library="audio" style="margin-top:6px">Téléverser / choisir un fichier</button></p>';
    echo '<p class="description" data-bh-hint="audio">Replay radio : collez le lien du fichier audio, ou cliquez sur « Téléverser / choisir un fichier » pour l’envoyer depuis votre ordinateur. Il apparaîtra sur la page Radio sous forme de lecteur audio.</p>';
    echo '<p><label for="bh-audio-url">URL audio (optionnel)</label><br><input type="url" id="bh-audio-url" name="bh_audio_url" class="widefat" value="' . esc_attr($audio) . '" placeholder="https://…/emission.mp3">'
        . '<button type="button" class="button bh-media-pick" data-target="bh-audio-url" data-library="audio" style="margin-top:6px">Téléverser / choisir un audio</button></p>';
    echo '<p class="description">Pour une vidéo YouTube/Facebook, vous pouvez en plus fournir ici la version audio : l’épisode sera aussi listé comme replay radio.</p>';
    echo '</div>';
}

add_action('save_post', function (int $post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    $type = get_post_type($post_id);
    if (!in_array($type, ['page', 'post', 'programme', 'episode', 'evenement', 'animateur'], true)) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    if (!isset($_POST['bh_ar_nonce']) || !wp_verify_nonce(wp_unslash($_POST['bh_ar_nonce']), 'bh_ar_' . $post_id)) {
        return;
    }
    update_post_meta($post_id, '_bh_title_ar', sanitize_text_field(wp_unslash($_POST['bh_title_ar'] ?? '')));
    $body = wp_kses_post(wp_unslash($_POST['bh_content_ar'] ?? ''));
    update_post_meta($post_id, '_bh_content_ar', $body ? wpautop($body) : '');
    update_post_meta($post_id, '_bh_excerpt_ar', sanitize_textarea_field(wp_unslash($_POST['bh_excerpt_ar'] ?? '')));

    if ('episode' === $type) {
        $stype = $_POST['bh_source_type'] ?? 'youtube';
        if (in_array($stype, ['youtube', 'facebook', 'file', 'audio'], true)) {
            update_post_meta($post_id, '_bh_source_type', $stype);
        }
        update_post_meta($post_id, '_bh_source_url', esc_url_raw(wp_unslash($_POST['bh_source_url'] ?? '')));
        update_post_meta($post_id, '_bh_audio_url', esc_url_raw(wp_unslash($_POST['bh_audio_url'] ?? '')));
        update_post_meta($post_id, '_bh_programme_id', (int) ($_POST['bh_programme_id'] ?? 0));
        update_post_meta($post_id, '_bh_date', sanitize_text_field(wp_unslash($_POST['bh_date'] ?? '')));
    }
});
