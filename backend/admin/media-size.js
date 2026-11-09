/**
 * Mediathek, Kachelansicht: Checkbox «Dateigrösse anzeigen».
 * Aktiv = auf jeder Kachel ein Etikett mit der Dateigrösse, zu grosse Dateien rot eingerahmt.
 * Die Angaben kommen von PHP (_media-size.php, Feld «mediaSize» an den Daten jeder Kachel).
 *
 * WordPress baut die Kacheln selbst per JavaScript (Backbone) und lädt beim Scrollen weitere nach.
 * Darum beobachtet ein MutationObserver die Seite und ergänzt jede neue Kachel.
 */
(function () {
    const grid = document.getElementById('wp-media-grid');
    if (!grid || !window.wp || !wp.media) {
        return; // Listenansicht: dort gibt es die Spalte «Dateigrösse»
    }

    const STORAGE_KEY = 'portfolio-media-size-visible';

    // Gespeicherte Einstellung lesen (bleibt nach dem Neuladen erhalten); Fehler still abfangen
    let visible = false;
    try {
        visible = localStorage.getItem(STORAGE_KEY) === '1';
    } catch (error) {}
    document.body.classList.toggle('media-size-visible', visible);

    // -> Checkbox neben dem Seitentitel «Mediathek»
    const label = document.createElement('label');
    label.className = 'media-size-toggle';
    label.innerHTML = '<input type="checkbox"> Dateigrösse anzeigen';
    const checkbox = label.querySelector('input');
    checkbox.checked = visible;
    checkbox.addEventListener('change', () => {
        document.body.classList.toggle('media-size-visible', checkbox.checked);
        try {
            localStorage.setItem(STORAGE_KEY, checkbox.checked ? '1' : '0');
        } catch (error) {}
    });
    const headerEnd = document.querySelector('.wp-header-end');
    (headerEnd || grid).before(label);

    // -> Eine Kachel ergänzen: Etikett mit Grösse, Klasse für «zu gross»
    const decorate = (tile) => {
        if (tile.querySelector('.media-size-badge')) {
            return;
        }
        const model = wp.media.attachment(tile.dataset.id);
        const info = model && model.get('mediaSize');
        if (!info) {
            return;
        }
        const badge = document.createElement('span');
        badge.className = 'media-size-badge';
        // textContent statt innerHTML: Text wird nie als HTML gelesen (sicher)
        badge.textContent = info.too_large ? `${info.label} · zu gross` : info.label;
        if (info.dimensions) {
            badge.title = info.dimensions;
        }
        tile.classList.toggle('is-too-large', info.too_large);
        tile.querySelector('.attachment-preview')?.appendChild(badge);
    };

    const decorateAll = () => grid.querySelectorAll('li.attachment[data-id]').forEach(decorate);

    // Neue Kacheln (Nachladen, Filter, Upload) automatisch ergänzen
    new MutationObserver(decorateAll).observe(grid, { childList: true, subtree: true });
    decorateAll();
})();
