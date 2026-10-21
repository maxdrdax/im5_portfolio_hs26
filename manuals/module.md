# Einen neues `Modul` hinzufügen
> In dieser Anleitung wird erklärt, wie man ein neues Modul in den Modulbaukasten hinzufügt.

## 📽️ Video-Anleitung
[https://youtu.be/QcPuMNu-aw8](https://youtu.be/QcPuMNu-aw8)

## 📑 Beispieldaten
In dieser Anleitung wird beispielhaft ein Modul mit diesen Angaben erstellt:

- **Modul-Name**: Hero Startseite
- **Modul-Slug**: hero_front
- **Inhaltsfeld 1 (Name, Slug, Typ)**: Titel, title, text
- **Inhaltsfeld 2 (Name, Slug, Typ)**: Bild, image, image

## ⚒️ Schritt-für-Schritt-Anleitung
1. Zuerst wird die Datei [backend/carbonfields/_modules.php](/backend/carbonfields/_modules.php) geöffnet.
2. Dort werden nach der Zeile `Field::make('complex', 'modules', 'Module')` die einzelnen Module registriert. Das kann dann so aussehen
   ```php
   // -> hero_front
   ->add_fields('hero_front', 'Hero Startseite', [
       Field::make('image', 'image', 'Bild'),
       Field::make('text', 'title', 'Titel'),
   ])
   ```
   Es wird zuerst mit `add_fields` das neue Modul erstellt und dann mit `Field::make` die einzelnen Inhaltsfelder hinzugefügt.
3. Die Konfiguration kann direkt auch im Backend getestet werden. Dafür auf eine Seite gehen, das neue Modul hinzufügen und schauen, ob die Konfiguration richtig aussieht. Wenn ja, gerne das Modul einmal mit Testinhalt befüllen.
4. Als nächstes können in der selben Datei, also immer noch [backend/carbonfields/_modules.php](/backend/carbonfields/_modules.php) etwas weiter unten, die Moduldaten modifiziert werden. Das ist relevant, wenn ein Inhaltsfeld nicht genau das zurück gibt, was im View-Template gebraucht wird. Das ist beispielsweise bei Bildern der Fall, dort wird jeweils nur die ID des Bildes zurückgegeben, wir brauchen jedoch die URL des Bildes und den Alt-Tag. In diesem Fall wäre ein Modifizieren der Moduldaten notwendig.
5. Falls die Daten modifiziert werden müssen, kann eine neue Case-Anweisung hinzugefügt werden, beispielsweise so:
   ```php
   case 'hero_front':
       $module['image'] = get_media_by_id($module['image'], 'full');
       break;
   ```
   In diesem Beispiel wird anhand der ID des Bildes mit der Helper-Funktion `get_media_by_id()` die Bild-URL und der Alt-Tag zurückgegeben.
6. Jetzt fehlt nur noch das View-Template. Das muss im Ordner [frontend/views/modules](/frontend/views/modules) erstellt werden. Dafür kann diese Vorlage als Grundlage genutzt werden:
   ```php
   <?php
   $data = $args['data'];
   ?>
   <section data-module="hero-front">
       <mark>Hier kommt der Modulinhalt</mark>
   </section>
   ```
7. In der Variabel `$data` sind nun alle Informationen, die in der Konfiguration des Moduls definiert wurden und im Backend befüllt wurden, gespeichert und können im View-Template verwendet werden. Beispielsweise so:
   ```php
   <section data-module="hero-front">
       <img class="image" src="<?= $data['image']['src'] ?>" alt="<?= $data['image']['alt'] ?>">
       <h1 class="title"><?= $data['title'] ?></h1>
   </section>
   ```
8. Wenn nun die Seite, auf der das Modul im Backend erfasst wurde, angezeigt wird, wird das neue Modul angezeigt. Das Modul kann nun auf jeder Seite in deinem Theme verwendet werden.

## ✨ Umsetzung mit KI
Schicke der KI diese Anleitung und gebe ihr folgende Angaben mit:

- **Name** deines Modules
- **Slug** deines Moduoes
- Alle **Inhaltsfelder** mit Name, Slug und Typ, aus denen dein Modul bestehen soll

## 📚 Ressourcen
- [Carbon Fields Dokumentation](https://docs.carbonfields.net/)
