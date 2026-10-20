<?php
/**
 * Islamic tools — server-side helpers shared by templates and the widget.
 * All heavy interactive math (zakat, qibla, calendars) runs client-side in
 * lib.js (tested); this file provides the static data + the "of the day"
 * rotators + mosque data.
 */

defined('ABSPATH') || exit;

/* ---------------------------------------------------------------------
 * "Of the day" rotators (99 names, 42 hadiths) — deterministic by day.
 * ------------------------------------------------------------------- */

function bh_name_of_day(): ?array {
    $data  = function_exists('bh_names99') ? bh_names99() : [];
    $names = is_array($data) ? ($data['names'] ?? []) : [];
    if (!$names) {
        return null;
    }
    return $names[(int) date('z') % count($names)];
}

/** Hadith of the day: delegated to the existing bh_daily_hadith(). */

/* ---------------------------------------------------------------------
 * Qibla — static direction per configured city (client refines with the
 * device magnetometer; the static value is the desktop fallback).
 * Great-circle initial bearing to the Kaaba (21.422487, 39.826206).
 * ------------------------------------------------------------------- */

function bh_kaaba_coords(): array {
    return ['lat' => 21.422487, 'lon' => 39.826206];
}

/** Initial bearing (degrees from true north, clockwise) from (lat, lon) to the Kaaba. */
function bh_qibla_bearing(float $lat, float $lon): float {
    $k  = bh_kaaba_coords();
    $f1 = deg2rad($lat);
    $f2 = deg2rad($k['lat']);
    $dl = deg2rad($k['lon'] - $lon);
    $y  = sin($dl) * cos($f2);
    $x  = cos($f1) * tan($f2) - sin($f1) * cos($dl);
    $br = rad2deg(atan2($y, $x));
    return fmod($br + 360, 360);
}

/** Known cities with coordinates (also used for the prayer page + qibla tool). */
function bh_known_cities(): array {
    return [
        'Ouagadougou'        => ['lat' => 12.3714, 'lon' => -1.5197],
        'Bobo-Dioulasso'     => ['lat' => 12.3678, 'lon' => -2.6041],
        'Koudougou'          => ['lat' => 12.2536, 'lon' => -1.5008],
        'Ganndougou'         => ['lat' => 10.0636, 'lon' => -2.2319],
        'Houndé'             => ['lat' => 13.1303, 'lon' => -0.5197],
        'Dédougou'           => ['lat' => 14.9620, 'lon' => -4.7353],
        'Fada Ngourma'      => ['lat' => 12.6467, 'lon' => 0.9583],
        'Bamako'             => ['lat' => 12.6392, 'lon' => -8.0029],
        'Niamey'             => ['lat' => 13.5137, 'lon' => 2.1067],
        'Dakar'              => ['lat' => 14.7167, 'lon' => -17.4677],
        'Abidjan'            => ['lat' => 5.3566, 'lon' => -4.0285],
        'Lomé'               => ['lat' => 6.1319, 'lon' => 1.2228],
        'Paris'              => ['lat' => 48.8566, 'lon' => 2.3522],
        'Bruxelles'          => ['lat' => 50.8503, 'lon' => 4.3517],
        'Genève'             => ['lat' => 46.2044, 'lon' => 6.1432],
    ];
}

/** Static qibla bearing for the prayer city (degrees, 0 = north). */
function bh_qibla_for_city(string $city): ?float {
    $cities = bh_known_cities();
    $key    = null;
    foreach ($cities as $name => $coords) {
        if (strcasecmp($name, $city) === 0) {
            $key = $name;
            break;
        }
    }
    if ($key === null) {
        return null;
    }
    return bh_qibla_bearing($cities[$key]['lat'], $cities[$key]['lon']);
}

/* ---------------------------------------------------------------------
 * Zakat — static reference data; calculation is client-side (lib.js).
 * ------------------------------------------------------------------- */

function bh_zakat_reference(): array {
    $s = bh_settings();
    return [
        'rate'        => 2.5, // % (2.5/100)
        'nisab_note'  => 'Le nisab = la valeur de 85 g d’or (ou 595 g d’argent).',
        'nisab_fr'    => (string) ($s['zakat_nisab_fr'] ?? ''),
        'nisab_ar'    => (string) ($s['zakat_nisab_ar'] ?? ''),
        'nisab_amount' => (float) ($s['zakat_nisab_amount'] ?? 0),
        'currency'    => (string) ($s['zakat_currency'] ?? 'FCFA'),
    ];
}

/* ---------------------------------------------------------------------
 * Ramadan seerah — 30 entries (public-domain historical facts), FR + AR.
 * ------------------------------------------------------------------- */

function bh_seerah_ramadan(): array {
    return [
        ['fr' => 'Le début de la révélation : la récitation du Coran commença à la Kaaba lors d’un mois de Ramadan, quand le Prophète ﷺ avait 40 ans.', 'ar' => 'بداية الوحي: بدأ نزول القرآن في رمضان حين كان النبي ﷺ في الأربعين من عمره.'],
        ['fr' => 'Le Prophète ﷺ dit : « Le Ramadan est arrivé pour vous, mois de baraka : Allah y impose le jeûne. »', 'ar' => 'قال النبي ﷺ: «قد جاءكم رمضان شهر مبارك فرض الله عليكم الصيام.»'],
        ['fr' => 'Les compagnons jeûnaient et veillaient la nuit pour prier et réciter le Coran, comme le faisait le Prophète ﷺ.', 'ar' => 'كان الصحابة يصومون ويقومون الليل للصلاة وقراءة القرآن كما كان يفعل النبي ﷺ.'],
        ['fr' => 'Abu Bakr r.a. achevait toute la récitation du Coran chaque nuit de Ramadan.', 'ar' => 'كان أبو بكر رضي الله عنه يختم القرآن كله كل ليلة في رمضان.'],
        ['fr' => 'Le Coran fut révélé dans sa totalité au ciel préservé (Al-Lawh al-Mahfuz) puis descendit successivement pendant 23 ans.', 'ar' => 'نزل القرآن مجموعاً إلى السماء الدنيا ثم نزل متفرقاً على النبي ﷺ في 23 سنة.'],
        ['fr' => 'Le jeûne fut imposé en l’an 2 de l’Hégire, au cours d’un mois de Ramadan.', 'ar' => 'فُرض الصيام في السنة الثانية للهجرة في شهر رمضان.'],
        ['fr' => 'Le Prophète ﷺ disait : « Qui jeûne le Ramadan par foi et espérance du réconfort, ses péchés passés lui sont pardonnés. »', 'ar' => 'قال ﷺ: «من صام رمضان إيماناً واحتساباً غُفر له ما تقدم من ذنبه.»'],
        ['fr' => 'La nuit du destin (Al-Qadr) est meilleure que mille mois ; elle se cherche dans les dix dernières nuits de Ramadan.', 'ar' => 'ليلة القدر خير من ألف شهر، وتُطلب في العشر الأواخر من رمضان.'],
        ['fr' => 'Le Prophète ﷺ s’isolait (i’tikaf) dans la mosquée les dix derniers jours de Ramadan.', 'ar' => 'كان النبي ﷺ يعتكف في العشر الأواخر من رمضان في المسجد.'],
        ['fr' => 'Ibn ‘Umar r.a. : « Le Prophète ﷺ allait à la mosquée et ne trouvait qu’un seul homme jeûnant et priant : c’était ‘Uthman. »', 'ar' => 'قال ابن عمر رضي الله عنهما: خرج النبي ﷺ إلى المسجد فإذا رجلان قد صوما وصليا: عثمان وعلي.'],
        ['fr' => 'Le Prophète ﷺ libérait les esclaves en Ramadan : Bilal et Khabbab furent libérés par leurs maîtres après la révélation de l’ordre de jeûner.', 'ar' => 'كان العتق في رمضان سبباً في انتشار الإسلام: أعتق السادة عبيدهم.'],
        ['fr' => 'Les veillées (qiyam) de Ramadan : le Prophète ﷺ entrait chez Hafsa et la trouvait prosternée, puis chez ‘Aisha et la trouva de même.', 'ar' => 'كان النبي ﷺ يدخل على حفصة فيجدَها ساجدة، ثم يدخل على عائشة فيجدَها كذلك.'],
        ['fr' => 'Al-Bara’ r.a. : « Nous jeûnions avec le Prophète ﷺ et il ne disait pas : j’ai vu la lune ; jusqu’à ce qu’Allah l’ait clarifié. »', 'ar' => 'قال البراء رضي الله عنه: «كنا نصوم مع النبي ﷺ فلا يقول رأيت الهلال حتى يبيّنه الله.»'],
        ['fr' => 'Le Prophète ﷺ fit le pèlerinage d’adieu (Hajjat al-Wada’) avec une préparation qui inclut les derniers rappels de Ramadan.', 'ar' => 'حجة الوداع: آخر حج للنبي ﷺ جمع فيه بين التذكير بالصيام وبركاته.'],
        ['fr' => 'Les orphelins de Médine étaient accueillis chez les musulmans pendant Ramadan, nourris et veillés.', 'ar' => 'كان اليتامى يُوَكَل بهم في رمضان، يُطعمون ويُصاحَبون في الصلاة.'],
        ['fr' => 'Le Prophète ﷺ partageait son iftar : il rompit son jeûne chez des Bédouins avec du lait et des dattes.', 'ar' => 'كان ﷺ يفطر عند البادية بالحليب والتمر، ولا يرفض دعوة.'],
        ['fr' => 'La première mosquée (Quba) fut construite peu avant le Ramadan de l’an 1 de l’Hégire.', 'ar' => 'أول مسجد بناه المسلمون مسجد قباء، قبل رمضان من السنة الأولى للهجرة.'],
        ['fr' => 'Le Coran fut révélé en dix mois et neuf jours et neuf heures, selon le calcul des sages à partir de sa durée de 23 ans.', 'ar' => 'حَسَب العلماء أن نزول القرآن استغرق 22 سنة و2 شهراً تقريباً.'],
        ['fr' => 'Le Prophète ﷺ dit : « Quand arrive le Ramadan, les portes du Paradis s’ouvrent et les portes de l’Enfer se ferment. »', 'ar' => 'قال ﷺ: «إذا جاء رمضان فُتحت أبواب الجنة وغُلقت أبواب النار.»'],
        ['fr' => 'Les musulmans de Médine organisaient les iftars collectifs : un dîner commun au soir, puis prière de tarawih en commun.', 'ar' => 'كان المسلمون يتسامرون على الإفطار ثم يقومون الصلاة مع الإمام.'],
        ['fr' => 'Abu Hurayra r.a. : « Le Prophète ﷺ était le plus généreux des hommes, et il l’était davantage en Ramadan quand Jibril le visitait. »', 'ar' => 'كان النبي ﷺ أجود الناس، وكان أجود ما يكون في رمضان حين يلقاه جبريل.'],
        ['fr' => 'Le jeûne protège : « Jeûnez et vous serez en santé. » — hadith du Prophète ﷺ.', 'ar' => 'قال ﷺ: «صوموا تصحّوا.»'],
        ['fr' => 'Les femmes de Médina apprenaient le Coran aux orphelines pendant les veillées de Ramadan.', 'ar' => 'كانت نساء المدينة يُعَلِّمن اليتيمات القرآن في ليالي القيام.'],
        ['fr' => 'Le Prophète ﷺ ne cessait pas de faire qiyam (prière de la nuit) toute l’année, et il l’intensifiait en Ramadan.', 'ar' => 'كان ﷺ يقوم الليل طوال السنة، ويشدد القيام في رمضان.'],
        ['fr' => 'La zakat al-fitr fut imposée en l’an 2 de l’Hégire pour purifier le jeûneur et nourrir le pauvre.', 'ar' => 'فُرضت زكاة الفطر في السنة الثانية للهجرة لتطهّر الصائم وتُغني المساكين.'],
        ['fr' => 'Le Prophète ﷺ dit : « L’eau est la purification de l’eau : le jeûneur doit se purifier le matin et rompre le jeûne l’après-midi. »', 'ar' => 'كان ﷺ يتوضأ بالغداة ويفطر بعد العصر، استحباباً.'],
        ['fr' => 'Les dix derniers jours : le Prophète ﷺ serrait sa ceinture, réveillait sa famille et s’appliquait plus que quiconque.', 'ar' => 'في العشر الأواخر كان ﷺ يشد مئزره ويوقظ أهله ويجتهد أكثر من غيره.'],
        ['fr' => 'La fête de l’Aïd ul-Fitr clôt le Ramadan : prière en groupe, salam, et joie partagée.', 'ar' => 'عيد الفطر: صلاة الجماعة والتسليم والفرح المشترك.'],
        ['fr' => 'Le Prophète ﷺ encourageait la lecture du Coran à voix haute en famille, soir après soir, jusqu’à la fin du mois.', 'ar' => 'كان ﷺ يقرئ القرآن في بيته كل مساء حتى آخر الشهر.'],
        ['fr' => 'Clôtture : « Votre mois est un mois de miséricorde, de pardon et de libération du Feu. »', 'ar' => '«شهركم شهر رحمة ومغفرة ونجاة من النار.»'],
    ];
}

/** Wudu (ablution) steps — FR + AR, public knowledge. */
function bh_wudu_steps(): array {
    return [
        ['fr' => 'Laver les mains jusqu’aux poignets, trois fois, en commençant par la bouche (polution légère).', 'ar' => 'اغسل يديك إلى الرسغين ثلاثاً، وابدأ بالشرب من الفم.'],
        ['fr' => 'Rincer la bouche trois fois.', 'ar' => 'امضمض ثلاثاً.'],
        ['fr' => 'Rincer le nez trois fois (inspiration légère, puis éternuement du doigt).', 'ar' => 'استنشق ثلاثاً ثم استنثر.'],
        ['fr' => 'Laver le visage trois fois (du haut du front aux mâchoires, des oreilles aux oreilles).', 'ar' => 'اغسل الوجه ثلاثاً من منبت الشعر إلى ذقن، ومن الأذن إلى الأذن.'],
        ['fr' => 'Laver les bras jusqu’aux coudes trois fois, en commençant par le droit.', 'ar' => 'اغسل يديك إلى المرفقين ثلاثاً، يبدأ باليمين.'],
        ['fr' => 'Essuyer la tête une fois (et les oreilles extérieures/intérieures).', 'ar' => 'امسح الرأس مرة، والأذنين خارجاً ودخلاً.'],
        ['fr' => 'Laver les pieds jusqu’aux chevilles trois fois, en commençant par le droit, en massant entre les orteils.', 'ar' => 'اغسل قدميك إلى الكعبين ثلاثاً، يبدأ باليمين، وفرّق الأصابع.'],
    ];
}

/* ---------------------------------------------------------------------
 * Mosques — from the CPT (registered in post-types.php).
 * ------------------------------------------------------------------- */

function bh_mosques(): array {
    $q = new \WP_Query([
        'post_type'      => 'mosquee',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
        'no_found_rows'  => true,
    ]);
    $out = [];
    foreach ($q->posts as $p) {
        $out[] = [
            'id'     => $p->ID,
            'name'   => bh_post_title($p),
            'name_ar' => (string) get_post_meta($p->ID, '_bh_title_ar', true),
            'address' => (string) get_post_meta($p->ID, '_bh_address', true),
            'imam'   => (string) get_post_meta($p->ID, '_bh_imam', true),
            'phone'  => (string) get_post_meta($p->ID, '_bh_phone', true),
            'notes'  => (string) get_post_meta($p->ID, '_bh_notes', true),
            'map'    => (string) get_post_meta($p->ID, '_bh_map_url', true),
        ];
    }
    return $out;
}
