// -> Fotos > Kategorien: Reihenfolge per Drag-and-Drop (Griff links) und sofort speichern
// jquery-ui-sortable ist in WordPress enthalten. termOrder (ajaxUrl, nonce) kommt aus _term-order.php.
jQuery(function ($) {
    const list = $('#the-list');
    if (!list.length || typeof termOrder === 'undefined') {
        return;
    }

    // Kurze Rückmeldung über der Tabelle (und für Screenreader)
    const notify = (text, type) => {
        $('.term-order-notice').remove();
        const notice = $('<div class="notice inline term-order-notice"><p></p></div>')
            .addClass(type === 'error' ? 'notice-error' : 'notice-success');
        notice.find('p').text(text);
        $('#posts-filter').before(notice);
        window.wp?.a11y?.speak(text);
        if (type !== 'error') {
            setTimeout(() => notice.fadeOut(300, () => notice.remove()), 2000);
        }
    };

    list.sortable({
        items: '> tr',
        handle: '.term-order-handle',
        axis: 'y',
        cursor: 'move',
        // Zeile behält beim Ziehen ihre Spaltenbreiten
        helper: (event, row) => {
            row.children().each(function () {
                $(this).width($(this).width());
            });
            return row;
        },
        update: () => {
            // Zeilen-IDs «tag-12» → 12, in der neuen Reihenfolge
            const order = list.children('tr').map(function () {
                return parseInt(this.id.replace('tag-', ''), 10);
            }).get().filter(Boolean);

            $.post(termOrder.ajaxUrl, { action: 'term_order', nonce: termOrder.nonce, order })
                .done((response) => {
                    if (response && response.success) {
                        notify('Reihenfolge gespeichert.');
                    } else {
                        notify('Reihenfolge konnte nicht gespeichert werden.', 'error');
                    }
                })
                .fail(() => notify('Reihenfolge konnte nicht gespeichert werden.', 'error'));
        },
    });
});
