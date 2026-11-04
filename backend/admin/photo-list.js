// -> QuickEdit in der Übersicht «Fotos»: aktuelle Werte in die Auswahlfelder übernehmen
// WordPress öffnet QuickEdit über inlineEditPost.edit(). Wir erweitern diese Funktion:
// zuerst das Original ausführen, dann die Werte aus der Tabellenzeile in die Felder setzen.
(function () {
    if (typeof window.inlineEditPost === 'undefined') {
        return;
    }

    const originalEdit = window.inlineEditPost.edit;

    window.inlineEditPost.edit = function (id) {
        originalEdit.apply(this, arguments);

        // id ist entweder die Post-ID oder das angeklickte Element
        const postId = typeof id === 'object' ? parseInt(this.getId(id), 10) : parseInt(id, 10);
        if (!postId) {
            return;
        }

        const row = document.getElementById('post-' + postId);
        const editRow = document.getElementById('edit-' + postId);
        if (!row || !editRow) {
            return;
        }

        const size = row.querySelector('[data-photo-size]')?.dataset.photoSize;
        const focus = row.querySelector('[data-photo-focus]')?.dataset.photoFocus;

        const sizeSelect = editRow.querySelector('[name="photo_grid_size"]');
        const focusSelect = editRow.querySelector('[name="photo_focus"]');
        if (sizeSelect && size) {
            sizeSelect.value = size;
        }
        if (focusSelect && focus) {
            focusSelect.value = focus;
        }
    };
})();
