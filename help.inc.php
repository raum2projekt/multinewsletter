<?php

/**
 * MultiNewsletter Addon
 *  
 * @author post[at]thomasgoellner[dot]de Thomas Goellner
 * @author <a href="http://www.thomasgoellner.de">www.thomasgoellner.de</a>
 * 
 * @package redaxo4
 */

?>
<strong>MultiNewsletter Addon</strong><br />
<br /><br />
              <p>ToDos:<br />
					- Versand an mehrere Gruppen gleichzeitig
					- Abmeldelink
					- Anmelde und Abmeldeformular mit XForm
					- Abmeldung löscht Benutzer aus DB
		<p>1.4.3:<br />
					- Anpassungen an Redaxo 4.6.x.<br />
		<p>1.4.2:<br />
					- Mehrfache Anmeldung wird geblockt (Danke Frood).<br />
		<p>1.4.1:<br />
					- Anrede wird korrekt gesetzt.<br />
					- Fehler wiederholtes Datenbankupdate wird bei leerer Tabelle behoben.<br />
              	<p>1.4:<br />
					- Akademischer Grad als zweiten Titel hinzugf&uuml;gt (Danke "steri").<br />
					- Probleme mit RexSEO und leading Slash behoben ("/" nach Domainnamen).<br />
              	<p>1.3:<br />
					- CSV Import hat nun die M&ouml;glichkeit eine Aktion auszuw&auml;hlen: l&ouml;schen, nur neue hinzuf&uuml;gen, &uuml;berschreiben.<br />
					- Module werden bei der Installation in Redaxo installiert.<br />
              	<p>1.2.5 Bugfixing:<br />
					- Hilfe f&uuml;r Sprache de_de aktualisiert.<br />
					- Fehler bei Personalisierung der Anrede bei Mehrsprachigkeit behoben.<br />
					- GruppenID kann in Spalte send_group im Import mit angegeben werden.<br />
        	<p>1.2.4 Bugfixing:<br />
					- Lang Fallback Fehler behoben.<br />
              	<p>1.2.3 Bugfixing:<br />
					- Kompatibilit&auml;t zu Redaxo 4.1 im Ausgabemodul hergestellt.<br />
              	<p>1.2.2 Bugfixing:<br />
					- Falls max_execution_time in der PHP Config auf 0 gesetzt wird, wird jetzt ein Standardwert gesetzt.<br />
              	<p>1.2.1 Bugfixing:<br />
					- Fehler im Abmeldungsmodul behoben<br />
              	<p>1.2:<br />
					- Usertracking verbessert. Gespeichert wird jetzt: Erstellungsdatum und -IP,
						Aktivierungsdatum und -IP, Updatedatum und IP sowie Anmeldeart (Web, Import, Backend)<br />
					- Bugfix: ab jetzt k&ouml;nnen auf Mac erzeugte Import CSV Dateien importiert werden.<br />
              	<p>1.1.7 Bugfixing und mehr:<br />
					- Nach Abmeldung funktioniert die erneute Anmeldung jetzt wieder.<br />
					- Es werden keine Abmeldebest&auml;tigungsmails mehr verschickt. Die Abmeldung erfolgt immer sofort.<br />
					- Sprachdatei de_de.lang hinzugef&uuml;gt<br />
					- kleinen &Uuml;bersetzungsfehler behoben<br />
					- In Benutzer&uuml;bersicht Erstellungs- und Aktualisierungsdatum hinzugef&uuml;gt<br />
              	<p>1.1.6 Bugfixing:<br />
					- Verzeichnis Module enth&auml;lt jetzt die Module f&uuml;r Anmeldung und Abmeldung<br />
					- Nutzt jetzt rex_mailer Klasse des PHP Mailer Addons (bisher PHPMailer Klasse).<br />
              	<p>1.1.5 Bugfixing:<br />
					- Weiteres PHP short_open_tag entfernt.<br />
					- Nutzt jetzt einheitlich PHPMailer Addon.<br />
              	<p>1.1.4 Bugfixing:<br />
					- &Uuml;bersetzungsfehler bei invalid_email behoben.<br />
					- Meldung wenn E-Mailadresse schon angemeldet ist.<br />
					- Backend PHP Einstellung short_open_tag = Off kompatibel.<br />
              	<p>1.1.3 Bugfixing:<br />
					- Link der Best&auml;tigungsmail wurde im Internet angezeigt. Jetzt entfernt.<br />
              	<p>1.1.2 Bugfixing:<br />
					- Erkennt und l&ouml;scht ung&uuml;ltige E-Mailadressen w&auml;hrend dem Newsletterversand.<br />
					- Speichert bei neuen Benutzern auch die Gruppen.<br />
					- Fehler beim Speichern von neuen Benutzern ohne Namen behoben<br />
					- Wenn w&auml;hrend dem Newsletterversand benutzer ge&auml;ndert werden, wird Versand nicht mehr unterbrochen.<br />
					- Sprachfallback Fehler beim Versenden behoben.</p>
              	<p>1.1 mit Serverlimits und Sprachfallback am 13. Mai 2012 von Tobias Krais.</p>
<strong>Version 1.1</strong> 2012/05/14<br />
<strong>Version 1.0</strong> 2008/08/21<br />
