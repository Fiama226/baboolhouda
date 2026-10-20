<?php
/**
 * Site settings — one options array, one admin page, grouped sections.
 * This is the "update the site" console for the station's team.
 */

defined('ABSPATH') || exit;

function bh_default_settings(): array {
    return [
        // Identité
        'logo_url'        => '',
        'og_image_url'    => '',
        'color_accent'    => '#0e7a4e',
        'facebook_url'    => 'https://www.facebook.com/profile.php?id=100083362387605',
        'youtube_url'     => '',
        'whatsapp'        => '+22676205930',
        'phone'           => '+22676205930',
        'email'           => '',
        'address'         => 'Ouagadougou, Burkina Faso',
        // En direct
        'live_enabled'    => '1',
        'live_platform'   => 'youtube', // youtube | facebook | hls | off
        'live_url'        => 'https://www.youtube.com/watch?v=TseWV8tk2kc', // défaut : chaîne 24/7 YouTube
        'live_title_fr'   => 'Diffusion en direct',
        'live_title_ar'   => 'بث مباشر',
        // Radio
        'radio_enabled'   => '0',
        'radio_url'       => '',
        'radio_title_fr'  => 'Radio Baaboul Houda',
        'radio_title_ar'  => 'إذاعة باب الهدى',
        'radio_cover_url' => '',
        // Grille des programmes : [ {day, start, end, programme_id} ]
        'schedule'        => [],
        // Prières
        'prayer_city'     => 'Ouagadougou',
        'prayer_method'   => '3', // Muslim World League (usuel en Afrique de l'Ouest)
        'prayer_cities'   => 'Ouagadougou,Bobo-Dioulasso,Koudougou,Ganndougou,Houndé',
        // Contact
        'contact_to'      => '',
        'contact_enabled' => '1',
        // Deuxième chaîne (« Coran 24/7 »)
        'live2_enabled'   => '0',
        'live2_platform'  => 'youtube',
        'live2_url'       => '',
        'live2_title_fr'  => 'Coran en continu',
        'live2_title_ar'  => 'القرآن الكريم 24/7',
        // Radio : historique « dernier programme diffusé »
        'radio_history'   => '', // lignes "Titre — HH:MM"
        // Adhan
        'adhan_enabled'   => '0',
        'adhan_url'       => '',
        // Zakat (référence affichée ; le calcul se fait côté visiteur)
        'zakat_nisab_amount' => 0,
        'zakat_currency'     => 'FCFA',
        'zakat_nisab_fr'     => '',
        'zakat_nisab_ar'     => '',
        // Dons
        'donate_enabled'  => '1',
        'donate_om'       => '',   // numéro Orange Money
        'donate_wave'     => '',   // numéro Wave
        'donate_bank'     => '',   // RIB / banque
        'donate_email'    => '',
        // Publicité
        'ads_enabled'     => '1',
        'ads_email'       => '',
        'ads_fr'          => '',
        'ads_ar'          => '',
        // Facebook auto-post (direct → page)
        'fb_autopost'     => '0',
        'fb_page_id'      => '',
        'fb_token'        => '',
        // Newsletter
        'newsletter_enabled' => '1',
        // Compteur de vues / tendances
        'views_enabled'   => '1',
        // Divers
        'analytics_enabled' => '0',
        'analytics_snippet' => '',
        'footer_text_fr'  => 'TV Baaboul Houda — Média islamique en ligne, Ouagadougou (Burkina Faso).',
        'footer_text_ar'  => 'قناة باب الهدى — إعلام إسلامي إلكتروني، واغادوغو (بوركينا فاسو).',
    ];
}

function bh_settings(): array {
    $defaults = bh_default_settings();
    $saved    = get_option('bh_settings', []);
    return is_array($saved) ? array_merge($defaults, $saved) : $defaults;
}

/* ---------------------------------------------------------------------
 * Registration + sanitization
 * ------------------------------------------------------------------- */
add_action('admin_init', function () {
    register_setting('bh_settings_group', 'bh_settings', [
        'sanitize_callback' => 'bh_sanitize_settings',
    ]);
});

function bh_sanitize_settings($input): array {
    $clean    = bh_default_settings();
    $input    = is_array($input) ? $input : [];

    $str = static fn ($k) => isset($input[$k]) ? sanitize_text_field(wp_unslash($input[$k])) : $clean[$k];

    foreach (['logo_url', 'og_image_url', 'facebook_url', 'youtube_url', 'whatsapp', 'phone', 'address',
              'live_url', 'radio_url', 'radio_cover_url', 'prayer_city', 'prayer_cities', 'contact_to',
              'live_title_fr', 'live_title_ar', 'radio_title_fr', 'radio_title_ar',
              'live2_url', 'live2_title_fr', 'live2_title_ar', 'adhan_url',
              'donate_om', 'donate_wave', 'donate_bank', 'donate_email', 'ads_email',
              'fb_page_id', 'fb_token',
              'footer_text_fr', 'footer_text_ar'] as $key) {
        $clean[$key] = $str($key);
    }
    foreach (['radio_history', 'zakat_nisab_fr', 'zakat_nisab_ar', 'ads_fr', 'ads_ar'] as $key) {
        $clean[$key] = isset($input[$key]) ? sanitize_textarea_field(wp_unslash($input[$key])) : $clean[$key];
    }
    $clean['color_accent'] = isset($input['color_accent']) && preg_match('/^#[0-9a-fA-F]{6}$/', $input['color_accent']) ? $input['color_accent'] : $clean['color_accent'];
    $clean['email']        = isset($input['email']) ? (sanitize_email($input['email']) ?: $clean['email']) : $clean['email'];
    $clean['contact_to']   = isset($input['contact_to']) ? (sanitize_email($input['contact_to']) ?: $clean['contact_to']) : $clean['contact_to'];
    $clean['analytics_snippet'] = isset($input['analytics_snippet']) ? wp_kses_post(wp_unslash($input['analytics_snippet'])) : $clean['analytics_snippet'];

    foreach (['live_enabled', 'radio_enabled', 'contact_enabled', 'live2_enabled', 'adhan_enabled',
              'donate_enabled', 'ads_enabled', 'fb_autopost', 'newsletter_enabled', 'views_enabled', 'analytics_enabled'] as $flag) {
        $clean[$flag] = !empty($input[$flag]) ? '1' : '0';
    }

    $clean['live_platform'] = in_array($input['live_platform'] ?? '', ['youtube', 'facebook', 'hls', 'off'], true) ? $input['live_platform'] : 'off';
    $clean['live2_platform'] = in_array($input['live2_platform'] ?? '', ['youtube', 'facebook', 'hls', 'off'], true) ? $input['live2_platform'] : 'youtube';
    $clean['zakat_nisab_amount'] = isset($input['zakat_nisab_amount']) ? max(0, (float) $input['zakat_nisab_amount']) : 0.0;
    $clean['prayer_method'] = isset($input['prayer_method']) && preg_match('/^[0-9]{1,2}$/', (string) $input['prayer_method']) ? (string) (int) $input['prayer_method'] : '3';

    // Schedule repeater.
    $rows = [];
    if (!empty($input['schedule']) && is_array($input['schedule'])) {
        foreach ($input['schedule'] as $row) {
            if (!is_array($row)) {
                continue;
            }
            $day   = isset($row['day']) ? (int) $row['day'] : -1;
            $start = isset($row['start']) ? sanitize_text_field($row['start']) : '';
            $end   = isset($row['end']) ? sanitize_text_field($row['end']) : '';
            $pid   = isset($row['programme_id']) ? (int) $row['programme_id'] : 0;
            if ($day < 0 || $day > 6 || !preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $start)) {
                continue;
            }
            $rows[] = [
                'day'          => $day,
                'start'        => $start,
                'end'          => preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $end) ? $end : $start,
                'programme_id' => $pid,
            ];
        }
    }
    $clean['schedule'] = $rows;

    return $clean;
}

/* ---------------------------------------------------------------------
 * Admin page
 * ------------------------------------------------------------------- */
add_action('admin_menu', function () {
    add_menu_page(
        'TV Baaboul Houda',
        'TV Baaboul Houda',
        'manage_options',
        'baaboul-houda',
        'bh_render_settings_page',
        'dashicons-tv',
        5,
    );
});

function bh_programme_options(): array {
    $options = [0 => bh_l('— Programme sans lien —', '— بدون برنامج —')];
    $q = new \WP_Query(['post_type' => 'programme', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC', 'no_found_rows' => true]);
    foreach ($q->posts as $p) {
        $options[$p->ID] = get_the_title($p->ID);
    }
    return $options;
}

function bh_day_labels(): array {
    return [
        0 => ['Lundi', 'الاثنين'],
        1 => ['Mardi', 'الثلاثاء'],
        2 => ['Mercredi', 'الأربعاء'],
        3 => ['Jeudi', 'الخميس'],
        4 => ['Vendredi', 'الجمعة'],
        5 => ['Samedi', 'السبت'],
        6 => ['Dimanche', 'الأحد'],
    ];
}

function bh_field($settings, $key, $type = 'text', $args = []): void {
    $value = $settings[$key] ?? '';
    $label = $args['label'] ?? $key;
    $help  = $args['help'] ?? '';
    echo '<p class="bh-field">';
    echo '<label for="bh-' . esc_attr($key) . '"><strong>' . esc_html($label) . '</strong></label>';
    $id  = 'bh-' . $key;
    $name = "bh_settings[$key]";
    switch ($type) {
        case 'url':
            echo '<input type="url" class="regular-text" id="' . esc_attr($id) . '" name="' . esc_attr($name) . '" value="' . esc_attr($value) . '">';
            break;
        case 'color':
            echo '<input type="color" id="' . esc_attr($id) . '" name="' . esc_attr($name) . '" value="' . esc_attr($value) . '">';
            break;
        case 'checkbox':
            echo '<label><input type="checkbox" id="' . esc_attr($id) . '" name="' . esc_attr($name) . '" value="1"' . checked(!empty($value), true, false) . '> ' . esc_html($label) . '</label>';
            break;
        case 'textarea':
            echo '<textarea class="large-text" rows="3" id="' . esc_attr($id) . '" name="' . esc_attr($name) . '">' . esc_textarea($value) . '</textarea>';
            break;
        case 'select':
            echo '<select id="' . esc_attr($id) . '" name="' . esc_attr($name) . '">';
            foreach ($args['options'] as $optValue => $optLabel) {
                echo '<option value="' . esc_attr($optValue) . '"' . selected($value, $optValue, false) . '>' . esc_html($optLabel) . '</option>';
            }
            echo '</select>';
            break;
        default:
            echo '<input type="text" class="regular-text" id="' . esc_attr($id) . '" name="' . esc_attr($name) . '" value="' . esc_attr($value) . '">';
    }
    if ($help) {
        echo '<span class="description">' . esc_html($help) . '</span>';
    }
    echo '</p>';
}

function bh_render_settings_page(): void {
    if (!current_user_can('manage_options')) {
        return;
    }
    $s = bh_settings();
    $programmes = bh_programme_options();
    $days       = bh_day_labels();
    ?>
    <div class="wrap bh-admin">
        <h1>TV Baaboul Houda — Réglages du site</h1>
        <p class="description">Ces réglages alimentent le site en direct, la radio, la grille des programmes, les prières et le contact. <strong>Enregistrer</strong> publie immédiatement.</p>
        <form method="post" action="options.php">
            <?php settings_fields('bh_settings_group'); ?>

            <h2>🎨 Identité & réseaux</h2>
            <?php
            bh_field($s, 'logo_url', 'url', ['label' => 'Logo (URL d’image)', 'help' => 'Videz le champ pour utiliser le logo par défaut du thème.']);
            bh_field($s, 'og_image_url', 'url', ['label' => 'Image de partage (OG) par défaut', 'help' => '1200×630 px, affichée sur Facebook/WhatsApp.']);
            bh_field($s, 'color_accent', 'color', ['label' => 'Couleur d’accent']);
            bh_field($s, 'facebook_url', 'url', ['label' => 'Page Facebook']);
            bh_field($s, 'youtube_url', 'url', ['label' => 'Chaîne YouTube']);
            bh_field($s, 'whatsapp', 'text', ['label' => 'WhatsApp', 'help' => 'Format international, ex. +22676205930']);
            bh_field($s, 'phone', 'text', ['label' => 'Téléphone']);
            bh_field($s, 'email', 'text', ['label' => 'Email de contact']);
            bh_field($s, 'address', 'text', ['label' => 'Adresse']);
            ?>

            <h2>🔴 En direct (TV)</h2>
            <p class="description">Pour passer en direct : collez l’URL de la vidéo en cours et cochez « La chaîne est en direct ». Pour la fermer : décochez. Aucun développeur nécessaire.</p>
            <?php
            bh_field($s, 'live_enabled', 'checkbox', ['label' => 'La chaîne est en direct (badge EN DIRECT dans le menu)']);
            bh_field($s, 'live_platform', 'select', [
                'label'     => 'Plateforme',
                'options'   => ['youtube' => 'YouTube', 'facebook' => 'Facebook', 'hls' => 'Flux vidéo propre (HLS/.m3u8)', 'off' => 'Hors ligne'],
            ]);
            bh_field($s, 'live_url', 'url', ['label' => 'URL de la diffusion', 'help' => 'YouTube : URL de la vidéo. Facebook : URL de la vidéo de la page. HLS : URL .m3u8. Laissez vide pour afficher la chaîne 24/7 par défaut.']);
            bh_field($s, 'live_title_fr', 'text', ['label' => 'Titre (français)']);
            bh_field($s, 'live_title_ar', 'text', ['label' => 'العنوان (العربية)']);
            ?>

            <h2>📻 Radio</h2>
            <?php
            bh_field($s, 'radio_enabled', 'checkbox', ['label' => 'Activer le player radio (barre en bas du site)']);
            bh_field($s, 'radio_url', 'url', ['label' => 'URL du flux audio', 'help' => 'Flux MP3/AAC (.mp3, .m3u8, .aac…), ex. flux de votre hébergeur radio.']);
            bh_field($s, 'radio_title_fr', 'text', ['label' => 'Titre (français)']);
            bh_field($s, 'radio_title_ar', 'text', ['label' => 'العنوان (العربية)']);
            bh_field($s, 'radio_cover_url', 'url', ['label' => 'Image du programme en cours (optionnel)']);
            ?>

            <h2>🗓️ Grille des programmes (hebdomadaire)</h2>
            <p class="description">Une ligne = un créneau horaire. Le jour, l’heure de début/fin et le programme lié.</p>
            <table class="widefat bh-schedule-table" id="bh-schedule">
                <thead>
                <tr><th style="width:130px">Jour</th><th style="width:110px">Début</th><th style="width:110px">Fin</th><th>Programme</th><th style="width:40px"></th></tr>
                </thead>
                <tbody>
                <?php
                $rows = $s['schedule'];
                if (!$rows) {
                    $rows = [];
                }
                foreach ($rows as $i => $row) :
                    ?>
                    <tr class="bh-schedule-row" data-i="<?php echo esc_attr($i); ?>">
                        <td><select name="bh_settings[schedule][<?php echo (int) $i; ?>][day]">
                            <?php foreach ($days as $d => $labels) : ?>
                                <option value="<?php echo (int) $d; ?>" <?php selected($row['day'], $d); ?>><?php echo esc_html($labels[0]); ?></option>
                            <?php endforeach; ?>
                        </select></td>
                        <td><input type="time" name="bh_settings[schedule][<?php echo (int) $i; ?>][start]" value="<?php echo esc_attr($row['start']); ?>"></td>
                        <td><input type="time" name="bh_settings[schedule][<?php echo (int) $i; ?>][end]" value="<?php echo esc_attr($row['end']); ?>"></td>
                        <td><select name="bh_settings[schedule][<?php echo (int) $i; ?>][programme_id]">
                            <?php foreach ($programmes as $pid => $ptitle) : ?>
                                <option value="<?php echo (int) $pid; ?>" <?php selected($row['programme_id'], $pid); ?>><?php echo esc_html($ptitle); ?></option>
                            <?php endforeach; ?>
                        </select></td>
                        <td><button type="button" class="button button-small bh-remove-row" title="Supprimer">×</button></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <p><button type="button" class="button" id="bh-add-schedule-row">+ Ajouter un créneau</button></p>
            <script>
                window.bhScheduleTemplate = <?php
                echo wp_json_encode('<tr class="bh-schedule-row" data-i="__I__">'
                    . '<td><select name="bh_settings[schedule][__I__][day]">'
                    . (function () use ($days) { $h = ''; foreach ($days as $d => $labels) { $h .= '<option value="' . (int) $d . '">' . esc_html($labels[0]) . '</option>'; } return $h; })()
                    . '</select></td>'
                    . '<td><input type="time" name="bh_settings[schedule][__I__][start]"></td>'
                    . '<td><input type="time" name="bh_settings[schedule][__I__][end]"></td>'
                    . '<td><select name="bh_settings[schedule][__I__][programme_id]">'
                    . (function () use ($programmes) { $h = ''; foreach ($programmes as $pid => $ptitle) { $h .= '<option value="' . (int) $pid . '">' . esc_html($ptitle) . '</option>'; } return $h; })()
                    . '</select></td>'
                    . '<td><button type="button" class="button button-small bh-remove-row">×</button></td></tr>');
                ?>;
            </script>

            <h2>🕌 Prières</h2>
            <?php
            bh_field($s, 'prayer_city', 'text', ['label' => 'Ville par défaut', 'help' => 'Ex. Ouagadougou. Les horaires proviennent de l’API Aladhan (gratuite).']);
            bh_field($s, 'prayer_method', 'select', [
                'label'   => 'Méthode de calcul',
                'options' => ['3' => 'Muslim World League (recommandé Afrique de l’Ouest)', '5' => 'Université islamique de Karachi', '2' => 'Ministère égyptien', '4' => 'Umm al-Qura (La Mecque)'],
            ]);
            bh_field($s, 'prayer_cities', 'text', ['label' => 'Villes proposées (séparées par des virgules)']);
            ?>

            <h2>✉️ Contact & pied de page</h2>
            <?php
            bh_field($s, 'contact_to', 'text', ['label' => 'Email de réception des messages', 'help' => 'Si vide, le formulaire affichera WhatsApp/phone à la place.']);
            bh_field($s, 'contact_enabled', 'checkbox', ['label' => 'Formulaire de contact actif']);
            bh_field($s, 'footer_text_fr', 'textarea', ['label' => 'Texte du pied de page (français)']);
            bh_field($s, 'footer_text_ar', 'textarea', ['label' => 'نص التذييل (العربية)']);
            bh_field($s, 'analytics_snippet', 'textarea', ['label' => 'Analytique (collez ici le script Google Analytics / Plausible…)', 'help' => 'Optionnel.']);
            bh_field($s, 'views_enabled', 'checkbox', ['label' => 'Compteur de vues (alimente « Les plus lus »)']);
            ?>

            <h2>📖 Deuxième chaîne — Coran 24/7</h2>
            <p class="description">Une seconde antenne en continu (ex. récitation du Coran non-stop). Quand elle est active, un sélecteur TV1 / TV2 apparaît sur la page d’accueil.</p>
            <?php
            bh_field($s, 'live2_enabled', 'checkbox', ['label' => 'Activer la chaîne 2 (TV2)']);
            bh_field($s, 'live2_platform', 'select', ['label' => 'Plateforme', 'options' => ['youtube' => 'YouTube', 'facebook' => 'Facebook', 'hls' => 'Flux vidéo (HLS/.m3u8)', 'off' => 'Hors ligne']]);
            bh_field($s, 'live2_url', 'url', ['label' => 'URL de la diffusion']);
            bh_field($s, 'live2_title_fr', 'text', ['label' => 'Titre (français)']);
            bh_field($s, 'live2_title_ar', 'text', ['label' => 'العنوان (العربية)']);
            ?>

            <h2>📻 Radio — historique</h2>
            <p class="description">Un programme par ligne : « Titre du programme — heure ». Affiché « Derniers programmes » sur la page radio.</p>
            <?php bh_field($s, 'radio_history', 'textarea', ['label' => 'Derniers programmes (les plus récents en premier)', 'help' => 'Ex. : Coran de la nuit — 06:00']); ?>

            <h2>🕌 Adhan & Zakat</h2>
            <?php
            bh_field($s, 'adhan_enabled', 'checkbox', ['label' => 'Jouer l’adhan à l’heure de la prière (si la page est ouverte)']);
            bh_field($s, 'adhan_url', 'url', ['label' => 'URL du fichier audio adhan (.mp3)', 'help' => 'Videz pour utiliser l’adhan par défaut (cdn.islamic.network).']);
            bh_field($s, 'zakat_nisab_amount', 'text', ['label' => 'Seuil du nisab (montant)', 'help' => 'Ex. 8500000 pour 8 500 000 FCFA. Mis à jour chaque mois selon le cours de l’or.']);
            bh_field($s, 'zakat_currency', 'text', ['label' => 'Devise', 'help' => 'Ex. FCFA, €']);
            bh_field($s, 'zakat_nisab_fr', 'text', ['label' => 'Note du nisab (français)']);
            bh_field($s, 'zakat_nisab_ar', 'text', ['label' => 'ملاحظة nisab (العربية)']);
            ?>

            <h2>💛 Dons</h2>
            <?php
            bh_field($s, 'donate_enabled', 'checkbox', ['label' => 'Afficher la page de dons']);
            bh_field($s, 'donate_om', 'text', ['label' => 'Orange Money (numéro)']);
            bh_field($s, 'donate_wave', 'text', ['label' => 'Wave (numéro)']);
            bh_field($s, 'donate_bank', 'text', ['label' => 'Banque / RIB']);
            bh_field($s, 'donate_email', 'text', ['label' => 'Email à indiquer après don']);
            ?>

            <h2>📢 Publicité & sponsoring</h2>
            <?php
            bh_field($s, 'ads_enabled', 'checkbox', ['label' => 'Afficher la page publicité']);
            bh_field($s, 'ads_email', 'text', ['label' => 'Email pour les annonceurs']);
            bh_field($s, 'ads_fr', 'textarea', ['label' => 'Taux & formats (français)', 'help' => 'Ex. : Spot TV 30s — 50 000 FCFA…']);
            bh_field($s, 'ads_ar', 'textarea', ['label' => 'الأسعار (العربية)']);
            ?>

            <h2>📣 Publication automatique Facebook</h2>
            <p class="description">Quand vous cochez « La chaîne est en direct », un message est publié automatiquement sur votre page Facebook.</p>
            <?php
            bh_field($s, 'fb_autopost', 'checkbox', ['label' => 'Activer la publication automatique']);
            bh_field($s, 'fb_page_id', 'text', ['label' => 'Page ID (visible dans l’URL de la page)']);
            bh_field($s, 'fb_token', 'text', ['label' => 'Jeton d’accès de la page (pages_show_list + pages_manage_posts)']);
            ?>

            <h2>📧 Newsletter</h2>
            <?php bh_field($s, 'newsletter_enabled', 'checkbox', ['label' => 'Activer le formulaire d’inscription (pied de page + page Newsletter)']); ?>
            <?php bh_field($s, 'analytics_enabled', 'checkbox', ['label' => 'Compteur de pages vues intégré (aucun tiers)']); ?>

            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
