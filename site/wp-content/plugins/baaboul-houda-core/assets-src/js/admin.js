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
