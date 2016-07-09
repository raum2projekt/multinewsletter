<h1>MultiNewsletter Changelog</h1>

<p>2.2.4:</p>
<ul>
	<li>Bugfix: Newsletter nur an aktive Nutzer mailen.</li>
</ul>
<p>2.2.3:</p>
<ul>
	<li>Nutzer mit GruppenID NULL werden in Liste der Benutzer ohne Gruppe angezeigt.</li>
	<li>E-Mailadressen konnten mit Leerzeichen gespeichert werden und haben so den Versand blockiert.</li>
	<li>Auf Bootstrap basierendes Beispieltemplate hinzugefügt.</li>
</ul>
<p>2.2.2:</p>
<ul>
	<li>HTMLBody im Archiv nun auch vom Typ Longtext sein.</li>
	<li>Anzahl Benutzer anzeigen.</li>
</ul>
<p>2.2.1:</p>
<ul>
	<li>Funktion MultiNewsletterUser:initByMail() gibt jetzt bei nicht
	gefundener E-Mailadresse korrekt false zurück.</li>
	<li>Anmeldung und Abmeldung verbessert.</li>
	<li>Platzhalter für Links beginnen und enden jetzt mit +++.</li>
</ul>
<p>2.2:</p>
<ul>
	<li>Bessere Dokumentation für Aktivierungslink. Thx @missmissr</li>
	<li>Vor Import werden unnötige Leerzeichen entfernt. Thx @missmissr</li>
	<li>Speichert jetzt auch IPv6 Adressen.</li>
</ul>
<p>2.1.1:</p>
<ul>
	<li>Fallbacksprache in den Einstellungen hatte Fehler wenn nur eine Sprache
		vorhanden war. Thx @missmissr</li>
</ul>
<p>2.1.0:</p>
<ul>
	<li>Auswahl Aktion beim Abmelden: Status auf Löschen oder Abgemeldet setzbar.</li>
	<li>Möglichkeit einer Benachrichtigung per Mail bei bei An- und Abmeldung.</li>
</ul>
<p>2.0.0:</p>
<ul>
	<li>Anpassungen an Redaxo 4.6: Einstellungen werden jetzt im data Verzeichnis gespeichert.</li>
	<li>Übersetzungen können jetzt ohne FTP in den Einstellungen bearbeitet werden.</li>
	<li>Minimal PHP 5.2; Notices entfernt.</li>
	<li>GUI Anpassungen.</li>
	<li>Bestätigungsmail bei Anmeldung</li>
	<li>Benutzerverwaltung: Suchkriterien bleiben beim Verlassen des Formulars erhalten</li>
	<li>Vorbelegungen für Absender bei Gruppen möglich.</li>
	<li>Vorbelegung für Testmails einstellbar</li>
	<li>BCC Versand von Newslettern entfernt.</li>
	<li>Bugfixing</li>
	<li>Objektorientierte Programmierung.</li>
	<li>Versand an mehrere Gruppen gleichzeitig möglich.</li>
	<li>Viele Kleinigkeiten mehr.</li>
</ul>
<p>1.4.2:</p>
<ul>
	<li>Mehrfache Anmeldung wird geblockt (Danke Frood)</li>
</ul>
<p>1.4.1:</p>
<ul>
	<li>Anrede wird korrekt gesetzt.</li>
	<li>Fehler wiederholtes Datenbankupdate wird bei leerer Tabelle behoben.</li>
</ul>
<p>1.4:</p>
<ul>
	<li>Akademischer Grad als zweiten Titel hinzugf&uuml;gt (Danke "steri").</li>
	<li>Probleme mit RexSEO und leading Slash behoben ("/" nach Domainnamen).</li>
</ul>
<p>1.3:</p>
<ul>
	<li>CSV Import hat nun die M&ouml;glichkeit eine Aktion auszuw&auml;hlen: l&ouml;schen, nur neue hinzuf&uuml;gen, &uuml;berschreiben.</li>
	<li>Module werden bei der Installation in Redaxo installiert.</li>
</ul>
<p>1.2.5 Bugfixing:</p>
<ul>
	<li>Hilfe f&uuml;r Sprache de_de aktualisiert.</li>
	<li>Fehler bei Personalisierung der Anrede bei Mehrsprachigkeit behoben.</li>
	<li>GruppenID kann in Spalte send_group im Import mit angegeben werden.</li>
</ul>
<p>1.2.4 Bugfixing:<br />
<ul>
	<li>Mehrfache Anmeldung wird geblockt (Danke Frood)</li>
</ul>
	- Lang Fallback Fehler behoben.<br />
<p>1.2.3 Bugfixing:<br />
<ul>
	<li>Kompatibilit&auml;t zu Redaxo 4.1 im Ausgabemodul hergestellt.</li>
</ul>
<p>1.2.2 Bugfixing:<br />
<ul>
	<li>Falls max_execution_time in der PHP Config auf 0 gesetzt wird, wird jetzt ein Standardwert gesetzt.</li>
</ul>
<p>1.2.1 Bugfixing:<br />
<ul>
	<li>Fehler im Abmeldungsmodul behoben</li>
</ul>
<p>1.2:<br />
<ul>
	<li>Usertracking verbessert. Gespeichert wird jetzt: Erstellungsdatum und -IP,
		Aktivierungsdatum und -IP, Updatedatum und IP sowie Anmeldeart (Web, Import, Backend)</li>
	<li>Bugfix: ab jetzt k&ouml;nnen auf Mac erzeugte Import CSV Dateien importiert werden.</li>
</ul>
<p>1.1.7 Bugfixing und mehr:<br />
<ul>
	<li>Nach Abmeldung funktioniert die erneute Anmeldung jetzt wieder.</li>
	<li>Es werden keine Abmeldebest&auml;tigungsmails mehr verschickt. Die Abmeldung erfolgt immer sofort.</li>
	<li>Sprachdatei de_de.lang hinzugef&uuml;gt</li>
	<li>kleinen &Uuml;bersetzungsfehler behoben</li>
	<li>In Benutzer&uuml;bersicht Erstellungs- und Aktualisierungsdatum hinzugef&uuml;gt</li>
</ul>
<p>1.1.6 Bugfixing:<br />
<ul>
	<li>Verzeichnis Module enth&auml;lt jetzt die Module f&uuml;r Anmeldung und Abmeldung</li>
	<li>Nutzt jetzt rex_mailer Klasse des PHP Mailer Addons (bisher PHPMailer Klasse).</li>
</ul>
<p>1.1.5 Bugfixing:<br />
<ul>
	<li>Weiteres PHP short_open_tag entfernt.</li>
	<li>Nutzt jetzt einheitlich PHPMailer Addon.</li>
</ul>
<p>1.1.4 Bugfixing:<br />
<ul>
	<li>&Uuml;bersetzungsfehler bei invalid_email behoben.</li>
	<li>Meldung wenn E-Mailadresse schon angemeldet ist.</li>
	<li>Backend PHP Einstellung short_open_tag = Off kompatibel.</li>
</ul>
<p>1.1.3 Bugfixing:<br />
<ul>
	<li>Link der Best&auml;tigungsmail wurde im Frontend angezeigt. Jetzt entfernt.</li>
</ul>
<p>1.1.2 Bugfixing:</p>
<ul>
	<li>Erkennt und l&ouml;scht ung&uuml;ltige E-Mailadressen w&auml;hrend dem Newsletterversand.</li>
	<li>peichert bei neuen Benutzern auch die Gruppen.</li>
	<li>Fehler beim Speichern von neuen Benutzern ohne Namen behoben</li>
	<li>Wenn w&auml;hrend dem Newsletterversand benutzer ge&auml;ndert werden, wird Versand nicht mehr unterbrochen.</li>
	<li>Sprachfallback Fehler beim Versenden behoben.</li>
</ul>
<p>1.1</p>
<ul>
	<li>Serverlimits und Sprachfallback</li>
</ul>
<p>1.0 Ver&ouml;ffentlicht am 20. August 2008 von <a href="http://thomasgoellner.de/" target="_blank">Thomas G&ouml;llner</a>.
	Spätere	Weiterentwicklung von <a href="http://www.design-to-use.de" target="blank">Tobias Krais</a>.</p>
