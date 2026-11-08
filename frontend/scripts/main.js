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

    // Weicher Wechsel per View Transition (innerhalb der Seite): Der Browser macht ein Bild vom Zustand
    // vorher und nachher. Jeder Eintrag bekommt kurz einen eigenen Namen, damit der Browser ihn
    // wiedererkennt: bleibt er sichtbar, gleitet er an den neuen Platz; neue blenden ein, alte aus.
    // Ohne Unterstützung oder mit «Bewegung reduzieren»: Filter wechselt wie bisher sofort.
    const groupIndex = [...document.querySelectorAll('[data-filter-group]')].indexOf(group);
    buttons.forEach((button) => {
        button.addEventListener('click', () => {
            const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            if (reduce || !document.startViewTransition) {
                applyFilter(button);
                return;
            }
            items.forEach((item, i) => {
                item.style.viewTransitionName = `filter-${groupIndex}-${i}`;
            });
            const transition = document.startViewTransition(() => applyFilter(button));
            // Namen wieder entfernen, sonst würden die Einträge auch beim Seitenwechsel einzeln animiert
            transition.finished.finally(() => {
                items.forEach((item) => item.style.removeProperty('view-transition-name'));
            });
        });
    });

    // Beim Laden den Standardfilter anwenden (Button, der im HTML aktiv ist)
    const defaultButton = group.querySelector('[data-filter][aria-pressed="true"]');
    if (defaultButton) {
        applyFilter(defaultButton);
    }
});

// -> Scroll-Pfeil: scrollt so weit, dass der Hero ganz unter dem Header verschwindet
document.querySelectorAll('[data-scroll-next]').forEach((button) => {
    button.addEventListener('click', () => {
        const hero = button.closest('section');
        const header = document.querySelector('[data-layout="header"]');
        if (!hero) {
            return;
        }

        // Unterkante des Heros auf der Seite (nicht nur im sichtbaren Bereich)
        const heroBottom = hero.getBoundingClientRect().bottom + window.scrollY;
        // Unterkante des fixen Headers im Fenster (inkl. Admin-Leiste, falls eingeloggt)
        const headerBottom = header ? header.getBoundingClientRect().bottom : 0;

        window.scrollTo({
            top: heroBottom - headerBottom,
            behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth',
        });
    });
});

// =====================================================================
// -> Videos (ohne Ton, Schleife), siehe frontend/views/teaser/video.php
// =====================================================================
const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const canHover = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
// Tablet und Handy (bis 1024 px): Highlights alle aufgeklappt, Galerie zum Wischen (kein Akkordeon).
// Als MediaQueryList, damit .matches auch nach einer Änderung der Fensterbreite stimmt.
const compactLayout = window.matchMedia('(max-width: 1024px)');

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

// =====================================================================
// -> Highlights-Akkordeon (Startseite)
// Mausrad/Trackpad: Ist die Maus über den Highlights, wird der Scrollweg gezählt; nach einer
// bestimmten Strecke klappt das nächste bzw. vorherige Projekt auf, statt dass die Seite scrollt.
// Am Anfang/Ende scrollt die Seite normal weiter.
// Zusätzlich: Hover, Tastatur (Tab) und Tippen (erstes Tippen klappt auf, zweites öffnet).
// =====================================================================

// ▼ Zum Ausprobieren anpassen
const ACCORDION_SCROLL_DISTANCE = 300; // Scrollweg in px pro Projekt (mehr = mehr scrollen nötig)
const ACCORDION_STEP_PAUSE = 700; // ms Mindestabstand zwischen zwei Projekten (bremst schnelle Trackpad-Wischer)
const ACCORDION_GESTURE_GAP = 500; // ms ohne Mausrad-Signal = Scrollweg beginnt wieder bei 0
const ACCORDION_HOVER_PAUSE = 500; // ms nach einem Mausrad-Schritt, in denen Hover ignoriert wird
// ▲

document.querySelectorAll('[data-accordion]').forEach((accordion) => {
    const items = [...accordion.querySelectorAll('[data-accordion-item]')];
    const DEFAULT_INDEX = 0; // Ausgangszustand: erstes Projekt offen (zeigt, wie das Akkordeon funktioniert)
    let activeIndex = -1;
    let lastWheel = 0; // Zeitpunkt des letzten Mausrad-Signals
    let lastStep = 0; // Zeitpunkt des letzten Mausrad-Schritts
    let scrolled = 0; // gesammelter Scrollweg seit dem letzten Schritt (positiv = nach unten)
    let gestureCaptured = null; // gehört die laufende Geste dem Akkordeon? (null = noch nicht entschieden)
    let videoTimer = null;

    // Dauer der Aufklapp-Animation aus dem CSS (--accordion-duration), z. B. "900ms" → 900
    const animationDuration = () => parseFloat(getComputedStyle(document.documentElement).getPropertyValue('--accordion-duration')) || 0;

    // Projekt aufklappen. Das Titelvideo startet erst nach der Animation:
    // Laden und Abspielen kosten Rechenleistung und würden sonst den Anfang der Animation stocken lassen.
    const activate = (index) => {
        if (index === activeIndex) {
            return;
        }
        activeIndex = index;
        clearTimeout(videoTimer);

        items.forEach((item, i) => {
            item.classList.toggle('is-active', i === index);
            item.querySelector('video')?.pause();
        });

        const video = items[index]?.querySelector('video');
        if (video && !reducedMotion && !compactLayout.matches) {
            videoTimer = setTimeout(() => playVideo(video), animationDuration());
        }
    };

    // Unterkante des klebenden Headers im Fenster
    const headerBottom = () => document.querySelector('[data-layout="header"]')?.getBoundingClientRect().bottom ?? 0;

    // Höhe eines Streifens und eines offenen Projekts messen. Nur beim Laden und bei geänderter
    // Fenstergrösse – nie während einer Animation, sonst springt die gemessene Karte.
    let sizes = { strip: 0, open: 0, gap: 0 };
    const measure = () => {
        // eine geschlossene Karte als Messobjekt (nicht die offene, die gerade animiert sein könnte)
        const probe = items.find((_, i) => i !== activeIndex) ?? items[0];
        const wasActive = probe.classList.contains('is-active');
        probe.style.transition = 'none';
        probe.classList.remove('is-active');
        const strip = probe.offsetHeight;
        probe.classList.add('is-active');
        const open = probe.offsetHeight;
        probe.classList.toggle('is-active', wasActive); // ursprünglichen Zustand wiederherstellen
        probe.offsetHeight; // Layout anwenden, bevor die Animation wieder eingeschaltet wird
        probe.style.removeProperty('transition');
        const gap = parseFloat(getComputedStyle(accordion).rowGap) || 0;
        sizes = { strip, open, gap };
    };

    // Seite sanft nachrücken, damit das offene Projekt ganz sichtbar ist
    // (Position aus den Endhöhen berechnet, weil die Animation noch läuft)
    const keepVisible = (index) => {
        if (index < 0) {
            return;
        }
        const { strip, open, gap } = sizes;
        const listTop = accordion.getBoundingClientRect().top + window.scrollY;
        const cardTop = listTop + index * (strip + gap);
        const viewTop = headerBottom() + 24;
        const viewBottom = window.innerHeight - 24;

        // Ein Streifen davor bleibt sichtbar (Orientierung), ausser beim ersten Projekt
        let target = cardTop - viewTop - (index > 0 ? strip + gap : 0);
        // Passt das offene Projekt dann nicht ganz, nach unten ausrichten
        if (cardTop + open - target > viewBottom) {
            target = cardTop + open - viewBottom;
        }
        window.scrollTo({ top: target, behavior: reducedMotion ? 'auto' : 'smooth' });
    };

    // Ist das Akkordeon so weit oben, dass das Aufklappen sichtbar ist?
    const inZone = () => {
        const rect = accordion.getBoundingClientRect();
        return rect.top <= headerBottom() + window.innerHeight * 0.35 && rect.bottom > headerBottom() + 120;
    };

    // Mausrad/Trackpad: Scrollweg sammeln; ab ACCORDION_SCROLL_DISTANCE das nächste Projekt öffnen
    accordion.addEventListener('wheel', (event) => {
        // Tablet/Handy: alle offen, Seite scrollt normal
        if (compactLayout.matches || Math.abs(event.deltaY) < Math.abs(event.deltaX)) {
            return; // seitliches Wischen ignorieren
        }
        const now = performance.now();

        // Pause zwischen den Signalen: neue Geste, Scrollweg zurücksetzen
        if (now - lastWheel > ACCORDION_GESTURE_GAP) {
            scrolled = 0;
            gestureCaptured = null;
        }
        lastWheel = now;

        // In Pixel umrechnen (Firefox meldet beim Mausrad teilweise Zeilen statt Pixel)
        const delta = event.deltaMode === 1 ? event.deltaY * 16 : event.deltaY;
        const direction = Math.sign(delta);
        if (!direction) {
            return;
        }
        const next = activeIndex + direction;
        const canStep = next >= 0 && next <= items.length - 1; // immer ein Projekt offen

        // Zu Beginn einer Geste entscheiden, ob das Akkordeon sie übernimmt
        if (gestureCaptured === null) {
            gestureCaptured = inZone() && canStep;
        }
        // Kein weiteres Projekt in dieser Richtung: Seite wieder normal scrollen lassen
        if (!gestureCaptured || !canStep) {
            gestureCaptured = false;
            return;
        }

        event.preventDefault(); // Seite nicht scrollen, solange das Akkordeon die Geste übernimmt

        // Richtung gewechselt: Scrollweg neu zählen
        if (Math.sign(scrolled) !== direction) {
            scrolled = 0;
        }
        scrolled += delta;

        // Genug gescrollt und genug Zeit seit dem letzten Schritt: nächstes Projekt
        if (Math.abs(scrolled) >= ACCORDION_SCROLL_DISTANCE && now - lastStep >= ACCORDION_STEP_PAUSE) {
            activate(next);
            keepVisible(next);
            lastStep = now;
            scrolled = 0;
        }
    }, { passive: false });

    items.forEach((item, index) => {
        // Hover klappt auf, ausser kurz nach einem Mausrad-Schritt
        // (sonst würde das nachrückende Scrollen ein anderes Projekt unter die Maus schieben)
        item.addEventListener('mouseenter', () => {
            if (!compactLayout.matches && performance.now() - lastStep > ACCORDION_HOVER_PAUSE) {
                activate(index);
            }
        });
        // Tastatur (Tab)
        item.addEventListener('focusin', () => {
            if (!compactLayout.matches) {
                activate(index);
            }
        });

        // Desktop ohne Maus (z. B. Touch-Laptop): erstes Tippen klappt auf, zweites öffnet das Projekt.
        // Tablet/Handy: alle offen, der Link öffnet sofort.
        item.querySelector('a')?.addEventListener('click', (event) => {
            if (!compactLayout.matches && !item.classList.contains('is-active')) {
                event.preventDefault();
                activate(index);
            }
        });
    });

    // Maus oder Fokus verlässt die Liste: das zuletzt geöffnete Projekt bleibt offen
    // (sonst würde es nach dem Scrollen beim kleinsten Mauswackeln auf Projekt 1 zurückspringen)

    // Bereich ganz aus dem Bild gescrollt: zurück zum Ausgangszustand (erstes Projekt offen)
    new IntersectionObserver((entries) => {
        if (!entries[0].isIntersecting) {
            activate(DEFAULT_INDEX);
        }
    }).observe(accordion);

    // Höhen bei geänderter Fensterbreite neu messen (verzögert)
    let resizeTimer = null;
    let lastWidth = window.innerWidth;
    window.addEventListener('resize', () => {
        if (window.innerWidth === lastWidth) {
            return;
        }
        lastWidth = window.innerWidth;
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(measure, 200);
    });

    // Start: erstes Projekt ist schon im HTML offen → nur den Zustand übernehmen, dann messen
    activate(DEFAULT_INDEX);
    measure();
});

// =====================================================================
// -> Logo-Laufschrift (Marquee): läuft endlos von rechts nach links
// =====================================================================
const MARQUEE_SPEED = 50; // Pixel pro Sekunde, unabhängig von der Anzahl Logos

// Kopie eines Logos: für Screenreader und Tab-Taste unsichtbar
const cloneLogo = (item) => {
    const clone = item.cloneNode(true);
    clone.dataset.clone = '';
    clone.setAttribute('aria-hidden', 'true');
    clone.querySelectorAll('a').forEach((link) => link.setAttribute('tabindex', '-1'));
    return clone;
};

if (!reducedMotion) {
    document.querySelectorAll('[data-marquee]').forEach((marquee) => {
        const track = marquee.querySelector('[data-marquee-track]');
        const originals = [...track.children];
        if (!originals.length) {
            return;
        }

        const build = () => {
            // Zurücksetzen (z. B. nach Änderung der Fensterbreite)
            track.querySelectorAll('[data-clone]').forEach((clone) => clone.remove());
            marquee.classList.add('is-running'); // einzeilig, damit die Breite messbar ist

            // 1. Logos wiederholen, bis eine Reihe breiter ist als der sichtbare Bereich
            let rounds = 0;
            while (track.offsetWidth < marquee.clientWidth && rounds < 50) {
                originals.forEach((item) => track.append(cloneLogo(item)));
                rounds++;
            }

            // 2. Ganze Reihe verdoppeln: Die Animation verschiebt um genau eine Hälfte (-50 %),
            //    danach sieht es gleich aus wie am Anfang → nahtlose Endlosschleife
            [...track.children].forEach((item) => track.append(cloneLogo(item)));

            // 3. Dauer aus der Strecke berechnen → immer gleiche Geschwindigkeit
            const distance = track.offsetWidth / 2;
            track.style.setProperty('--marquee-duration', `${distance / MARQUEE_SPEED}s`);
        };

        build();

        // Bei geänderter Breite neu aufbauen (verzögert, damit es nicht bei jedem Pixel läuft)
        let resizeTimer = null;
        let lastWidth = marquee.clientWidth;
        new ResizeObserver(() => {
            if (marquee.clientWidth === lastWidth) {
                return;
            }
            lastWidth = marquee.clientWidth;
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(build, 200);
        }).observe(marquee);
    });
}

// =====================================================================
// -> Cursor-Punkt: folgt dem Mauszeiger weich verzögert
// Nur mit Maus/Trackpad und ohne «Bewegung reduzieren». Der normale Mauszeiger bleibt sichtbar.
// =====================================================================
if (canHover && !reducedMotion) {
    const dot = document.createElement('div');
    dot.className = 'cursor-dot';
    dot.setAttribute('aria-hidden', 'true');
    document.body.append(dot);

    // Elemente, über denen der Punkt grösser wird
    const INTERACTIVE = 'a, button, [role="button"], input, textarea, select, label';
    const EASING = 0.18; // Anteil der Distanz pro Frame: kleiner = mehr Verzögerung

    let targetX = 0;
    let targetY = 0;
    let currentX = 0;
    let currentY = 0;
    let frame = null;

    const animate = () => {
        currentX += (targetX - currentX) * EASING;
        currentY += (targetY - currentY) * EASING;
        dot.style.transform = `translate3d(${currentX}px, ${currentY}px, 0)`;

        // Animation anhalten, wenn der Punkt angekommen ist (spart Leistung)
        if (Math.abs(targetX - currentX) < 0.1 && Math.abs(targetY - currentY) < 0.1) {
            frame = null;
            return;
        }
        frame = requestAnimationFrame(animate);
    };

    document.addEventListener('mousemove', (event) => {
        targetX = event.clientX;
        targetY = event.clientY;

        // Erste Bewegung: Punkt direkt an die Maus setzen und einblenden
        if (!dot.classList.contains('is-visible')) {
            currentX = targetX;
            currentY = targetY;
            dot.classList.add('is-visible');
        }

        // Grösser über Links und Buttons
        dot.classList.toggle('is-hover', Boolean(event.target.closest?.(INTERACTIVE)));

        if (!frame) {
            frame = requestAnimationFrame(animate);
        }
    });

    // Maus verlässt das Fenster: Punkt ausblenden
    document.documentElement.addEventListener('mouseleave', () => {
        dot.classList.remove('is-visible');
    });
}

// =====================================================================
// -> Galerie auf der Projektseite: horizontales Akkordeon
// Desktop: Streifen gleich breit, Hover/Fokus öffnet ein Bild; das Video darin spielt.
// Touch, Tablet und Handy: Bilder zum Wischen (CSS), Videos spielen, sobald sie sichtbar sind.
// =====================================================================
document.querySelectorAll('[data-strip]').forEach((strip) => {
    const items = strip.querySelectorAll('[data-strip-item]');

    if (!canHover || compactLayout.matches) {
        if (reducedMotion) {
            return;
        }
        // Touch: Video im sichtbaren Bild abspielen, sonst pausieren
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                const video = entry.target.querySelector('video');
                if (!video) {
                    return;
                }
                if (entry.isIntersecting) {
                    playVideo(video);
                } else {
                    video.pause();
                }
            });
        }, { threshold: 0.6 });
        items.forEach((item) => observer.observe(item));
        return;
    }

    const activate = (activeItem) => {
        items.forEach((item) => {
            const isActive = item === activeItem;
            item.classList.toggle('is-active', isActive);

            const video = item.querySelector('video');
            if (!video) {
                return;
            }
            if (isActive && !reducedMotion) {
                playVideo(video);
            } else {
                video.pause();
            }
        });
    };

    items.forEach((item) => {
        item.addEventListener('mouseenter', () => activate(item));
        item.addEventListener('focusin', () => activate(item));
    });

    // Maus verlässt die Galerie: alle Streifen wieder gleich breit
    strip.addEventListener('mouseleave', () => activate(null));
    strip.addEventListener('focusout', (event) => {
        if (!strip.contains(event.relatedTarget)) {
            activate(null);
        }
    });
});

// =====================================================================
// -> Navigation: Overlay-Menü und transparenter Header
// Markup: frontend/views/layout/header.php, Stil: frontend/styles/_navigation.css
// =====================================================================
const siteHeader = document.querySelector('[data-layout="header"]');
const menuToggle = document.querySelector('[data-menu-toggle]');
const menu = document.querySelector('[data-menu]');

// -> Text-Scramble: Buchstaben wirbeln kurz zufällig durcheinander und setzen sich
// von links nach rechts zum richtigen Wort zusammen.
// ▼ Zum Ausprobieren anpassen
const SCRAMBLE_DURATION = 1000; // ms bis das ganze Wort steht
const SCRAMBLE_SPEED = 70; // ms pro Zeichenwechsel (kleiner = hektischer)
// ▲
const SCRAMBLE_UPPER = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
const SCRAMBLE_LOWER = 'abcdefghijklmnopqrstuvwxyz';
const scrambleFrames = new WeakMap(); // laufende Animation pro Element (zum Abbrechen)

const scrambleText = (element) => {
    if (reducedMotion) {
        return;
    }
    // Originaltext beim ersten Mal merken (auch wenn gerade eine Animation läuft)
    const original = element.dataset.text ?? element.textContent.trim();
    element.dataset.text = original;
    cancelAnimationFrame(scrambleFrames.get(element));

    // Pro Zeichen ein Zeitpunkt, ab dem es feststeht: von links nach rechts, leicht zufällig versetzt
    const chars = [...original];
    const settleAt = chars.map((_, i) => (i / chars.length) * SCRAMBLE_DURATION * 0.7 + Math.random() * SCRAMBLE_DURATION * 0.3);
    const start = performance.now();
    let lastSwap = 0;

    // Zufallszeichen in gleicher Gross-/Kleinschreibung (wirkt ruhiger); Leerzeichen bleiben
    const randomChar = (char) => {
        if (char === ' ') {
            return ' ';
        }
        const set = char === char.toUpperCase() ? SCRAMBLE_UPPER : SCRAMBLE_LOWER;
        return set[Math.floor(Math.random() * set.length)];
    };

    const step = (now) => {
        const elapsed = now - start;
        // Zeichen nur alle SCRAMBLE_SPEED ms wechseln, sonst flimmert es zu stark
        if (now - lastSwap >= SCRAMBLE_SPEED) {
            lastSwap = now;
            element.textContent = chars.map((char, i) => (elapsed >= settleAt[i] ? char : randomChar(char))).join('');
        }
        if (elapsed < SCRAMBLE_DURATION) {
            scrambleFrames.set(element, requestAnimationFrame(step));
        } else {
            element.textContent = original;
        }
    };
    scrambleFrames.set(element, requestAnimationFrame(step));
};

if (siteHeader && menuToggle && menu) {
    const links = [...menu.querySelectorAll('[data-menu-target]')];
    const backgrounds = [...menu.querySelectorAll('[data-menu-bg]')];
    // Bereiche hinter dem Overlay: werden beim Öffnen «inert» (Fokus bleibt im Menü)
    const behind = document.querySelectorAll('main, [data-layout="footer"]');
    // Startbild: aktuelle Seite bzw. erster Menüpunkt (im HTML schon aktiv)
    const startId = backgrounds.find((bg) => bg.classList.contains('is-active'))?.dataset.menuBg ?? null;
    let isOpen = false;
    let imagesLoaded = false;
    let closeTimer = null;
    // ms: so lange fahren die Menüpunkte hinaus, bevor der Vorhang schliesst (CSS: .is-closing)
    const MENU_CLOSE_DELAY = reducedMotion ? 0 : 350;
    // Dauer des Vorhangs aus dem CSS (--menu-duration), z. B. "700ms" → 700
    const menuDuration = () => parseFloat(getComputedStyle(document.documentElement).getPropertyValue('--menu-duration')) || 0;

    // Bilder erst beim ersten Öffnen laden: data-src → src (spart Daten, wenn niemand das Menü öffnet)
    const loadImages = () => {
        if (imagesLoaded) {
            return;
        }
        imagesLoaded = true;
        menu.querySelectorAll('img[data-src]').forEach((img) => {
            img.src = img.dataset.src;
            img.removeAttribute('data-src');
        });
    };

    // Hintergrund wechseln (Crossfade per CSS). Nur das Video des aktiven Hintergrunds läuft,
    // und nur solange das Menü offen ist.
    // Gemeinsamer Hintergrund aus den Globals: bleibt immer aktiv, wechselt beim Hover nicht
    const fixedBackground = menu.hasAttribute('data-menu-fixed');

    const showBackground = (id) => {
        backgrounds.forEach((bg) => {
            const active = fixedBackground || bg.dataset.menuBg === id;
            bg.classList.toggle('is-active', active);
            const video = bg.querySelector('video[data-video="manual"]');
            if (!video) {
                return;
            }
            if (active && isOpen && !reducedMotion) {
                playVideo(video);
            } else {
                video.pause();
            }
        });
    };

    // instant: ohne Animation schliessen (z. B. «Zurück» im Browser)
    const setMenu = (open, { returnFocus = true, instant = false } = {}) => {
        if (open === isOpen) {
            return;
        }
        isOpen = open;
        if (open) {
            loadImages();
        }

        // aria-expanded steuert auch die Beschriftung «Menü»/«Schliessen» und das X (CSS)
        menuToggle.setAttribute('aria-expanded', String(open));
        // inert: geschlossenes Menü ist für Tab und Screenreader weg; offen ist der Rest der Seite weg
        menu.inert = !open;
        behind.forEach((element) => {
            element.inert = open;
        });

        clearTimeout(closeTimer);
        if (open) {
            menu.classList.remove('is-closing');
            menu.classList.add('is-open');
            // Body-Scroll sperren (CSS: body.menu-open { overflow: hidden })
            document.body.classList.add('menu-open');
        } else if (instant) {
            menu.classList.remove('is-open', 'is-closing');
            document.body.classList.remove('menu-open');
        } else {
            // Schliessen in zwei Phasen: 1. Menüpunkte fahren hinaus, 2. Vorhang schliesst
            menu.classList.add('is-closing');
            closeTimer = setTimeout(() => {
                menu.classList.remove('is-open', 'is-closing');
                // 3. Erst wenn der Vorhang ganz zu ist: Header wieder dunkel mit Linie, Scrollen wieder frei
                //    (sonst blitzt die Header-Linie über dem noch sichtbaren Overlay auf)
                closeTimer = setTimeout(() => {
                    document.body.classList.remove('menu-open');
                }, reducedMotion ? 0 : menuDuration());
            }, MENU_CLOSE_DELAY);
        }

        if (open) {
            // Beim Öffnen immer mit dem Startbild beginnen
            showBackground(startId);
        } else {
            // Beim Schliessen bleibt das Bild stehen (kein Wechsel während der Animation), Videos pausieren
            menu.querySelectorAll('video[data-video="manual"]').forEach((video) => video.pause());
        }

        if (open) {
            // Fokus ins Menü (Tastatur, Screenreader). preventScroll: sonst verschiebt der Browser
            // die noch eingefahrenen Menüpunkte innerhalb ihrer Zeile.
            links[0]?.focus({ preventScroll: true });
        } else if (returnFocus) {
            // Fokus zurück auf den Button, damit man auf der Seite nicht «verloren» ist
            menuToggle.focus({ preventScroll: true });
        }
    };

    menuToggle.addEventListener('click', () => setMenu(!isOpen));

    // Esc schliesst das Menü
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && isOpen) {
            setMenu(false);
        }
    });

    // Klick auf E-Mail oder Socials (neuer Tab / Mailprogramm) schliesst das Menü.
    // Menüpunkte nicht: Das Menü bleibt offen, und die neue Seite blendet darüber ein
    // (View Transition, siehe main.css). Sonst sähe man kurz die alte Seite unter dem Vorhang.
    menu.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => {
            if (link.matches('[data-menu-target]') && link.target !== '_blank') {
                return;
            }
            setMenu(false, { returnFocus: false });
        });
    });

    // Hover und Tastatur-Fokus: Hintergrund des Menüpunkts einblenden, Wort «scrambeln»
    links.forEach((link) => {
        const word = link.querySelector('[data-scramble]');
        const activate = () => {
            showBackground(link.dataset.menuTarget);
            if (word) {
                scrambleText(word);
            }
        };
        if (canHover) {
            link.addEventListener('mouseenter', activate);
        }
        // Nur bei Tastatur-Fokus (:focus-visible). Beim Öffnen per Maus setzt das Script den Fokus
        // auf den ersten Punkt; der soll weder scrambeln noch den Hintergrund wechseln.
        link.addEventListener('focus', () => {
            if (link.matches(':focus-visible')) {
                activate();
            }
        });
    });

    // «Zurück» im Browser zeigt die Seite aus dem Cache (mit noch offenem Menü):
    // sofort schliessen, ohne Animation. .no-transitions schaltet die CSS-Übergänge kurz aus.
    window.addEventListener('pageshow', (event) => {
        if (!event.persisted || !isOpen) {
            return;
        }
        document.documentElement.classList.add('no-transitions');
        setMenu(false, { returnFocus: false, instant: true });
        // zwei Frames warten: erst dann hat der Browser den geschlossenen Zustand gezeichnet
        requestAnimationFrame(() => requestAnimationFrame(() => {
            document.documentElement.classList.remove('no-transitions');
        }));
    });
}


// =====================================================================
// -> Einblenden beim Scrollen: Inhalte weiter unten blenden weich ein, sobald sie ins Bild kommen
// Was beim Laden schon sichtbar ist, bleibt einfach stehen (kein Aufblitzen).
// Stil: .reveal / .is-revealed in main.css
// =====================================================================
const REVEAL_SELECTOR = [
    '.module-title',
    '[data-module="projects"] .card',
    '[data-module="projects"] .filter',
    '[data-module="projects"] tbody tr',
    '[data-module="gallery-photos"] .filter',
    '[data-module="gallery-photos"] .photo',
    '[data-module="logos"] .logo-marquee',
    '[data-module="profile"] .profile-aside',
    '[data-module="profile"] .profile-main',
    '[data-module="experience"] .experience-head',
    '[data-module="experience"] .station',
    '[data-module="contact"] .contact-list > li',
    '[data-module="text"] .rich-text',
    '[data-page="project"] .project-intro',
    '[data-page="project"] .project-description',
    '[data-page="project"] .project-role',
    '[data-page="project"] .project-video',
    '[data-page="project"] .project-gallery',
].join(', ');
const REVEAL_STAGGER = 80; // ms Versatz, wenn mehrere Elemente gleichzeitig erscheinen

if (!reducedMotion) {
    const revealObserver = new IntersectionObserver((entries) => {
        let index = 0;
        entries.forEach((entry) => {
            if (!entry.isIntersecting) {
                return;
            }
            // Gleichzeitig sichtbare Elemente nacheinander einblenden (z. B. Fotos im Raster)
            entry.target.style.animationDelay = `${index * REVEAL_STAGGER}ms`;
            entry.target.classList.add('is-revealed');
            revealObserver.unobserve(entry.target); // nur einmal einblenden
            index++;
        });
    }, { rootMargin: '0px 0px -8% 0px' }); // erst, wenn das Element etwas im Bild ist

    document.querySelectorAll(REVEAL_SELECTOR).forEach((element) => {
        // Beim Laden schon sichtbar (oder ausgeblendet durch einen Filter): nicht animieren
        if (element.getBoundingClientRect().top < window.innerHeight) {
            return;
        }
        element.classList.add('reveal');
        revealObserver.observe(element);
    });
}

// =====================================================================
// -> Header: durchsichtig über dem Hero, danach Milchglas
// Stil: _navigation.css, Abschnitt 3
// =====================================================================
if (siteHeader) {
    // -> Milchglas (.is-solid): über dem Hero durchsichtig, danach mit Hintergrund
    // Beginnt die Seite mit einem Bild-Hero? (Modul «Hero Bild» oder Projektseite)
    const hero = document.querySelector('main > [data-module="hero-image"]:first-child, main[data-page="project"] > .project-hero');

    if (!hero) {
        // Kein Hero: Milchglas von Anfang an (ganz oben liegt nur der dunkle Seitenhintergrund dahinter)
        siteHeader.classList.add('is-solid');
    } else {
        // IntersectionObserver meldet, ob der Hero noch unter dem Header sichtbar ist.
        // rootMargin oben = Unterkante des Headers (inkl. Admin-Leiste): Der Bereich hinter dem Header zählt nicht.
        // +1 px: Liegt der Hero genau an der Header-Kante (nach Klick auf den Scroll-Pfeil), gilt er als «weg».
        const headerBottom = Math.ceil(siteHeader.getBoundingClientRect().bottom) + 1;
        new IntersectionObserver(([entry]) => {
            siteHeader.classList.toggle('is-solid', !entry.isIntersecting);
        }, { rootMargin: `-${headerBottom}px 0px 0px 0px` }).observe(hero);
    }
}

// =====================================================================
// -> Bilder weich einblenden, sobald sie geladen sind (statt «aufzuploppen»)
// Nur Bilder mit loading="lazy" (laden beim Scrollen nach). Hero-Bilder sind sofort da und bleiben aussen vor.
// Stil: .img-fade / .is-loaded in main.css
// =====================================================================
if (!reducedMotion) {
    document.querySelectorAll('main img[loading="lazy"]').forEach((img) => {
        // Schon geladen (z. B. aus dem Cache): nichts tun.
        // Logo-Laufschrift: Die Logos werden kopiert; Kopien hätten keinen Listener und blieben unsichtbar.
        if (img.complete || img.closest('[data-marquee]')) {
            return;
        }
        img.classList.add('img-fade');
        const show = () => img.classList.add('is-loaded');
        img.addEventListener('load', show, { once: true });
        img.addEventListener('error', show, { once: true }); // Fehler: trotzdem zeigen (Alt-Text)
    });
}
