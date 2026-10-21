# Eine neue `Taxonomie` hinzufügen
> In dieser Anleitung wird erklärt, wie in diesem Theme eine neue Taxonomie hinzugefügt wird und in einem View-Template verwendet wird.

## 📽️ Video-Anleitung
[https://youtu.be/bciKU3UrTI8](https://youtu.be/bciKU3UrTI8)

## 📑 Beispieldaten
In dieser Anleitung wird beispielhaft eine Taxonomie mit folgenden Angaben hinzugefügt:

- **Name**: Abteilung
- **Slug**: person_department
- **Gehört zum Post Type**: person

## ⚒️ Schritt-für-Schritt-Anleitung
**Wichtig**: Bevor du eine Taxonomie erstellst, solltest du den CPT, dem du die Taxonomie zuweisen möchtest, schon erstellt haben.

1. Als Erstes muss im Ordner [backend/tax] eine neue Datei für die Konfiguration der Taxonomie erstellt werden, die `_person_department.php` heisst (abgeleitet aus dem Slug deiner Taxonomie).
2. Füge in diese Datei diese Vorlage einer CPT-Konfiguration ein:
   ```php
   <?php
   register_taxonomy(
     'person_department',
     ['person'],
     [
       'label' => 'Abteilung',
       'labels' => [
         'name' => 'Abteilungen',
         'singular_name' => 'Abteilung',
         'menu_name' => 'Abteilungen',
         'all_items' => 'Alle Abteilungen',
         'edit_item' => 'Abteilung bearbeiten',
         'view_item' => 'Abteilung ansehen',
         'update_item' => 'Abteilung updaten',
         'add_new_item' => 'Neue Abteilung hinzufügen',
       ],
       'public' => true,
       'show_in_rest' => true,
       'show_in_nav_menus' => true,
       'show_admin_column' => true,
       'query_var' => true,
       'hierarchical' => true,
       'rewrite' => ['slug' => 'abteilung'],
     ],
   );
   
   ```
   Passe die Werte in `labels`, `label` und unter `rewrite>slug` auf deine Taxonomie an. 
3. Wechsle in die Datei [backend/tax/index.php](/backend/cpt/index.php) und lade dort die Datei, in der du deine Taxonomie registriert hast.
   ```php
   require_once __DIR__ . '/_person_department.php';
   ```
4. Wenn du nun ins Backend wechselst, solltest du deinen Einträgen, denen du diese Taxonomie zugewiesen hast (in dem Beispiel den Einträgen vom CPT `person`) die entsprechende Taxonomie geben können. Füge deinen im Backend Einträgen die entsprechenden Taxonomien hinzu.
5. Es gibt viele verschiedene Arten, diese Taxonomien nun einzusetzen, beispielsweise um Filter zu bauen oder um die Taxonomie in einem View-Template anzuzeigen. Wir schauen uns an, wie wir die Taxonomie anzeigen können. 
6. Suche im Ordner [backend/getter](/backend/getter) nach dem Getter deines CPT. Dort kannst du mit diesem Code-Snippet die dem einen Eintrag zugewiesenen Taxonomien auslesen:
   ```php
   $departments = array_map(function ($department) {
       return $department->name;
   } ,get_the_terms($id, 'person_department'));
   ```
7. Dieses Array mit allen Taxonomien kann nun im Getter im return zurückgegeben werden und dann wie jede andere Information im View-Template angezeigt werden.
   ```php
   return [
       'title' => get_the_title($id),
       'departments' => $departments,
   ];
   ```

## ✨ Umsetzung mit KI
Schicke der KI diese Anleitung und gebe ihr folgende Angaben mit: 

- **Name** der Taxonomie
- **Slug** der Taxonomie
- **Slug vom CPT**, zu welchem die Taxonomie gehört
- **Anweisungen**, wie die Taxonomie verarbeitet werden soll

## 📚 Ressourcen
- [WordPress Taxonomy](https://developer.wordpress.org/reference/functions/register_taxonomy/)
- [get_the_terms()](https://developer.wordpress.org/reference/functions/get_the_terms/)
