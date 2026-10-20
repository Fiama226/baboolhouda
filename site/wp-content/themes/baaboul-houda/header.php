<?php
/**
 * Theme header: slim topbar (date, contact, social, language) + main bar
 * (brand, primary nav + "Plus" menu, search, theme toggle, EN DIRECT button).
 */
defined('ABSPATH') || exit;

// Group the nav: visible links, the "Plus" menu, and the live button.
$bh_main = $bh_more = [];
$bh_live_item = null;
foreach (bh_nav_items() as $bh_item) {
    $bh_item['current'] = bh_nav_is_current($bh_item);
    if (!empty($bh_item['live'])) {
        $bh_live_item = $bh_item;
    } elseif (($bh_item['zone'] ?? 'main') === 'more') {
        $bh_more[] = $bh_item;
    } else {
        $bh_main[] = $bh_item;
    }
}
$bh_more_current = (bool) array_filter($bh_more, static fn($i) => $i['current']);
$bh_live         = function_exists('bh_live') ? bh_live() : ['enabled' => false];
$bh_is_live      = !empty($bh_live['enabled']);
$bh_chevron      = '<svg class="bh-ico bh-ico--chev" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>';
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
            <span data-bh-date><?php echo esc_html(date_i18n('j F Y', current_time('timestamp'))); ?></span>
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
            echo bh_lang_switch_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built with esc_ in functions.php
            ?>
        </span>
    </div>
</div>

<header class="bh-header" x-data="{ open: false, scrolled: false }"
        @scroll.window.passive="scrolled = window.scrollY > 4" :class="{ 'is-scrolled': scrolled }"
        @keydown.escape.window="open = false">
    <div class="bh-container bh-header__inner">
        <a class="bh-header__brand" href="<?php echo esc_url(function_exists('bh_alt_url') ? bh_alt_url(bh_lang()) : home_url('/')); ?>">
            <?php echo bh_logo_html(46); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <span class="bh-header__names">
                <strong><?php bloginfo('name'); ?></strong>
                <em><?php echo esc_html(bh_l('TV & Radio islamiques', 'قناة تلفزيونية وإذاعية إسلامية')); ?></em>
            </span>
        </a>

        <nav class="bh-nav" id="bh-nav" :class="{ 'bh-nav--open': open }" @click.outside="open = false" aria-label="<?php echo esc_attr(bh_l('Navigation principale', 'التنقل الرئيسي')); ?>">
            <ul class="bh-nav__list">
                <?php foreach ($bh_main as $bh_item) : ?>
                    <li<?php echo $bh_item['current'] ? ' class="is-active"' : ''; ?>>
                        <a class="bh-nav__link" href="<?php echo esc_url(bh_nav_url($bh_item['slug'])); ?>"<?php echo $bh_item['current'] ? ' aria-current="page"' : ''; ?>>
                            <?php echo esc_html(bh_l($bh_item['label_fr'], $bh_item['label_ar'])); ?>
                        </a>
                    </li>
                <?php endforeach; ?>

                <?php if ($bh_more) : ?>
                    <li class="bh-nav__more<?php echo $bh_more_current ? ' is-active' : ''; ?>" x-data="{ more: false }" @click.outside="more = false" @keydown.escape="more = false">
                        <button type="button" class="bh-nav__link bh-nav__more-btn" @click="more = !more" :aria-expanded="more ? 'true' : 'false'" aria-haspopup="true" aria-controls="bh-nav-more">
                            <?php echo esc_html(bh_l('Plus', 'المزيد')); ?>
                            <?php echo $bh_chevron; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        </button>
                        <ul class="bh-nav__menu" id="bh-nav-more" x-show="more" x-cloak>
                            <?php foreach ($bh_more as $bh_item) : ?>
                                <li>
                                    <a class="bh-nav__sublink<?php echo $bh_item['current'] ? ' is-current' : ''; ?>" href="<?php echo esc_url(bh_nav_url($bh_item['slug'])); ?>"<?php echo $bh_item['current'] ? ' aria-current="page"' : ''; ?>>
                                        <?php echo esc_html(bh_l($bh_item['label_fr'], $bh_item['label_ar'])); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
            <div class="bh-nav__tools">
                <button type="button" class="bh-icon-btn bh-theme-toggle" data-bh-theme-toggle>
                    <svg class="bh-ico bh-ico--moon" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 14.5A8 8 0 0 1 9.5 4a8 8 0 1 0 10.5 10.5z"/></svg>
                    <svg class="bh-ico bh-ico--sun" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2.5v2.2M12 19.3v2.2M2.5 12h2.2M19.3 12h2.2M5.3 5.3l1.6 1.6M17.1 17.1l1.6 1.6M18.7 5.3l-1.6 1.6M6.9 17.1l-1.6 1.6"/></svg>
                    <span><?php echo esc_html(bh_l('Mode sombre / clair', 'الوضع الداكن/الفاتح')); ?></span>
                </button>
            </div>
        </nav>

        <div class="bh-header__actions">
            <div class="bh-search" x-data="bhSearch()" @click.outside="close()" @keydown.escape.window="close()">
                <button type="button" class="bh-icon-btn" @click="toggle()" :aria-expanded="panel ? 'true' : 'false'" aria-controls="bh-search-panel"
                        aria-label="<?php echo esc_attr(bh_l('Rechercher sur le site', 'ابحث في الموقع')); ?>">
                    <svg class="bh-ico" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="6.5"/><path d="m16 16 4.5 4.5"/></svg>
                </button>
                <div class="bh-search__panel" id="bh-search-panel" x-show="panel" x-cloak>
                    <input type="search" x-ref="q" x-model="q" @input="_timer()" @keydown.enter.prevent="submit()"
                           placeholder="<?php echo esc_attr(bh_l('Rechercher une émission, une sourate…', 'ابحث عن برنامج أو سورة…')); ?>"
                           aria-label="<?php echo esc_attr(bh_l('Rechercher sur le site', 'ابحث في الموقع')); ?>"
                           autocomplete="off">
                    <ul class="bh-search__list" x-show="open" x-cloak>
                        <template x-for="r in results" :key="r.url">
                            <li><a :href="r.url"><span class="bh-search__type" x-text="r.type"></span><span x-text="r.title"></span></a></li>
                        </template>
                    </ul>
                </div>
            </div>

            <button type="button" class="bh-icon-btn bh-theme-toggle" data-bh-theme-toggle aria-label="<?php echo esc_attr(bh_l('Mode sombre / clair', 'الوضع الداكن/الفاتح')); ?>">
                <svg class="bh-ico bh-ico--moon" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 14.5A8 8 0 0 1 9.5 4a8 8 0 1 0 10.5 10.5z"/></svg>
                <svg class="bh-ico bh-ico--sun" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2.5v2.2M12 19.3v2.2M2.5 12h2.2M19.3 12h2.2M5.3 5.3l1.6 1.6M17.1 17.1l1.6 1.6M18.7 5.3l-1.6 1.6M6.9 17.1l-1.6 1.6"/></svg>
            </button>

            <?php if ($bh_live_item) : ?>
                <a class="bh-live-cta<?php echo $bh_is_live ? ' is-on' : ''; ?>" href="<?php echo esc_url(bh_nav_url($bh_live_item['slug'])); ?>">
                    <?php if ($bh_is_live) : ?><span class="bh-live-dot" aria-hidden="true"></span><?php endif; ?>
                    <?php echo esc_html($bh_is_live ? bh_l('EN DIRECT', 'مباشر') : bh_l('Direct', 'البث')); ?>
                </a>
            <?php endif; ?>

            <button class="bh-burger" type="button" @click.stop="open = !open" :class="{ 'is-open': open }" :aria-expanded="open ? 'true' : 'false'" aria-controls="bh-nav" aria-label="<?php echo esc_attr(bh_l('Menu', 'القائمة')); ?>">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
</header>

<main class="bh-main" id="main">
