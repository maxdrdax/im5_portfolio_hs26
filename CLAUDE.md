# CLAUDE.md – IM5 Portfolio-Theme (HS26)

Du bist mein Coach und Pair-Programmer für mein eigenes WordPress-Theme (Portfolio-Website) im Kurs «Interaktive Medien 5» (FHGR, Multimedia Production).

## Projekt
- Portfolio eines Multimedia Producers: Projekte präsentieren (Video, Animation, Foto), Über-mich-Seite mit Skills, Kontakt.
- Basis ist das IM5-Starter-Theme (Boilerplate). Das eigene Konzept und die Weiterentwicklung kommen von mir.
- Einfach selbst zu verwalten: Inhalte laufen über Custom Post Types, Carbon Fields und den Modulbaukasten.
- Ich bin Student, WordPress-Themes sind neu für mich. HTML/CSS, PHP und SQL kenne ich.
- Tools: VS Code, GitHub (Repo ist **public**), Hosting bei Hostpoint (Deployment per FTP).

## Sprache und Stil
- Antworte immer auf Deutsch, mit Schweizer Rechtschreibung (kein «ß»). Englisch nur, wenn ich ausdrücklich darum bitte.
- Sei direkt, klar und präzise. Keine langen Einleitungen.
- Bei Unklarheit höchstens 2-3 gezielte Rückfragen, bevor du loslegst.

## Wie du arbeitest
- Arbeite in kleinen, nummerierten Schritten und erkläre kurz, warum.
- Nenne vor grösseren Änderungen die betroffenen Dateien und Ordner.
- Ich will das Theme verstehen und im Kurs erklären können: Erkläre neue WordPress-Konzepte (The Loop, Hooks, CPT usw.) in 2-3 Sätzen.
- Weise mich auf häufige Anfängerfehler und Sicherheitsrisiken hin.
- Bei Fehlern: wahrscheinliche Ursache, Test zur Prüfung, dann die Lösung. Ich gebe dir die genaue Fehlermeldung.

## Code-Regeln
- Gib vollständigen, kopierfertigen Code und nenne immer Dateiname und Pfad (z. B. `frontend/views/pages/_project.php`).
- Kommentiere kurz auf Deutsch.
- Halte dich an die WordPress-Coding-Standards und die Template-Hierarchie.
- **Escaping ist Pflicht** bei jeder Ausgabe: `esc_html()`, `esc_attr()`, `esc_url()`, `wp_kses_post()`. Die Beispiele in `manuals/` geben teils roh mit `<?= ... ?>` aus; übernimm das nicht, sondern escape immer.
- Styles und Scripts nur über `wp_enqueue_style()` / `wp_enqueue_script()`, keine Inline-Hacks.
- Keine Plugins, wenn es sauber mit Theme-Code geht. Ausnahme: Carbon Fields ist im Starter-Theme gesetzt. Andere Plugins nur als Alternative nennen.
- Keine sensiblen Daten ins Repo (`wp-config.php`, `.env`, Passwörter, FTP-Zugänge). Das Repo ist öffentlich.
- Performance und Responsive: Lazy Loading für Bilder und Videos, optimierte Einbettungen, Mobile-First. Fokus liegt auf Video- und Bildinhalten.

## CMS-Konzept (verbindlich)
@docs/cms-konzept.md

- Das Konzept ist die Vorgabe für alle Anleitungen in `manuals/`: Name, Slug und Felder für «Umsetzung mit KI» kommen aus dem Konzept, frag sie nicht noch einmal ab.
- Slugs und Feldnamen nicht ändern. Weicht etwas ab, zuerst nachfragen und das Konzept anpassen.
- Zuordnung zum Starter-Theme: A (Post Types) → `backend/cpt/`, `backend/carbonfields/_{cpt}.php`, `backend/getter/`; B (Taxonomien) → `backend/tax/`; C (Module) → `backend/carbonfields/_modules.php` und `frontend/views/modules/`; D (Menus) → `backend/nav/`; E (Globals) → `backend/carbonfields/_globals.php`.
- Empfohlene Reihenfolge: 1. Taxonomien und Post Types, 2. Globals und Menus, 3. einfache Module (`text`, `hero_text`, `hero_image`), 4. `projects`, `logos`, `profile`, `experience`, `gallery_photos`, `contact`.
- Highlights und Directory ziehen ihre Daten aus dem Post Type Projekt (Getter-Funktionen). Im Modul wird nichts doppelt erfasst.

## Theme-Architektur
```
/ (Theme-Root)
├── style.css, functions.php, header.php, footer.php, index.php, page.php
├── single-{cpt}.php          WordPress-Template pro Custom Post Type
├── backend/                  alles, was Funktionalität betrifft
│   ├── cpt/                  Custom Post Types (_{slug}.php + index.php)
│   ├── tax/                  Taxonomien
│   ├── carbonfields/         Inhaltsfelder (_modules.php, _globals.php, _{cpt}.php)
│   ├── getter/               Getter-Funktionen für Daten (get_{cpt}_by_id() usw.)
│   └── nav/                  Menu-Registrierung
├── frontend/                 alles, was die Anzeige betrifft
│   └── views/
│       ├── layout/           header.php, footer.php
│       ├── pages/            Page-Templates pro CPT / Seite
│       └── modules/          View-Templates für Module
└── manuals/                  Anleitungen für Standard-Erweiterungen
```
- Neue PHP-Dateien in `backend/` werden jeweils über die `index.php` des Ordners per `require_once __DIR__ . '/_name.php';` geladen.
- Daten für Templates kommen aus Getter-Funktionen, nicht direkt aus der Datenbank im View-Template.
- Helper: `get_media_by_id()` (Bild-URL und Alt-Text), `get_menu_by_slug()` (Menu-Einträge).

## Anleitungen in `manuals/` (vor der Arbeit lesen und befolgen)
| Aufgabe | Anleitung |
|---|---|
| Neuer Custom Post Type inkl. Felder, Template, Getter | `manuals/cpt.md` |
| Neue Taxonomie | `manuals/taxonomy.md` |
| Neues Modul im Modulbaukasten | `manuals/module.md` |
| Neues Menu | `manuals/menu.md` |
| Globale Einstellungen erweitern | `manuals/globals.md` |
| WordPress und Entwicklungsumgebung aufsetzen | `manuals/installation.md` |
| Vor dem Livegang | `manuals/go-live-checkliste.md` |

- Lies die passende Anleitung **zuerst**, bevor du Code schreibst. Frag mich nach den Angaben, die dort unter «Umsetzung mit KI» stehen (Name, Slug, Felder usw.).
- Bekannte Fehler in den Anleitungen: In `cpt.md` (Getter) steht ein `;` mitten im Array nach `carbon_get_post_meta(...)`. In `taxonomy.md` kann `get_the_terms()` `false` liefern und `array_map` bricht dann. Prüfe das vor dem Kopieren.
- Nach dem Anlegen eines CPT: Permalinks einmal speichern (Einstellungen > Permalinks).
- `installation.md` beschreibt PHPStorm. Ich arbeite mit VS Code, der Rest (Hostpoint, FTP, GitHub) gilt gleich.

## Kursrahmen (IM5, HS26)
- Abgabe: **Freitag, 08.01.2027, 23:59 Uhr**. Verspätung kostet 3 Punkte pro angefangene 24 Stunden.
- Coaching-Phase bis Freitag, 04.12.2026, 18:00 Uhr.
- Abgabe umfasst: öffentliches GitHub-Repo (Code und Doku), funktionsfähiges Projekt online, `README.md` mit Kurzbeschreibung, Setup, Screenshots, Learnings, Schwierigkeiten und Ressourcen.
- Regelmässig im Repo dokumentieren (Screenshots, Texte, Reflexion).
- Bewertung (45 Punkte): Technische Umsetzung und Tiefe (10), Funktionalität (5), UX und Usability (5), Aufwand und Projekttiefe (10), Originalität und Eigenständigkeit (5), Komplexität der Technologien (10; eigenes WordPress-Theme +5, PHP/DB +5, API +2, nicht im IM besprochene Technologien +2).
- Wichtig: Das Starter-Theme ist eine Boilerplate. Originalität und Eigenleistung zählen, und ich muss den Code erklären können.
