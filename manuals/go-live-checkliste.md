# `Go Live` Checkliste

##  Allgemeine Checkliste
> **Legende**
> - 🔴 must
> - 🟠 should
> - 🟢 could

### SEO
- [ ] 🟠 Ich habe [Yoast SEO](#yoast-seo) installiert, aktiviert und konfiguriert.
- [ ] 🔴 Ich erlaube in den Einstellungen das Crawlen der Webseite (Einstellungen > Lesen).
- [ ] 🔴 Ich habe eine korrekte robots.txt Datei. 
- [ ] 🟠 Ich habe eine korrekte sitemap.xml Datei.
- [ ] 🟢 Ich habe eine korrekte llms.txt Datei.

### Security
- [ ] 🔴 Die Webseite hat HTTPS aktiviert.
- [ ] 🔴 Ich habe [All in One WP Security](#all-in-one-wp-security) installiert und konfiguriert.
- [ ] 🟠 Ich habe alle Plugins, die ich nicht brauche, gelöscht.
- [ ] 🟠 Ich habe alle Themes, die ich nicht brauche, gelöscht.
- [ ] 🔴 Ich habe nur Nutzer:innen in meinem Backend, welche wirklich auch Zugang dazu brauchen. 
- [ ] 🟠 Ich habe [Updraft-Plus](#updraft-plus) installiert und konfiguriert.

### Performance
- [ ] 🟠 Ich habe mit Google Lighthouse einen Performance-Check durchgeführt und wo nötig Optimierungen vorgenommen.

### Rechtliches
- [ ] 🔴 Die Webseite hat ein korrektes Impressum. [Mehr zur Impressumspflicht](https://www.hostpoint.ch/blog/impressumspflicht-f%C3%BCr-websites/)
- [ ] 🔴 Die Webseite hat eine korrekte Datenschutzpage, welche in den Einstellungen verlinkt ist. [Mehr zur Datenschutzerklärung](https://www.edoeb.admin.ch/de/datenschutzerklaerungen-im-internet)
- [ ] 🔴 Die Webseite hat, falls nötig, einen Cookie-Banner installiert. [Mehr zu Cookie-Bannern](https://www.hostpoint.ch/blog/das-neue-dsg-und-der-cookie-banner-wirrwarr-was-gilt-denn-nun-eigentlich/)

### Allgemeines
- [ ] 🟢 Das Projekt liegt auf GitHub.
- [ ] 🟠 Das Projekt hat ein Favicon.
- [ ] 🟠 Auto-Updates für Plugins sind aktiviert oder ein Update-Zyklus ist definiert und geplant.

### Final Touches
- [ ] 🔴 Basic-Auth ist entfernt.
- [ ] 🟢 Ich habe den [Uptime-Robot](#uptime-robot) installiert und meine Webseite hinzugefügt.

## Checkliste pro Tools

### Yoast SEO
> [Yoast SEO](https://wordpress.org/plugins/wordpress-seo/)

- [ ] Installieren (Suchbegriff: Yoast SEO)
- [ ] Yoast AI deaktivieren (Einstellungen)
- [ ] Website-Grundlagen ausfüllen (v.a. Bild)
- [ ] Umarbeitung der Titel erzwingen > aktivieren
- [ ] Zeige Projektkategorien in den Suchergebnissen > deaktivieren, weil keine Archiv-Pages
- [ ] autoren-Archive, Datums-Archive & Format-Archive deaktivieren
- [ ] Seiten: SEO-Titel & Beschreibung
- [ ] Projekte: SEO-Titel & Beschreibung
- [ ] robots.txt checken
- [ ] sitemap.xml checken
- [ ] llms.txt aktivieren & checken

### All in One WP Security
> [All-In-One Security (AIOS)](https://wordpress.org/plugins/all-in-one-wp-security-and-firewall/)

- [ ] Plugin Installieren (Suchbegriff: All-In-One Security)
- [ ] Erst-Konfiguration überspringen
- [ ] Settings > .htaccess file > `Backup .htaccess file`
- [ ] Settings > wp-config.php file > `Backup wp-config.php file`
- [ ] Settings > WP version info > **Aktivieren**
- [ ] User Security > User Accounts > **User `admin` umbenennen wenn der existiert**
- [ ] User Security > User Accounts > **Display-Name soll nicht Login-Name sein**
- [ ] User Security > User Accounts > Prevent User enumeration > **Aktivieren**
- [ ] User Security > User Accounts > Enforce strong password > **Aktivieren**
- [ ] User Security > Login Lockout > **Aktivieren**
- [ ] User Security > Force Logout > **Aktivieren**
- [ ] Database-Security > Database Prefix > **Change database prefix**
- [ ] File Security > File Permissions > **WP-Config Permission setzen**
- [ ] File Security > File Protections > Delete WP default files > **Aktivieren**
- [ ] File Security > File Protections > Prevent hotlinking > **Aktivieren**
- [ ] File Security > File Protections > Disable PHP file editing > **Aktivieren**
- [ ] Firewall > PHP Rules > Security enhancements > **Aktivieren**
- [ ] Firewall > PHP Rules > Comment Protection > **Aktivieren**
- [ ] Firewall > PHP Rules > URL security > **Aktivieren**
- [ ] Firewall > PHP Rules > String filtering > **Aktivieren**
- [ ] Firewall > PHP Rules > nG firewall rules > **Aktivieren**
- [ ] Firewall > PHP Rules > Internet Bot Settings > **Aktivieren**
- [ ] Firewall > .htaccess rules > Basic Firewall settings > **Aktivieren**
- [ ] Firewall > .htaccess rules > Block debug log > **Aktivieren**
- [ ] Firewall > .htaccess rules > Listing directory content > **Aktivieren**
- [ ] Brute Force > Rename Login Page > **Aktivieren und neuer Pfad notieren**
- [ ] Brute Force > Honey Pot > **Beide aktivieren**

### Updraft-Plus
> [Updraft-Plus](https://wordpress.org/plugins/updraftplus/)

- [ ] Installieren (Suchbegriff: UpdraftPlus)
- [ ] Settings > Definieren, wie oft gebackupt werden soll
- [ ] FTP als `Storage Location` auswählen
- [ ] Folder für die Backups mit FileZilla erstellen
- [ ] Angaben zum FTP-Account machen
- [ ] Alle Einstellungen so lassen
- [ ] Backup Now durchführen
- [ ] Mit FileZilla den Server prüfen

### Uptime Robot
> [Uptime Robot](https://uptimerobot.com/)

- [ ] Account über GitHub erstellen
- [ ] Monitoring für die Webseite einrichten (nachdem das Basic Auth entfernt wurde)

### Kansleri Cookie Consent
> [Kansleri Cookie Consent](https://wordpress.org/plugins/kansleri-cookie-consent/)

🚨 Nur installieren, wenn wirklich nötig.

- [ ] Installieren (Suchbegriff: Kansleri Cookie Consent)
- [ ] Einstellungen > Cookie Consent
- [ ] Style selber definieren
- [ ] Texte übersetzen
- [ ] Empty Cookie Categories > **Hide... auswählen**
- [ ] Floating settings button > **Deaktivieren**
- [ ] In text.php Modul das hier `<?= wpautop($data['text']); ?>` mit diesem Code ersetzen:
  `<?= apply_filters('the_content',$data['text']); ?>`
- [ ] Shortcode auf der Datenschutz-Page einfügen
- [ ] Cookies > **Scann cookies**
- [ ] Cookies **Save cookies**
- [ ] Policy Page > **Datenschutz-Seite verlinken**
