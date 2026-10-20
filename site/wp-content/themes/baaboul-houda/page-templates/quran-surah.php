<?php
/**
 * Quran surah reader — served via /coran/{n}/ (plugin rewrite rule).
 * Renders server-side (SEO + shareable URLs). Client (quran.js chunk,
 * lazy-loaded): FR translation(s), recitations, speed/repeat, continuous
 * play + highlight, bookmarks & notes, tafsir, word-by-word, share card,
 * reading plan, memorization quiz.
 */
defined('ABSPATH') || exit;

$surahNum = (int) get_query_var('bh_surah');
$surah    = function_exists('bh_quran_surah') ? bh_quran_surah($surahNum) : null;
if (!$surah) {
    wp_safe_redirect(home_url('/coran/'));
    exit;
}
$bhCoranBase = bh_is_ar() ? home_url('/ar/coran/') : home_url('/coran/');
$prevSurah = $surahNum > 1 ? $surahNum - 1 : 114;
$nextSurah = $surahNum < 114 ? $surahNum + 1 : 1;

get_header();
?>

<section class="bh-section bh-quran-reader-section">
    <div class="bh-container bh-container--narrow">
        <nav class="bh-quran-reader__nav" aria-label="<?php echo esc_attr(bh_l('Navigation entre les sourates', 'التنقل بين السور')); ?>">
            <a class="bh-btn bh-btn-ghost" href="<?php echo esc_url($bhCoranBase . $prevSurah . '/'); ?>">← <?php echo esc_html(bh_l('Sourate précédente', 'السورة السابقة')); ?></a>
            <a class="bh-btn bh-btn-ghost" href="<?php echo esc_url(bh_nav_url('coran')); ?>"><?php echo esc_html(bh_l('Toutes les sourates', 'كل السور')); ?></a>
            <a class="bh-btn bh-btn-ghost" href="<?php echo esc_url($bhCoranBase . $nextSurah . '/'); ?>"><?php echo esc_html(bh_l('Sourate suivante', 'السورة التالية')); ?> →</a>
        </nav>

        <div class="bh-quran-reader"
             data-bh-reader
             data-surah="<?php echo (int) $surah['n']; ?>"
             data-start-global="<?php echo (int) $surah['start_global']; ?>"
             x-data="bhReader(<?php echo (int) $surah['n']; ?>)" x-init="init()">

            <header class="bh-quran-reader__head">
                <div class="bh-quran-reader__title">
                    <h1 dir="rtl" class="bh-font-quran"><?php echo esc_html($surah['name_ar']); ?></h1>
                    <p>
                        <strong><?php echo esc_html($surah['name_fr']); ?></strong> — <?php echo esc_html($surah['meaning_fr']); ?>
                        · <?php echo (int) $surah['ayah_count']; ?> <?php echo esc_html(bh_l('versets', 'آيات')); ?>
                        · <?php echo esc_html(bh_l($surah['type'], $surah['type'] === 'mecquoise' ? 'مكية' : 'مدنية')); ?>
                    </p>
                </div>
                <div class="bh-quran-reader__tools">
                    <label class="bh-reader-reciter">
                        <span><?php echo esc_html(bh_l('Récitateur', 'القارئ')); ?></span>
                        <select x-model="reciter" class="bh-select" aria-label="<?php echo esc_attr(bh_l('Récitateur', 'القارئ')); ?>">
                            <option value="ar.alafasy">Mishary Alafasy</option>
                            <option value="ar.minshawi">Minshawi</option>
                            <option value="ar.abdulbasitmurattal">Abdul Basit (Murattal)</option>
                        </select>
                    </label>
                    <label class="bh-reader-toggle" x-show="frEdition !== ''">
                        <input type="checkbox" x-model="showFr">
                        <span><?php echo esc_html(bh_l('Traduction', 'الترجمة')); ?></span>
                    </label>
                    <label class="bh-reader-toggle">
                        <input type="checkbox" x-model="simpleScript">
                        <span><?php echo esc_html(bh_l('Script simplifié', 'خط مبسط')); ?></span>
                    </label>
                    <label class="bh-reader-toggle">
                        <input type="checkbox" x-model="tafsirOpen">
                        <span><?php echo esc_html(bh_l('Tafsir', 'التفسير')); ?></span>
                    </label>
                    <label class="bh-reader-reciter">
                        <span aria-hidden="true">⏩</span>
                        <select x-model="speed" class="bh-select" aria-label="<?php echo esc_attr(bh_l('Vitesse de lecture', 'سرعة القراءة')); ?>">
                            <option value="0.75">0.75×</option>
                            <option value="1">1×</option>
                            <option value="1.25">1.25×</option>
                            <option value="1.5">1.5×</option>
                        </select>
                    </label>
                    <label class="bh-reader-reciter">
                        <span aria-hidden="true">🔁</span>
                        <select x-model="repeatN" class="bh-select" aria-label="<?php echo esc_attr(bh_l('Répéter le verset', 'تكرار الآية')); ?>">
                            <option value="0"><?php echo esc_html(bh_l('Non', 'لا')); ?></option>
                            <option value="1">1×</option>
                            <option value="3">3×</option>
                            <option value="5">5×</option>
                            <option value="10">10×</option>
                        </select>
                    </label>
                    <label class="bh-reader-toggle">
                        <input type="checkbox" x-model="sequential">
                        <span><?php echo esc_html(bh_l('Verset par verset', 'آية بآية')); ?></span>
                    </label>
                    <button class="bh-btn bh-btn-primary" type="button" @click="toggleAll()">
                        <span x-show="!allPlaying">▶ <?php echo esc_html(bh_l('Écouter la sourate', 'استمع للسورة')); ?></span>
                        <span x-show="allPlaying">⏸ <?php echo esc_html(bh_l('Arrêter', 'إيقاف')); ?></span>
                    </button>
                    <a class="bh-btn bh-btn-ghost" href="#bh-quiz"><?php echo esc_html(bh_l('Quiz mémorisation', 'اختبار الحفظ')); ?></a>
                </div>
            </header>

            <div class="bh-quran-reader__body" dir="rtl" lang="ar">
                <p class="bh-bismillah bh-font-quran" x-show="surahN !== 1 && surahN !== 9">بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ</p>
                <?php foreach ($su = $surah['ayahs'] as $i => $text) : ?>
                    <?php $n = (int) $i + 1; ?>
                    <div class="bh-ayah" data-ayah="<?php echo $n; ?>" data-global="<?php echo (int) $surah['start_global'] + $i; ?>"
                       :class="{ 'is-playing': playingAyah === <?php echo $n; ?> }">
                        <span class="bh-ayah__ar bh-font-quran" x-html="arText(<?php echo $n; ?>)">
                            <?php echo $text; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- verified Uthmani text ?>
                            <a class="bh-ayah__num" href="#a-<?php echo $n; ?>" id="a-<?php echo $n; ?>"><?php echo $n; ?></a>
                        </span>
                        <span class="bh-ayah__fr" x-show="showFr" x-text="frText(<?php echo $n; ?>)" dir="ltr" lang="fr"></span>
                        <div class="bh-ayah__actions">
                            <button type="button" class="bh-ayah__act" @click="shareAyah(<?php echo $n; ?>)" title="<?php echo esc_attr(bh_l('Partager (image)', 'مشاركة (صورة)')); ?>" aria-label="<?php echo esc_attr(bh_l('Partager', 'مشاركة')); ?>">⤴</button>
                            <button type="button" class="bh-ayah__act" @click="toggleBookmark(<?php echo $n; ?>)" :class="{ 'is-on': isBookmarked(<?php echo $n; ?>) }" title="<?php echo esc_attr(bh_l('Signet', 'علامة')); ?>" aria-label="<?php echo esc_attr(bh_l('Signet', 'علامة')); ?>">★</button>
                            <button type="button" class="bh-ayah__act" @click="toggleNote(<?php echo $n; ?>)" title="<?php echo esc_attr(bh_l('Note personnelle', 'ملاحظة شخصية')); ?>" aria-label="<?php echo esc_attr(bh_l('Note', 'ملاحظة')); ?>">✎</button>
                            <button type="button" class="bh-ayah__act" @click="toggleTafsir(<?php echo $n; ?>)" :class="{ 'is-on': tafsirAyah === <?php echo $n; ?> }" title="<?php echo esc_attr(bh_l('Tafsir', 'التفسير')); ?>" aria-label="<?php echo esc_attr(bh_l('Tafsir', 'التفسير')); ?>">✦</button>
                            <button type="button" class="bh-ayah__act" @click="toggleW2W(<?php echo $n; ?>)" :class="{ 'is-on': w2wAyah === <?php echo $n; ?> }" title="<?php echo esc_attr(bh_l('Mot à mot', 'كلمة بكلمة')); ?>" aria-label="<?php echo esc_attr(bh_l('Mot à mot', 'كلمة بكلمة')); ?>">Aa</button>
                            <button type="button" class="bh-ayah__act" @click="playAyah(<?php echo $n; ?>)" title="<?php echo esc_attr(bh_l('Écouter ce verset', 'استمع لهذه الآية')); ?>" aria-label="<?php echo esc_attr(bh_l('Écouter', 'استمع')); ?>">▶</button>
                        </div>
                        <div class="bh-ayah__note" x-show="noteOpen === <?php echo $n; ?>" x-cloak>
                            <textarea rows="2" x-model="noteDraft" placeholder="<?php echo esc_attr(bh_l('Votre note sur ce verset…', 'ملاحظتك على هذه الآية…')); ?>"></textarea>
                            <span>
                                <button type="button" class="bh-btn bh-btn--ghost" @click="saveNote(<?php echo $n; ?>)"><?php echo esc_html(bh_l('Enregistrer', 'حفظ')); ?></button>
                                <button type="button" class="bh-btn bh-btn--ghost" @click="noteOpen = null"><?php echo esc_html(bh_l('Fermer', 'إغلاق')); ?></button>
                            </span>
                        </div>
                        <div class="bh-ayah__tafsir" x-show="tafsirOpen && tafsirAyah === <?php echo $n; ?>" x-cloak>
                            <p class="bh-muted" x-text="tafsirLoading() ? '…' : ''"></p>
                            <div class="bh-ayah__tafsir-body" x-html="tafsirHtml(<?php echo $n; ?>)"></div>
                        </div>
                        <div class="bh-ayah__w2w" x-show="w2wAyah === <?php echo $n; ?>" x-cloak>
                            <div class="bh-w2w" x-html="w2wHtml(<?php echo $n; ?>)"></div>
                            <p class="bh-muted" x-show="w2wError()"><?php echo esc_html(bh_l('Analyse mot-à-mot indisponible hors ligne.', 'التحليل كلمة-بكلمة غير متاح دون اتصال.')); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <footer class="bh-quran-reader__foot">
                <div class="bh-reader-bookmarks" x-show="bookmarksFor(thisSurah()).length">
                    <strong><?php echo esc_html(bh_l('Vos signets dans cette sourate :', 'علاماتكم في هذه السورة:')); ?></strong>
                    <template x-for="b in bookmarksFor(thisSurah())" :key="b">
                        <a class="bh-tag" :href="'#a-' + b" @click="playAyah(b)" x-text="'آية ' + b"></a>
                    </template>
                </div>
                <nav class="bh-quran-reader__foot-nav">
                    <a class="bh-btn bh-btn-ghost" href="<?php echo esc_url($bhCoranBase . $prevSurah . '/'); ?>">← <?php echo esc_html(bh_l('Précédente', 'السابق')); ?></a>
                    <a class="bh-btn bh-btn-ghost" href="<?php echo esc_url(bh_nav_url('coran')); ?>"><?php echo esc_html(bh_l('Sourates', 'السور')); ?></a>
                    <a class="bh-btn bh-btn-ghost" href="<?php echo esc_url($bhCoranBase . $nextSurah . '/'); ?>"><?php echo esc_html(bh_l('Suivante', 'التالي')); ?> →</a>
                </nav>
            </footer>

            <section class="bh-quran-plan" id="bh-quiz" x-data="bhPlan()" x-init="init()">
                <div class="bh-quran-plan__inner">
                    <h2><?php echo esc_html(bh_l('Plan de lecture & quiz', 'خطة القراءة والاختبار')); ?></h2>
                    <div class="bh-grid bh-grid--2">
                        <div>
                            <label class="bh-reader-toggle">
                                <span><?php echo esc_html(bh_l('Plan :', 'الخطة:')); ?></span>
                                <select x-model="planDays" @change="setPlan()" class="bh-select">
                                    <option value="30"><?php echo esc_html(bh_l('30 jours (1 juzu/jour)', '30 أيام (جزء/يوم)')); ?></option>
                                    <option value="15"><?php echo esc_html(bh_l('15 jours', '15 يوم')); ?></option>
                                    <option value="7"><?php echo esc_html(bh_l('7 jours', '7 أيام')); ?></option>
                                </select>
                            </label>
                            <div class="bh-plan-progress" role="progressbar" :aria-valuenow="pct()" aria-valuemin="0" aria-valuemax="100">
                                <span :style="'width:' + pct() + '%'"></span>
                            </div>
                            <p class="bh-plan-status" x-text="planStatus()"></p>
                        </div>
                        <div>
                            <button type="button" class="bh-btn" @click="openQuiz()"><?php echo esc_html(bh_l('Quiz : mémoriser cette sourate', 'اختبار: حفظ هذه السورة')); ?></button>
                        </div>
                    </div>
                </div>
                <div class="bh-quiz" x-show="quizActive" x-cloak>
                    <p class="bh-quiz__q" x-text="quizQuestion()"></p>
                    <div class="bh-quiz__opts">
                        <template x-for="opt in quizOptions()" :key="opt">
                            <button type="button" class="bh-quiz__opt" @click="quizPick(opt)" x-text="opt" dir="rtl"></button>
                        </template>
                    </div>
                    <p class="bh-quiz__res" x-text="quizResult()"></p>
                    <p>
                        <button type="button" class="bh-btn" x-show="quizDone()" @click="nextQuiz()"><?php echo esc_html(bh_l('Question suivante', 'السؤال التالي')); ?></button>
                        <span x-text="'Question ' + quizIndex() + '/' + quizTotal()"></span>
                    </p>
                </div>
            </section>
        </div>
    </div>
</section>

<?php get_footer(); ?>
