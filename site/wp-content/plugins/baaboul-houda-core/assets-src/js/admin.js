/**
 * Admin JS — settings page: schedule repeater (add/remove rows).
 */
import '../css/admin.css';

(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var addBtn = document.getElementById('bh-add-schedule-row');
        if (!addBtn || !window.bhScheduleTemplate) return;

        var counter = window.bhScheduleRowsUsed || 0;

        function nextIndex() {
            var rows = document.querySelectorAll('#bh-schedule .bh-schedule-row');
            var max = 0;
            rows.forEach(function (r) {
                var i = parseInt(r.dataset.i || '0', 10);
                if (i > max) max = i;
            });
            return max + 1;
        }

        function bind(row) {
            var remove = row.querySelector('.bh-remove-row');
            if (remove) {
                remove.onclick = function () { row.remove(); };
            }
        }

        addBtn.onclick = function () {
            var i = nextIndex();
            var html = window.bhScheduleTemplate.split('__I__').join(i);
            var tmp = document.createElement('div');
            tmp.innerHTML = html;
            var row = tmp.firstChild;
            document.querySelector('#bh-schedule tbody').appendChild(row);
            bind(row);
        };

        document.querySelectorAll('#bh-schedule .bh-schedule-row').forEach(bind);
    });
})();

/**
 * Episode editor: "Téléverser / choisir un fichier" opens the WordPress media
 * library (upload from the computer or pick an existing file) and writes the
 * file URL into the matching field. The main URL button follows the source type.
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var typeSel = document.getElementById('bh-source-type');
        var buttons = document.querySelectorAll('.bh-media-pick');
        if (!typeSel || !buttons.length) return;

        var urlInput = document.getElementById('bh-source-url');
        var mainBtn = document.querySelector('.bh-media-pick[data-target="bh-source-url"]');
        var audioHint = document.querySelector('[data-bh-hint="audio"]');

        // Show the upload button only for self-hosted sources; pick audio vs video.
        function syncType() {
            var t = typeSel.value;
            var hosted = t === 'audio' || t === 'file';
            if (mainBtn) {
                mainBtn.style.display = hosted ? '' : 'none';
                mainBtn.setAttribute('data-library', t === 'file' ? 'video' : 'audio');
                mainBtn.textContent = t === 'file' ? 'Téléverser / choisir une vidéo' : 'Téléverser / choisir un fichier audio';
            }
            if (audioHint) audioHint.style.display = t === 'audio' ? '' : 'none';
            if (urlInput) urlInput.placeholder = t === 'audio' ? 'https://…/emission.mp3' : (t === 'file' ? 'https://…/video.mp4' : 'https://…');
        }
        typeSel.addEventListener('change', syncType);
        syncType();

        buttons.forEach(function (btn) {
            var frame = null;
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                if (!window.wp || !window.wp.media) {
                    window.alert('La médiathèque WordPress n’est pas disponible sur cet écran.');
                    return;
                }
                var library = btn.getAttribute('data-library') || 'audio';
                // The library type can change with the source type: rebuild the frame.
                if (frame && frame.bhLibrary !== library) frame = null;
                if (!frame) {
                    frame = window.wp.media({
                        title: library === 'video' ? 'Choisir une vidéo' : 'Choisir un fichier audio',
                        button: { text: 'Utiliser ce fichier' },
                        library: { type: library },
                        multiple: false,
                    });
                    frame.bhLibrary = library;
                    frame.on('select', function () {
                        var file = frame.state().get('selection').first().toJSON();
                        var target = document.getElementById(btn.getAttribute('data-target'));
                        if (target && file && file.url) {
                            target.value = file.url;
                            target.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                    });
                }
                frame.open();
            });
        });
    });
})();
