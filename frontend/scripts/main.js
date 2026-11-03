// -> Filter (Projektliste und Fotogalerie)
// Buttons mit [data-filter] blenden Einträge mit passendem [data-categories] ein/aus.
document.querySelectorAll('[data-filter-group]').forEach((group) => {
    const buttons = group.querySelectorAll('[data-filter]');
    const items = group.querySelectorAll('[data-categories]');

    buttons.forEach((button) => {
        button.addEventListener('click', () => {
            const value = button.dataset.filter;

            buttons.forEach((other) => {
                other.setAttribute('aria-pressed', String(other === button));
            });

            items.forEach((item) => {
                const categories = item.dataset.categories.split(' ');
                item.hidden = value !== 'all' && !categories.includes(value);
            });
        });
    });
});

// -> Scroll-Pfeil: scrollt zum nächsten Abschnitt nach dem Hero
document.querySelectorAll('[data-scroll-next]').forEach((button) => {
    button.addEventListener('click', () => {
        const next = button.closest('section')?.nextElementSibling;
        next?.scrollIntoView({ behavior: 'smooth' });
    });
});
