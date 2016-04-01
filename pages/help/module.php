<fieldset>
	<legend>Installation Module</legend>
	<p>Die benötigten Module werden bei der Installation von Multinewsletter automatisch
		installiert und bei der Deinstallation auch wieder gelöscht. Auch die "Array
		Save Action" wird mitinstalliert und verknüpft.</p>
</fieldset>
<fieldset>
	<legend>Anmeldung zum Newsletter</legend>
	<p>Moduleingabe</p>
	<?php
		print rex_string::highlight(file_get_contents(rex_path::addon("multinewsletter") ."modules/anmeldung-in.php"));
	?>
	<p>Modulausgabe</p>
	<?php
		print rex_string::highlight(file_get_contents(rex_path::addon("multinewsletter") ."modules/anmeldung-out.php"));
	?>
	<p>Das Modul zur Anmeldung muss mit den unten aufgeführten Aktion zum Speichern
		einer Mehrfachauswahl verknüpft werden.</p>
</fieldset>
<fieldset>
	<legend>Abmeldung vom Newsletter</legend>
	<p>Moduleingabe</p>
	<?php
		print rex_string::highlight(file_get_contents(rex_path::addon("multinewsletter") ."modules/abmeldung-in.php"));
	?>
	<p>Modulausgabe</p>
	<?php
		print rex_string::highlight(file_get_contents(rex_path::addon("multinewsletter") ."modules/abmeldung-out.php"));
	?>
</fieldset>
<fieldset>
	<legend>Aktion für Modul: Array Save Action</legend>
	<p><b>Preview-Action</b></p>
	<p>Die folgende Aktion muss nur für das Event "ADD" zugewiesen werden.</p>
	<?php
		$action = file_get_contents(rex_path::addon("multinewsletter") ."modules/array-save-action.php");
		print rex_string::highlight($action);
	?>
	<p><b>Presave-Action</b></p>
	<p>Die folgende Aktion ist die gleiche wie bei der Preview-action. Sie muss aber
		für das Event "ADD" und "EDIT" zugewiesen werden.</p>
	<?php print rex_string::highlight($action); ?>
	<p><b>Postsave-Action</b></p>
	<p>Hier sind keine Eingaben nötig.</p>
</fieldset>