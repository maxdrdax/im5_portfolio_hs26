// -> Filter (Projektliste und Fotogalerie)
// Buttons mit [data-filter] blenden Einträge mit passendem [data-categories] ein/aus.
// Sonderwerte: "all" = alles, "featured" = nur Einträge mit data-featured="true".
document.querySelectorAll('[data-filter-group]').forEach((group) => {
    const buttons = group.querySelectorAll('[data-filter]');
    const items = group.querySelectorAll('[data-categories]');

    const applyFilter = (activeButton) => {
        const value = activeButton.dataset.filter;

        buttons.forEach((button) => {
            button.setAttribute('aria-pressed', String(button === activeButton));
        });

        items.forEach((item) => {
            let visible = true;
            if (value === 'featured') {
                visible = item.dataset.featured === 'true';
            } else if (value !== 'all') {
                visible = item.dataset.categories.split(' ').includes(value);
            }
            item.hidden = !visible;
        });
    };

    buttons.forEach((button) => {
        button.addEventListener('click', () => applyFilter(button));
    });

    // Beim Laden den Standardfilter anwenden (Button, der im HTML aktiv ist)
    const defaultButton = group.querySelector('[data-filter][aria-pressed="true"]');
    if (defaultButton) {
        applyFilter(defaultButton);
    }
});

// -> Scroll-Pfeil: scrollt zum nächsten Abschnitt nach dem Hero
document.querySelectorAll('[data-scroll-next]').forEach((button) => {
    button.addEventListener('click', () => {
        const next = button.closest('section')?.nextElementSibling;
        next?.scrollIntoView({ behavior: 'smooth' });
    });
});
