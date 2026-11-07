/**
 * Backend «Design > Menüs» (geladen über backend/admin/_nav-menus.php)
 * 1. Vorschaubild in die zugeklappte Zeile jedes Menüeintrags setzen
 * 2. Button «Alle aufklappen / Alle zuklappen»
 */
(function () {
    const list = document.getElementById('menu-to-edit');
    if (!list) {
        return;
    }

    // -> 1. Vorschaubilder: Daten-Element aus dem aufgeklappten Bereich → Bild in der Titelzeile
    const addThumbs = () => {
        list.querySelectorAll('.menu-item').forEach((item) => {
            const data = item.querySelector('.portfolio-menu-thumb-data');
            const title = item.querySelector('.menu-item-handle .item-title');
            if (!data || !title || title.querySelector('.portfolio-menu-thumb')) {
                return;
            }
            const img = document.createElement('img');
            img.className = 'portfolio-menu-thumb';
            img.src = data.dataset.thumb; // URL ist in PHP mit esc_url() geprüft
            img.alt = '';
            title.prepend(img);
        });
    };
    addThumbs();

    // -> 2. Button «Alle aufklappen / Alle zuklappen»
    // WordPress klappt einen Eintrag per Klick auf den Pfeil (.item-edit) auf. Der Button «klickt»
    // diesen Pfeil bei allen Einträgen, die noch nicht im gewünschten Zustand sind.
    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'button portfolio-menu-toggle-all';
    button.textContent = 'Alle aufklappen';
    let expanded = false;

    button.addEventListener('click', () => {
        expanded = !expanded;
        list.querySelectorAll('.menu-item').forEach((item) => {
            const isOpen = item.classList.contains('menu-item-edit-active');
            if (isOpen !== expanded) {
                item.querySelector('.item-edit')?.click();
            }
        });
        button.textContent = expanded ? 'Alle zuklappen' : 'Alle aufklappen';
    });

    list.before(button);
})();
