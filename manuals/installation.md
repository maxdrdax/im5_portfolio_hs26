# Wordpress & Entwicklungsumgebung `aufsetzen`
> In dieser Anleitung wird schriftlich beschrieben, wie man Wordpress über Hostpoint installiert und sich eine professionelle Entwicklungsumgebung aufsetzt.

## 📽️ Video-Anleitung
[https://youtu.be/hf16bk_F_zs](https://youtu.be/hf16bk_F_zs)

## ⚒️ Schritt-für-Schritt-Anleitung
1. Logge dich in Hostpoint ein und bereite dir eine Domain oder Subdomain vor, die du für dein Projekt verwenden möchtest. Das kannst du in Wordpress im Tab `Domains` machen.
   1. **Subdomain**: Klicke auf eine schon bestehende Domain und füge dann mit `Subdomain konfigurieren` eine neue Subdomain hinzu.
   2. **Neue Domain**: Kaufe dir über den Shop die entsprechende Domain und füge sie deinem Hosting hinzu.
2. Gehe im Hostpoint auf die Server-Übersicht und klicke dort in der linken Leiste auf `Websites>One-Klick Apps`.
3. Wähle `Wordpress` aus und klicke auf `Installieren`.
4. Wähle deinen Domainnamen aus und klicke bei `Login-Daten` auf  `Eigene Login-Daten` und vergib diese. Die restlichen Einstellungen kannst du so belassen. Klicke am Schluss auf `Installieren`.
5. Warte nun wenn möglich mind. 15 Minuten, bis du weiterfährst. So kann die Installation komplett abgeschlossen werden.
6. Logge dich im Backend ein unter `deinedomain.ch/wp-login.php` und schaue, ob das funktioniert.
7. Öffne FileZilla und verbinde dich dort mit deinem Server (vermutlich hast du das schon mal gemacht). Gehe nun auf deiner neu erstellten Webseite zu folgendem Ordner: `deineurl.ch/wp-content/themes/`
8. Erstelle dort einen neuen Ordner mit dem Namen `portfolio`
9. Gehe auf github.com, logge dich ein und erstelle ein neues Repository. Das nennst du am besten `portfolio`.
10. Öffne nun die GitHub Desktop App auf deinem PC und gehe zu `File>Clone Repository`. Wähle dort im Reiter oben `Github.com` aus und wähle dein neu erstelltes Repository aus. Definiere unter `Local Path` wo dein Portfolio-Code auf deinem PC gespeichert werden soll.
11. Öffne diesen Ordner nun mit PHP-Storm (oder einer IDE deiner Wahl).
12. Erstelle eine `.gitignore` Datei mit folgendem Inhalt: 
    ```gitignore
    .idea
    ```
13. Erstelle eine `index.php` Datei mit folgendem Inhalt:
    ```php
    <h1>Portfolio von [Dein Name]</h1>
    ```
14. Erstelle eine `style.css` Datei mit folgendem Inhalt:
    ```css
    /**
    * Theme Name: Portfolio von [Dein Name]
    */
    ```
15. Gehe nun auf Hsostpoint und wähle in der linken Leiste `Advanced>FTP` aus. Klicke dort auf `FTP-Account hinzufügen`.
16. Vergib einen Usernamen und ein Passwort. Beim Pfad gibst du Folgendes ein: `www/deineurl.ch/wp-content/themes/portfolio`. Klicke auf `Erstellen`. Lasse dieses Browserfenster offen.
17. Wechsle nun zu PHP-Storm und öffne `Tools>Deployment>Configuration`. Klicke links oben auf `+` und wähle `FTP` aus. Gib einen Namen für die Verbindung ein und fülle die Felder `Host`, `Username` und `Password` aus. Klicke zuerst auf `Text Connection` und schaue, ob das funktioniert.
18. Wechsle oben nun in den Tab `Mappings` und gibt bei `Deployment path` einen `/` ein. Wenn du dann unten einen Fehler bekommst, klicke auf `Fix` und lösche in der erscheinenden Liste einfach den untersten Eintrag. Klicke abschliessend auf `Ok`.
19. Gehe nun in PHP-Storm zu `Tools>Deployment>Options` und wähle bei `Upload changed files automatically to default server` die Option `On explicit save action` aus. 
20. Wähle nun die zwei von dir erstellten Dateien `index.php` und `style.css` an und mache einen Rechtsklick. Fast zuunterst findest du einen Punkt der heisst `Upload to [Servername]`. Wähle das aus. 
21. Schau nun in FileZilla nach, ob die Dateien hochgeladen wurden.
22. Aktiviere im Wordpress-Backend dein neues Theme.
