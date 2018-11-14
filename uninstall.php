<?php
$sql = rex_sql::factory();
// Delete tables
$sql->setQuery('DROP TABLE IF EXISTS `' . rex::getTablePrefix() . '375_archive`');
$sql->setQuery('DROP TABLE IF EXISTS `' . rex::getTablePrefix() . '375_group`');
$sql->setQuery('DROP TABLE IF EXISTS `' . rex::getTablePrefix() . '375_user`');

// Remove CronJobs
if(!class_exists('multinewsletter_cronjob_sender')) {
	// Load class in case addon is deactivated
	require_once 'lib/cronjob_sender.php';
}
if(multinewsletter_cronjob_sender::isInstalled()) {
	multinewsletter_cronjob_sender::delete();
}
if(!class_exists('multinewsletter_cronjob_cleanup')) {
	// Load class in case addon is deactivated
	require_once 'lib/cronjob_cleanup.php';
}
if(multinewsletter_cronjob_cleanup::isInstalled()) {
	multinewsletter_cronjob_cleanup::delete();
}