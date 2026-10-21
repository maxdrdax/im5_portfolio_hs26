# Ein neues `Menu` hinzufügen
> In dieser Anleitung wird erklärt, wie in diesem Theme ein neues Menu erstellt und in einem Template (z.B. dem Header oder Footer) verwendet werden kann.

## 📽️ Video-Anleitung
[https://youtu.be/dgPxCSRMBUk](https://youtu.be/dgPxCSRMBUk)

## 📑 Beispieldaten
In dieser Anleitung wird beispielhaft ein Menu mit folgenden Angaben hinzugefügt:

- **Name**: Hauptnavigation
- **Slug**: main

## ⚒️ Schritt-für-Schritt-Anleitung
1. In der Datei [backend/nav/index.php](/backend/nav/index.php) eine Zeile mit `Slug` und `Name` hinzufügen:
    ```php
    add_filter('init', function () {
        register_nav_menus([
            'main' => 'Hauptnavigation', // diese Zeile wurde hinzugefügt
        ]);
    });
    ```
2. Nun im Backend unter `Design > Menus` ein Menu hinzufügen, dem neu registrierten Menu zuweisen und beliebig mit Navigationspunkten befüllen.
3. Jetzt kann die Navigation in einem beliebigen View-Template verwendet werden. Dafür das entsprechende View-Template öffnen (z.B. [frontend/views/layout/header.php](/frontend/views/layout/header.php)) und in eine Variable mit beliebigen Namen mit der Helper-Funktion `get_menu_by_slug()` die Navigations-Einträge als Array laden.
   ```php
   $menu_items = get_menu_by_slug('main');
   ```

## ✨ Umsetzung mit KI
Schicke der KI diese Anleitung und gebe ihr folgende Angaben mit: 

- **Name** des Menus
- **Slug** des Menus
- Pfad zum **View-Template**, wo das Menu angezeigt werden soll

## 📚 Ressourcen
- [WordPress Menus](https://developer.wordpress.org/reference/functions/register_nav_menus/)
