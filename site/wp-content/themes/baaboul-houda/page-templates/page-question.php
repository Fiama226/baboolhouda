<?php
/**
 * Template Name: Posez une question
 */
defined('ABSPATH') || exit;
get_header();
$ok  = isset($_GET['bh_ok']) && 'question' === $_GET['bh_ok'];
$err = isset($_GET['bh_err']);
?>
<div class="bh-container bh-page">
    <nav class="bh-breadcrumb" aria-label="Fil d’Ariane">
        <a href="<?php echo esc_url(bh_nav_url('direct')); ?>"><?php echo esc_html(bh_l('Accueil', 'الرئيسية')); ?></a>
        <span aria-hidden="true">/</span> <span><?php echo esc_html(bh_l('Posez une question', 'اطرح سؤالك')); ?></span>
    </nav>
    <header class="bh-page__head">
        <h1><?php echo esc_html(bh_post_title(get_queried_object_id())); ?></h1>
        <p class="bh-page__lead"><?php echo esc_html(bh_l('Envoyez votre question : les meilleures sont traitées à l’antenne, avec votre accord.', 'أرسل سؤالك: تُطرح أفضل الأسئلة على الهواء بموافقتكم.')); ?></p>
    </header>
    <form class="bh-card bh-question-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="bh_question">
        <?php wp_nonce_field('bh_question', 'bh_question_nonce'); ?>
        <label><?php echo esc_html(bh_l('Votre nom', 'اسمك')); ?>
            <input type="text" name="name" maxlength="80" required>
        </label>
        <label><?php echo esc_html(bh_l('Votre email (pour vous répondre, facultatif)', 'بريدك (للرد عليك، اختياري)')); ?>
            <input type="email" name="email" maxlength="120">
        </label>
        <label><?php echo esc_html(bh_l('Votre question', 'سؤالك')); ?>
            <textarea name="question" rows="5" required maxlength="1200"></textarea>
        </label>
        <button type="submit" class="bh-btn"><?php echo esc_html(bh_l('Envoyer ma question', 'إرسال سؤالي')); ?></button>
        <?php if ($ok) : ?>
            <p class="bh-form-ok"><?php echo esc_html(bh_l('Merci ! Votre question a bien été envoyée.', 'شكراً! تم إرسال سؤالك.')); ?></p>
        <?php endif; ?>
        <?php if ($err) : ?>
            <p class="bh-form-err"><?php echo esc_html(bh_l('Question vide ou lien expiré — réessayez.', 'سؤال فارغ أو رابط منتهي — أعد المحاولة.')); ?></p>
        <?php endif; ?>
    </form>
    <p class="bh-note"><?php echo esc_html(bh_l('Consulter aussi l’archive des questions déjà répondues.', 'راجعوا أيضاً أرشيف الأسئلة المجابة.')); ?>
        <a class="bh-link" href="<?php echo esc_url(bh_nav_url('fatwas')); ?>"><?php echo esc_html(bh_l('Questions & réponses →', 'الأسئلة والأجوبة ←')); ?></a>
    </p>
</div>
<?php get_footer();
