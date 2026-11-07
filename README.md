# Portfolio Max Hutmacher – WordPress-Theme
> Eigenes WordPress-Theme für mein Portfolio als Multimedia Producer ([maxhutmacher.ch](https://maxhutmacher.ch)). Entstanden im Modul «Interaktive Medien 5» (FHGR, HS26) auf Basis des IM5-Starter-Themes.

## Kurzbeschreibung
Das Portfolio präsentiert meine Projekte aus Video, Animation und Fotografie. Alle Inhalte lassen sich im WordPress-Backend selbst verwalten, ohne den Code anzupassen:

- **Projekte** (Custom Post Type) mit Titelbild oder Titelvideo, Steckbrief, Projektbeschrieb, Rollenbeschrieb, Galerie (Bilder und Videos) und eingebettetem YouTube- oder Vimeo-Video.
- **Fotos** (Custom Post Type) als filterbares Raster.
- **Modulbaukasten** für alle Seiten: Hero mit Bild oder Video, Projekte als Karussell oder Directory, Fotogalerie, Logos, Profil, Erfahrung, Kontakt, Text.
- **Directory** mit Filter («Ausgewählte Projekte» als Standard, «Alle», Kategorien) und einer Hover-Vorschau, die dem Mauszeiger folgt.
- **Globale Einstellungen** für Logo, Copyright, E-Mail und Socials.

Das Inhaltsmodell ist im [CMS-Konzept](docs/cms-konzept.md) festgehalten.

## Screenshots
Links die Website, rechts die Pflege der Inhalte im WordPress-Backend.

### Startseite
Hero Bild → Projekte (Karussell «Highlights») → Logos

| Website | Backend |
|---|---|
| <img src="docs/screenshots/03.1_startseite_website.jpg" alt="Startseite nach Figma: Hero, Highlights-Akkordeon und Logo-Laufschrift" width="400"> | <img src="docs/screenshots/02.6_startseite1-wordpress.jpg" alt="Backend: Modul Hero Bild" width="400"><br><img src="docs/screenshots/02.6_startseite2-wordpress.jpg" alt="Backend: Modul Projekte als Karussell" width="400"><br><img src="docs/screenshots/02.6_startseite3-wordpress.jpg" alt="Backend: Modul Logos" width="400"> |

### Projekte (Directory)
Hero Text → Projekte (Liste) mit Filter «Ausgewählte Projekte» und Hover-Vorschau

| Website | Backend |
|---|---|
| <img src="docs/screenshots/02.4_projektdirectory_website.jpg" alt="Projekt-Directory mit Filter und Hover-Vorschau" width="400"> | <img src="docs/screenshots/02.5_projektedirectory-wordpress.jpg" alt="Backend: Seite Projekte mit Modulen" width="400"> |

### Fotos
Fotogalerie mit Filter nach Fotokategorie; das Raster ergibt sich aus dem Bildformat

| Website | Backend |
|---|---|
| <img src="docs/screenshots/02.2_fotos_website.jpg" alt="Fotogalerie mit Filter" width="400"> | <img src="docs/screenshots/02.2_fotos-wordpress.jpg" alt="Backend: Seite Fotos mit Modul Fotogalerie" width="400"> |

### Über mich
Hero Bild → Profil → Erfahrung

| Website | Backend |
|---|---|
| <img src="docs/screenshots/02.7_ueber_mich_website.jpg" alt="Über mich: Hero, Profil mit Skills, Erfahrung mit Stationen" width="400"> | <img src="docs/screenshots/02.7_ueber_mich-wordpress.jpg" alt="Backend: Modul Erfahrung mit Stationen" width="400"> |

### Kontakt
Hero Text mit kursivem Akzent (`*Kontakt*`) → Kontakt (E-Mail und Socials aus den Globals)

| Website | Backend |
|---|---|
| <img src="docs/screenshots/02.4_kontakt_website.jpg" alt="Kontaktseite" width="400"> | <img src="docs/screenshots/02.4_kontakt-wordpress.jpg" alt="Backend: Seite Kontakt" width="400"> |

### Navigation (Overlay-Menü)
Vollbild-Menü mit Hintergrund (gemeinsam oder pro Menüpunkt); Hover: Wort kursiv mit Text-Scramble, die anderen Punkte treten zurück

| Website | Backend |
|---|---|
| <img src="docs/screenshots/05.1_navigation_website.jpg" alt="Offenes Overlay-Menü mit Hintergrundbild, Hover auf «Projekte»" width="400"> | <img src="docs/screenshots/05.2_navigation_wordpress.jpg" alt="Backend: Design > Menüs mit Button «Alle aufklappen», Hintergrundbild und -video nebeneinander" width="400"> |

### Backend: Struktur
| Admin-Menü | Seitenübersicht | Impressum (Modul Text) |
|---|---|---|
| <img src="docs/screenshots/02_neue-reiter-wordpress.jpg" alt="Admin-Menü mit Projekte, Fotos und Globales" width="120"> | <img src="docs/screenshots/02.1_neue-seiten-wordpress.jpg" alt="Alle Seiten im Backend" width="320"> | <img src="docs/screenshots/02.3_impressum-wordpress.jpg" alt="Backend: Impressum mit Modul Text" width="320"> |

### Vorher: Stand mit dem Starter-Theme
Zum Vergleich der Stand vor der Umsetzung des CMS-Konzepts (Beispielmodule des Starter-Themes).

| Website | Backend |
|---|---|
| <img src="docs/screenshots/01_landing-page.jpg" alt="Startseite mit Starter-Modulen" width="400"> | <img src="docs/screenshots/01_modules-wordpress.jpg" alt="Backend: Starter-Module" width="400"> |

## Technische Umsetzung

### Aufbau
| Ordner | Inhalt |
|---|---|
| `backend/cpt/` | Custom Post Types `project` und `photo` |
| `backend/tax/` | Taxonomien `project_category` und `photo_category`, Begriffe werden einmalig automatisch angelegt |
| `backend/carbonfields/` | Inhaltsfelder (Carbon Fields) für Module, Projekte, Fotos und Globals |
| `backend/getter/` | Getter-Funktionen: Die Templates erhalten ihre Daten nur über diese Funktionen |
| `backend/helpers/` | Hilfsfunktionen (Medien, Videos, Rich Text, Titel-Akzent, Taxonomien) |
| `backend/init/` | Theme-Setup, Laden von Styles und Scripts (`wp_enqueue_*`) |
| `backend/admin/` | Anpassungen im WordPress-Backend, z. B. Vorschaubild und QuickEdit in der Fotoliste, Vorschaubilder und «Alle aufklappen» unter Design > Menüs |
| `frontend/views/` | Templates für Layout, Seiten, Module und wiederverwendbare Teile (z. B. `teaser/video.php`) |
| `frontend/scripts/main.js` | Filter, Videosteuerung, Hover-Vorschau im Directory |
| `docs/` | CMS-Konzept und Screenshots |

### Eingesetzte Technologien
- **Eigenes WordPress-Theme** (PHP) mit Custom Post Types, Taxonomien und Template-Hierarchie (`single-project.php`)
- **Carbon Fields** für die Inhaltsfelder und den Modulbaukasten
- **WP_Query / get_posts** für Projekte und Fotos, inkl. manueller Reihenfolge über ein Association-Feld
- **oEmbed** (WordPress-API) für YouTube und Vimeo, zwischengespeichert als Transient (1 Woche)
- **Vanilla JavaScript** (ES-Modul): IntersectionObserver für Videos, `requestAnimationFrame` für die Hover-Vorschau
- **CSS** mit Nesting, Grid, Scroll-Snap (Karussell), `aspect-ratio`, `:has()` und `clip-path`
- **View Transitions API** (`@view-transition`) für weiche Seitenwechsel

### Sicherheit, Performance und Barrierefreiheit
- Jede Ausgabe wird escaped (`esc_html()`, `esc_attr()`, `esc_url()`, `wp_kses_post()`); Video-Embeds werden mit `wp_kses()` auf ein `<iframe>` beschränkt.
- Bilder und Iframes mit Lazy Loading, Hero-Bilder mit `fetchpriority="high"`, Breite/Höhe gegen Layout-Sprünge.
- Videos laden erst bei Bedarf (`preload="none"`) und laufen nur, solange sie sichtbar sind.
- YouTube wird über `youtube-nocookie.com` eingebunden.
- Schriften (Geist, Playfair Display) sind lokal eingebunden: keine Verbindung zu Google Fonts, WOFF2 als variable Fonts, `font-display: swap`, Preload der sofort sichtbaren Schriften.
- Mit «Bewegung reduzieren» (`prefers-reduced-motion`) und auf Touch-Geräten wird statt Hover-Videos das Titelbild gezeigt.
- Filter-Buttons mit `aria-pressed`, unsichtbare H1 für Screenreader, wo der Titel Teil des Bildes ist.

## Setup
1. WordPress installieren (bei mir: Hostpoint).
2. Plugin **Carbon Fields** installieren und aktivieren.
3. Dieses Repository in `wp-content/themes/` ablegen (bei mir per FTP mit der VS-Code-Extension SFTP) und das Theme unter **Design > Themes** aktivieren.
4. **Einstellungen > Permalinks** einmal speichern, damit die Projekt-URLs (`/projekt/name/`) funktionieren.
5. **Einstellungen > Lesen**: «Eine statische Seite» wählen und die Startseite festlegen.
6. Unter **Globales** Logo, Copyright, E-Mail und Socials erfassen.
7. Seiten mit Modulen aufbauen (Reihenfolge siehe [CMS-Konzept](docs/cms-konzept.md), Abschnitt «Module pro Seite»).
8. Unter **Design > Menüs** die Menüs «Hauptnavigation» und «Rechtliches» anlegen und zuweisen.

> Zugangsdaten (`wp-config.php`, FTP) sind nicht im Repository. `.vscode/` ist in der `.gitignore`.

## Entwicklungsverlauf
| Datum | Schritt |
|---|---|
| 03.11.2026 | CMS-Konzept erstellt (Post Types, Taxonomien, Module, Menus, Globals) |
| 03.11.2026 | Grundstruktur nach Konzept: Post Types, Taxonomien, Felder, Getter, 9 Module, Projekt-Detailseite, Filter |
| 03.11.2026 | Projekt: YouTube/Vimeo-Embed (`video_url`) und Filter «Ausgewählte Projekte» (`featured`) |
| 04.11.2026 | Videos an allen Stellen: Hero, Karussell-Hover, Directory-Vorschau, Galerie |
| 04.11.2026 | Inhalte aller Seiten erfasst, README mit Screenshots |
| 04.11.2026 | Bugfix Carbon Fields: `value` ist reserviert, Steckbrief-Feld heisst neu `detail`; dunkler Seitenhintergrund und Footer immer am unteren Rand |
| 04.11.2026 | Design nach Figma (Startseite): Design-Tokens, Header/Footer, Hero, Highlights als Akkordeon (Hover), Farbumkehr des Hero-Texts über Video (`mix-blend-mode: difference`), Logo-Laufschrift (Endlos-Marquee) |
| 04.11.2026 | Cursor-Punkt (folgt der Maus, invertiert, grösser über Links), Hero immer bildschirmfüllend (inkl. Admin-Leiste), Scroll-Pfeil berechnet die Zielposition exakt |
| 04.11.2026 | Design nach Figma (Projekte): Directory zweispaltig (Filter links, Tabelle rechts), ganze Zeile anklickbar, grüner Hover, Vorschau 16:9 abgerundet; Active State für Navigation und Filter (`aria-current`, `aria-pressed`); Hero-Text-Titel grün, auf Kontakt weiss (`:has()`) |
| 04.11.2026 | Design nach Figma (Fotos): Raster mit 3 Spalten und fester Zeilenhöhe (Container-Query-Einheiten), Kachelgrösse und Bildausschnitt pro Foto wählbar (`grid_size`, `focus`), `srcset`/`sizes`; Backend-Liste mit Vorschaubild, Spalten für Grösse/Ausschnitt und QuickEdit |
| 04.11.2026 | Design nach Figma (Über mich): Profil und Erfahrung im gemeinsamen Raster (4 : 8 Spalten), Stationen zweispaltig, Steckbrief mit rechtsbündigen Werten, grüner Button; Oberkanten von Titel und Name bündig (`text-box-trim` mit Fallback über `cap`/`lh`) |
| 04.11.2026 | Design nach Figma (Projektseite): Titel und Eckdaten sichtbar unter dem Hero (ausblendbar mit `hide_title`), Projektbeschrieb zweispaltig (CSS `columns`), grüne Fläche für Rolle und Team, Galerie als horizontales Akkordeon (öffnet bei Hover im Format 3:2, Handy: Wischen mit Scroll-Snap) |
| 04.11.2026 | Impressum und Datenschutz (Vorlagen nach Schweizer DSG) im Stil der Seiten: Titel grün, Text max. 780 px, Zwischentitel H3 und H4 (Geist Bold), Links und Listen im Fliesstext |
| 07.11.2026 | Highlights per Mausrad/Trackpad: Scrollweg wird gesammelt (300 px pro Projekt, Mindestpause zwischen Schritten), erstes Projekt beim Laden offen, Video startet nach der Animation, Messung nur beim Laden (kein Ruckeln), `contain: layout paint`; Steckbrief: Werte mit Komma stehen untereinander |
| 07.11.2026 | Google Fonts lokal eingebunden (Datenschutz): 6 WOFF2-Dateien in `frontend/assets/fonts/`, `@font-face` mit `unicode-range`, Preload für Geist und Playfair Italic |
| 07.11.2026 | Neue Navigation (CMS-Konzept v04, Prototyp in `docs/prototypes/`): Header mit «Kontakt» und Menü-Button (`aria-expanded`, Striche werden zum X), Vollbild-Overlay mit `clip-path`, Menüpunkte mit Text-Scramble, Einrücken und Abdunkeln der anderen Punkte (`:has()`), Hintergrund pro Menüpunkt oder gemeinsam (Bild/Video, Felder am Menüeintrag `nav_menu_item` und in Globals), Bilder laden erst beim ersten Öffnen; Fokus-Steuerung mit `inert`, Esc, Scroll-Lock mit `scrollbar-gutter`; Backend «Design > Menüs» mit Vorschaubildern und «Alle aufklappen» |
| 07.11.2026 | Weiche Übergänge: Seitenwechsel per View Transitions API (Menü bleibt beim Klick offen, neue Seite blendet darüber), Inhalte blenden beim Scrollen ein (IntersectionObserver, gestaffelt) |

## Learnings
<!-- In eigenen Worten ergänzen -->
- _Folgt._

## Schwierigkeiten
<!-- In eigenen Worten ergänzen -->
- _Folgt._

## Ressourcen
- IM5-Starter-Theme (Boilerplate, FHGR) und Anleitungen in [`manuals/`](manuals/)
- [WordPress Developer Resources](https://developer.wordpress.org/)
- [Carbon Fields Dokumentation](https://docs.carbonfields.net/)
- [MDN Web Docs](https://developer.mozilla.org/) (IntersectionObserver, `<video>`, CSS)
- [HandBrake](https://handbrake.fr/) zum Komprimieren der Videos
- Schriften [Geist](https://fonts.google.com/specimen/Geist) und [Playfair Display](https://fonts.google.com/specimen/Playfair+Display) von Google Fonts, Lizenz [SIL Open Font License 1.1](https://openfontlicense.org/)
- Referenz für das Directory: [houmahani.com/directory](https://houmahani.com/directory)
- KI-Unterstützung: Claude Code (Anthropic) als Coach und Pair-Programmer
