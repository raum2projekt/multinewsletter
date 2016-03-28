<?php
$sql = rex_sql::factory();
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
UNIQUE KEY `setupdate` (`setupdate`,`clang_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;');

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;');

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;');

if (!$this->hasConfig()) {
    $this->setConfig('sender', '');
    $this->setConfig('link', 0);
    $this->setConfig('linkname', '');
    $this->setConfig('link_abmeldung', 0);
    $this->setConfig('linkname_abmeldung', '');
    $this->setConfig('max_mails', 15);
    $this->setConfig('versandschritte_nacheinander', 20);
    $this->setConfig('sekunden_pause', 305);
    $this->setConfig('default_lang', 0);
    $this->setConfig('default_test_anrede', 0);
    $this->setConfig('default_test_email', rex::getProperty('ERROR_EMAIL'));
    $this->setConfig('default_test_vorname', 'Max');
    $this->setConfig('default_test_nachname', 'Mustermann');
    $this->setConfig('default_test_article', rex_article::getSiteStartArticleId());
    $this->setConfig('default_test_article_name', '');
    $this->setConfig('default_test_sprache', 0);
    $this->setConfig('unsubscribe_action', 'delete');
    $this->setConfig('subscribe_meldung_email', '');
}

/*
$myself = 'multinewsletter';
$myself_path = $REX['INCLUDE_PATH'] . '/addons/multinewsletter';
$error = array();
$messages = array();

// append lang file
$I18N->appendFile($myself_path . '/lang/');

// includes
require_once($myself_path . '/classes/class.multinewsletter_utils.inc.php');

// 
if(!class_exists("rex_mailer")) {
	$error[] = $I18N->msg('multinewsletter_error_no_phpmailer');
}

if(count($error) == 0) {
	// Modul Aktion ID holen
	$result_action = new rex_sql();
	$query_action = "SELECT id FROM ". rex::getTablePrefix() ."action WHERE createuser = 'Multinewsletter Addon Installer'";
	$result_action -> setQuery($query_action);
	$num_rows_action = $result_action -> getRows();
	
	if($num_rows_action == 0) {
		// Modul Aktion
		$result_action = new rex_sql();
		$query_action = "INSERT INTO `". rex::getTablePrefix() ."action` (`name`, `preview`, `presave`, `postsave`, `previewmode`, `presavemode`, `postsavemode`, `createuser`, `createdate`)
		VALUES ('Multinewsletter Array-Save-Action', 
		\"". mysql_real_escape_string(file_get_contents($myself_path .'/modules/array-save-action.inc.php')) ."\", 
		\"". mysql_real_escape_string(file_get_contents($myself_path .'/modules/array-save-action.inc.php')) ."\", 
		'', 
		2, 
		3, 
		0, 
		'Multinewsletter Addon Installer', 
		". time() .")";
		$result_action -> setQuery($query_action);
		if($result_action -> error != "") {
			$error[] = htmlspecialchars($query_action) ."<br />". $result_action -> error ."<br /><br />";
		}

		// Anmeldeformular
		$result_anmeldung = new rex_sql();
		$query_anmeldung = "INSERT INTO `". rex::getTablePrefix() ."module` (`name`, `eingabe`, `ausgabe`, `createuser`, `createdate`) VALUES
		('Multinewsletter Anmeldeformular', '".  mysql_real_escape_string(file_get_contents($myself_path .'/modules/anmeldung-in.inc.php')) ."', '".  mysql_real_escape_string(file_get_contents($myself_path .'/modules/anmeldung-out.inc.php')) ."', 'Multinewsletter Addon Installer', ". time() .")";
		$result_anmeldung -> setQuery($query_anmeldung);
		if($result_anmeldung -> error != "") {
			$error[] =  htmlspecialchars($query_anmeldung) ."<br />". $result_anmeldung -> error ."<br /><br />";
		}

		// Anmeldeformular mit Aktion verknuepfen
		$result_anmeldung_action = new rex_sql();
		$query_anmeldung_action = "INSERT INTO `". rex::getTablePrefix() ."module_action` (`module_id`, `action_id`) VALUES (". $result_anmeldung -> getLastId() .", ". $result_action -> getLastId() .")";
		$result_anmeldung_action -> setQuery($query_anmeldung_action);
		if($result_anmeldung_action -> error != "") {
			$error[] = htmlspecialchars($query_anmeldung_action) ."<br />". $result_anmeldung_action -> error ."<br /><br />";
		}

		// Abmeldeformular
		$result_abmeldung = new rex_sql();
		$query_abmeldung = "INSERT INTO `". rex::getTablePrefix() ."module` (`name`, `eingabe`, `ausgabe`, `createuser`, `createdate`) VALUES
		('Multinewsletter Abmeldeformular', '".  mysql_real_escape_string(file_get_contents($myself_path .'/modules/abmeldung-in.inc.php')) ."', '".  mysql_real_escape_string(file_get_contents($myself_path .'/modules/abmeldung-out.inc.php')) ."', 'Multinewsletter Addon Installer', ". time() .")";
		$result_abmeldung -> setQuery($query_abmeldung);
		if($result_abmeldung -> error != "") {
			$error[] = htmlspecialchars($query_abmeldung) ."<br />". $result_abmeldung -> error ."<br /><br />";
		}

		$messages[] = $I18N->msg('multinewsletter_install_modules_added');
	}
}
 * 
 */
?>
