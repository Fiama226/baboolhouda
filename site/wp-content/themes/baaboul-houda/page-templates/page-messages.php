<?php
/**
 * Template Name: Mots des fidèles
 */
defined('ABSPATH') || exit;
get_header();
$q = new \WP_Query([
    'post_type'      => 'message',
    'post_status'    => 'publish',
    'posts_per_page' => 24,
    'no_found_rows'  => true,
]);
$ok  = isset($_GET['bh_ok']) && 'wall' === $_GET['bh_ok'];
$err = isset($_GET['bh_err']);
?>
<div class="bh-container bh-page">
    <nav class="bh-breadcrumb" aria-label="Fil d’Ariane">
        <a href="<?php echo esc_url(bh_nav_url('direct')); ?>"><?php echo esc_html(bh_l('Accueil', 'الرئيسية')); ?></a>
        <span aria-hidden="true">/</span> <span><?php echo esc_html(bh_l('Mots des fidèles', 'كلمات المصلين')); ?></span>
    </nav>
    <header class="bh-page__head">
        <h1><?php echo esc_html(bh_post_title(get_queried_object_id())); ?></h1>
        <p class="bh-page__lead"><?php echo esc_html(bh_l('Vos messages, prières et encouragements.', 'رسائلكم ودعاؤكم وتشجيعكم.')); ?></p>
    </header>

    <div class="bh-grid bh-grid--2">
        <form class="bh-card bh-wall-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="bh_message_wall">
            <?php wp_nonce_field('bh_wall', 'bh_wall_nonce'); ?>
            <label><?php echo esc_html(bh_l('Votre nom (facultatif)', 'اسمك (اختياري)')); ?>
                <input type="text" name="name" maxlength="80">
            </label>
            <label><?php echo esc_html(bh_l('Votre message', 'رسالتك')); ?>
                <textarea name="message" rows="4" required maxlength="600"></textarea>
            </label>
            <button type="submit" class="bh-btn"><?php echo esc_html(bh_l('Envoyer', 'إرسال')); ?></button>
            <?php if ($ok) : ?>
                <p class="bh-form-ok"><?php echo esc_html(bh_l('Merci ! Votre message sera publié après modération.', 'شكراً! ستنشر رسالتك بعد المراجعة.')); ?></p>
            <?php endif; ?>
            <?php if ($err) : ?>
                <p class="bh-form-err"><?php echo esc_html(bh_l('Message vide ou lien expiré — réessayez.', 'رسالة فارغة أو رابط منتهي — أعد المحاولة.')); ?></p>
            <?php endif; ?>
        </form>
        <div class="bh-wall-list">
            <?php if (!$q->have_posts()) : ?>
                <p class="bh-empty"><?php echo esc_html(bh_l('Soyez le premier à laisser un mot !', 'كن أول من يترك كلمة!')); ?></p>
            <?php else : ?>
                <?php
                while ($q->have_posts()) :
                    $q->the_post();
                    $name = (string) get_post_meta(get_the_ID(), '_bh_question_name', true);
                    ?>
                    <div class="bh-card bh-card--wallmsg">
                        <p><?php echo esc_html(bh_post_excerpt(get_the_ID(), 50)); ?></p>
                        <small class="bh-muted"><?php echo esc_html($name !== '' ? $name : bh_l('Fidèle', 'مؤمن')); ?></small>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php get_footer();
