<?php
$sql = rex_sql::factory();
// Tabellen löschen
$sql->setQuery('DROP TABLE IF EXISTS `' . rex::getTablePrefix() . '375_archive`');
$sql->setQuery('DROP TABLE IF EXISTS `' . rex::getTablePrefix() . '375_group`');
$sql->setQuery('DROP TABLE IF EXISTS `' . rex::getTablePrefix() . '375_user`');

// Module löschen
$result_modul = rex_sql::factory();
$query_modul = "DELETE FROM ". rex::getTablePrefix() ."module WHERE createuser = 'Multinewsletter Addon Installer'";
$result_modul->setQuery($query_modul);