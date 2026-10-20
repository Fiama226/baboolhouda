<?php
/**
 * Content types for the station.
 *  - programme : une émission / program (TV or radio)
 *  - episode   : un épisode / replay (source: YouTube, Facebook, fichier auto-hébergé)
 *  - animateur : un présentateur / une présentatrice
 *  - evenement : un événement (conférence, ramadan, iftar, concours…)
 *
 * French is native; Arabic lives in _bh_title_ar / _bh_content_ar meta.
 */

defined('ABSPATH') || exit;

add_action('init', function () {
    register_post_type('programme', [
        'labels' => [
            'name'          => 'Programmes',
            'singular_name' => 'Programme',
            'add_new_item'  => 'Ajouter un programme',
            'edit_item'     => 'Modifier le programme',
        ],
        'public'       => true,
        'has_archive'  => false, // the /programmes/ section page lists everything
        'menu_icon'    => 'dashicons-controls-play',
        'supports'     => ['title', 'editor', 'thumbnail', 'excerpt'],
        'rewrite'      => ['slug' => 'programme', 'with_front' => false],
        'show_in_rest' => true,
    ]);

    register_post_type('episode', [
        'labels' => [
            'name'          => 'Épisodes (replays)',
            'singular_name' => 'Épisode',
            'add_new_item'  => 'Ajouter un épisode',
            'edit_item'     => 'Modifier l’épisode',
        ],
        'public'       => true,
        'has_archive'  => false, // the /emissions/ section page lists everything
        'menu_icon'    => 'dashicons-video-alt2',
        'supports'     => ['title', 'editor', 'thumbnail', 'excerpt', 'revisions'],
        'rewrite'      => ['slug' => 'emission', 'with_front' => false],
        'show_in_rest' => true,
    ]);

    register_post_type('animateur', [
        'labels' => [
            'name'          => 'Animateurs',
            'singular_name' => 'Animateur',
            'add_new_item'  => 'Ajouter un animateur',
            'edit_item'     => 'Modifier l’animateur',
        ],
        'public'       => true,
        'has_archive'  => false,
        'menu_icon'    => 'dashicons-microphone',
        'supports'     => ['title', 'editor', 'thumbnail'],
        'rewrite'      => ['slug' => 'animateur', 'with_front' => false],
        'show_in_rest' => true,
    ]);

    register_post_type('evenement', [
        'labels' => [
            'name'          => 'Événements',
            'singular_name' => 'Événement',
            'add_new_item'  => 'Ajouter un événement',
            'edit_item'     => 'Modifier l’événement',
        ],
        'public'       => true,
        'has_archive'  => false,
        'menu_icon'    => 'dashicons-calendar-alt',
        'supports'     => ['title', 'editor', 'thumbnail'],
        'rewrite'      => ['slug' => 'evenement', 'with_front' => false],
        'show_in_rest' => true,
    ]);

    register_taxonomy('categorie_programme', 'programme', [
        'labels' => [
            'name'          => 'Catégories de programmes',
            'singular_name' => 'Catégorie',
        ],
        'public'       => true,
        'hierarchical' => true,
        'rewrite'      => ['slug' => 'categorie-programme', 'with_front' => false],
    ]);
});

/** Episode source helpers. */
function bh_episode_source(int $episode_id): array {
    $type = get_post_meta($episode_id, '_bh_source_type', true);
    if (!in_array($type, ['youtube', 'facebook', 'file', 'audio'], true)) {
        $type = 'youtube';
    }
    return [
        'type'  => $type,
        'url'   => (string) get_post_meta($episode_id, '_bh_source_url', true),
        'audio' => (string) get_post_meta($episode_id, '_bh_audio_url', true),
    ];
}

function bh_episode_programme(int $episode_id): ?\WP_Post {
    $id = (int) get_post_meta($episode_id, '_bh_programme_id', true);
    return $id ? get_post($id) : null;
}

/** Programme logo (thumbnail) URL, sized. */
function bh_programme_logo(int $programme_id, int $size = 300): string {
    $id = get_post_thumbnail_id($programme_id);
    if (!$id) {
        return '';
    }
    $src = wp_get_attachment_image_src($id, 'large');
    return $src ? $src[0] : '';
}
