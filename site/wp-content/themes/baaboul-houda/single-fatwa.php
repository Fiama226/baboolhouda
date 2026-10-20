<?php
/**
 * Single fatwa (question → answer).
 */
defined('ABSPATH') || exit;
get_header();
while (have_posts()) :
    the_post();
    ?>
    <div class="bh-container bh-page">
        <nav class="bh-breadcrumb" aria-label="Fil d’Ariane">
            <a href="<?php echo esc_url(bh_nav_url('direct')); ?>"><?php echo esc_html(bh_l('Accueil', 'الرئيسية')); ?></a>
            <span aria-hidden="true">/</span>
            <a href="<?php echo esc_url(bh_nav_url('fatwas')); ?>"><?php echo esc_html(bh_l('Questions & réponses', 'الأسئلة والأجوبة')); ?></a>
            <span aria-hidden="true">/</span> <span><?php echo esc_html(get_the_title()); ?></span>
        </nav>
        <header class="bh-page__head">
            <h1><?php echo esc_html(get_the_title()); ?></h1>
            <?php $terms = get_the_terms(get_the_ID(), 'sujet_fatwa'); ?>
            <?php if ($terms) : ?>
                <span class="bh-tag"><?php echo esc_html($terms[0]->name); ?></span>
            <?php endif; ?>
        </header>
        <article class="bh-card bh-fatwa">
            <div class="bh-fatwa__answer"><?php echo wp_kses_post(bh_post_content(get_the_ID())); ?></div>
        </article>
        <p class="bh-note">
            <a class="bh-link" href="<?php echo esc_url(bh_nav_url('question')); ?>"><?php echo esc_html(bh_l('Vous avez une question ? Envoyez-la →', 'لديك سؤال؟ أرسله ←')); ?></a>
            ·
            <span class="bh-share" data-bh-share data-title="<?php echo esc_attr(get_the_title()); ?>" data-url="<?php echo esc_attr(get_permalink()); ?>"></span>
        </p>
    </div>
    <?php
endwhile;
get_footer();
