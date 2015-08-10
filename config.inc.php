<?php
// register addon
$REX['ADDON']['rxid']['multinewsletter'] = '375';
$REX['ADDON']['name']['multinewsletter'] = 'MultiNewsletter';
$REX['ADDON']['version']['multinewsletter'] = '2.1.1';
$REX['ADDON']['author']['multinewsletter'] = 'Thomas Göllner, RexDude, Tobias Krais';
$REX['ADDON']['supportpage']['multinewsletter'] = 'forum.redaxo.de';
$REX['ADDON']['perm']['multinewsletter'] = 'multinewsletter[]';

// permissions
$REX['PERM'][] = 'multinewsletter[]';

// append lang file
if ($REX['REDAXO']) {
	$I18N->appendFile($REX['INCLUDE_PATH'] . '/addons/multinewsletter/lang/');
}

// includes
require_once($REX['INCLUDE_PATH'] . '/addons/multinewsletter/classes/class.multinewsletter_utils.inc.php');

// consts
define('MULTINEWSLETTER_ARRAY_DELIMITER', '|');
define('MULTINEWSLETTER_DATA_DIR', $REX['INCLUDE_PATH'] . '/data/addons/multinewsletter/');
define('MULTINEWSLETTER_BACKUP_DIR', $REX['INCLUDE_PATH'] . '/data/addons/multinewsletter/backup/');

// default settings (user settings are saved in data dir!)
$REX['ADDON']['multinewsletter']['settings'] = array(
	'sender' => '',
	'link' => 0,
	'linkname' => '',
	'link_abmeldung' => 0,
	'linkname_abmeldung' => '',
	'max_mails' => 15,
	'versandschritte_nacheinander' => 20,
	'sekunden_pause' => 305,
	'default_lang' => 0,
	'default_test_anrede' => 0,
	'default_test_email' => $REX['ERROR_EMAIL'],
	'default_test_vorname' => 'Max',
	'default_test_nachname' => 'Mustermann',
	'default_test_article' => $REX['START_ARTICLE_ID'],
	'default_test_article_name' => '',
	'default_test_sprache' => 0,
	'unsubscribe_action' => 'delete',
	'subscribe_meldung_email' => '',
);

// overwrite default settings with user settings
multinewsletter_utils::includeSettingsFile();

if ($REX['REDAXO']) {
	// add css/js files to page header
	if (rex_request('page') == 'multinewsletter') {
		rex_register_extension('PAGE_HEADER', 'multinewsletter_utils::appendToPageHeader');
	}
}