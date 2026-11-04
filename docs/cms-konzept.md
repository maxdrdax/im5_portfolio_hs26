# CMS-Konzept: Portfolio maxhutmacher.ch (v03)

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
| Meine Rolle | role | text |
| Projektbeschrieb | description | rich_text |
| Rolle und Teamabgrenzung (Text) | role_text | rich_text |
| Rolle und Teamabgrenzung (Bild) | role_image | image |
| Bildergalerie (Bilder und Videos) | gallery | media_gallery (image, video) |
| Video (YouTube/Vimeo-Link) | video_url | text (type url) |
| Ausgewähltes Projekt | featured | checkbox |

- Alle Angaben werden nur hier erfasst. Highlights und Directory rufen sie ab und zeigen nur einen Teil.
- `cover` ist Hero im Projektbeitrag, Karte im Highlights-Karussell und Hover-Vorschau im Directory. Der Schriftzug im Hero («Interaktiver Film») ist Teil des Bildes.
- Kategorie kommt aus der Taxonomie `project_category`.
- Rollen-Sektion ist optional: Text und Bild leer = nicht ausgeben.
- `cover_video` ist optional: läuft ohne Ton in Schleife im Hero; im Highlights-Karussell und im Directory startet es beim Hover. `cover` bleibt Vorschaubild und Fallback (Touch-Geräte, «Bewegung reduzieren»).
- Videos in der Galerie laufen ohne Ton in Schleife, sobald sie sichtbar sind.
- `video_url` ist optional (volle Videos, YouTube/Vimeo): wird per oEmbed eingebettet (vor der Galerie), leer = nicht ausgeben.
- `featured` markiert das Projekt für den Filter «Ausgewählte Projekte».

### Foto `photo`: gleichförmiger Aufbau
| Name | Slug | Feldtyp |
|---|---|---|
| Titel | title | Systemfeld |
| Bild | image | image |

- Keine Detailseite. Titel = Alt-Text. Raster-Layout ergibt sich aus dem Bildformat.

## B | Taxonomien
| Name | Slug | Post Type | Begriffe |
|---|---|---|---|
| Projektkategorie | project_category | project | Video, Animation, Print, Fotos, Digital |
| Fotokategorie | photo_category | photo | Menschen, Natur, Architektur, Street |

- «Ausgewählte Projekte» und «Alle» sind Standardfilter im Theme, keine Begriffe. «Ausgewählte Projekte» zeigt Projekte mit `featured` und ist beim Laden aktiv (gibt es keine, ist «Alle» aktiv). «Alle» zeigt alles.
- Schreibweise der Kategorie: «Fotos» (entschieden).

## C | Module

Titel-Felder (text) unterstützen einen kursiven Akzent: Wort in `*Sternchen*`. Ausgabe: erst `esc_html()`, dann Sternchen zu `<em>`.

### Hero Bild `hero_image`
| Name | Slug | Feldtyp |
|---|---|---|
| Titel | title | text |
| Bild | image | image |
| Video (MP4) | video | file (video) |

Startseite (mit Titel), Über mich (Titel leer). Video optional: läuft ohne Ton in Schleife, Bild ist Vorschau und Fallback.

### Hero Text `hero_text`
| Name | Slug | Feldtyp |
|---|---|---|
| Titel | title | text |
| Text | text | textarea |

Projekte (Text leer), Fotos, Kontakt.

### Projekte `projects`
| Name | Slug | Feldtyp |
|---|---|---|
| Titel | title | text |
| Darstellung | layout | select (Karussell / Liste) |
| Projekte | projects | association (Post Type project) |

Startseite: «Highlights» als Karussell mit ausgewählten Projekten. Seite Projekte: Liste als Tabelle mit Hover-Vorschau und Filterliste (aus `project_category`). Leeres Feld «Projekte» = alle Projekte automatisch. Daten kommen immer aus dem Post Type Projekt.

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
| Startseite | startseite | hero_image → projects (Karussell) → logos |
| Projekte | projekte | hero_text → projects (Liste) |
| Fotos | fotos | hero_text → gallery_photos |
| Über mich | ueber-mich | hero_image → profile → experience |
| Kontakt | kontakt | hero_text → contact |
| Impressum | impressum | text |
| Datenschutz | datenschutz | text |

## D | Menus
| Name | Slug | Hierarchiestufen | Einträge |
|---|---|---|---|
| Hauptnavigation | main | 1 | Projekte, Fotos, Über mich, Kontakt (Header) |
| Rechtliches | legals | 1 | Impressum, Datenschutz (Footer) |

## E | Globals
| Name | Slug | Feldtyp |
|---|---|---|
| Logo | logo | text |
| Copyright | copyright | text |
| E-Mail | email | text |
| Socials | socials | complex |
| ↳ Titel | title | text |
| ↳ Link | url | url |
| ↳ Icon | icon | image |

- Logo = Schriftzug «Max Hutmacher» im Header (Link auf Startseite). Copyright im Footer.
- Footer zeigt nur Socials mit Icon (LinkedIn). Kontaktseite listet alle (LinkedIn, Instagram).

## Statisch im Theme (nicht im CMS)
Scroll-Pfeil, ↗-Pfeile, Hover-Vorschau im Directory (folgt dem Mauszeiger), Beschriftungen Jahr / Kontext / Kategorie / Meine Rolle, Zwischentitel «Projektbeschrieb» und «Meine Rolle und Teamabgrenzung», Button-Text Lebenslauf, Standardfilter, Karussell, Hover-Effekt (Chroma/Verflüssigen, Cursor), Streifen-Galerie, Foto-Raster.

## Offen
- Video: entschieden. Kurze Clips als MP4 (`video`, `cover_video`, Galerie), volle Videos über `video_url`.
- Directory-Hover: Referenz houmahani.com/directory, Umsetzung vorerst «Vorschau folgt Mauszeiger», ohne Chroma-Effekt.
- Impressum und Datenschutz ohne Screen.
- Platzhalter im Design: Lorem ipsum, email@gmail.com, «Projekt 04/05», Instagram-Link.
- Kategorien: «Fotos» entschieden. «Digital» fehlt im Design-Filter (Filter wird automatisch aus den verwendeten Begriffen erzeugt).
