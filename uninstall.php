<?php
$sql = rex_sql::factory();
// Tabellen löschen
$sql->setQuery('DROP TABLE IF EXISTS `' . rex::getTablePrefix() . '375_archive`');
$sql->setQuery('DROP TABLE IF EXISTS `' . rex::getTablePrefix() . '375_group`');
$sql->setQuery('DROP TABLE IF EXISTS `' . rex::getTablePrefix() . '375_user`');