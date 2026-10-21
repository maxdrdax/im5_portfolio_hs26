# Einen neuen `Custom Post Type (CPT)` hinzufügen
> In dieser Anleitung wird erklärt, wie man einen neuen CPT hinzufügt, mit Inhaltsfeldern erweitert und in einem View-Template anzeigt.

## 📽️ Video-Anleitung
[https://youtu.be/cUSiK0wLvPc](https://youtu.be/cUSiK0wLvPc)

## 📑 Beispieldaten
In dieser Anleitung wird beispielhaft ein CPT mit folgenden Angaben hinzugefügt:

- **CPT-Name**: Vorlage
- **CPT-Slug**: preset
- **Inhaltsfeld-Name**: Beschreibung
- **Inhaltsfeld-Slug**: description
- **Inhaltsfeld-Typ**: textarea

Diese müssen, wenn nach dieser Anleitung gearbeitet wird, jeweils entsprechend angepasst werden. Je nachdem, wie dein CPT heissen soll.

## ⚒️ Schritt-für-Schritt-Anleitung
1. Als Erstes muss im Ordner [backend/cpt] eine neue Datei für die Konfiguration des CPT erstellt werden, die `_preset.php` heisst (abgeleitet aus dem Slug deines CPT).
2. Füge in diese Datei diese Vorlage einer CPT-Konfiguration ein: 
   ```php
   <?php
   register_post_type('preset', [
       'label' => 'Vorlage',
       'labels' => [
           'name' => 'Vorlagen',
           'singular_name' => 'Vorlage',
           'menu_name' => 'Projekte',
           'name_admin_bar' => 'Vorlage',
           'all_items' => 'Alle Vorlagen',
           'add_new_item' => 'Neue Vorlage hinzufügen',
           'add_new' => 'Neue Vorlage',
           'new_item' => 'Neue Vorlage',
           'edit_item' => 'Vorlage bearbeiten',
           'update_item' => 'Vorlage updaten',
           'view_item' => 'Vorlage ansehen',
           'view_items' => 'Vorlagen ansehen',
           'search_items' => 'Vorlage suchen',
           'not_found' => 'Keine Vorlage gefunden',
           'not_found_in_trash' => 'Keine Vorlage im Papierkorb gefunden',
       ],
       'supports' => ['title'],
       'hierarchical' => false,
       'public' => true,
       'show_ui' => true,
       'show_in_menu' => true,
       'menu_position' => 20,
       'menu_icon' => 'dashicons-hammer',
       'show_in_admin_bar' => false,
       'show_in_nav_menus' => false,
       'has_archive' => false,
       'exclude_from_search' => true,
       'publicly_queryable' => true,
       'capability_type' => 'post',
       'show_in_rest' => false,
       'rewrite' => [
           'slug' => 'vorlage',
           'with_front' => false,
           'pages' => true,
           'feeds' => true,
       ],
   ]);
   ```
   Passe die Werte in `labels`, `label` und unter `rewrite>slug` auf deinen CPT an.
3. Suche dir [hier](https://developer.wordpress.org/resource/dashicons) ein passendes Menu-Icon für deinen Post Type heraus und ersetze das bei `menu_icon`.
4. Wechsle in die Datei [backend/cpt/index.php](/backend/cpt/index.php) und lade dort die Datei, in der du deinen CPT registriert hast.
   ```php
   require_once __DIR__ . '/_preset.php';
   ```
5. Nun solltest du im Backend deinen CPT sehen.
6. Konfiguriere jetzt die Inhaltsfelder für deinen CPT mit Carbon Fields. Wechsle dazu in den Ordner [backend/carbonfields](/backend/carbonfields) und erstelle eine neue Datei, die `_preset.php` heisst (gleich wie die Datei für die Konfiguration deines CPT).
7. Füge in diese Datei diese Vorlage ein: 
   ```php
   <?php
   use Carbon_Fields\Container;
   use Carbon_Fields\Field;
   Container::make('post_meta', 'Vorlage')
       ->where('post_type', '=', 'preset')
       ->add_fields([
           // Felder hier einfügen
       ]);
   ```
8. Gehe nun in die Datei [backend/carbonfields/index.php](/backend/carbonfields/index.php) und lade dort deine soeben erstellte Datei.
   ```php
   require_once __DIR__ . '/_preset.php';
   ```
9. Nun kannst du die gewollten Felder mit der Carbon-Fields Make-Schreibweise ergänzen.
   ```php
   Field::make('textarea', 'description', 'Beschreibung'),
   ```
   Die verschiedenen Feldtypen, die benutzt werden können, sind in der [Dokumentation](https://docs.carbonfields.net/) zu finden.
10. Um zu testen, ob das funktioniert, kannst du im Backend einen Eintrag deines CPT erstellen und schauen, ob die Inhaltsfelder-Konfiguration so aussieht, wie du dir das vorstellst.
11. Wenn alles konfiguriert ist, erstellst du dir mindestens einen Eintrag deines CPT im Backend. Den brauchst du nachher zum Testen.
12. Nun brauchen wir ein View-Template, um unseren Custom Post Type im Frontend anzuzeigen. Dazu erstellst du zuerst eine neue Datei im Ordner [frontend/views/pages](/frontend/views/pages) und benennst sie _preset.php (nach deinem CPT). Füge dort zum Testen einfach mal Folgendes ein:
   ```html
   <mark>Hier kommt der Inhalt vom Post Type Vorlage</mark>
   ```
13. Jetzt brauchst du noch eine Wordpress-Template-Datei, diese erstellst du auf der äussersten Ebene deines Codes und nennst sie `single-preset.php` (nach deinem CPT benannt). Füge dort dann diese Vorlage ein: 
   ```html
   <?php get_header() ?>
   <?php get_template_part('frontend/views/layout/header') ?>
   <?php get_template_part('frontend/views/pages/preset') ?>
   <?php get_template_part('frontend/views/layout/footer') ?>
   <?php get_footer() ?>
   
   ```
   Ändere diese Vorlage so ab, dass in der mittleren Zeile dein vorher erstelltes Template geladen wird.
14. Gehe dann ins Wordpress-Backend zu `Einstellungen/Permalinks` und speichere einmal, ohne etwas zu verändern. Das ist notwendig, damit Wordpress dein neu erstelltes Template korrekt erkennt.
15. Nun kannst du im Backend zu deinem CPT gehen und auf Anzeigen klicken. Du solltest dann eine Seite mit Header & Foooter und dem `mark`-Tag den du eingefügt hast sehen.
16. Was jetzt noch fehlt, ist, dass die Inhalte, die wir im Backend bei unserem CPT angelegt haben, im Frontend angezeigt werden. Dazu erstellen wir uns wieder einen Getter im Ordner [backend/getter](/backend/getter) und nennen ihn `_preset.php` (nach dem CPT).
17. Füge in diese Datei nun diese Vorlage ein:
   ```php
   function get_preset_by_id (int $id): array {
       return [
           'title' => get_the_title($id),
           'description' => carbon_get_post_meta($id, 'description');
       ];
   }
   ```
   Mit der Funktion `carbon_get_post_meta([ID des Posts], 'Slug des Inhaltsfeldes')` kannst du dir nun die Inhalte deines CPT auslesen und in der Funktion zurückgeben lassen.
18. Lade deine erstellte Getter-Datei nun noch in die Datei [backend/getter/index.php](/backend/getter/index.php)
   ```php
   require_once __DIR__ . '/_preset.php';
   ```
19. Jetzt ist deine Getter-Funktion überall verfügbar. Du kannst sie nun in deinem erstellten Page-Template im Ordner [frontend/views/pages](/frontend/views/pages) verwenden und deine Inhalte laden.
   ```php
   <?php
   $preset = get_preset_by_id(get_the_ID());
   ?>
   ```

## ✨ Umsetzung mit KI
Schicke der KI diese Anleitung und gebe ihr folgende Angaben mit:

- **Name** deines CPT
- **Slug** deines CPT
- Alle **Inhaltsfelder** mit Name, Slug und Typ, die dein CPT haben soll

## 📚 Ressourcen
- [Wordpress Custom Post Type](https://developer.wordpress.org/reference/functions/register_post_type/)
- [Wordpress Dashicons](https://developer.wordpress.org/resource/dashicons)
- [Carbon Fields Post Meta](https://docs.carbonfields.net/learn/containers/post-meta.html)
- [Carbon Fields Dokumentation](https://docs.carbonfields.net/)
