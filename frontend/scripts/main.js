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
// Projektliste und «Weiterführende Informationen» (beide Listen, Bereich = [data-preview-area]).
// Über einer aufgeklappten Zeile der Projektliste (.is-open) ist sie ausgeblendet, sonst wäre das Bild doppelt.
if (canHover) {
    document.querySelectorAll('[data-directory]').forEach((directory) => {
        const preview = directory.querySelector('[data-directory-preview]');
        const body = directory.querySelector('[data-preview-area]');
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

        // Vorschau zur Zeile unter der Maus zeigen. Nur bei einem Wechsel etwas tun (mousemove feuert oft).
        let shownId = null;
        const showRow = (row) => {
            const id = row && !row.classList.contains('is-open') ? row.dataset.previewTarget || null : null;
            if (id === shownId) {
                return;
            }
            shownId = id;
            showItem(id);
            // Nur sichtbar, wenn es zu dieser Zeile ein Vorschaubild gibt (sonst bliebe das vorherige stehen)
            preview.classList.toggle('is-visible', [...items].some((item) => item.dataset.previewId === id));
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
            showRow(event.target.closest('[data-preview-target]'));
        });

        // Nach dem Auf-/Zuklappen neu prüfen (die Maus steht dann still, mousemove kommt nicht)
        body.addEventListener('click', (event) => {
            showRow(event.target.closest('[data-preview-target]'));
        });

        body.addEventListener('mouseleave', () => {
            showRow(null);
            // Animation stoppen, wenn die Maus die Liste verlässt (spart Leistung)
            cancelAnimationFrame(frame);
            frame = null;
        });
    });
}

// -> Handy/Tablet: Projekt in der Bildschirmmitte hervorheben (Ersatz für Hover)
// Gilt für die Projektliste (Directory) und die Highlights auf der Startseite.
// Beim Scrollen wird das Element gesucht, dessen Mitte am nächsten an der Bildschirmmitte liegt.
// Gerechnet statt IntersectionObserver: So ist auch das letzte Element erreichbar, obwohl es
// am Seitenende nie ganz in die Mitte kommt. requestAnimationFrame: höchstens einmal pro Bild rechnen.
// Klassen für das CSS: .is-scroll-highlight am Container, .is-current am hervorgehobenen Element.
function initScrollHighlight(container, items) {
    let ticking = false;

    const update = () => {
        ticking = false;
        const active = !canHover || compactLayout.matches;
        container.classList.toggle('is-scroll-highlight', active);
        if (!active) {
            items.forEach((item) => item.classList.remove('is-current'));
            return;
        }
        const center = window.innerHeight / 2;
        let closest = null;
        let closestDistance = Infinity;
        items.forEach((item) => {
            if (item.hidden) {
                return; // vom Filter ausgeblendet
            }
            const rect = item.getBoundingClientRect();
            // nur Elemente, die gerade im Fenster sind
            if (rect.bottom < 0 || rect.top > window.innerHeight) {
                return;
            }
            const distance = Math.abs(rect.top + rect.height / 2 - center);
            if (distance < closestDistance) {
                closest = item;
                closestDistance = distance;
            }
        });
        items.forEach((item) => item.classList.toggle('is-current', item === closest));
    };

    const requestUpdate = () => {
        if (!ticking) {
            ticking = true;
            requestAnimationFrame(update);
        }
    };

    window.addEventListener('scroll', requestUpdate, { passive: true });
    window.addEventListener('resize', requestUpdate);
    requestUpdate();
    return requestUpdate;
}

// Projektliste (Seite Projekte)
document.querySelectorAll('[data-directory-list]').forEach((list) => {
    const requestUpdate = initScrollHighlight(list, [...list.querySelectorAll('.directory-item')]);
    // Nach dem Filtern ändern sich die sichtbaren Zeilen. Der Filter tauscht sie in einem
    // Seitenübergang (View Transition) aus, der etwas später läuft → kurz warten, dann neu rechnen.
    list.closest('[data-directory]').addEventListener('click', (event) => {
        if (event.target.closest('[data-filter]')) {
            setTimeout(requestUpdate, 100);
        }
    });
    // Nach dem Auf-/Zuklappen einer Vorschau verschieben sich die Zeilen darunter → neu rechnen
    list.addEventListener('transitionend', requestUpdate);
});

// =====================================================================
// -> Projektliste: Klick auf eine Zeile klappt die Vorschau darunter auf (Markup: modules/projects.php)
// 1. Jede Zeile ist ein <button> mit aria-expanded (offen ja/nein) und aria-controls (ID der Vorschau).
// 2. Es ist immer nur eine Zeile offen; ein zweiter Klick auf dieselbe Zeile schliesst sie.
// 3. Geschlossene Vorschau ist inert: nicht per Tab und nicht für Screenreader erreichbar.
// 4. Das Titelvideo lädt und spielt nur, solange die Zeile offen ist.
// Die Animation macht das CSS (grid-template-rows 0fr → 1fr, .is-open am <li>).
// Ohne JavaScript bleiben alle Vorschauen offen (CSS: @media (scripting: none)).
// =====================================================================
document.querySelectorAll('[data-directory-list]').forEach((list) => {
    const items = [...list.querySelectorAll('.directory-item')];

    const setOpen = (item, open) => {
        const button = item.querySelector('.row-toggle');
        const panel = document.getElementById(button.getAttribute('aria-controls'));
        if (!panel) {
            return;
        }
        button.setAttribute('aria-expanded', String(open));
        item.classList.toggle('is-open', open);
        panel.inert = !open;

        const video = panel.querySelector('video');
        if (!video) {
            return;
        }
        if (open && !reducedMotion) {
            playVideo(video); // play() lädt das Video erst jetzt (preload="none")
        } else {
            video.pause();
        }
    };

    items.forEach((item) => {
        setOpen(item, false); // Startzustand: alles zu (und inert)
        item.querySelector('.row-toggle').addEventListener('click', () => {
            const open = !item.classList.contains('is-open');
            // Andere offene Zeile zuerst schliessen
            items.forEach((other) => {
                if (other !== item && other.classList.contains('is-open')) {
                    setOpen(other, false);
                }
            });
            setOpen(item, open);
        });
    });
});

// Highlights (Startseite): auf Handy/Tablet sind alle Projekte offen
document.querySelectorAll('[data-accordion]').forEach((accordion) => {
    initScrollHighlight(accordion, [...accordion.querySelectorAll('[data-accordion-item]')]);
});

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
const ACCORDION_MOMENTUM_GAP = 150; // ms ohne Signal = Nachlauf des Trackpads ist vorbei
const ACCORDION_HOVER_INTENT = 60; // ms, die die Maus auf einem Projekt ruhen muss, bis es aufklappt (Überfahren ignorieren)
// ▲

// -> Kurve aus dem CSS (cubic-bezier) in JavaScript nachbauen
// Damit das Scrollen exakt gleich beschleunigt und abbremst wie das Aufklappen (--accordion-ease).
// Eine Bézier-Kurve liefert zu einem Zeitpunkt x den Fortschritt y; x → t wird schrittweise angenähert (Bisektion).
function cubicBezier(x1, y1, x2, y2) {
    const point = (t, p1, p2) => 3 * (1 - t) ** 2 * t * p1 + 3 * (1 - t) * t ** 2 * p2 + t ** 3;
    return (x) => {
        let low = 0;
        let high = 1;
        let t = x;
        for (let i = 0; i < 20; i++) {
            t = (low + high) / 2;
            if (point(t, x1, x2) < x) {
                low = t;
            } else {
                high = t;
            }
        }
        return point(t, y1, y2);
    };
}

// Kurve aus einer CSS-Variable lesen, z. B. "cubic-bezier(.2, .65, .25, 1)"
function easingFromCss(name) {
    const value = getComputedStyle(document.documentElement).getPropertyValue(name);
    const numbers = value.match(/-?[\d.]+/g)?.map(Number);
    return numbers?.length === 4 ? cubicBezier(...numbers) : (x) => x;
}

// -> Seite selbst scrollen (statt behavior: 'smooth'), mit eigener Dauer und Kurve.
// requestAnimationFrame: Der Browser ruft die Funktion vor jedem neuen Bild auf (ca. 60× pro Sekunde).
let scrollFrame = null;
function stopScrollAnimation() {
    cancelAnimationFrame(scrollFrame);
}
function animateScrollTo(target, duration, ease) {
    stopScrollAnimation();
    const start = window.scrollY;
    const maxScroll = document.documentElement.scrollHeight - window.innerHeight;
    const end = Math.min(Math.max(target, 0), maxScroll);
    // behavior 'instant': Das CSS hat scroll-behavior: smooth, das würde jeden Einzelschritt verzögern
    if (reducedMotion || duration <= 0) {
        window.scrollTo({ top: end, behavior: 'instant' });
        return;
    }
    const startTime = performance.now();
    const frame = (now) => {
        const progress = Math.min((now - startTime) / duration, 1);
        window.scrollTo({ top: start + (end - start) * ease(progress), behavior: 'instant' });
        if (progress < 1) {
            scrollFrame = requestAnimationFrame(frame);
        }
    };
    scrollFrame = requestAnimationFrame(frame);
}

// -> Zähler «01 / 05» mit Fortschrittslinie (Highlights und Galerie)
// index -1 = nichts offen (Galerie): Zähler blendet aus (.is-idle), Zahl und Linie bleiben stehen,
// damit beim Ausblenden nichts springt
function updateProgress(progress, index, total) {
    if (!progress) {
        return;
    }
    progress.classList.toggle('is-idle', index < 0);
    if (index < 0) {
        return;
    }
    progress.querySelector('[data-progress-current]').textContent = String(index + 1).padStart(2, '0');
    progress.querySelector('[data-progress-fill]').style.setProperty('--progress', (index + 1) / total);
}

// -> Nachlauf (Momentum) des Trackpads erkennen
// Nach einem Wischer sendet das Trackpad noch 1–2 Sekunden lang immer schwächere Scroll-Signale.
// Nach einem Schritt wird darum gesperrt (lock), bis die Signale kurz aufhören oder ein neuer Wischer
// beginnt (Signal wird plötzlich wieder deutlich stärker oder die Richtung wechselt).
// So klappt ein Wischer genau ein Projekt bzw. ein Bild auf.
function createMomentumLock() {
    let locked = false;
    let lastAbs = 0;
    let lastTime = 0;
    let lastDirection = 0;

    return {
        lock() {
            locked = true;
        },
        // true = Signal gehört zum Nachlauf des letzten Schritts → ignorieren
        isMomentum(delta, now) {
            const abs = Math.abs(delta);
            const direction = Math.sign(delta);
            if (locked) {
                const quiet = now - lastTime > ACCORDION_MOMENTUM_GAP;
                const newSwipe = direction !== lastDirection || (abs >= 8 && abs > lastAbs * 2);
                if (quiet || newSwipe) {
                    locked = false;
                }
            }
            lastAbs = abs;
            lastTime = now;
            lastDirection = direction;
            return locked;
        },
    };
}

document.querySelectorAll('[data-accordion]').forEach((accordion) => {
    const items = [...accordion.querySelectorAll('[data-accordion-item]')];
    const DEFAULT_INDEX = 0; // Ausgangszustand: erstes Projekt offen (zeigt, wie das Akkordeon funktioniert)
    let activeIndex = -1;
    let lastWheel = 0; // Zeitpunkt des letzten Mausrad-Signals
    let lastStep = 0; // Zeitpunkt des letzten Mausrad-Schritts
    let scrolled = 0; // gesammelter Scrollweg seit dem letzten Schritt (positiv = nach unten)
    let gestureCaptured = null; // gehört die laufende Geste dem Akkordeon? (null = noch nicht entschieden)
    const momentum = createMomentumLock();
    let videoTimer = null;

    // Dauer der Aufklapp-Animation aus dem CSS (--accordion-duration), z. B. "900ms" → 900
    const animationDuration = () => parseFloat(getComputedStyle(document.documentElement).getPropertyValue('--accordion-duration')) || 0;

    // Projekt aufklappen. Das Titelvideo startet erst nach der Animation:
    // Laden und Abspielen kosten Rechenleistung und würden sonst den Anfang der Animation stocken lassen.
    // Zähler «01 / 05» und Fortschrittslinie (Markup in modules/projects.php)
    const progress = accordion.parentElement.querySelector('[data-accordion-progress]');

    const activate = (index) => {
        if (index === activeIndex) {
            return;
        }
        activeIndex = index;
        updateProgress(progress, index, items.length);
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

    // Seite sanft mitscrollen, bis das offene Projekt in der Mitte des sichtbaren Bereichs
    // (zwischen Header und unterem Fensterrand) steht.
    // Position aus den Endhöhen berechnet, weil die Aufklapp-Animation noch läuft.
    const centerItem = (index) => {
        if (index < 0) {
            return;
        }
        const { strip, open, gap } = sizes;
        const listTop = accordion.getBoundingClientRect().top + window.scrollY;
        // Alle Projekte davor sind zu, also Streifen: Oberkante = Index × (Streifen + Abstand)
        const cardTop = listTop + index * (strip + gap);
        const viewTop = headerBottom();
        const viewHeight = window.innerHeight - viewTop;

        // Mitte der Karte auf die Mitte des sichtbaren Bereichs.
        // Ist die Karte höher als der Bereich, oben ausrichten (Titel bleibt sichtbar).
        const target = open < viewHeight
            ? cardTop + open / 2 - (viewTop + viewHeight / 2)
            : cardTop - viewTop;
        // Gleiche Dauer und Kurve wie das Aufklappen (CSS): Scrollen und Aufklappen laufen gleichzeitig,
        // das Projekt wächst so in der Mitte auf, statt aufzuklappen und dann nachzurutschen
        animateScrollTo(target, animationDuration(), easingFromCss('--accordion-ease'));
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
        // Nachlauf nach einem Schritt: schlucken (auch die Seite scrollt nicht), kein weiteres Projekt
        if (momentum.isMomentum(delta, now)) {
            event.preventDefault();
            return;
        }
        const next = activeIndex + direction;
        const canStep = next >= 0 && next <= items.length - 1; // immer ein Projekt offen

        // Zu Beginn einer Geste entscheiden, ob das Akkordeon sie übernimmt:
        // Maus ist über den Highlights (sonst käme kein wheel-Event) und es gibt in dieser Richtung
        // noch ein Projekt → Akkordeon blättert, die Seite bleibt stehen
        if (gestureCaptured === null) {
            gestureCaptured = canStep;
        }
        // Kein weiteres Projekt in dieser Richtung: Seite wieder normal scrollen lassen
        // (laufendes Zentrieren abbrechen, sonst kämpfen die beiden Scrollbewegungen gegeneinander)
        if (!gestureCaptured || !canStep) {
            gestureCaptured = false;
            stopScrollAnimation();
            return;
        }

        event.preventDefault(); // Seite nicht scrollen, solange das Akkordeon die Geste übernimmt

        // Richtung gewechselt: Scrollweg neu zählen
        if (Math.sign(scrolled) !== direction) {
            scrolled = 0;
        }
        scrolled += delta;

        // Genug gescrollt und genug Zeit seit dem letzten Schritt: nächstes Projekt,
        // die Seite scrollt mit, bis es in der Mitte steht
        if (Math.abs(scrolled) >= ACCORDION_SCROLL_DISTANCE && now - lastStep >= ACCORDION_STEP_PAUSE) {
            activate(next);
            centerAndHold(next);
            momentum.lock();
            // Melden: Besucher hat das Blättern verstanden (Cursor-Label wechselt zu «Ansehen»)
            accordion.dispatchEvent(new CustomEvent('accordion:step'));
            lastStep = now;
            scrolled = 0;
        }
    }, { passive: false });

    // Letzte Mausposition: Beim Mitscrollen schiebt sich ein anderes Projekt unter die stillstehende Maus.
    // Der Browser meldet das als Mausbewegung, die Position im Fenster bleibt aber gleich → nicht aufklappen.
    let lastPointer = { x: -1, y: -1 };

    // Nach dem automatischen Zentrieren liegt ein anderes Projekt unter der Maus. Damit es nicht beim
    // kleinsten Wackeln aufklappt (und die Seite wieder scrollt = «Nachjagen»), pausiert der Hover,
    // bis die Maus das offene Projekt erreicht oder die Liste verlässt.
    let hoverHold = false;
    let centerTimer = null;

    const centerAndHold = (index) => {
        clearTimeout(centerTimer);
        centerItem(index);
        hoverHold = true;
    };

    items.forEach((item, index) => {
        // Hover klappt auf: nur bei echter Mausbewegung und nicht kurz nach einem Mausrad-Schritt
        // (mousemove statt mouseenter, weil mouseenter auch beim Scrollen unter der Maus auslöst)
        item.addEventListener('mousemove', (event) => {
            const moved = event.clientX !== lastPointer.x || event.clientY !== lastPointer.y;
            lastPointer = { x: event.clientX, y: event.clientY };
            if (!moved || compactLayout.matches) {
                return;
            }
            // Hover pausiert: erst wieder frei, wenn die Maus auf dem offenen Projekt ist
            if (hoverHold) {
                hoverHold = index !== activeIndex;
                return;
            }
            // Schon offen: nichts tun (sonst würde jedes Wackeln die Seite neu ausrichten)
            if (index === activeIndex) {
                clearTimeout(centerTimer);
                return;
            }
            if (performance.now() - lastStep > ACCORDION_HOVER_PAUSE) {
                // Aufklappen und Zentrieren gemeinsam, sobald die Maus kurz ruht
                // (jede Bewegung startet die Wartezeit neu). So bewegt das blosse Überfahren die Seite nicht.
                clearTimeout(centerTimer);
                centerTimer = setTimeout(() => {
                    activate(index);
                    centerAndHold(index);
                }, ACCORDION_HOVER_INTENT);
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

    // -> Pfeiltasten: ↓ nächstes Projekt, ↑ vorheriges, wenn die Maus über den Highlights ist
    // oder der Fokus darin liegt. Am Anfang/Ende scrollt die Seite normal.
    let pointerOver = false;
    accordion.addEventListener('mouseenter', () => { pointerOver = true; });
    accordion.addEventListener('mouseleave', () => {
        pointerOver = false;
        // Maus verlässt die Liste: nicht mehr zentrieren, Hover wieder frei
        clearTimeout(centerTimer);
        hoverHold = false;
    });

    document.addEventListener('keydown', (event) => {
        if (compactLayout.matches || !(pointerOver || accordion.contains(document.activeElement))) {
            return;
        }
        const direction = { ArrowDown: 1, ArrowUp: -1 }[event.key];
        if (!direction) {
            return;
        }
        // Maus über den Highlights: Pfeiltasten scrollen die Seite nie (auch nicht am Anfang/Ende)
        if (pointerOver) {
            event.preventDefault();
        }
        const next = activeIndex + direction;
        if (next < 0 || next > items.length - 1) {
            return;
        }
        event.preventDefault();
        activate(next);
        // Wie beim Mausrad: offenes Projekt in die Mitte scrollen
        centerAndHold(next);
        accordion.dispatchEvent(new CustomEvent('accordion:step'));
        lastStep = performance.now();
        // Tastatur: Fokus auf den Link des offenen Projekts (Enter öffnet es)
        if (accordion.contains(document.activeElement)) {
            items[next].querySelector('a')?.focus({ preventScroll: true });
        }
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
    // inkl. Elemente, die per Tab erreichbar sind (tabindex="0"), z. B. die Bilder der Galerie:
    // keine Links, aber interaktiv (Hover/Fokus klappt auf)
    const INTERACTIVE = 'a, button, [role="button"], input, textarea, select, label, [tabindex="0"]';
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
// -> Cursor-Label über den Highlights (modules/projects.php) und der Galerie (pages/project.php)
// Ersetzt dort den Cursor-Punkt: Pille sitzt mittig auf dem Mauszeiger und folgt ihm weich.
// Nur mit Maus und auf dem Desktop (Tablet/Handy: Akkordeon ist offen bzw. Galerie zum Wischen).
// =====================================================================
document.querySelectorAll('[data-cursor-label]').forEach((label) => {
    const area = label.parentElement.querySelector('[data-accordion], [data-strip]');
    if (!canHover || !area) {
        label.remove();
        return;
    }
    // In den <body> verschieben: position: fixed bezieht sich sonst evtl. auf ein Elternelement
    // mit transform/translate (z. B. Einblenden beim Scrollen) statt auf das Fenster
    document.body.append(label);

    // Anleitung «Scrollen ↓» nur, bis zum ersten Mal geblättert wurde. Danach «Ansehen» (Highlights)
    // oder ganz ausblenden, wenn es keinen zweiten Text gibt (Galerie: Klick öffnet nichts).
    // sessionStorage merkt sich das für diesen Besuch (Tab), getrennt pro Bereich (data-cursor-label).
    // try/catch: Speicher kann blockiert sein (z. B. privates Fenster).
    const LEARNED_KEY = `${label.dataset.cursorLabel || 'label'}-learned`;
    const hasViewText = Boolean(label.querySelector('.cursor-label-text--view'));
    let retired = false; // true = Label wird nicht mehr gezeigt

    const applyLearned = () => {
        label.classList.add('is-learned');
        if (!hasViewText) {
            retired = true;
            setVisible(false);
        }
    };
    const learn = () => {
        applyLearned();
        try {
            sessionStorage.setItem(LEARNED_KEY, '1');
        } catch (error) {
            // ohne Speicher: gilt nur bis zum Neuladen
        }
    };
    area.addEventListener('accordion:step', learn);

    const EASING = reducedMotion ? 1 : 0.25; // Anteil der Distanz pro Frame (1 = ohne Verzögerung)
    let targetX = 0;
    let targetY = 0;
    let currentX = 0;
    let currentY = 0;
    let frame = null;
    let visible = false;

    // translate(-50%, -50%): Mitte der Pille auf die Mausspitze
    const place = () => {
        label.style.transform = `translate3d(${currentX}px, ${currentY}px, 0) translate(-50%, -50%)`;
    };

    const animate = () => {
        currentX += (targetX - currentX) * EASING;
        currentY += (targetY - currentY) * EASING;
        place();
        if (Math.abs(targetX - currentX) < 0.1 && Math.abs(targetY - currentY) < 0.1) {
            frame = null;
            return;
        }
        frame = requestAnimationFrame(animate);
    };

    function setVisible(state) {
        visible = state;
        label.classList.toggle('is-visible', state);
        // Cursor-Punkt solange ausblenden (CSS)
        document.body.classList.toggle('has-cursor-label', state);
    }

    // Schon in diesem Besuch gelernt? (erst hier, weil applyLearned setVisible braucht)
    try {
        if (sessionStorage.getItem(LEARNED_KEY)) {
            applyLearned();
        }
    } catch (error) {
        // ohne Speicher: Anleitung wird angezeigt
    }

    area.addEventListener('mousemove', (event) => {
        if (compactLayout.matches || retired) {
            return;
        }
        targetX = event.clientX;
        targetY = event.clientY;
        // Beim Hineinfahren: zuerst sofort an die Maus setzen (noch unsichtbar), erst dann einblenden.
        // So erscheint die Pille an Ort und Stelle, statt vom letzten Ort bzw. der Ecke herzufliegen.
        if (!visible) {
            cancelAnimationFrame(frame);
            frame = null;
            currentX = targetX;
            currentY = targetY;
            place();
            setVisible(true);
        }
        if (!frame) {
            frame = requestAnimationFrame(animate);
        }
    });

    area.addEventListener('mouseleave', () => setVisible(false));
    // Seite scrollt die Highlights unter der stillstehenden Maus weg (z. B. Zentrieren): mouseleave kommt
    // dann nicht zuverlässig → beim Scrollen prüfen, ob die Maus noch über den Highlights ist
    window.addEventListener('scroll', () => {
        if (visible && !area.matches(':hover')) {
            setVisible(false);
        }
    }, { passive: true });
});

// =====================================================================
// -> Galerie auf der Projektseite: horizontales Akkordeon
// Desktop: Streifen gleich breit, Hover/Fokus öffnet ein Bild; das Video darin spielt.
// Touch, Tablet und Handy: Bilder zum Wischen (CSS), Videos spielen, sobald sie sichtbar sind.
// =====================================================================
document.querySelectorAll('[data-strip]').forEach((strip) => {
    const items = strip.querySelectorAll('[data-strip-item]');

    if (!canHover || compactLayout.matches) {
        // Anleitung «← Wischen →»: blendet nach dem ersten Wischen aus und bleibt für diesen Besuch weg
        // (sessionStorage, wie das Cursor-Label am Desktop). try/catch: Speicher kann blockiert sein.
        const hint = strip.parentElement.querySelector('[data-strip-hint]');
        if (hint) {
            const HINT_KEY = 'gallery-swipe-learned';
            let learned = false;
            try {
                learned = Boolean(sessionStorage.getItem(HINT_KEY));
            } catch (error) {
                // ohne Speicher: Anleitung wird angezeigt
            }
            if (learned) {
                hint.classList.add('is-learned');
            } else {
                // Gewischt = Galerie ist seitlich gescrollt (10 px Toleranz gegen Zittern)
                const onScroll = () => {
                    if (strip.scrollLeft < 10) {
                        return;
                    }
                    hint.classList.add('is-learned');
                    strip.removeEventListener('scroll', onScroll);
                    try {
                        sessionStorage.setItem(HINT_KEY, '1');
                    } catch (error) {
                        // ohne Speicher: gilt nur bis zum Neuladen
                    }
                };
                strip.addEventListener('scroll', onScroll, { passive: true });
            }
        }

        if (reducedMotion) {
            return;
        }
        // Punkte unter der Galerie: zeigen, dass man wischen kann und wo man ist; antippen springt zum Bild
        const dots = [];
        if (items.length > 1) {
            const nav = document.createElement('div');
            nav.className = 'strip-dots';
            nav.setAttribute('role', 'group');
            nav.setAttribute('aria-label', 'Bilder der Galerie');
            items.forEach((item, index) => {
                const dot = document.createElement('button');
                dot.type = 'button';
                dot.setAttribute('aria-label', `Bild ${index + 1} von ${items.length}`);
                dot.setAttribute('aria-current', String(index === 0));
                dot.addEventListener('click', () => {
                    item.scrollIntoView({
                        behavior: reducedMotion ? 'auto' : 'smooth',
                        block: 'nearest',
                        inline: 'start', // wie scroll-snap-align: Bild am linken Rand, nächstes schaut rechts herein
                    });
                });
                nav.append(dot);
                dots.push(dot);
            });
            strip.after(nav);
        }

        // Sichtbares Bild: Punkt aktiv, Video abspielen; andere pausieren
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                const index = [...items].indexOf(entry.target);
                if (entry.isIntersecting) {
                    dots.forEach((dot, i) => dot.setAttribute('aria-current', String(i === index)));
                }
                const video = entry.target.querySelector('video');
                if (!video || reducedMotion) {
                    return;
                }
                if (entry.isIntersecting) {
                    playVideo(video);
                } else {
                    video.pause();
                }
            });
        }, { threshold: 0.6 }); // Bezug: Fenster (horizontal und vertikal sichtbar)
        items.forEach((item) => observer.observe(item));
        return;
    }

    const itemList = [...items];
    let activeIndex = -1; // -1 = kein Bild offen (alle Streifen gleich breit)
    const progress = strip.parentElement.querySelector('[data-accordion-progress]'); // Zähler (project.php)

    const activate = (activeItem) => {
        activeIndex = itemList.indexOf(activeItem);
        updateProgress(progress, activeIndex, items.length);
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

    // -> Mausrad/Trackpad: horizontal durch die Bilder blättern (gleiches Prinzip wie die Highlights)
    // Scrollweg wird gesammelt; nach ACCORDION_SCROLL_DISTANCE öffnet sich das nächste bzw. vorherige Bild.
    // Am ersten/letzten Bild scrollt die Seite normal weiter. Konstanten: siehe Highlights-Akkordeon.
    let lastWheel = 0;
    let lastStep = 0;
    let scrolled = 0;
    const momentum = createMomentumLock(); // Trackpad-Nachlauf (siehe Highlights)

    // Galerie in die Mitte des sichtbaren Bereichs (zwischen Header und unterem Fensterrand) scrollen.
    // Gleiche Dauer und Kurve wie das Aufklappen der Bilder (--accordion-duration, --accordion-ease),
    // damit Scrollen und Aufklappen eine Bewegung sind. Die Galerie ist horizontal: Senkrechtes Scrollen
    // ändert nicht, welches Bild unter der Maus liegt.
    const centerStrip = () => {
        const rect = strip.getBoundingClientRect();
        const header = document.querySelector('[data-layout="header"]');
        const top = header ? header.getBoundingClientRect().bottom : 0;
        const offset = rect.top + rect.height / 2 - (top + (window.innerHeight - top) / 2);
        if (Math.abs(offset) < 4) {
            return; // steht schon in der Mitte
        }
        const duration = parseFloat(getComputedStyle(document.documentElement).getPropertyValue('--accordion-duration')) || 0;
        animateScrollTo(window.scrollY + offset, duration, easingFromCss('--accordion-ease'));
    };

    // Ein Bild weiter (+1) oder zurück (-1) und Galerie zentrieren; false am Anfang/Ende (dann scrollt die Seite normal)
    const step = (direction) => {
        const next = activeIndex + direction;
        if (next < 0 || next > items.length - 1) {
            return false;
        }
        activate(items[next]);
        centerStrip();
        lastStep = performance.now();
        return true;
    };

    // Regel: Ist die Maus über der Galerie (Rechteck der Streifen, inkl. Lücken dazwischen), wird geblättert,
    // immer, solange es in dieser Richtung noch ein Bild gibt. Ausserhalb scrollt die Seite normal.
    // Am ersten/letzten Bild scrollt die Seite weiter (sonst käme man ohne Maus-Umweg nie an der Galerie vorbei).
    //
    // Warum auf window statt auf der Galerie: Browser schicken während einer Scroll-Geste alle Signale an
    // das Element, auf dem die Geste begonnen hat («Scroll-Latching»). Beginnt man kurz neben der Galerie
    // zu scrollen, käme bei ihr nichts an. Darum entscheidet die aktuelle Mausposition (clientX/clientY).
    let lastCaptured = 0; // Zeitpunkt des letzten Signals, das die Galerie übernommen hat

    window.addEventListener('wheel', (event) => {
        if (compactLayout.matches || Math.abs(event.deltaY) < Math.abs(event.deltaX)) {
            return; // Tablet/Handy (Wischen) und seitliches Wischen auf dem Trackpad: nicht abfangen
        }
        const now = performance.now();
        const rect = strip.getBoundingClientRect();
        const inside = event.clientX >= rect.left && event.clientX <= rect.right
            && event.clientY >= rect.top && event.clientY <= rect.bottom;

        // Ausserhalb: Seite scrollt normal. Ausnahme: Nachlauf einer Galerie-Geste, die gerade erst
        // die Galerie verlassen hat (Signale ohne Pause) → schlucken, bis kurz Ruhe ist.
        if (!inside) {
            if (now - lastCaptured <= ACCORDION_MOMENTUM_GAP) {
                event.preventDefault();
                lastCaptured = now;
            }
            return;
        }

        if (now - lastWheel > ACCORDION_GESTURE_GAP) {
            scrolled = 0; // neue Geste: Scrollweg beginnt bei 0
        }
        lastWheel = now;

        const delta = event.deltaMode === 1 ? event.deltaY * 16 : event.deltaY;
        const direction = Math.sign(delta);
        if (!direction) {
            return;
        }
        // Nachlauf des Trackpads nach einem Schritt ignorieren (siehe createMomentumLock)
        if (momentum.isMomentum(delta, now)) {
            event.preventDefault();
            lastCaptured = now;
            return;
        }
        const next = activeIndex + direction;
        if (next < 0 || next > items.length - 1) {
            stopScrollAnimation(); // Ende erreicht: laufendes Zentrieren abbrechen, Seite scrollt weiter
            return;
        }

        event.preventDefault(); // Seite nicht scrollen, solange die Galerie blättert
        lastCaptured = now;

        if (Math.sign(scrolled) !== direction) {
            scrolled = 0;
        }
        scrolled += delta;

        if (Math.abs(scrolled) >= ACCORDION_SCROLL_DISTANCE && now - lastStep >= ACCORDION_STEP_PAUSE) {
            step(direction);
            momentum.lock();
            scrolled = 0;
            // Melden: Blättern verstanden (Cursor-Label blendet aus)
            strip.dispatchEvent(new CustomEvent('accordion:step'));
        }
    }, { passive: false });

    // -> Pfeiltasten: ↓/→ nächstes Bild, ↑/← vorheriges, wenn die Maus über der Galerie ist
    // oder der Fokus darin liegt. Am Anfang/Ende scrollt die Seite normal.
    let pointerOver = false;
    strip.addEventListener('mouseenter', () => { pointerOver = true; });
    strip.addEventListener('mouseleave', () => { pointerOver = false; });

    document.addEventListener('keydown', (event) => {
        if (compactLayout.matches || !(pointerOver || strip.contains(document.activeElement))) {
            return;
        }
        const keys = { ArrowDown: 1, ArrowRight: 1, ArrowUp: -1, ArrowLeft: -1 };
        const direction = keys[event.key];
        if (!direction) {
            return;
        }
        // Maus über der Galerie: Pfeiltasten scrollen die Seite nie, und die Seite rückt nicht nach
        if (pointerOver) {
            event.preventDefault();
        }
        if (step(direction)) {
            event.preventDefault();
            strip.dispatchEvent(new CustomEvent('accordion:step'));
            // Tastatur: Fokus wandert mit, damit Screenreader das offene Bild kennen
            if (strip.contains(document.activeElement)) {
                items[activeIndex].focus({ preventScroll: true });
            }
        }
    });

    items.forEach((item) => {
        item.addEventListener('focusin', () => {
            activate(item);
            centerStrip();
        });
    });

    // -> Hover öffnet das Bild, aber nur, wenn die Maus sich wirklich bewegt hat.
    // Scrollt man die Seite, schiebt sich die Galerie unter die stillstehende Maus; der Browser meldet
    // dann ebenfalls Maus-Ereignisse (mit gleicher Position). Ohne diese Prüfung würde die Galerie
    // aufklappen und sich in die Mitte scrollen, gegen die Scrollrichtung (ruckelt). Gleich wie bei den Highlights.
    // Ausserdem nicht kurz nach einem Mausrad-Schritt (die Streifen verschieben sich beim Blättern unter der Maus).
    // Position auf der ganzen Seite merken: Auch das erste Ereignis beim Hineinscrollen wird so erkannt.
    // Wie bei den Highlights: Aufklappen erst, wenn die Maus kurz auf einem Bild ruht (ACCORDION_HOVER_INTENT).
    // Jede Bewegung startet die Wartezeit neu. So gehen beim Überfahren nicht alle Streifen nacheinander auf.
    let lastPointer = { x: -1, y: -1 };
    let hoverTimer = null;
    document.addEventListener('mousemove', (event) => {
        const moved = event.clientX !== lastPointer.x || event.clientY !== lastPointer.y;
        lastPointer = { x: event.clientX, y: event.clientY };
        if (!moved || !strip.contains(event.target) || compactLayout.matches || performance.now() - lastStep <= ACCORDION_HOVER_PAUSE) {
            return;
        }
        clearTimeout(hoverTimer);
        const item = event.target.closest('[data-strip-item]');
        // In der Lücke zwischen zwei Bildern oder schon offen: nichts tun
        if (!item || itemList.indexOf(item) === activeIndex) {
            return;
        }
        hoverTimer = setTimeout(() => {
            activate(item);
            centerStrip();
        }, ACCORDION_HOVER_INTENT);
    });

    // Maus verlässt die Galerie: alle Streifen wieder gleich breit
    strip.addEventListener('mouseleave', (event) => {
        if (event.clientX === lastPointer.x && event.clientY === lastPointer.y) {
            return;
        }
        clearTimeout(hoverTimer);
        // Maus wirklich weg: laufendes Zentrieren abbrechen, sonst scrollt die Seite danach noch weiter
        stopScrollAnimation();
        activate(null);
    });
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
    '[data-module="projects"] .directory-item',
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
    '[data-page="project"] .project-links .project-subtitle',
    '[data-page="project"] .link-list > li',
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
            // Nach dem Einblenden aufräumen: Die Animation hält sonst opacity: 1 fest (fill-mode both)
            // und überstimmt andere Regeln, z. B. das Dimmen der Projektliste beim Scrollen auf dem Handy
            entry.target.addEventListener('animationend', (event) => {
                if (event.animationName !== 'reveal') {
                    return; // Animation eines Kindelements (z. B. Pfeil) ignorieren
                }
                entry.target.classList.remove('reveal', 'is-revealed');
                entry.target.style.removeProperty('animation-delay');
            });
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
// -> Zeitlinie (Über mich, Modul «Erfahrung», Markup: modules/experience.php)
// 1. Messlinie bei 70 % der Fensterhöhe: So weit ist die Linie von oben grün gefüllt.
// 2. --progress (0–1) = Anteil der Linie über der Messlinie; das CSS macht daraus scaleY().
// 3. Station erreicht (Punkt auf oder über der Messlinie) → .is-reached: Punkt wird grün und grösser.
// requestAnimationFrame + passiver Scroll-Listener: höchstens einmal pro Bild rechnen.
// Bei «Bewegung reduzieren» (oder ohne JS) passiert nichts: Linie und Punkte sind ganz grün (CSS-Standard).
// =====================================================================
const TIMELINE_MARK = 0.7; // Anteil der Fensterhöhe: Messlinie

if (!reducedMotion) {
    document.querySelectorAll('[data-timeline]').forEach((timeline) => {
        const stations = [...timeline.querySelectorAll('.station')];
        let ticking = false;

        // Ab jetzt animiert: Punkte grau, bis sie erreicht sind (CSS)
        timeline.classList.add('is-animated');

        const update = () => {
            ticking = false;
            const mark = window.innerHeight * TIMELINE_MARK;
            // Linie beginnt auf Höhe des ersten Punkts (--timeline-top im CSS) und endet unten an der Liste
            const lineOffset = parseFloat(getComputedStyle(timeline).getPropertyValue('--timeline-top')) || 0;
            const rect = timeline.getBoundingClientRect();
            const lineTop = rect.top + lineOffset;
            const lineHeight = rect.height - lineOffset;
            const progress = Math.min(Math.max((mark - lineTop) / lineHeight, 0), 1);
            timeline.style.setProperty('--progress', progress.toFixed(4));

            stations.forEach((station) => {
                const dotY = station.getBoundingClientRect().top + lineOffset;
                station.classList.toggle('is-reached', dotY <= mark);
            });
        };

        const requestUpdate = () => {
            if (!ticking) {
                ticking = true;
                requestAnimationFrame(update);
            }
        };

        window.addEventListener('scroll', requestUpdate, { passive: true });
        window.addEventListener('resize', requestUpdate);
        requestUpdate();
    });
}

// =====================================================================
// -> Intro: Text färbt sich beim Scrollen Wort für Wort ein (Markup: modules/intro.php)
// 1. Jedes Wort bekommt ein <span class="word"> (Text bleibt für Screenreader normal lesbar).
// 2. Beim Scrollen wird der Fortschritt berechnet: 0 = Text taucht unten auf (Oberkante bei 85 %
//    der Fensterhöhe), 1 = Text ist durch (Unterkante bei 40 %).
// 3. Daraus bekommt jedes Wort --lit (0–1). Mehrere Wörter blenden gleichzeitig weich über
//    (INTRO_SOFTNESS), statt hart eins nach dem anderen umzuspringen.
// Bei «Bewegung reduzieren» passiert nichts: Der Text ist dann einfach weiss.
// =====================================================================
const INTRO_START = 0.65; // Anteil der Fensterhöhe: hier beginnt das Einfärben (Oberkante des Texts)
const INTRO_END = 0.3; // hier ist alles eingefärbt (Unterkante des Texts)
const INTRO_SOFTNESS = 4; // über wie viele Wörter der Übergang verläuft (mehr = weicher)

if (!reducedMotion) {
    document.querySelectorAll('[data-scroll-text]').forEach((text) => {
        // -> 1. Wörter in <span> verpacken. TreeWalker geht durch alle Textstücke, auch die im <em>.
        const walker = document.createTreeWalker(text, NodeFilter.SHOW_TEXT);
        const textNodes = [];
        while (walker.nextNode()) {
            textNodes.push(walker.currentNode);
        }
        textNodes.forEach((node) => {
            const fragment = document.createDocumentFragment();
            // split mit Klammern behält die Leerzeichen als eigene Teile
            node.textContent.split(/(\s+)/).forEach((part) => {
                if (part === '') {
                    return;
                }
                if (/^\s+$/.test(part)) {
                    fragment.append(part);
                    return;
                }
                const word = document.createElement('span');
                word.className = 'word';
                word.textContent = part;
                fragment.append(word);
            });
            node.replaceWith(fragment);
        });

        const words = [...text.querySelectorAll('.word')];
        // Markierungen (**Wort**): werden mit ihren Wörtern von links nach rechts «gezogen».
        // Indizes der Wörter in der Markierung merken, damit beim Scrollen nicht gesucht werden muss.
        const marks = [...text.querySelectorAll('mark')].map((mark) => ({
            mark,
            indexes: [...mark.querySelectorAll('.word')].map((word) => words.indexOf(word)),
        }));
        let ticking = false;

        // -> 2. und 3. Fortschritt berechnen und auf die Wörter verteilen
        const update = () => {
            ticking = false;
            const rect = text.getBoundingClientRect();
            const height = window.innerHeight;
            const distance = rect.height + height * (INTRO_START - INTRO_END);
            const progress = Math.min(Math.max((height * INTRO_START - rect.top) / distance, 0), 1);
            const position = progress * (words.length + INTRO_SOFTNESS);
            const litValues = words.map((word, index) => {
                const lit = Math.min(Math.max((position - index) / INTRO_SOFTNESS, 0), 1);
                word.style.setProperty('--lit', lit.toFixed(3));
                return lit;
            });
            // Markierung: Breite = Durchschnitt ihrer Wörter (0 = noch nichts markiert, 1 = ganz)
            marks.forEach(({ mark, indexes }) => {
                if (!indexes.length) {
                    return;
                }
                const sum = indexes.reduce((total, index) => total + litValues[index], 0);
                mark.style.setProperty('--lit', (sum / indexes.length).toFixed(3));
            });
        };

        const requestUpdate = () => {
            if (!ticking) {
                ticking = true;
                requestAnimationFrame(update);
            }
        };

        window.addEventListener('scroll', requestUpdate, { passive: true });
        window.addEventListener('resize', requestUpdate);
        requestUpdate();
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
