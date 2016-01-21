<?php
if ($I18N->hasMsg('multinewsletter_update_msg')) {
	$msg = $I18N->msg('multinewsletter_update_msg');
} else {
	$msg = 'Multinewsletter: Bitte beachten Sie die <a href="index.php?page=multinewsletter&subpage=help&chapter=updatehinweise">Update-Hinweise</a> für diese Version!';
}
echo rex_info($msg);

// Wenn alte Konfigurationsdatei existiert muss MultiNewsletter auf Version 2.0.0 aktualisiert werden
$old_config_file = $REX['INCLUDE_PATH'] . '/addons/multinewsletter/files/.configfile';
if(file_exists($old_config_file)) {
	// includes
	require_once($REX['INCLUDE_PATH'] . '/addons/multinewsletter/classes/class.multinewsletter_update.inc.php');
	multinewsletter_update::updateConfig();
	multinewsletter_update::updateDatabase();
}

// rex_375_user auf Version 2.2 aktualisieren
$result = new rex_sql();
$query = "ALTER TABLE ". $REX['TABLE_PREFIX'] ."375_user CHANGE `createip` `createip` VARCHAR(45) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;";
$result->setQuery($query);
$query = "ALTER TABLE ". $REX['TABLE_PREFIX'] ."375_user CHANGE `activationip` `activationip` VARCHAR(45) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;";
$result->setQuery($query);
$query = "ALTER TABLE ". $REX['TABLE_PREFIX'] ."375_user CHANGE `updateip` `updateip` VARCHAR(45) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;";
$result->setQuery($query);

// Newsletter Templates beinhalten oft auch komplette CSS Dateien. Dafür ist aber zu wenig Platz. Hier die Lösung:
$query = "ALTER TABLE ". $REX['TABLE_PREFIX'] ."template` CHANGE `content` `content` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL";
$result->setQuery($query);

$REX['ADDON']['update']['multinewsletter'] = 1;