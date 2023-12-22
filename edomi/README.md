```
# Hier mal eine erste Plain Text Doku zum Thema EDOMI Oauth2 Server und Alexa Actionable Notification . 
# Wichtig ist, dass als erstes der Reverse Proxy korrekt funktioniert (Schritt 1). 
# Die Einrichtung eines RevProxy habe ich hier nicht mehr detailliert dokumentiert, 
# da dies ja bereits mehrfach im EDOMI Forum gemacht wurde und auch in manchen LBS Dokus enthalten ist. z.B. im 19001201.
#
# Voraussetzungen:
# ================
#    * Amazon Account mit Alexa Echo Device
#    * EDOMI mit funktionsfähigem Alexa-Control-LBS
#    * Erreichbarkeit des EDOMI-Servers aus dem Internet via Reverse Proxy (EDOMI selbst muss nicht erreichbar sein, sondern der apache Webserver)
#
# 1. Reverse-Proxy
#        https://<EDOMI-DNS-Name>/alexa muss aus dem Internet erreichbar sein (gültiges SSL Zertifikat ist notwendig, z.B. letsencrypt)
#         Diese URL muss intern auf /usr/local/edomi/www/admin/include/php/alexa-actions auf dem EDOMI Server verlinkt sein.
#       Zusätzlich muss https://<EDOMI-DNS-Name>/auth auf /usr/local/edomi/www/admin/include/php/alexa-actions/oauth2 verlinkt sein.
#         Dies erreicht man über einen Reverse-Proxy (apache oder nginx) => Detaillierte Anleitungen sind im EDOMI Forum zu finden (z.B. in der Doku zum LBS 19001201)
#
#        Wenn man schon einen funktionsfähigen Rreverse Proxy vor EDOMI hat, dann reichen vermutlich folgende Ergänzungen im RevProxy Konfigurationsfile
#        (gilt nur für apache; bei nginx sieht das anders aber ähnlich aus):
#
#        ProxyPass /auth http://edomi-test.feld.home/admin/include/php/alexa-actions/oauth2
#        ProxyPassReverse /auth http://edomi-test.feld.home/admin/include/php/alexa-actions/oauth2
#        ProxyPass /alexa http://edomi-test.feld.home/admin/include/php/alexa-actions
#        ProxyPassReverse /alexa http://edomi-test.feld.home/admin/include/php/alexa-actions
#
#        Funktion testen:
#        Zuerst legt man auf dem EDOMI Server die notwendigen Verzeichnisse an:
#
mkdir -p /usr/local/edomi/www/admin/include/php/alexa-actions/oauth2
#
#        Dann legt man eine Datei mit dem Namen phpinfo.php im Verzeichnis /usr/local/edomi/www/admin/include/php/alexa-actions an und fügt folgende Zeile ein:
#            <?php phpinfo(); ?>
#        Diese Datei kopiert man auch ins oauth2 Unterverzeichnis.
#          Wenn der Reverse Proxy korrekt konfiguriert ist, sollte man aus dem Internet folgende Seiten aufrufen können
#            https://<EDOMI-DNS-Name>/alexa/phpinfo.php
#            https://<EDOMI-DNS-Name>/auth/phpinfo.php
#        Wenn beides funktioniert, dann läuft der Reverse Proxy korrekt.
#        Erst dann mit Schritt 2 weitermachen!!!
#
# 2. Alexa Custom Skill gemäß https://www.youtube.com/watch?v=uoifhNyEErE anlegen
#
# 3. Spezifische Anpassungen im Alexa Custom Skill für EDOMI:
#         - Name des Skills ist grundsätzlich beliebig. Ich habe ihn "EDOMI Actionable Notification" genannt
#        - Im Build-Tab des Skills sind im Vergelich zum Video folgende Änderungen zu machen:
#            Tools => Account Linking
#                Your Web Authorization URI: https://<EDOMI-DNS-Name>/auth/authorize.php
#                Access Token URI: https://<EDOMI-DNS-Name>/auth/token.php
#                Your Secret: <beliebiges Passwort> (wird auch später bei der OAUTH2 Konfiguration verwendet
#                Your Authentication Scheme: Credentials in request body
#                Scope: smart_home
#                Auf dieser Seite steht auch die Redirect URL: https://layla.amazon.com/api/skill/link/***********
#        - Im Code-Tab des Skills sind im Vergelich zum Video folgende Dinge zu tun :
#
#           Alle Dateien von https://github.com/jonofe/alexa-actions/tree/master/lambda im Code Bereich des Skill hochladen bzw. falls schon vorhanden, anpassen.
#           Dann folgende Änderungen machen:
#
#            in const.py
#                Zeile 9:     INPUT_TEXT_ENTITY = "input_text.alexa_actionable_notification.php"
#
#            in lambda_function.py
#                  Zeile 4:  HOME_ASSISTANT_URL = 'https://<EDOMI-DNS-Name>'
#                Zeile 291:  response = self._get("alexa", INPUT_TEXT_ENTITY)
#                Zeile 322:  response = self._post("rocky", "alexa", "alexa_actionable_notification.php", body=body)
#
#                Man kann optional jedes Vorkommen von "home assistant" in strings ersetzen durch EDOMI. (ändert nichts an der Funktion, bietet aber neues Risiko etwas kaputt zu machen. :))
#            in language_strings.json (optional)
#                Wer mag, kann hier an allen Stellen den String "home assistant" durch EDOMI ersetzen.
#            Alle anderen Dateien können unverändert übernommen werden.
#            Nach Änderungen im Code-Tab müssen diese immer mit "Save" und "Deploy" angewendet werden.
#
# 4. Jetzt sollte der Skill und die Reverse-Proxy Konfiguration fertig sein.                    
#     Der Skill sollte in der Alexa App unter Mehr=>Skills und Spiele=> (ganz runter scrollen) => Meine Skills => (ganz nach rechts scrollen) => Entwickler
#     Es wird allerdings noch "Kontoverknüpfung erforderlich" angezeigt. Diese Verknüpfung kann erst dann gemacht werden, wenn die nun folgenden Installationen/Konfigurationen auf dem EDOMI Server erfolgt sind.
#
# 5. Neue Version 3.1 des Alexa-Control-LBS 19000809 installieren und die im ZIP File enthaltene Datei alexa-actions-for-edomi.tar.gz nach /tmp auf dem EDOMI Server kopieren
#
# 6. Folgende Befehle auf dem EDOMI Server ausführen:
#
mkdir -p /usr/local/edomi/www/admin/include/php/alexa-actions/oauth2
cd /usr/local/edomi/www/admin/include/php/alexa-actions/oauth2
git clone https://github.com/jonofe/oauth2-server-php-EDOMI.git -b master
cp oauth2-server-php/edomi/authorize.php  oauth2-server-php/edomi/oauth.sql oauth2-server-php/edomi/server.php oauth2-server-php/edomi/token.php .
#
# Dateien alexa_actionable_notification.php und input_text.alexa_actionable_notification.php in das Verzeichnis /usr/local/edomi/www/admin/include/php/alexa-actions/ entpacken.
#
cp oauth2-server-php/edomi/alexa_actionable_notification.php oauth2-server-php/edomi/input_text.alexa_actionable_notification.php ..
 #
# OAuth2 DB anlegen
#
mysql -u root -p oauth2 < oauth.sql
mysql -u root -e "insert into oauth2.oauth_scopes (scope) values ('smart_home');"
#
# Im folgenden mysql-Befehl müssen die Werte in <> durch die Werte aus der Account Linking Seite des Skills in der Alexa Developer Console ersetzt werden.
# Als Alexa Redirect URL sollte die URL verwendet werden, welche mit dem String der Client-ID beginnt (siehe Account-Linking Seite im Alexa Developer Portal ganz unten).
# Das Client Secret ist nur bei der Ersteingabe sichtbar und sollte daher bei der Eingabe (s.o) notiert werden, damit es im folgenden Befehl eingegeben werden kann.
# Ist das Client Secret nicht mehr bekannt, dann muss es neu im Alexa Developer Portal (und im folgenden mysql Befehl) gesetzt werden.
#
mysql -u root -e "insert into oauth2.oauth_clients (client_id, client_secret, redirect_uri, scope) values ('<CLIENT-ID>','<CLIENT-SECRET>','<ALEXA_REDIRECT_URL>','smart_home')"
#
# 7. Response iKO anlegen
# In EDOMI unter Konfiguration=>Kommunikationsobjekte ein neues iKO z.B. mit dem Namen "AlexaActionResponse" anlegen und die ID merken/aufschreiben.
# Danach das neu angelegte iKO für den Fernzugriff freigeben (EDOMI Admin=>Konfiguration=>Fernzugriff).
#
# 8. Datei alexa_actionable_notification.php anpassen:
#
nano /usr/local/edomi/www/admin/include/php/alexa-actions/alexa_actionable_notification.php
#    Zeile 2: hier die lokale Edomi-URL eintragen. Eigentlich sollte hier 'http://localhost' ausreichen
#    Zeile 3-5: Den EDOMI Remote User, Remote Passwort und die iKO-ID aus Schritt 7 eintragen
#
# 9. Skill ID im Alexa Control LBS eintragen
#     Alexa developer console aufrufen und auf der Übersichtsseite aller Skill, beim neue angelegten Skill auf "Skill-ID kopieren" klicken. (Auch im Video beschrieben)
#     Diese ID kann dann an E56 des LBS eingetragen werden.
#     Auf der Seite des Alexa Control LBS fügt  man am besten noch eine Klemme ein, an der man dann an E1 das AlexaActionResponse iKO (s.o.) verknüpft.
#     Damit kann man beim Testen (Schritt 12) direkt sehen, ob eine Antwort angekommen ist.
#
# 10. EDOMI Projekt aktivieren
#
# 11. Kontoverknüpfung in der ALexa App
#      Alexa App auf dem telefon starten => Mehr => Skills und Spiele => Meine/Ihre Skills => (nach rechts wischen) => Entwickler
#      Auf den neuen Skill "EDOMI Actionable Notification" klicken.
#      "Zur Verwendung aktivieren" klicken.
#      Nun erscheint eine Login Maske, in der man sich mit der EDOMI Kennung anmeldet. (Default bei EDOMI: admin/admin, falls das nicht schon geändert wurde)
#      Danach erscheint eine Seite, ob man den Skill autorisieren will. Dort auf YES klicken.
#      Danach sollte die Bestätigung kommen, dass die Verknüpfung mit dem Konto erfolgreich verlaufen ist.
#
# 12. Damit sollte die Installation beendet sein und man kann über den Eingang E55 einen ersten Test machen.
#      Dazu an E55 in der Liveansicht z.B. folgenden String eingeben:
#
#        LichtID|Soll ich das Licht ausmachen?
#
#      Wenn alles korrekt installiert wurde, dann sollte der Echo, für den der Alexa Control LBS konfiguriert ist, die Frage stellen.
#      Man kann nun mit Ja, Nein oder gar nicht antworten (timeout).
#       Danach sollte das Ergebnis (d.h. die gegebene Antwort) im AlexaActionResponse iKO stehen, welches durch drücken des Buttons "Liveansicht 1" an der oben eingefügten Klemme sichtbar sein sollte.
#
# 13. Logfiles für die Fehlersuche:
#
#        Alexa Developer Console: => Skill => Code => CloudWatch Logs (richtige Zone wählen! Die Zone in der der Skill deployed wurde)
#        RevProxy: Logs des Webservers, Apache: /var/log/apache2/access.log und /var/log/apache2/error.log
#        EDOMI Webserver: /var/log/https/access.log und /var/log/https/error.log
#        EDOMI LBS: CUSTOMLOG_Alexa_Actions.log und CUSTOMLOG_Alexa_Control-LBS19000809-<ID>.<log|htm>
#
```
``
