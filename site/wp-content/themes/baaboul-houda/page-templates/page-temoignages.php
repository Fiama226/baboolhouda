<?php
/**
 * Template Name: Témoignages
 */
defined('ABSPATH') || exit;
get_header();
$q = new \WP_Query([
    'post_type'      => 'temoignage',
    'posts_per_page' => 12,
    'no_found_rows'  => true,
]);
?>
<div class="bh-container bh-page">
    <nav class="bh-breadcrumb" aria-label="Fil d’Ariane">
        <a href="<?php echo esc_url(bh_nav_url('direct')); ?>"><?php echo esc_html(bh_l('Accueil', 'الرئيسية')); ?></a>
        <span aria-hidden="true">/</span> <span><?php echo esc_html(bh_l('Témoignages', 'الشهادات')); ?></span>
    </nav>
    <header class="bh-page__head">
        <h1><?php echo esc_html(bh_post_title(get_queried_object_id())); ?></h1>
        <p class="bh-page__lead"><?php echo esc_html(bh_l('Ce que disent nos fidèles et partenaires.', 'ماذا يقول أمتنا وشركاؤنا.')); ?></p>
    </header>
    <div class="bh-grid bh-grid--3">
        <?php if (!$q->have_posts()) : ?>
            <p class="bh-empty"><?php echo esc_html(bh_l('Aucun témoignage pour le moment.', 'لا شهادات بعد.')); ?></p>
        <?php else : ?>
            <?php
            while ($q->have_posts()) :
                $q->the_post();
                // Title format: "Nom — Rôle" (rôle optionnel).
                $title = get_the_title();
                $bits  = array_map('trim', explode('—', $title, 2));
                $name  = $bits[0];
                $role  = isset($bits[1]) ? $bits[1] : '';
                ?>
                <figure class="bh-card bh-card--quote">
                    <blockquote>
                        <?php
                        $body = (function_exists('bh_post_excerpt')) ? bh_post_excerpt(get_the_ID(), 60) : get_the_excerpt();
                        echo esc_html($body);
                        ?>
                    </blockquote>
                    <figcaption>
                        <strong><?php echo esc_html($name); ?></strong>
                        <?php if ($role !== '') : ?><small><?php echo esc_html($role); ?></small><?php endif; ?>
                    </figcaption>
                </figure>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</div>
<?php get_footer();
