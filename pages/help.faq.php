<fieldset>
	<legend>MultiNewsletter FAQ</legend>

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