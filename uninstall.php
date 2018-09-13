<?php
$sql = rex_sql::factory();
// Delete tables
$sql->setQuery('DROP TABLE IF EXISTS `' . rex::getTablePrefix() . '375_archive`');
$sql->setQuery('DROP TABLE IF EXISTS `' . rex::getTablePrefix() . '375_group`');
$sql->setQuery('DROP TABLE IF EXISTS `' . rex::getTablePrefix() . '375_user`');

// Remove CronJob
if(multinewsletter_cronjob_sender::isInstalled()) {
	multinewsletter_cronjob_sender::delete();
}