#!/bin/sh
# =============================================================================
# DEV-ONLY demo content for TV Baaboul Houda.
#
# Runs inside the wpcli container (called from docker-compose.yml) after the
# theme/plugin are active. Idempotent:
#   - skipped when the option bh_demo_seeded exists (unless FORCE=1)
#   - every item is keyed by slug, so even a forced re-run updates in place
#     instead of duplicating.
#
# Disable : SEED_DEMO=0 in .env
# Re-seed : docker compose run --rm -e FORCE=1 --entrypoint sh wpcli /seed-demo.sh
#
# All texts are generic show descriptions (FR + AR). YouTube/Facebook/audio
# URLs are PLACEHOLDERS: replace them with the channel's real replays.
# =============================================================================
set -e

if [ "${FORCE:-0}" != "1" ] && [ -n "$(wp option get bh_demo_seeded 2>/dev/null || true)" ]; then
  echo ">> Demo content already seeded (FORCE=1 to redo)"
  exit 0
fi

PHP_FILE="$(mktemp /tmp/bh-seed.XXXXXX)"
trap 'rm -f "$PHP_FILE"' EXIT

cat > "$PHP_FILE" <<'PHP'
<?php
/** Upsert a post by (type, slug). Returns the post ID. */
function bh_seed_post(string $type, string $slug, array $a, array $meta = []): int {
    $existing = get_posts([
        'post_type' => $type, 'name' => $slug, 'post_status' => 'any',
        'numberposts' => 1, 'fields' => 'ids',
    ]);
    $args = [
        'post_type'    => $type,
        'post_name'    => $slug,
        'post_status'  => 'publish',
        'post_title'   => $a['title'],
        'post_content' => $a['content'] ?? '',
        'post_excerpt' => $a['excerpt'] ?? '',
    ];
    if (!empty($a['date'])) {
        $args['post_date']     = $a['date'];
        $args['post_date_gmt'] = get_gmt_from_date($a['date']);
    }
    if ($existing) {
        $args['ID'] = $existing[0];
        $id = wp_update_post($args, true);
    } else {
        $id = wp_insert_post($args, true);
    }
    if (is_wp_error($id)) {
        fwrite(STDERR, "seed error ($type/$slug): " . $id->get_error_message() . "\n");
        return 0;
    }
    // Future-dated posts (events) must stay "publish" so they show up as upcoming.
    if (!empty($a['date']) && get_post_status($id) !== 'publish') {
        global $wpdb;
        $wpdb->update($wpdb->posts, ['post_status' => 'publish'], ['ID' => $id]);
        clean_post_cache($id);
    }
    foreach (['title_ar', 'content_ar', 'excerpt_ar'] as $k) {
        if (isset($a[$k])) {
            update_post_meta($id, '_bh_' . $k, $a[$k]);
        }
    }
    foreach ($meta as $k => $v) {
        update_post_meta($id, $k, $v);
    }
    return (int) $id;
}

$days = function (int $n, string $time = '12:00:00'): string {
    return date('Y-m-d', strtotime("-$n days")) . ' ' . $time;
};
$ahead = function (int $n, string $time = '18:00:00'): string {
    return date('Y-m-d', strtotime("+$n days")) . ' ' . $time;
};

/* ---- Programmes ---------------------------------------------------- */
$prog = [];
$programmes = [
    'quran-et-vie' => [
        'Coran et Vie',
        'Une émission quotidienne de récitation et de méditation, pour accompagner le début de la journée en famille.',
        'قرآن وحياة',
        'برنامج يومي للتلاوة والتأمل يرافق بداية يومكم مع الأسرة.',
    ],
    'questions-de-foi' => [
        'Questions de Foi',
        'Des questions-réponses sur la pratique quotidienne, avec des invités et des auditeurs qui appellent en direct.',
        'أسئلة الإيمان',
        'أسئلة وأجوبة حول الممارسة اليومية مع ضيوف ومستمعين يتصلون مباشرة.',
    ],
    'jeunesse-et-valeurs' => [
        'Jeunesse et Valeurs',
        'Un rendez-vous pour les jeunes : études, travail, famille et valeurs islamiques dans la vie moderne.',
        'الشباب والقيم',
        'موعد للشباب حول الدراسة والعمل والأسرة والقيم الإسلامية في الحياة العصرية.',
    ],
    'regards-sur-la-communaute' => [
        'Regards sur la Communauté',
        'Reportages et rencontres avec les mosquées, associations et acteurs de la solidarité à Ouagadougou et au-delà.',
        'نظرات على المجتمع',
        'تقارير ولقاءات مع المساجد والجمعيات وأهل التضامن في واغادوغو وخارجها.',
    ],
];
foreach ($programmes as $slug => $p) {
    $prog[$slug] = bh_seed_post('programme', $slug, [
        'title' => $p[0], 'content' => '<p>' . $p[1] . '</p>', 'excerpt' => $p[1],
        'title_ar' => $p[2], 'content_ar' => '<p>' . $p[3] . '</p>', 'excerpt_ar' => $p[3],
    ]);
}

/* ---- Animateurs ---------------------------------------------------- */
$pres = [];
$presenters = [
    'presentateur-demo-un' => ['Ibrahim Ouédraogo', 'Animateur de la chaîne (profil de démonstration), il présente les rendez-vous du matin et les émissions de questions-réponses.', 'إبراهيم ويدراوغو', 'مقدّم في القناة (ملف تجريبي)، يقدّم برامج الصباح وبرامج الأسئلة والأجوبة.'],
    'presentatrice-demo' => ['Aminata Sawadogo', 'Animatrice de la chaîne (profil de démonstration), responsable des émissions jeunesse et famille.', 'أمينة سوادوغو', 'مقدّمة في القناة (ملف تجريبي)، مسؤولة عن برامج الشباب والأسرة.'],
    'presentateur-demo-deux' => ['Moussa Compaoré', 'Journaliste et animateur (profil de démonstration), il réalise les reportages communautaires.', 'موسى كومباوري', 'صحفي ومقدّم (ملف تجريبي)، يعدّ التقارير المجتمعية.'],
];
foreach ($presenters as $slug => $p) {
    $pres[] = bh_seed_post('animateur', $slug, [
        'title' => $p[0], 'content' => '<p>' . $p[1] . '</p>',
        'title_ar' => $p[2], 'content_ar' => '<p>' . $p[3] . '</p>',
    ]);
}

/* ---- Episodes ------------------------------------------------------ */
$yt = fn ($id) => 'https://www.youtube.com/watch?v=' . $id;
$mp3 = fn ($n) => 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-' . $n . '.mp3';
$episodes = [
    // slug, fr title, ar title, fr text, ar text, programme, presenter idx, days ago, type, url
    ['demo-ep-coran-vie-1', 'Coran et Vie : bien commencer la journée', 'قرآن وحياة: بداية يوم مبارك', 'Récitation et courte méditation pour démarrer la journée.', 'تلاوة وتأمل قصير لبداية اليوم.', 'quran-et-vie', 0, 2, 'youtube', $yt('9bZkp7q19f0')],
    ['demo-ep-coran-vie-2', 'Coran et Vie : la patience au quotidien', 'قرآن وحياة: الصبر في الحياة اليومية', 'Réflexion sur la patience dans le travail et la famille.', 'تأمل في الصبر في العمل والأسرة.', 'quran-et-vie', 0, 9, 'audio', $mp3(1)],
    ['demo-ep-questions-foi-1', 'Questions de Foi : la prière en voyage', 'أسئلة الإيمان: الصلاة في السفر', 'Les auditeurs posent leurs questions sur la prière lors des déplacements.', 'المستمعون يطرحون أسئلتهم حول الصلاة أثناء التنقل.', 'questions-de-foi', 0, 4, 'youtube', $yt('jNQXAC9IVRw')],
    ['demo-ep-questions-foi-2', 'Questions de Foi : le jeûne et la santé', 'أسئلة الإيمان: الصيام والصحة', 'Un échange sur le jeûne et les situations particulières.', 'حوار حول الصيام والحالات الخاصة.', 'questions-de-foi', 0, 16, 'facebook', 'https://www.facebook.com/facebook/videos/10153231379946729/'],
    ['demo-ep-jeunesse-1', 'Jeunesse et Valeurs : réussir ses études', 'الشباب والقيم: النجاح في الدراسة', 'Conseils d’organisation et de motivation pour les étudiants.', 'نصائح في التنظيم والتحفيز للطلاب.', 'jeunesse-et-valeurs', 1, 6, 'youtube', $yt('aqz-KE-bpKQ')],
    ['demo-ep-jeunesse-2', 'Jeunesse et Valeurs : métier et honnêteté', 'الشباب والقيم: العمل والأمانة', 'Comment concilier ambition professionnelle et valeurs.', 'كيف نوفّق بين الطموح المهني والقيم.', 'jeunesse-et-valeurs', 1, 21, 'audio', $mp3(2)],
    ['demo-ep-communaute-1', 'Regards sur la Communauté : la solidarité de quartier', 'نظرات على المجتمع: تضامن الحي', 'Reportage sur les initiatives d’entraide dans un quartier de Ouagadougou.', 'تقرير عن مبادرات التعاون في أحد أحياء واغادوغو.', 'regards-sur-la-communaute', 2, 11, 'youtube', $yt('M7lc1UVf-VE')],
    ['demo-ep-communaute-2', 'Regards sur la Communauté : rencontre avec une association', 'نظرات على المجتمع: لقاء مع جمعية', 'Rencontre avec une association locale et ses bénévoles.', 'لقاء مع جمعية محلية ومتطوعيها.', 'regards-sur-la-communaute', 2, 28, 'facebook', 'https://www.facebook.com/facebook/videos/10153231379946729/'],
];
foreach ($episodes as $e) {
    $meta = [
        '_bh_source_type'  => $e[8],
        '_bh_source_url'   => $e[9],
        '_bh_programme_id' => $prog[$e[5]],
        '_bh_presenter_id' => $pres[$e[6]],
        '_bh_date'         => date('Y-m-d', strtotime('-' . $e[7] . ' days')),
    ];
    if ('audio' === $e[8]) {
        $meta['_bh_audio_url'] = $e[9];
    }
    bh_seed_post('episode', $e[0], [
        'title' => $e[1], 'content' => '<p>' . $e[3] . '</p>', 'excerpt' => $e[3],
        'title_ar' => $e[2], 'content_ar' => '<p>' . $e[4] . '</p>', 'excerpt_ar' => $e[4],
        'date' => $days($e[7]),
    ], $meta);
}

/* ---- News (posts) -------------------------------------------------- */
$news = [
    ['demo-actu-nouvelle-grille', 'Nouvelle grille des programmes', 'شبكة برامج جديدة', 'La chaîne présente sa grille : des rendez-vous du matin au soir, en français et en arabe.', 'تقدّم القناة شبكتها: مواعيد من الصباح إلى المساء بالفرنسية والعربية.', 1],
    ['demo-actu-radio-24h', 'La radio diffuse désormais en continu', 'الإذاعة تبثّ الآن على مدار الساعة', 'Écoutez la radio à tout moment depuis le site ou le mini-lecteur en bas de page.', 'استمعوا إلى الإذاعة في أي وقت من الموقع أو من المشغّل الصغير أسفل الصفحة.', 5],
    ['demo-actu-replays', 'Les replays sont disponibles', 'الإعادات متاحة الآن', 'Retrouvez les émissions déjà diffusées dans la rubrique Émissions, en vidéo ou en audio.', 'تجدون الحلقات السابقة في قسم الحلقات بالفيديو أو الصوت.', 8],
    ['demo-actu-appel-benevoles', 'Appel aux bénévoles', 'نداء للمتطوعين', 'L’équipe recherche des bénévoles pour la technique, la rédaction et l’animation.', 'يبحث الفريق عن متطوعين للجانب التقني والتحرير والتقديم.', 13],
];
foreach ($news as $n) {
    bh_seed_post('post', $n[0], [
        'title' => $n[1], 'content' => '<p>' . $n[3] . '</p>', 'excerpt' => $n[3],
        'title_ar' => $n[2], 'content_ar' => '<p>' . $n[4] . '</p>', 'excerpt_ar' => $n[4],
        'date' => $days($n[5]),
    ]);
}

/* ---- Events (future dates) ----------------------------------------- */
$events = [
    ['demo-evt-conference', 'Conférence publique de la communauté', 'محاضرة عامة للمجتمع', 'Une soirée d’échanges ouverte à tous (événement de démonstration). Date et lieu à confirmer.', 'أمسية حوار مفتوحة للجميع (فعالية تجريبية). التاريخ والمكان يُحدَّدان لاحقًا.', 12],
    ['demo-evt-portes-ouvertes', 'Journée portes ouvertes de la chaîne', 'يوم مفتوح للقناة', 'Visitez les studios et rencontrez l’équipe (événement de démonstration).', 'زوروا الاستوديوهات وتعرّفوا على الفريق (فعالية تجريبية).', 30],
];
foreach ($events as $ev) {
    bh_seed_post('evenement', $ev[0], [
        'title' => $ev[1], 'content' => '<p>' . $ev[3] . '</p>',
        'title_ar' => $ev[2], 'content_ar' => '<p>' . $ev[4] . '</p>',
        'date' => $ahead($ev[5]),
    ]);
}

/* ---- Mosques ------------------------------------------------------- */
$mosques = [
    ['demo-mosquee-centrale', 'Mosquée centrale (démo)', 'المسجد المركزي (تجريبي)', 'Centre-ville, Ouagadougou', 'Imam de démonstration', 'Adresse et horaires fictifs, à remplacer.'],
    ['demo-mosquee-quartier', 'Mosquée du quartier (démo)', 'مسجد الحي (تجريبي)', 'Ouagadougou', 'Imam de démonstration', 'Adresse et horaires fictifs, à remplacer.'],
];
foreach ($mosques as $m) {
    bh_seed_post('mosquee', $m[0], ['title' => $m[1], 'title_ar' => $m[2]], [
        '_bh_address' => $m[3], '_bh_imam' => $m[4], '_bh_phone' => '', '_bh_notes' => $m[5],
    ]);
}

/* ---- Testimonials -------------------------------------------------- */
$testis = [
    ['demo-temoignage-1', 'Auditeur de Ouagadougou', 'مستمع من واغادوغو', 'J’écoute la radio chaque matin avec ma famille. Un contenu simple et utile. (témoignage de démonstration)', 'أستمع إلى الإذاعة كل صباح مع أسرتي. محتوى بسيط ومفيد. (شهادة تجريبية)'],
    ['demo-temoignage-2', 'Étudiante, spectatrice', 'طالبة ومشاهدة', 'Les émissions jeunesse m’aident à organiser mes journées. (témoignage de démonstration)', 'تساعدني برامج الشباب على تنظيم أيامي. (شهادة تجريبية)'],
];
foreach ($testis as $t) {
    bh_seed_post('temoignage', $t[0], [
        'title' => $t[1], 'content' => '<p>' . $t[3] . '</p>',
        'title_ar' => $t[2], 'content_ar' => '<p>' . $t[4] . '</p>',
    ]);
}

/* ---- Fatwas (generic placeholders, no rulings invented) ------------ */
$fatwas = [
    ['demo-fatwa-1', 'Comment poser une question à l’équipe religieuse de la chaîne ?', 'كيف أطرح سؤالًا على الفريق الشرعي في القناة؟', 'Utilisez la page « Poser une question ». Les réponses sont publiées ici après relecture par une personne qualifiée. (contenu de démonstration)', 'استخدموا صفحة «اطرح سؤالًا». تُنشر الأجوبة هنا بعد مراجعتها من شخص مؤهَّل. (محتوى تجريبي)'],
    ['demo-fatwa-2', 'Où trouver les horaires de prière sur le site ?', 'أين أجد مواقيت الصلاة في الموقع؟', 'La rubrique Prières affiche les horaires de la journée pour la ville choisie. (contenu de démonstration)', 'يعرض قسم الصلاة مواقيت اليوم للمدينة المختارة. (محتوى تجريبي)'],
];
foreach ($fatwas as $f) {
    bh_seed_post('fatwa', $f[0], [
        'title' => $f[1], 'content' => '<p>' . $f[3] . '</p>',
        'title_ar' => $f[2], 'content_ar' => '<p>' . $f[4] . '</p>',
    ]);
}

/* ---- Weekly schedule: every day, 00:00 -> 23:59 -------------------- */
$s = get_option('bh_settings', []);
if (!is_array($s)) {
    $s = [];
}
if (empty($s['schedule']) || getenv('FORCE') === '1') {
    $keys  = array_keys($prog);
    $times = [['00:00', '06:00'], ['06:00', '09:00'], ['09:00', '12:00'], ['12:00', '16:00'], ['16:00', '20:00'], ['20:00', '23:59']];
    $rows  = [];
    for ($d = 0; $d < 7; $d++) {
        foreach ($times as $i => $t) {
            $rows[] = [
                'day' => $d, 'start' => $t[0], 'end' => $t[1],
                'programme_id' => $prog[$keys[($d + $i) % count($keys)]],
            ];
        }
    }
    $s['schedule'] = $rows;
    update_option('bh_settings', $s);
}

update_option('bh_demo_seeded', gmdate('c'), false);
flush_rewrite_rules(false);
echo ">> Demo content seeded\n";
PHP

wp eval-file "$PHP_FILE"
