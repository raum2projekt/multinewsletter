<fieldset>
	<legend>Installation Module</legend>
	<p>Die benötigten Module werden bei der Installation von Multinewsletter automatisch
		installiert und bei der Deinstallation auch wieder gelöscht.</p>
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