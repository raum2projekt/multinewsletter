<h1>Installation Module</h1>
<p>Die benötigten Module werden bei der Installation von Multinewsletter automatisch
	installiert und bei der Deinstallation auch wieder gelöscht. Auch die "Array
	Save Action" wird mitinstalliert und verknüpft.</p>
<h2>Anmeldung zum Newsletter</h2>
<p>Moduleingabe</p>
<?php
$anmeldung_eingabe = file_get_contents(rex_path::addonAssets("multinewsletter") ."/modules/anmeldung-in.inc.php");
?>
<textarea class="rex-form-textarea" rows="15" name="eingabe" style="width: 100%"><?php print htmlspecialchars($anmeldung_eingabe); ?></textarea>
<p>Modulausgabe</p>
<?php
$anmeldung_ausgabe = file_get_contents(rex_path::addonAssets("multinewsletter") ."/modules/anmeldung-out.inc.php");
?>
<textarea class="rex-form-textarea" rows="15" name="ausgabe" style="width: 100%"><?php print htmlspecialchars($anmeldung_ausgabe); ?></textarea>
<p>Das Modul zur Anmeldung muss mit den unten aufgeführten Aktion zum Speichern
	einer Mehrfachauswahl verknüpft werden.</p>

<h2>Abmeldung vom Newsletter</h2>
<p>Moduleingabe</p>
<?php
$abmeldung_eingabe = file_get_contents(rex_path::addonAssets("multinewsletter") ."/modules/abmeldung-in.inc.php");
?>
<textarea class="rex-form-textarea" rows="3" name="eingabe" style="width: 100%"><?php print htmlspecialchars($abmeldung_eingabe); ?></textarea>
<p>Modulausgabe</p>
<?php
$abmeldung_ausgabe = file_get_contents(rex_path::addonAssets("multinewsletter") ."/modules/abmeldung-out.inc.php");
?>
<textarea class="rex-form-textarea" rows="15" name="ausgabe" style="width: 100%"><?php print htmlspecialchars($abmeldung_ausgabe); ?></textarea>

<h2>Aktion für Modul: Array Save Action</h2>
<p><b>Preview-Action</b></p>
<p>Die folgende Aktion muss nur für das Event "ADD" zugewiesen werden.</p>
<?php
$action = file_get_contents(rex_path::addonAssets("multinewsletter") ."/modules/array-save-action.inc.php");
?>
<textarea class="rex-form-textarea" rows="15" name="preview" style="width: 100%"><?php print htmlspecialchars($action); ?></textarea>
<p><b>Presave-Action</b></p>
<p>Die folgende Aktion ist die gleiche wie bei der Preview-action. Sie muss aber
	für das Event "ADD" und "EDIT" zugewiesen werden.</p>
<textarea class="rex-form-textarea" rows="15" name="ausgabe" style="width: 100%"><?php print htmlspecialchars($action); ?></textarea>
<p><b>Postsave-Action</b></p>
<p>Hier sind keine Eingaben nötig.</p>