<?php
$sql = rex_sql::factory();
// Datenbanktabellen erstellen
$sql->setQuery('CREATE TABLE IF NOT EXISTS `' . rex::getTablePrefix() . '375_archive` (
	`archive_id` int(11) unsigned NOT NULL auto_increment,
	`clang_id` int(11) NOT NULL,
	`subject` varchar(255) NOT NULL,
	`htmlbody` longtext NOT NULL,
	`recipients` longtext NOT NULL,
	`group_ids` text NOT NULL,
	`sender_email` varchar(255) NOT NULL,
	`sender_name` varchar(255) NOT NULL,
	`setupdate` int(11) NOT NULL,
	`sentdate` int(11) NOT NULL,
	`sentby` varchar(255) NOT NULL,
PRIMARY KEY(`archive_id`),
UNIQUE KEY `setupdate` (`setupdate`, `clang_id`)
) ENGINE=INNODB DEFAULT CHARSET=utf8;');
$sql->setQuery('CREATE TABLE IF NOT EXISTS `' . rex::getTablePrefix() . '375_group` (
	`group_id` int(11) unsigned NOT NULL auto_increment,
	`name` varchar(255) NOT NULL,
	`default_sender_email` varchar(255) NOT NULL,
	`default_sender_name` varchar(255) NOT NULL,
	`default_article_id` int(11) unsigned NOT NULL,
	`createdate` int(11) NOT NULL,
	`updatedate` int(11) NOT NULL,
PRIMARY KEY(`group_id`),
UNIQUE KEY `name` (`name`)
) ENGINE=INNODB DEFAULT CHARSET=utf8;');
$sql->setQuery('CREATE TABLE IF NOT EXISTS `' . rex::getTablePrefix() . '375_user` (
	`user_id` int(11) unsigned NOT NULL auto_increment,
	`email` varchar(255) NOT NULL,
	`grad` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
	`firstname` varchar(255) NOT NULL,
	`lastname` varchar(255) NOT NULL,
	`title` tinyint(4) NOT NULL,
	`clang_id` int(11) NOT NULL,
	`status` tinyint(1) NOT NULL,
	`group_ids` text NOT NULL,
	`send_archive_id` tinyint(1) unsigned NOT NULL,
	`createdate` int(11) NOT NULL,
	`createip` varchar(45) NOT NULL,
	`activationdate` int(11) NOT NULL,
	`activationip` varchar(45) NOT NULL,
	`updatedate` int(11) NOT NULL,
	`updateip` varchar(45) NOT NULL,
	`subscriptiontype` varchar(16) NOT NULL,
	`activationkey` int(6) NOT NULL,
PRIMARY KEY(`user_id`),
UNIQUE KEY `email` (`email`)
) ENGINE=INNODB DEFAULT CHARSET=utf8;');

// Standartkonfiguration erstellen
if (!$this->hasConfig()) {
   $this->setConfig('sender', '');
    $this->setConfig('link', 0);
    $this->setConfig('link_abmeldung', 0);
    $this->setConfig('max_mails', 15);
    $this->setConfig('versandschritte_nacheinander', 20);
    $this->setConfig('sekunden_pause', 305);
    $this->setConfig('default_lang', rex_clang::getStartId());
    $this->setConfig('default_test_anrede', 0);
    $this->setConfig('default_test_email', rex::getProperty('ERROR_EMAIL'));
    $this->setConfig('default_test_vorname', 'Max');
    $this->setConfig('default_test_nachname', 'Mustermann');
    $this->setConfig('default_test_article', rex_article::getSiteStartArticleId());
    $this->setConfig('default_test_article_name', '');
    $this->setConfig('default_test_sprache', $default_clang_id);
    $this->setConfig('unsubscribe_action', 'delete');
    $this->setConfig('subscribe_meldung_email', '');
}