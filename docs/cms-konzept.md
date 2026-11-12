# CMS-Konzept: Portfolio maxhutmacher.ch (v06)

> **Ergänzung v06:** Neues Projektmodul `prototype` (Figma-Prototyp: Vorschaubild mit Link zu Figma, ein oder zwei Prototypen). Neues Global `prototype_title`. Nicht im Standardaufbau neuer Projekte (nur bei Bedarf hinzufügen). Externe Links öffnen auf der ganzen Website in einem neuen Tab.

> **Änderung v05:** Projektseite mit Modulen (`project_modules`): Projektbeschrieb, Meine Rolle, Video, Galerie und Weiterführende Informationen lassen sich pro Projekt verschieben, weglassen und ergänzen. Favicon über das WordPress-Website-Icon statt eigenes Feld.

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
| Ausgewähltes Projekt | featured | checkbox |
| Titel unter dem Hero ausblenden | hide_title | checkbox |
| Module der Projektseite | project_modules | complex (Module, siehe unten) |

#### Module der Projektseite `project_modules`
Inhalt unter dem Kopf (Titel, Eckdaten) als Module: verschieben, weglassen, ergänzen, auch mehrfach (z. B. zwei Galerien). Neues Projekt startet mit dem Standardaufbau Projektbeschrieb → Meine Rolle → Video → Galerie → Weiterführende Informationen. Leere Module werden nicht ausgegeben. Feldname `project_modules` statt `modules`, weil Carbon Fields Felder gleichen Namens über alle Container verwechselt (Seiten haben `modules`).

| Modul | Slug | Felder |
|---|---|---|
| Projektbeschrieb | description | title (text), text (rich_text) |
| Meine Rolle | role | title (text), text (rich_text), image (image) |
| Video | video | title (text), url (text, type url) |
| Galerie | gallery | title (text), gallery (media_gallery: image, video) |
| Weiterführende Informationen | links | title (text), links (complex: title, source, url, image) |
| Figma-Prototyp | prototype | title (text), prototypes (complex, max. 2: label (text), url (text, type url), poster (image), format (select: landscape / portrait)) |

- `title` ist in jedem Modul optional: leer = Standard aus den Globals.
- Umzug (v05): Die früheren festen Felder (`description`, `description_title`, `role_text`, `role_title`, `role_image`, `video_url`, `video_title`, `gallery`, `gallery_title`, `links`, `links_title`) wurden einmalig in die Module übertragen (Script danach entfernt). Die alten Werte bleiben in der Datenbank, werden aber nicht mehr gelesen.

- Alle Angaben werden nur hier erfasst. Highlights und Directory rufen sie ab und zeigen nur einen Teil (nur die festen Felder oben, nicht die Module).
- `cover` ist Hero im Projektbeitrag, Karte im Highlights-Karussell und Hover-Vorschau im Directory. Der Schriftzug im Hero («Interaktiver Film») ist Teil des Bildes.
- Kategorie kommt aus der Taxonomie `project_category`.
- Rollen-Sektion ist optional: Text und Bild leer = nicht ausgeben.
- Projektbeschrieb und Meine Rolle im gleichen Raster: Zwischentitel links (1/3), Text rechts (2/3) in einer Spalte (begrenzte Zeilenlänge statt zwei Spalten), Trennlinie darüber, dunkler Seitenhintergrund (kein grüner Block). Bild im Modul `role` optional unter dem Rollentext, Format 3:2. Handy: Titel über dem Text.
- Zwischentitel (`title` in jedem Modul) sind optional: leer = Standard aus den Globals (dort leer = «Projektbeschrieb», «Meine Rolle», «Video», «Einblicke», «Weiterführende Informationen» bzw. «Prototyp»). Video, Galerie, Links und Prototyp haben einen Zwischentitel mit Linie darüber (Handy ohne Linie), damit sie nicht wie ein Teil von «Meine Rolle» wirken.
- Modul `links` (Weiterführende Informationen): Linkliste, standardmässig am Ende der Projektseite, z. B. Festival, Artikel, Making-of. Gestaltung wie die Projektliste (Zeilen mit Trennlinie, Grossbuchstaben, Quelle grau, Pfeil ↗, Hover grün). Externe Links öffnen in einem neuen Tab. Leer = nicht ausgeben.
- Hover-Vorschau pro Link (wie in der Projektliste, folgt dem Mauszeiger, nur Desktop): eigenes `image`; sonst bei Social-Media-Links (Domain passt zu einem Eintrag in den Globals `socials`) das Icon der Plattform weiss auf grüner Fläche; sonst holt der Server beim Speichern des Projekts das Vorschaubild der Website (Open Graph `og:image`) und legt es in der Mediathek ab (Besucher laden nichts von fremden Servern). Kein Bild = keine Vorschau.
- `cover_video` ist optional: läuft ohne Ton in Schleife im Hero; im Highlights-Karussell und im Directory startet es beim Hover. `cover` bleibt Vorschaubild und Fallback (Touch-Geräte, «Bewegung reduzieren»).
- Videos in der Galerie laufen ohne Ton in Schleife, sobald sie sichtbar sind.
- Darstellung in der Galerie pro Bild/Video: Feld `gallery_fit` direkt an der Datei in der Mediathek (select: `cover` = Füllen, Standard / `contain` = Einpassen). Füllen = Fläche ganz gefüllt, Ränder werden abgeschnitten. Einpassen = ganzes Bild sichtbar (z. B. Moodboard), freie Fläche dunkel und neutral. Gilt überall, wo die Datei in einer Projektgalerie steht; soll dasselbe Bild einmal gefüllt und einmal eingepasst erscheinen, wird es ein zweites Mal hochgeladen. Umsetzung mit WordPress-Hooks (`attachment_fields_to_edit` / `attachment_fields_to_save`), weil `media_gallery` nur IDs speichert und keine Zusatzfelder pro Bild kennt. Verworfen: zweites Feld «Einpassen» im Modul (Bild musste doppelt ausgewählt werden, verwirrend) und Galerie als complex mit einer Zeile pro Bild (kein Mehrfach-Auswählen, Umzug nötig).
- Modul `video` (volle Videos, YouTube/Vimeo): wird per oEmbed eingebettet, standardmässig vor der Galerie; leerer Link = nicht ausgeben.
- Modul `prototype` (Figma-Prototyp): ein oder zwei Prototypen (`prototypes`, z. B. Handy und Web). Zwei Prototypen stehen ab Tablet nebeneinander und gleich hoch, auf dem Handy untereinander. Ungültige Einträge entfallen, ohne gültigen Eintrag wird das Modul nicht ausgegeben. Umbau (v06): Die ersten Felder `url`, `poster`, `format` direkt im Modul wurden durch `prototypes` ersetzt (einmalig neu eintragen; alte Werte bleiben ungelesen in der Datenbank).
  - Keine Einbettung (entschieden): Pro Prototyp eine Fläche mit `poster` (ohne Bild: dunkle, neutrale Fläche) und unten dem Button «Figma-Prototyp öffnen ↗» (Stil wie der Lebenslauf-Button). Die ganze Fläche ist ein Link und öffnet den Prototyp in Figma in einem neuen Tab. Verworfen: Einbettung als iframe mit Click-to-load und Vollbild (fixe Navigation im Embed abgeschnitten, Buttons im Prototyp auf dem Handy nicht bedienbar, mehr Fehlerquellen).
  - Im Feld `url` steht der normale Share-Link aus Figma (`figma.com/proto/…` oder `figma.com/design/…`). Whitelist: nur `https`, Host `figma.com`, `www.figma.com` oder `embed.figma.com`, Pfad beginnt mit `/proto/` oder `/design/`; leerer oder ungültiger Link = nicht ausgeben. Der Link führt immer auf `www.figma.com`.
  - Parameter aus dem Share-Link: nur `node-id`, `starting-point-node-id` (Start des Flows), `page-id`, `version-id`, `scaling`, `content-scaling` werden übernommen (Tracking `t`, `viewport` und die Flow-Liste `show-proto-sidebar` fallen weg).
  - `format`: `landscape` (16:10, Standard) oder `portrait` (9:16, schmal und zentriert, für Mobile-Prototypen); bestimmt das Format der Vorschaufläche.
  - `label` (optional, z. B. «Mobile», «Web»): Beschriftung linksbündig über der Vorschau, Stil wie die Beschriftungen der Eckdaten (klein, grau, Grossbuchstaben). Leer = keine Beschriftung. Für Screenreader Teil des Linktexts (leer = «Handy» bzw. «Web» aus dem Format, nur bei zwei Prototypen).
  - Nicht im Standardaufbau neuer Projekte (`get_project_default_modules()` bleibt unverändert), nur bei Bedarf hinzufügen.
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

Startseite: «Highlights» als Karussell mit ausgewählten Projekten. Seite Projekte: Liste als Tabelle mit Hover-Vorschau und Filterliste (aus `project_category`, mit Anzahl pro Filter, als Chips); ganze Zeile ist ein Link (kein Aufklappen, verworfen); auf dem Handy pro Projekt ein Vorschaubild 1:1 über Titel und Jahr, Kategorie unter dem Titel, Filter untereinander. Kein Hervorheben beim Scrollen auf Handy und Tablet (verworfen: Abdunkeln der anderen Projekte erschwert das Überfliegen und wirkt unruhig). Leeres Feld «Projekte» = alle Projekte automatisch. Daten kommen immer aus dem Post Type Projekt.

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
| Projektseite: Titel «Prototyp» | prototype_title | text |
| Menü-Hintergrundbild | menu_bg_image | image |
| Menü-Hintergrundvideo (MP4) | menu_bg_video | file (video) |

- Logo = Schriftzug «Max Hutmacher» im Header (Link auf Startseite). Copyright im Footer.
- `site_private`: Aktiv = Besucher ohne Login werden zur Anmeldung umgeleitet, auch die REST-API ist gesperrt (Phase vor dem Livegang; Testzugänge mit der Rolle «Abonnent»). Beim Livegang ausschalten (Go-live-Checkliste).
- Favicon: kein eigenes Feld, sondern WordPress-Standard unter Einstellungen > Allgemein > «Website-Icon» (Signet in Grün, PNG mindestens 512 × 512 px). WordPress erzeugt daraus alle Grössen, auch das Homescreen-Symbol auf dem iPhone.
- `logo_signet`: Auf Tablet und Handy (bis 1024 px) zeigt der Header das Signet statt des Schriftzugs (weisses SVG). Leer = Schriftzug. Für Screenreader bleibt der Logo-Text der Name des Links.
- Footer zeigt nur Socials mit Icon (LinkedIn). Kontaktseite listet alle (LinkedIn, Instagram).

## Statisch im Theme (nicht im CMS)
Icons aus der eigenen Icon-Bibliothek (`frontend/assets/icons/`, Ausgabe über `get_icon()`; Pfeil nach unten = Scroll-Pfeil, schräg nach rechts oben = Teaser, Kontakt, Socials), Punkte unter der Galerie (Tablet/Handy), Hover-Vorschau im Directory (folgt dem Mauszeiger), Beschriftungen Jahr / Kontext / Kategorie / Meine Rolle, Standard der Zwischentitel «Projektbeschrieb» und «Meine Rolle» (anpassbar in den Globals und pro Projekt), Button-Text Lebenslauf, Button «Figma-Prototyp öffnen ↗» (Modul `prototype`), externe Links im neuen Tab (überall: Module, Menüs, Rich Text; Hinweis «öffnet in neuem Tab» für Screenreader), Mauszeiger ausgeblendet, solange der Cursor-Punkt aktiv ist (nur Maus, nicht bei «Bewegung reduzieren»; über iframes blendet der Punkt aus), Standardfilter, Karussell, Hover-Effekt (Chroma/Verflüssigen, Cursor), Cursor-Label über Highlights und Galerie (grüne Pille, ersetzt dort den Cursor-Punkt; «Scrollen ↓» bis zum ersten Blättern, danach bei den Highlights «Ansehen», bei der Galerie ausgeblendet), Zähler «01 / 05» mit Fortschrittslinie unter Highlights und Galerie (nur Desktop), Streifen-Galerie, Foto-Raster, Verlauf oben im Hero, Menü-Button, Overlay-Animation, Nummerierung der Menüpunkte, Seitenübergänge, Einblenden beim Scrollen (gilt für alle Inhalte, auch neue Module und Sektionen; ausser Hero und Intro), Text-Hervorhebung beim Scrollen (Intro), Zähler im Filter (Anzahl Einträge pro Filter, aus den vorhandenen Daten gezählt; Filter ohne Treffer entfallen), Filter als Chips (umrandete Pillen, aktiv grün), Gestaltung des Intro-Links (grün mit Pfeil ↗), Zeitlinie im Modul «Erfahrung» (füllt sich beim Scrollen grün, Punkt pro Station; bei «Bewegung reduzieren» ganz grün).

## Offen
- Video: entschieden. Kurze Clips als MP4 (`video`, `cover_video`, Galerie), volle Videos über das Projektmodul `video`.
- Directory-Hover: Referenz houmahani.com/directory, Umsetzung vorerst «Vorschau folgt Mauszeiger», ohne Chroma-Effekt.
- Impressum und Datenschutz ohne Screen.
- Platzhalter im Design: Lorem ipsum, email@gmail.com, «Projekt 04/05», Instagram-Link.
- Kategorien: «Fotos» entschieden. «Digital» fehlt im Design-Filter (Filter wird automatisch aus den verwendeten Begriffen erzeugt).
- Navigation: Textanimation im Overlay (Idee für später). Teaser-Text pro Menüpunkt vorerst verworfen.
