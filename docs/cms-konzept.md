# CMS-Konzept: Portfolio maxhutmacher.ch (v04)

> **Ergänzung v04:** Neues Modul `intro` (Vorstellung auf der Startseite nach dem Hero).
>
> **Änderungen v04:** Neue Navigation (Overlay-Menü, fixer Header: über dem Hero durchsichtig, danach Milchglas). Neue Felder am Menüeintrag: `menu_image`, `menu_video`. Neue Globals für einen gemeinsamen Menü-Hintergrund: `menu_bg_image`, `menu_bg_video`. Hero Bild: Position des Titels `title_position`. Globals: `logo_signet`, `site_private`.

Quelle: Screendesign v06 (Figma). Felder im Carbon-Fields-Stil.
`↳` = Unterfeld des complex-Felds direkt darüber.

## A | Post Types

### Seite `page`: Modulbaukasten
Alle 7 Seiten (Startseite, Projekte, Fotos, Über mich, Kontakt, Impressum, Datenschutz). Aufbau über Module (siehe C).

### Projekt `project`: gleichförmiger Aufbau
| Name | Slug | Feldtyp |
|---|---|---|
| Titel | title | Systemfeld |
| Titelbild | cover | image |
| Titelvideo (MP4) | cover_video | file (video) |
| Jahr | year | text |
| Kontext | context | text |
| Meine Rolle (kurz) | role | text |
| Titel über dem Projektbeschrieb | description_title | text |
| Projektbeschrieb | description | rich_text |
| Titel über «Meine Rolle» | role_title | text |
| Meine Rolle | role_text | rich_text |
| Meine Rolle (Bild) | role_image | image |
| Bildergalerie (Bilder und Videos) | gallery | media_gallery (image, video) |
| Video (YouTube/Vimeo-Link) | video_url | text (type url) |
| Titel über dem Video | video_title | text |
| Titel über der Bildergalerie | gallery_title | text |
| Titel über «Weiterführende Informationen» | links_title | text |
| Weiterführende Informationen | links | complex |
| ↳ Titel | title | text |
| ↳ Quelle | source | text |
| ↳ Link | url | url |
| ↳ Vorschaubild | image | image |
| Ausgewähltes Projekt | featured | checkbox |
| Titel unter dem Hero ausblenden | hide_title | checkbox |

- Alle Angaben werden nur hier erfasst. Highlights und Directory rufen sie ab und zeigen nur einen Teil.
- `cover` ist Hero im Projektbeitrag, Karte im Highlights-Karussell und Hover-Vorschau im Directory. Der Schriftzug im Hero («Interaktiver Film») ist Teil des Bildes.
- Kategorie kommt aus der Taxonomie `project_category`.
- Rollen-Sektion ist optional: Text und Bild leer = nicht ausgeben.
- Projektbeschrieb und Meine Rolle im gleichen Raster: Zwischentitel links (1/3), Text rechts (2/3) in einer Spalte (begrenzte Zeilenlänge statt zwei Spalten), Trennlinie darüber, dunkler Seitenhintergrund (kein grüner Block). `role_image` optional unter dem Rollentext, Format 3:2. Handy: Titel über dem Text.
- `description_title` und `role_title` sind optional: leer = Standard aus den Globals (dort leer = «Projektbeschrieb», «Meine Rolle» bzw. «Weiterführende Informationen»). Gleich für `video_title` (Standard «Video») und `gallery_title` (Standard «Einblicke»): Video, Galerie und Links haben einen Zwischentitel mit Linie darüber (Handy ohne Linie), damit sie nicht wie ein Teil von «Meine Rolle» wirken.
- `links` ist optional: Linkliste am Ende der Projektseite (nach der Galerie), z. B. Festival, Artikel, Making-of. Gestaltung wie die Projektliste (Zeilen mit Trennlinie, Grossbuchstaben, Quelle grau, Pfeil ↗, Hover grün). Externe Links öffnen in einem neuen Tab. Leer = nicht ausgeben.
- Hover-Vorschau pro Link (wie in der Projektliste, folgt dem Mauszeiger, nur Desktop): eigenes `image`; sonst bei Social-Media-Links (Domain passt zu einem Eintrag in den Globals `socials`) das Icon der Plattform weiss auf grüner Fläche; sonst holt der Server beim Speichern des Projekts das Vorschaubild der Website (Open Graph `og:image`) und legt es in der Mediathek ab (Besucher laden nichts von fremden Servern). Kein Bild = keine Vorschau.
- `cover_video` ist optional: läuft ohne Ton in Schleife im Hero; im Highlights-Karussell und im Directory startet es beim Hover. `cover` bleibt Vorschaubild und Fallback (Touch-Geräte, «Bewegung reduzieren»).
- Videos in der Galerie laufen ohne Ton in Schleife, sobald sie sichtbar sind.
- `video_url` ist optional (volle Videos, YouTube/Vimeo): wird per oEmbed eingebettet (vor der Galerie), leer = nicht ausgeben.
- `featured` markiert das Projekt für den Filter «Ausgewählte Projekte».
- Unter dem Hero stehen Kategorie und Titel sichtbar (Figma). `hide_title` blendet sie aus, z. B. wenn der Schriftzug schon im Titelbild steht; für Screenreader bleibt der Titel erhalten.
- Bildergalerie als horizontales Akkordeon (Figma): Streifen gleich breit, beim Hover öffnet sich ein Bild; mit Mausrad/Trackpad blättert man durch die Bilder (gleiches Prinzip wie die Highlights). Auf Tablet und Handy Bilder zum Wischen (88 % breit, das nächste Bild schaut rechts herein), mit Punkten darunter und der Anleitung «← Wischen →», die nach dem ersten Wischen ausblendet.

### Foto `photo`: gleichförmiger Aufbau
| Name | Slug | Feldtyp |
|---|---|---|
| Titel | title | Systemfeld |
| Bild | image | image |
| Grösse im Raster | grid_size | select (Automatisch / Klein 1×1 / Breit 2×1 / Hoch 1×2 / Gross 2×2 / Panorama 3×2) |
| Bildausschnitt | focus | select (Mitte / Oben / Unten / Links / Rechts) |

- Keine Detailseite. Titel = Alt-Text.
- Raster mit 3 Spalten und fester Zeilenhöhe (Figma). Kachelgrösse über `grid_size`; «Automatisch» = aus dem Bildformat (Quer → Breit, Hoch → Hoch, Quadrat → Klein).
- `focus` bestimmt, welcher Teil des Bildes sichtbar bleibt, wenn die Kachel ein anderes Seitenverhältnis hat als das Foto.

## B | Taxonomien
| Name | Slug | Post Type | Begriffe |
|---|---|---|---|
| Projektkategorie | project_category | project | Video, Animation, Print, Fotos, Digital |
| Fotokategorie | photo_category | photo | Menschen, Natur, Architektur, Street |

- «Ausgewählte Projekte» und «Alle» sind Standardfilter im Theme, keine Begriffe. «Ausgewählte Projekte» zeigt Projekte mit `featured` und ist beim Laden aktiv (gibt es keine, ist «Alle» aktiv). «Alle» zeigt alles.
- Schreibweise der Kategorie: «Fotos» (entschieden).
- Ordner in der Mediathek: Plugin «Folders» (Premio, Gratis-Version) mit Drag-and-Drop. Eigene Taxonomie `media_folder` verworfen (Drag-and-Drop in der Kachelansicht wäre aufwendig und bei WordPress-Updates anfällig).

## C | Module

Titel-Felder (text) unterstützen einen kursiven Akzent: Wort in `*Sternchen*`. Zusätzlich `**doppelte Sternchen**` = Markierung (grün hinterlegt; im Intro wird sie beim Scrollen von links nach rechts gezogen). Ausgabe: erst `esc_html()`, dann `**…**` zu `<mark>` und `*…*` zu `<em>`.

### Hero Bild `hero_image`
| Name | Slug | Feldtyp |
|---|---|---|
| Titel | title | text |
| Position des Titels | title_position | select (Oben links / Oben Mitte / Oben rechts / Mitte links / Mitte / Mitte rechts / Unten links / Unten rechts) |
| Bild | image | image |
| Video (MP4) | video | file (video) |

Startseite (mit Titel), Über mich (Titel leer). Video optional: läuft ohne Ton in Schleife, Bild ist Vorschau und Fallback.
`title_position`: Standard «Mitte links». «Unten Mitte» gibt es nicht, dort liegt der Scroll-Pfeil. Raster statt freier Position, damit der Titel auf jeder Bildschirmgrösse im Bild bleibt.
Ist `hero_image` das erste Modul einer Seite, beginnt der Hero ganz oben und der Header liegt darüber (Verhalten siehe D).

### Hero Text `hero_text`
| Name | Slug | Feldtyp |
|---|---|---|
| Titel | title | text |
| Text | text | textarea |

Projekte (Text leer), Fotos, Kontakt.

### Intro `intro`
| Name | Slug | Feldtyp |
|---|---|---|
| Text | text | textarea |
| Link-Text | link_text | text |
| Link | link | association (Seite) |

Startseite, direkt nach dem Hero: kurze Vorstellung in zwei, drei Sätzen (auf dem Handy steht im Header nur das Signet). Grosse Schrift, beim Scrollen färbt sich der Text Wort für Wort von gedimmt zu weiss (Referenz: structureengineering.com.au). Akzent mit `*Sternchen*` (kursiv, grün) oder `**doppelten Sternchen**` (grün hinterlegt). Link optional (z. B. «Mehr über mich» → Über mich), leer = nicht ausgeben. Bei «Bewegung reduzieren» ist der Text von Anfang an weiss.

### Projekte `projects`
| Name | Slug | Feldtyp |
|---|---|---|
| Titel | title | text |
| Darstellung | layout | select (Karussell / Liste) |
| Projekte | projects | association (Post Type project) |

Startseite: «Highlights» als Karussell mit ausgewählten Projekten. Seite Projekte: Liste als Tabelle mit Hover-Vorschau und Filterliste (aus `project_category`); auf dem Handy pro Projekt ein Vorschaubild 1:1 über Titel und Jahr, Filter untereinander. Auf Handy und Tablet wird beim Scrollen das Projekt in der Bildschirmmitte hervorgehoben, die anderen treten zurück (Liste und Highlights). Leeres Feld «Projekte» = alle Projekte automatisch. Daten kommen immer aus dem Post Type Projekt.

### Fotogalerie `gallery_photos`
Keine Felder. Listet alle Fotos, Filter aus `photo_category`.

### Logos `logos`
| Name | Slug | Feldtyp |
|---|---|---|
| Titel | title | text |
| Logos | logos | complex |
| ↳ Logo | logo | image |
| ↳ Link | link | url |

Startseite: «Berufserfahrung / Ausbildung» (GLB, plattformj.ch).

### Profil `profile`
| Name | Slug | Feldtyp |
|---|---|---|
| Name | name | text |
| Funktion | position | text |
| Skills | skills | complex |
| ↳ Skill | skill | text |
| Steckbrief | facts | complex |
| ↳ Bezeichnung | label | text |
| ↳ Wert | detail | text |
| Titel | title | text |
| Text | text | rich_text |

Über mich: links Name, Funktion, Skill-Tags, Steckbrief; rechts Titel und Text.
Hinweis: `value` ist in Carbon Fields ein reservierter Name für Unterfelder von complex-Feldern, darum `detail`.

### Erfahrung `experience`
| Name | Slug | Feldtyp |
|---|---|---|
| Titel | title | text |
| Text | text | textarea |
| Stationen | stations | complex |
| ↳ Titel | title | text |
| ↳ Institution | institution | text |
| ↳ Zeitraum | period | text |
| ↳ Text | text | textarea |
| ↳ Tags (mit Komma getrennt) | tags | text |
| Lebenslauf (PDF) | cv | file |

Über mich. Button-Beschriftung «Lebenslauf als PDF downloaden» ist statisch.

### Kontakt `contact`
Keine Felder. Gibt E-Mail und Socials aus den Globals aus (unter dem Hero Text).

### Text `text`
| Name | Slug | Feldtyp |
|---|---|---|
| Titel | title | text |
| Text | text | rich_text |

Impressum und Datenschutz.

### Module pro Seite
| Seite | Slug | Module (Reihenfolge) |
|---|---|---|
| Startseite | startseite | hero_image → intro → projects (Karussell) → logos |
| Projekte | projekte | hero_text → projects (Liste) |
| Fotos | fotos | hero_text → gallery_photos |
| Über mich | ueber-mich | hero_image → profile → experience |
| Kontakt | kontakt | hero_text → contact |
| Impressum | impressum | text |
| Datenschutz | datenschutz | text |

## D | Menus
| Name | Slug | Hierarchiestufen | Einträge |
|---|---|---|---|
| Hauptnavigation | main | 1 | Start, Projekte, Fotos, Über mich, Kontakt (Overlay-Menü; «Kontakt» zusätzlich im Header) |
| Rechtliches | legals | 1 | Impressum, Datenschutz (Footer) |

### Felder am Menüeintrag (nur Hauptnavigation `main`)
| Name | Slug | Feldtyp |
|---|---|---|
| Hintergrundbild | menu_image | image |
| Hintergrundvideo (MP4) | menu_video | file (video) |

- Header: Logo links, rechts «Kontakt» (auf dem Desktop sichtbar, solange das Menü geschlossen ist) und Button «Menü»/«Schliessen». Die Hauptnavigation steht nicht mehr im Header, sondern im Overlay.
- Header auf allen Seiten fix oben, ohne Linie, Schrift weiss. Über Bild-Heros (`hero_image`, Projekt-Hero) liegt ein weicher dunkler Verlauf oben im Bild für die Lesbarkeit. Sobald der Hero aus dem Bild ist (und auf Seiten ohne Hero), liegt Milchglas hinter dem Header (Seitenfarbe halbtransparent + Blur), damit der Inhalt darunter nicht scharf neben der Navigation steht (vor allem auf dem Handy). Auf Tablet und Handy (bis 1024 px) liegt das Milchglas immer hinter dem Header, auch über dem Hero; der Verlauf im Hero entfällt dort. Verworfen: Glas-Effekt schon ganz oben über dem Hero (deckt das Bild ab), Ausblenden beim Runterscrollen (unruhig, besonders auf dem Handy) und Farbumkehr (`mix-blend-mode: difference`, auf mittleren Tönen zu wenig Kontrast).
- Tablet und Handy (bis 1024 px): Signet links, Menü-Symbol (ohne Beschriftung, für Screenreader vorhanden) in der Mitte, «Kontakt» als umrandeter, abgerundeter Button rechts (bleibt auch bei offenem Menü sichtbar; auf der Kontaktseite ausgefüllt).
- «Kontakt» im Header = Eintrag aus `main`, der auf die Seite `kontakt` verweist (kein eigenes Feld).
- Overlay-Menü: Vollbild, Menüpunkte gross mit fortlaufender Nummer (01, 02 …). «Start» (Startseite) ist der erste Punkt. Weitere Seiten lassen sich in Design > Menüs ergänzen; bei vielen Punkten oder niedrigem Fenster wird die Schrift kleiner, damit alle auf den Bildschirm passen. Aktive Seite bleibt weiss (nur für Screenreader markiert, `aria-current`). Unten E-Mail und Socials aus den Globals.
- Klick auf einen Menüpunkt: Das Menü bleibt offen, die neue Seite blendet weich darüber ein (Seitenübergang für die ganze Website).
- `menu_image` ist der Hintergrund des Overlays beim Hover/Fokus auf den Menüpunkt (Crossfade). `menu_video` optional: läuft ohne Ton in Schleife, Bild ist Vorschau und Fallback. Ohne Bild: einfarbiger Hintergrund.
- Handy/Touch: Hintergrund zeigt den Eintrag der aktuellen Seite, sonst den ersten.
- Carbon Fields zeigt die Felder bei allen Menüeinträgen an (auch `legals`); ausgegeben werden sie nur in `main`.
- Ist in den Globals `menu_bg_image` (oder `menu_bg_video`) gesetzt, gilt dieser Hintergrund für das ganze Overlay; beim Hover wechselt er nicht. Leer = Hintergrund pro Menüpunkt (`menu_image`, `menu_video`).
- Backend (Design > Menüs): Button «Alle aufklappen», Vorschaubild in der zugeklappten Zeile, Bild und Video nebeneinander.

## E | Globals
| Name | Slug | Feldtyp |
|---|---|---|
| Logo | logo | text |
| Signet (Handy) | logo_signet | image |
| Favicon | favicon | image |
| Copyright | copyright | text |
| E-Mail | email | text |
| Socials | socials | complex |
| ↳ Titel | title | text |
| ↳ Link | url | url |
| ↳ Icon | icon | image |
| Website nur für angemeldete Benutzer | site_private | checkbox |
| Projektseite: Titel «Projektbeschrieb» | description_title | text |
| Projektseite: Titel «Meine Rolle» | role_title | text |
| Projektseite: Titel «Video» | video_title | text |
| Projektseite: Titel «Einblicke» (Galerie) | gallery_title | text |
| Projektseite: Titel «Weiterführende Informationen» | links_title | text |
| Menü-Hintergrundbild | menu_bg_image | image |
| Menü-Hintergrundvideo (MP4) | menu_bg_video | file (video) |

- Logo = Schriftzug «Max Hutmacher» im Header (Link auf Startseite). Copyright im Footer.
- `site_private`: Aktiv = Besucher ohne Login werden zur Anmeldung umgeleitet, auch die REST-API ist gesperrt (Phase vor dem Livegang; Testzugänge mit der Rolle «Abonnent»). Beim Livegang ausschalten (Go-live-Checkliste).
- `favicon`: Symbol im Browser-Tab (Signet in Grün, PNG 512 × 512 px; PNG zusätzlich als Homescreen-Symbol auf dem iPhone). Leer = Standard des Browsers.
- `logo_signet`: Auf Tablet und Handy (bis 1024 px) zeigt der Header das Signet statt des Schriftzugs (weisses SVG). Leer = Schriftzug. Für Screenreader bleibt der Logo-Text der Name des Links.
- Footer zeigt nur Socials mit Icon (LinkedIn). Kontaktseite listet alle (LinkedIn, Instagram).

## Statisch im Theme (nicht im CMS)
Icons aus der eigenen Icon-Bibliothek (`frontend/assets/icons/`, Ausgabe über `get_icon()`; Pfeil nach unten = Scroll-Pfeil, schräg nach rechts oben = Teaser, Kontakt, Socials), Punkte unter der Galerie (Tablet/Handy), Hover-Vorschau im Directory (folgt dem Mauszeiger), Beschriftungen Jahr / Kontext / Kategorie / Meine Rolle, Standard der Zwischentitel «Projektbeschrieb» und «Meine Rolle» (anpassbar in den Globals und pro Projekt), Button-Text Lebenslauf, Standardfilter, Karussell, Hover-Effekt (Chroma/Verflüssigen, Cursor), Cursor-Label über Highlights und Galerie (grüne Pille, ersetzt dort den Cursor-Punkt; «Scrollen ↓» bis zum ersten Blättern, danach bei den Highlights «Ansehen», bei der Galerie ausgeblendet), Zähler «01 / 05» mit Fortschrittslinie unter Highlights und Galerie (nur Desktop), Streifen-Galerie, Foto-Raster, Verlauf oben im Hero, Menü-Button, Overlay-Animation, Nummerierung der Menüpunkte, Seitenübergänge, Einblenden beim Scrollen (gilt für alle Inhalte, auch neue Module und Sektionen; ausser Hero und Intro), Text-Hervorhebung beim Scrollen (Intro).

## Offen
- Video: entschieden. Kurze Clips als MP4 (`video`, `cover_video`, Galerie), volle Videos über `video_url`.
- Directory-Hover: Referenz houmahani.com/directory, Umsetzung vorerst «Vorschau folgt Mauszeiger», ohne Chroma-Effekt.
- Impressum und Datenschutz ohne Screen.
- Platzhalter im Design: Lorem ipsum, email@gmail.com, «Projekt 04/05», Instagram-Link.
- Kategorien: «Fotos» entschieden. «Digital» fehlt im Design-Filter (Filter wird automatisch aus den verwendeten Begriffen erzeugt).
- Navigation: Textanimation im Overlay (Idee für später). Teaser-Text pro Menüpunkt vorerst verworfen.
