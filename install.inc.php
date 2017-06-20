<?php
$myself = 'multinewsletter';
$myself_path = $REX['INCLUDE_PATH'] . '/addons/multinewsletter';
$error = [];
$messages = [];

// append lang file
$I18N->appendFile($myself_path . '/lang/');

// includes
require_once($myself_path . '/classes/class.multinewsletter_utils.inc.php');

// check redaxo version
if (version_compare($REX['VERSION'] . '.' . $REX['SUBVERSION'] . '.' . $REX['MINORVERSION'], '4.4.1', '<=')) {
	$error[] = $I18N->msg('multinewsletter_install_rex_version');
}

// 
if(!class_exists("rex_mailer")) {
	$error[] = $I18N->msg('multinewsletter_error_no_phpmailer');
}

if(count($error) == 0) {
	// Modul Aktion ID holen
	$result_action = new rex_sql();
	$query_action = "SELECT id FROM ". $REX['TABLE_PREFIX'] ."action WHERE createuser = 'Multinewsletter Addon Installer'";
	$result_action -> setQuery($query_action);
	$num_rows_action = $result_action -> getRows();
	
	if($num_rows_action == 0) {
		// Modul Aktion
		$result_action = new rex_sql();
		$query_action = "INSERT INTO `". $REX['TABLE_PREFIX'] ."action` (`name`, `preview`, `presave`, `postsave`, `previewmode`, `presavemode`, `postsavemode`, `createuser`, `createdate`)
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
		$query_anmeldung = "INSERT INTO `". $REX['TABLE_PREFIX'] ."module` (`name`, `eingabe`, `ausgabe`, `createuser`, `createdate`) VALUES
		('Multinewsletter Anmeldeformular', '".  mysql_real_escape_string(file_get_contents($myself_path .'/modules/anmeldung-in.inc.php')) ."', '".  mysql_real_escape_string(file_get_contents($myself_path .'/modules/anmeldung-out.inc.php')) ."', 'Multinewsletter Addon Installer', ". time() .")";
		$result_anmeldung -> setQuery($query_anmeldung);
		if($result_anmeldung -> error != "") {
			$error[] =  htmlspecialchars($query_anmeldung) ."<br />". $result_anmeldung -> error ."<br /><br />";
		}

		// Anmeldeformular mit Aktion verknuepfen
		$result_anmeldung_action = new rex_sql();
		$query_anmeldung_action = "INSERT INTO `". $REX['TABLE_PREFIX'] ."module_action` (`module_id`, `action_id`) VALUES (". $result_anmeldung -> getLastId() .", ". $result_action -> getLastId() .")";
		$result_anmeldung_action -> setQuery($query_anmeldung_action);
		if($result_anmeldung_action -> error != "") {
			$error[] = htmlspecialchars($query_anmeldung_action) ."<br />". $result_anmeldung_action -> error ."<br /><br />";
		}

		// Abmeldeformular
		$result_abmeldung = new rex_sql();
		$query_abmeldung = "INSERT INTO `". $REX['TABLE_PREFIX'] ."module` (`name`, `eingabe`, `ausgabe`, `createuser`, `createdate`) VALUES
		('Multinewsletter Abmeldeformular', '".  mysql_real_escape_string(file_get_contents($myself_path .'/modules/abmeldung-in.inc.php')) ."', '".  mysql_real_escape_string(file_get_contents($myself_path .'/modules/abmeldung-out.inc.php')) ."', 'Multinewsletter Addon Installer', ". time() .")";
		$result_abmeldung -> setQuery($query_abmeldung);
		if($result_abmeldung -> error != "") {
			$error[] = htmlspecialchars($query_abmeldung) ."<br />". $result_abmeldung -> error ."<br /><br />";
		}

		$messages[] = $I18N->msg('multinewsletter_install_modules_added');
	}
}


if (count($error) > 0) {
	$REX['ADDON']['installmsg'][$myself] = '<br />' . implode($error, '<br />');
	$REX['ADDON']['install'][$myself] = 0;
}
else {
	rex_info('<br />' . implode($messages, '<br />'));
	$REX['ADDON']['install'][$myself] = 1;	
}
?>
