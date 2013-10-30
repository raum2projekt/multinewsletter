<?php

require_once($REX['INCLUDE_PATH'].'/addons/multinewsletter/config.inc.php');

// CREATE/UPDATE DATABASE
// CREATE/UPDATE ACTIONS
// CREATE/UPDATE MODULES
// CREATE/UPDATE MODULE_ACTIONS
// CREATE/UPDATE PAGES
// CREATE/UPDATE FILES
// REGENERATE SITE
// COPY FILES

// ERRMSG IN CASE: $REX['ADDON']['installmsg']['example'] = 'Error occured while installation';
if(!class_exists("rex_mailer"))
  $REX['ADDON']['installmsg'][$REX['ADDON375']['addon_name']] = $REX['ADDON375']['I18N']->msg('error_no_phpmailer');


print rex_info("Multinewsletter An- und Abmeldemodule werden zu Redaxo hinzugef&uuml;gt.");
$verzeichnis = dirname(__FILE__);

// Modul Aktion
$result_action = new rex_sql();
$query_action = "INSERT INTO `". $REX['TABLE_PREFIX'] ."action` (`name`, `preview`, `presave`, `postsave`, `previewmode`, `presavemode`, `postsavemode`, `createuser`, `createdate`)
VALUES ('Multinewsletter Array-Save-Action', 
\"". mysql_real_escape_string(file_get_contents($verzeichnis .'/modules/action-preview.inc.php')) ."\", 
\"". mysql_real_escape_string(file_get_contents($verzeichnis .'/modules/action-presave.inc.php')) ."\", 
'', 
2, 
3, 
0, 
'Multinewsletter Addon Installer', 
". time() .")";
$result_action -> setQuery($query_action);
if($result_action -> error != "") {
	print htmlspecialchars($query_action) ."<br />";
	print $result_action -> error ."<br />";
	print "<br />";
}

// Anmeldeformular
$result_anmeldung = new rex_sql();
$query_anmeldung = "INSERT INTO `". $REX['TABLE_PREFIX'] ."module` (`name`, `eingabe`, `ausgabe`, `createuser`, `createdate`) VALUES
('Multinewsletter Anmeldeformular', '".  mysql_real_escape_string(file_get_contents($verzeichnis .'/modules/anmeldung-in.inc.php')) ."', '".  mysql_real_escape_string(file_get_contents($verzeichnis .'/modules/anmeldung-out.inc.php')) ."', 'Multinewsletter Addon Installer', ". time() .")";
$result_anmeldung -> setQuery($query_anmeldung);
if($result_anmeldung -> error != "") {
	print htmlspecialchars($query_anmeldung) ."<br />";
	print $result_anmeldung -> error ."<br />";
	print "<br />";
}

// Anmeldeformular mit Aktion verknuepfen
$result_anmeldung_action = new rex_sql();
$query_anmeldung_action = "INSERT INTO `". $REX['TABLE_PREFIX'] ."module_action` (`module_id`, `action_id`) VALUES (". $result_anmeldung -> getLastId() .", ". $result_action -> getLastId() .")";
$result_anmeldung_action -> setQuery($query_anmeldung_action);
if($result_anmeldung_action -> error != "") {
	print htmlspecialchars($query_anmeldung_action) ."<br />";
	print $result_anmeldung_action -> error ."<br />";
	print "<br />";
}

// Abmeldeformular
$result_abmeldung = new rex_sql();
$query_abmeldung = "INSERT INTO `". $REX['TABLE_PREFIX'] ."module` (`name`, `eingabe`, `ausgabe`, `createuser`, `createdate`) VALUES
('Multinewsletter Abmeldeformular', '".  mysql_real_escape_string(file_get_contents($verzeichnis .'/modules/abmeldung-in.inc.php')) ."', '".  mysql_real_escape_string(file_get_contents($verzeichnis .'/modules/abmeldung-out.inc.php')) ."', 'Multinewsletter Addon Installer', ". time() .")";
$result_abmeldung -> setQuery($query_abmeldung);
if($result_abmeldung -> error != "") {
	print htmlspecialchars($query_abmeldung) ."<br />";
	print $result_abmeldung -> error ."<br />";
	print "<br />";
}

print rex_info("Falls die Sprache in den Modulen falsch ist, bitte die Dateien im Addonverzeichnis/files/ tauschen bzw. editieren.");


$REX['ADDON']['install'][$REX['ADDON375']['addon_name']] = 1;
?>
