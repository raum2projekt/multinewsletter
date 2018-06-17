<fieldset>
	<legend>MultiNewsletter FAQ</legend>

	<p><strong>Frage: Gibt es Informationen zum Datenschutz?</strong></p>
	<p>MultiNewsletter erhebt speichert personenbezogene Daten. Diese sind: Name,
		E-Mailadresse, Geschlecht, IP Adresse. Daher muss eine Zustimmung des
		Benutzers eingeholt werden. Im Beispielmodul wird das gemacht.</p>
	<p>Informationen wie die Zustimmung rechtlich wirksam eingeholt werden kann
		gibt es hier: <a href="https://www.heise.de/-4023584" target="_blank">
		https://www.heise.de/-4023584</a>.</p>
	<p>Bei der Benutzung von MailChimp muss der Benutzer informiert werden, dass
		seine Daten anden Betreiber von MailChimp weiter gegeben werden.</p>

	<p><strong>Frage: Meine Aktivierungsmail wird nicht verschickt. Warum?</strong></p>
	<p>Das könnte mehrere Ursachen haben. Ist der <a href="<?php print rex_url::backendPage('phpmailer/config'); ?>">
		PHPMailer</a> korrekt konfiguriert?
		Sind in den <a href="<?php print rex_url::backendPage('multinewsletter/config'); ?>">
		MultiNewsletter Einstellungen die Übersetzungen</a> eingepflegt?</p>

	<p><strong>Frage: Warum ist der Link in der Bestätigungsmail in manchen Mailprogrammen
		nicht als Link aktiviert?</strong></p>
	<p>Der Link wird nur dann immer aktiviert, wenn er in den <a href="<?php print rex_url::backendPage('multinewsletter/config'); ?>">
		Einstellungen, bei den Übersetzungen</a> unter "Text der
		Bestätigungsmail" auch als HTML-Link programmiert wurde. Bitte deshalb
		das "a href=..." nicht vergessen!</p>

	<p><strong>Frage: Der Link in der Aktivierungsmail ist zwar aktiviert, funktioniert
		aber nicht. Warum?</strong></p>
	<p>In den <a href="<?php print rex_url::backendPage('system/settings'); ?>">
		Redaxo Systemeinstellungen</a> muss im Feld "URL der Webseite" die URL inklusive
		http:// (oder https://) und am Ende / eingegeben werden.</p>

	<p><strong>Frage: Warum wird die Aktivierungsmail nicht verschickt, die anderen
			Mails aber schon?</strong></p>
	<p>Wenn die <a href="<?php print rex_url::backendPage('multinewsletter/config'); ?>">
			MultiNewsletter Spracheinstellungen</a> eingegeben sind sollte dieser
			"Fehler" behoben sein.</p>
</fieldset>