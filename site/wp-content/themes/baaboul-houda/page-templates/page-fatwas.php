<?php
/**
 * Template Name: Questions & réponses
 */
defined('ABSPATH') || exit;
get_header();
$q = new \WP_Query([
    'post_type'      => 'fatwa',
    'posts_per_page' => 30,
]);
$total = (int) $q->found_posts;
?>
<div class="bh-container bh-page">
    <nav class="bh-breadcrumb" aria-label="Fil d’Ariane">
        <a href="<?php echo esc_url(bh_nav_url('direct')); ?>"><?php echo esc_html(bh_l('Accueil', 'الرئيسية')); ?></a>
        <span aria-hidden="true">/</span> <span><?php echo esc_html(bh_l('Questions & réponses', 'الأسئلة والأجوبة')); ?></span>
    </nav>
    <header class="bh-page__head">
        <h1><?php echo esc_html(bh_post_title(get_queried_object_id())); ?></h1>
        <p class="bh-page__lead"><?php echo esc_html(bh_l('Archive des questions posées et réponses données à l’antenne.', 'أرشيف الأسئلة المطروحة والإجابات المقدمة.')); ?></p>
    </header>

    <?php if (!$q->have_posts()) : ?>
        <p class="bh-empty"><?php echo esc_html(bh_l('Aucune question répondue pour le moment.', 'لا أسئلة مجابة بعد.')); ?></p>
    <?php else : ?>
        <div class="bh-faq">
            <?php
            while ($q->have_posts()) :
                $q->the_post();
                ?>
                <details class="bh-faq__item">
                    <summary>
                        <?php echo esc_html(get_the_title()); ?>
                        <?php
                        $terms = get_the_terms(get_the_ID(), 'sujet_fatwa');
                        if ($terms) {
                            echo '<span class="bh-tag">' . esc_html($terms[0]->name) . '</span>';
                        }
                        ?>
                    </summary>
                    <div class="bh-faq__answer"><?php echo wp_kses_post(bh_post_content(get_the_ID())); ?></div>
                </details>
            <?php endwhile; ?>
        </div>
        <?php if ($total > 30) : ?>
            <p class="bh-note"><?php echo esc_html(sprintf(bh_l('%d questions au total.', 'إجمالي %d سؤال.'), $total)); ?></p>
        <?php endif; ?>
    <?php endif; ?>

    <p class="bh-note"><a class="bh-link" href="<?php echo esc_url(bh_nav_url('question')); ?>"><?php echo esc_html(bh_l('Vous avez une question ? Envoyez-la →', 'لديك سؤال؟ أرسله ←')); ?></a></p>
</div>
<?php get_footer();
