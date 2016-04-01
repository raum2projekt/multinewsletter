<?php
$sql = rex_sql::factory();
// Tabellen löschen
$sql->setQuery('DROP TABLE IF EXISTS `' . rex::getTablePrefix() . '375_archive`');
$sql->setQuery('DROP TABLE IF EXISTS `' . rex::getTablePrefix() . '375_group`');
$sql->setQuery('DROP TABLE IF EXISTS `' . rex::getTablePrefix() . '375_user`');

// Module und Aktion löschen
$result_action = rex_sql::factory();
$query_action = "SELECT id FROM ". rex::getTablePrefix() ."action WHERE createuser = 'Multinewsletter Addon Installer'";
$result_action->setQuery($query_action);
$num_rows_action = $result_action->getRows();
$aktionen_ids = array();
for($i = 0; $i < $num_rows_action; $i++) {
	$aktionen_ids[] = $result_action->getValue("id");
	$result_action->next();
}
// Verknüpfung Aktion und Modul löschen
foreach($aktionen_ids as $aktionen_id) {
	$result_anmeldung_action = rex_sql::factory();
	$query_anmeldung_action = "DELETE FROM ". rex::getTablePrefix() ."module_action WHERE action_id = ". $aktionen_id;
	$result_anmeldung_action->setQuery($query_anmeldung_action);
}
// Aktion löschen
$result_action = rex_sql::factory();
$query_action = "DELETE FROM ". rex::getTablePrefix() ."action WHERE createuser = 'Multinewsletter Addon Installer'";
$result_action->setQuery($query_action);
// Module löschen
$result_modul = rex_sql::factory();
$query_modul = "DELETE FROM ". rex::getTablePrefix() ."module WHERE createuser = 'Multinewsletter Addon Installer'";
$result_modul->setQuery($query_modul);