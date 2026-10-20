<?php
/** Theme header: topbar + logo + nav (+ live badge) + mobile menu. */
defined('ABSPATH') || exit;
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div class="bh-topbar">
    <div class="bh-container bh-topbar__inner">
        <span class="bh-topbar__date">
            <span data-bh-date><?php echo esc_html(bh_l(date_i18n('j F Y', current_time('timestamp')), date_i18n('j F Y', current_time('timestamp')))); ?></span>
            <span class="bh-topbar__hijri" data-bh-hijri></span>
        </span>
        <span class="bh-topbar__links">
            <?php
            $s = function_exists('bh_settings') ? bh_settings() : [];
            if (!empty($s['phone'])) {
                echo '<a class="bh-topbar__phone" href="tel:' . esc_attr(preg_replace('/[^0-9+]/', '', $s['phone'])) . '"><svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor" aria-hidden="true"><path d="M6.6 10.8a15.9 15.9 0 0 0 6.6 6.6l2.2-2.2c.3-.3.7-.4 1-.2 1.2.4 2.4.6 3.7.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1C10.9 21 3 13.1 3 3.9c0-.6.4-1 1-1h3.4c.6 0 1 .4 1 1 0 1.2.2 2.5.6 3.6.1.4 0 .8-.2 1l-2.2 2.3z"/></svg><span class="bh-hide-mobile">' . esc_html($s['phone']) . '</span></a>';
            }
            if (!empty($s['whatsapp'])) {
                echo '<a class="bh-topbar__wa" href="https://wa.me/' . esc_attr(preg_replace('/[^0-9+]/', '', $s['whatsapp'])) . '" target="_blank" rel="noopener" aria-label="WhatsApp"><svg viewBox="0 0 24 24" width="15" height="15" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.5 15.3L2 22l4.8-1.4A10 10 0 1 0 12 2zm5.5 14.1c-.2.7-1.3 1.3-1.9 1.4-.5.1-1 .2-3.4-.7-2.9-1.1-4.7-4-4.9-4.2-.1-.2-1.1-1.5-1.1-2.9s.7-2 1-2.3c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.4l.8 2c.1.2.1.4 0 .6l-.4.6-.5.5c-.2.2-.3.4-.1.7.2.3.8 1.4 1.8 2.2 1.2 1.1 2.3 1.5 2.6 1.6.3.1.5.1.7-.1l1-1.2c.2-.3.4-.2.7-.1l2 .9c.3.2.5.2.6.4 0 .1 0 .7-.2 1.4z"/></svg></a>';
            }
            if (!empty($s['facebook_url'])) {
                echo '<a class="bh-topbar__social" href="' . esc_url($s['facebook_url']) . '" target="_blank" rel="noopener" aria-label="Facebook"><svg viewBox="0 0 24 24" width="15" height="15" fill="currentColor" aria-hidden="true"><path d="M13.5 21v-8h2.7l.4-3.1h-3.1V7.9c0-.9.3-1.5 1.6-1.5h1.6V3.6c-.3 0-1.3-.1-2.4-.1-2.4 0-4 1.4-4 4.1v2.3H7.6V13h2.7v8h3.2z"/></svg></a>';
            }
            if (!empty($s['youtube_url'])) {
                echo '<a class="bh-topbar__social" href="' . esc_url($s['youtube_url']) . '" target="_blank" rel="noopener" aria-label="YouTube"><svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" aria-hidden="true"><path d="M21.6 7.2a2.5 2.5 0 0 0-1.8-1.8C18.2 5 12 5 12 5s-6.2 0-7.8.4A2.5 2.5 0 0 0 2.4 7.2 26 26 0 0 0 2 12a26 26 0 0 0 .4 4.8 2.5 2.5 0 0 0 1.8 1.8c1.6.4 7.8.4 7.8.4s6.2 0 7.8-.4a2.5 2.5 0 0 0 1.8-1.8A26 26 0 0 0 22 12a26 26 0 0 0-.4-4.8zM10 15.5v-7l6 3.5-6 3.5z"/></svg></a>';
            }
            ?>
            <?php echo bh_lang_switch_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built with esc_ in functions.php ?>
        </span>
    </div>
</div>

<header class="bh-header">
    <div class="bh-container bh-header__inner" x-data="{ open: false }">
        <a class="bh-header__brand" href="<?php echo esc_url(function_exists('bh_alt_url') ? bh_alt_url(bh_lang()) : home_url('/')); ?>">
            <?php echo bh_logo_html(50); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <span class="bh-header__names">
                <strong><?php bloginfo('name'); ?></strong>
                <em><?php echo esc_html(bh_l('TV &amp; Radio islamiques', 'قناة تلفزيونية وإذاعية إسلامية')); ?></em>
            </span>
        </a>

        <button class="bh-burger" type="button" @click="open = !open" :aria-expanded="open ? 'true' : 'false'" aria-label="<?php echo esc_attr(bh_l('Menu', 'القائمة')); ?>">
            <span></span><span></span><span></span>
        </button>

        <nav class="bh-nav" :class="{ 'bh-nav--open': open }" @click.outside="open = false" aria-label="<?php echo esc_attr(bh_l('Navigation principale', 'التنقل الرئيسي')); ?>">
            <ul class="bh-nav__list">
                <?php
                $live = function_exists('bh_live') ? bh_live() : ['enabled' => false];
                foreach (bh_nav_items() as $item) {
                    $isLiveBadge = !empty($item['live']);
                    $url = $isLiveBadge
                        ? (function_exists('bh_alt_url') ? bh_alt_url(bh_lang()) : home_url('/'))
                        : bh_nav_url($item['slug']);
                    $active = false;
                    if (function_exists('bh_page_id')) {
                        $id = bh_page_id($item['slug']);
                        if ($id && in_array($id, [get_queried_object_id(), (int) get_option('page_on_front')], true)) {
                            $active = true;
                        }
                    }
                    $label = bh_l($item['label_fr'], $item['label_ar']);
                    echo '<li' . ($active ? ' class="is-active"' : '') . '>';
                    if ($isLiveBadge) {
                        echo '<a class="bh-nav__link bh-nav__link--live" href="' . esc_url($url) . '">';
                        if (!empty($live['enabled'])) {
                            echo '<span class="bh-live-dot" aria-hidden="true"></span>';
                        }
                        echo esc_html($label) . '</a>';
                    } else {
                        echo '<a class="bh-nav__link" href="' . esc_url($url) . '">' . esc_html($label) . '</a>';
                    }
                    echo '</li>';
                }
                ?>
            </ul>
        </nav>
    </div>
</header>

<main class="bh-main" id="main">
