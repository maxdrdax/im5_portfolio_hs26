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

// =====================================================================
// -> Videos (ohne Ton, Schleife), siehe frontend/views/teaser/video.php
// =====================================================================
const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const canHover = window.matchMedia('(hover: hover) and (pointer: fine)').matches;

// play() gibt ein Promise zurück; Fehler (z. B. Autoplay blockiert) still abfangen
const playVideo = (video) => {
    video.play().catch(() => {});
};

// Video erst einblenden, wenn es wirklich läuft (kein schwarzes Aufblitzen)
document.querySelectorAll('video[data-video]').forEach((video) => {
    video.addEventListener('playing', () => video.classList.add('is-playing'));
    video.addEventListener('pause', () => video.classList.remove('is-playing'));
});

// -> «inview»-Videos (Hero, Galerie): spielen nur, solange sie sichtbar sind
const inviewVideos = document.querySelectorAll('video[data-video="inview"]');
if (reducedMotion) {
    // Bewegung reduzieren: nicht abspielen, nur das erste Bild zeigen
    inviewVideos.forEach((video) => {
        video.preload = 'metadata';
    });
} else {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                playVideo(entry.target);
            } else {
                entry.target.pause();
            }
        });
    }, { rootMargin: '200px 0px' });
    inviewVideos.forEach((video) => observer.observe(video));
}

// -> «hover»-Videos im Karussell: starten beim Hover über die Karte
if (canHover && !reducedMotion) {
    document.querySelectorAll('[data-hover-video]').forEach((target) => {
        const video = target.querySelector('video[data-video="hover"]');
        if (!video) {
            return;
        }
        target.addEventListener('mouseenter', () => playVideo(video));
        target.addEventListener('mouseleave', () => video.pause());
    });
}

// -> Directory: Vorschau (Bild/Video) folgt dem Mauszeiger
if (canHover) {
    document.querySelectorAll('[data-directory]').forEach((directory) => {
        const preview = directory.querySelector('[data-directory-preview]');
        const rows = directory.querySelectorAll('[data-preview-target]');
        const body = directory.querySelector('tbody');
        if (!preview || !body) {
            return;
        }
        const items = preview.querySelectorAll('[data-preview-id]');

        // Zielposition (Maus) und aktuelle Position (weich nachgezogen)
        let targetX = 0;
        let targetY = 0;
        let currentX = 0;
        let currentY = 0;
        let frame = null;

        const animate = () => {
            // Lineare Interpolation: pro Frame 15 % der Distanz → weiches Nachziehen
            currentX += (targetX - currentX) * 0.15;
            currentY += (targetY - currentY) * 0.15;
            preview.style.transform = `translate(${currentX}px, ${currentY}px)`;
            frame = requestAnimationFrame(animate);
        };

        const showItem = (id) => {
            items.forEach((item) => {
                const active = item.dataset.previewId === id;
                item.classList.toggle('is-active', active);
                const video = item.querySelector('video');
                if (!video) {
                    return;
                }
                if (active && !reducedMotion) {
                    playVideo(video);
                } else {
                    video.pause();
                }
            });
        };

        body.addEventListener('mousemove', (event) => {
            targetX = event.clientX;
            targetY = event.clientY;
            if (!frame) {
                // Beim ersten Eintritt direkt an die Maus springen, danach nachziehen
                currentX = targetX;
                currentY = targetY;
                frame = requestAnimationFrame(animate);
            }
        });

        rows.forEach((row) => {
            row.addEventListener('mouseenter', () => {
                showItem(row.dataset.previewTarget);
                preview.classList.add('is-visible');
            });
        });

        body.addEventListener('mouseleave', () => {
            preview.classList.remove('is-visible');
            showItem(null);
            // Animation stoppen, wenn die Maus die Liste verlässt (spart Leistung)
            cancelAnimationFrame(frame);
            frame = null;
        });
    });
}
