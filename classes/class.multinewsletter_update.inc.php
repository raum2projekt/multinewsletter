<?php
/**
 * Klasse um MultiNewsletter automatisch auf die aktuelle Version zu aktualisieren.
 */
class multinewsletter_update {
	/**
	 * Liest die Konfiguration aus einer alten MultiNewsletter Konfiguration aus
	 * und aktualisiert sie. Alte Dateien werden ebenfalls gelöscht.
	 * @global Array $REX
	 * @global type $I18N
	 */
	public static function updateConfig() {
		global $REX;
		global $I18N;

		$old_config_folder = $REX['INCLUDE_PATH'] . '/addons/multinewsletter/files/';
		$old_config_file = $old_config_folder . '.configfile';
		
		// Alte Konfiguration auslesen und speichern
		if(file_exists($old_config_file)) {
			require_once($old_config_file);
			$REX['ADDON']['multinewsletter']['settings']['sender'] = $REX['ADDON375']['config']['sender'];
			$REX['ADDON']['multinewsletter']['settings']['link'] = $REX['ADDON375']['config']['link'];
			$REX['ADDON']['multinewsletter']['settings']['link_abmeldung'] = $REX['ADDON375']['config']['link'];
			$REX['ADDON']['multinewsletter']['settings']['max_mails'] = $REX['ADDON375']['config']['max_mails'] + $REX['ADDON375']['config']['bcc_per_mail'];
			$REX['ADDON']['multinewsletter']['settings']['format'] = $REX['ADDON375']['config']['format'];
			if(array_key_exists('default_lang', $REX['ADDON375']['config'])) {
				$REX['ADDON']['multinewsletter']['settings']['default_lang'] = array_search($REX['ADDON375']['config']['default_lang'], $REX['CLANG']);
			}
			if(array_key_exists('1und1', $REX['ADDON375']['config'])) {
				$REX['ADDON']['multinewsletter']['settings']['versandschritte_nacheinander'] = round($REX['ADDON375']['config']['1und1']['mail_limit'] / $REX['ADDON375']['config']['max_mails']);
				$REX['ADDON']['multinewsletter']['settings']['sekunden_pause'] = $REX['ADDON375']['config']['1und1']['time_distance'];
			}
			foreach($REX['CLANG'] as $lang_key => $lang_shortcode) {
				if(array_key_exists($lang_key, $REX['ADDON375']['config']['default_content'])) {
					$default_content = array(
						'title' => $REX['ADDON375']['config']['default_content'][$lang_key]['title'],
						'title_0' => $REX['ADDON375']['config']['default_content'][$lang_key]['titles'][0],
						'title_1' => $REX['ADDON375']['config']['default_content'][$lang_key]['titles'][1],
						'confirmsubject' => $REX['ADDON375']['config']['default_content'][$lang_key]['confirmsubject'],
						'confirmcontent' => base64_decode($REX['ADDON375']['config']['default_content'][$lang_key]['confirm']),
						'sendername' => $REX['ADDON375']['config']['default_content'][$lang_key]['sendername'],
					);
				}
			}
		}
		
		// Jetzt die Daten aus den Sprachdateien auslesen
		foreach($REX['CLANG'] as $lang_key => $lang_shortcode) {
			$lang_file = $old_config_folder .'clang'. $lang_key .'.lang';
			if(file_exists($lang_file)) {
				$i18n_lang_file = new i18n($lang_shortcode, $old_config_folder);
				$i18n_lang_file->appendFileName($lang_file);

				$default_content['compulsory'] = $i18n_lang_file->msg('compulsory');
				$default_content['action'] = $i18n_lang_file->msg('action');
				$default_content['invalid_email'] = $i18n_lang_file->msg('invalid_email');
				$default_content['invalid_firstname'] = $i18n_lang_file->msg('invalid_firstname');
				$default_content['invalid_lastname'] = $i18n_lang_file->msg('invalid_lastname');
				$default_content['send_error'] = $i18n_lang_file->msg('send_error');
				$default_content['software_failure'] = $i18n_lang_file->msg('software_failure');
				$default_content['no_userdata'] = $i18n_lang_file->msg('no_userdata');
				$default_content['already_unsubscribed'] = $i18n_lang_file->msg('already_unsubscribed');
				$default_content['already_subscribed'] = $i18n_lang_file->msg('already_subscribed');
				$default_content['already_confirmed'] = $i18n_lang_file->msg('already_confirmed');
				$default_content['user_not_found'] = $i18n_lang_file->msg('user_not_found');
				$default_content['safety'] = $i18n_lang_file->msg('safety');
				$default_content['status0'] = $i18n_lang_file->msg('status0');
				$default_content['status1'] = $i18n_lang_file->msg('status1');
				$default_content['invalid_key'] = $i18n_lang_file->msg('invalid_key');
				$default_content['confirmation_successful'] = $i18n_lang_file->msg('confirmation_successful');
				$default_content['confirmation_sent'] = $i18n_lang_file->msg('confirmation_sent');
				$default_content['email'] = $i18n_lang_file->msg('email');
				$default_content['firstname'] = $i18n_lang_file->msg('firstname');
				$default_content['lastname'] = $i18n_lang_file->msg('lastname');
				$default_content['grad'] = $i18n_lang_file->msg('grad');
				$default_content['title'] = $i18n_lang_file->msg('title');
				$default_content['select_newsletter'] = $i18n_lang_file->msg('select_group');
				$default_content['subscribe'] = $i18n_lang_file->msg('subscribe');
				$default_content['unsubscribe'] = $i18n_lang_file->msg('unsubscribe');
				$default_content['nogroup_selected'] = $i18n_lang_file->msg('nogroup_selected');
			}
			// Alte Sprachdatei löschen
			unlink($lang_file);
			if(file_exists($REX['MEDIAFOLDER'] ."/addons/multinewsletter/clang". $lang_key .".lang")) {
				unlink($REX['MEDIAFOLDER'] ."/addons/multinewsletter/clang". $lang_key .".lang");
			}

			$REX['ADDON']['multinewsletter']['settings']['lang'][$lang_key] = $default_content;
		}
		multinewsletter_utils::updateSettingsFile(false);
		rex_info($I18N->msg('multinewsletter_config_update'));

		// Alte Konfigurationsdatei löschen
		unlink($old_config_file);
		if(file_exists($REX['MEDIAFOLDER'] ."/addons/multinewsletter/.configfile")) {
			unlink($REX['MEDIAFOLDER'] ."/addons/multinewsletter/.configfile");
		}

		// Andere alte Dateien löschen
		if(file_exists($REX['INCLUDE_PATH'] . '/addons/multinewsletter/pages/archiveout.inc.php')) {
			unlink($REX['INCLUDE_PATH'] . '/addons/multinewsletter/pages/archiveout.inc.php');
		}
		if(file_exists($REX['INCLUDE_PATH'] . '/addons/multinewsletter/pages/index_database_updates.inc.php')) {
			unlink($REX['INCLUDE_PATH'] . '/addons/multinewsletter/pages/index_database_updates.inc.php');
		}
		if(file_exists($REX['INCLUDE_PATH'] . '/addons/multinewsletter/pages/menu.inc.php')) {
			unlink($REX['INCLUDE_PATH'] . '/addons/multinewsletter/pages/menu.inc.php');
		}

		if(file_exists($REX['INCLUDE_PATH'] . '/addons/multinewsletter/css/backend.css')) {
			unlink($REX['INCLUDE_PATH'] . '/addons/multinewsletter/css/backend.css');
			rmdir($REX['INCLUDE_PATH'] . '/addons/multinewsletter/css/');
		}

		if(file_exists($REX['INCLUDE_PATH'] . '/addons/multinewsletter/classes/class.csv.inc.php')) {
			unlink($REX['INCLUDE_PATH'] . '/addons/multinewsletter/classes/class.csv.inc.php');
		}
		if(file_exists($REX['INCLUDE_PATH'] . '/addons/multinewsletter/lang/de_de_utf8.lang')) {
			unlink($REX['INCLUDE_PATH'] . '/addons/multinewsletter/lang/de_de_utf8.lang');
		}
		if(file_exists($REX['INCLUDE_PATH'] . '/addons/multinewsletter/lang/help_de_de.html')) {
			unlink($REX['INCLUDE_PATH'] . '/addons/multinewsletter/lang/help_de_de.html');
		}
		if(file_exists($REX['INCLUDE_PATH'] . '/addons/multinewsletter/lang/help_de_de_utf8.html')) {
			unlink($REX['INCLUDE_PATH'] . '/addons/multinewsletter/lang/help_de_de_utf8.html');
		}

		if(file_exists($REX['INCLUDE_PATH'] . '/addons/multinewsletter/scripts/scripts.js')) {
			unlink($REX['INCLUDE_PATH'] . '/addons/multinewsletter/scripts/scripts.js');
			rmdir($REX['INCLUDE_PATH'] . '/addons/multinewsletter/scripts/');
		}

		if(file_exists($REX['INCLUDE_PATH'] . '/addons/multinewsletter/modules/action-presave.inc.php')) {
			unlink($REX['INCLUDE_PATH'] . '/addons/multinewsletter/modules/action-presave.inc.php');
			unlink($REX['INCLUDE_PATH'] . '/addons/multinewsletter/modules/action-preview.inc.php');
		}

		if(file_exists($REX['INCLUDE_PATH'] . '/addons/multinewsletter/help.inc.php')) {
			unlink($REX['INCLUDE_PATH'] . '/addons/multinewsletter/help.inc.php');
		}

		if(file_exists($REX['INCLUDE_PATH'] . '/addons/multinewsletter/newsletteruser.csv')) {
			unlink($REX['INCLUDE_PATH'] . '/addons/multinewsletter/newsletteruser.csv');
		}

		if(file_exists($REX['INCLUDE_PATH'] . '/addons/multinewsletter/functions/basics.inc.php')) {
			unlink($REX['INCLUDE_PATH'] . '/addons/multinewsletter/functions/basics.inc.php');
			unlink($REX['INCLUDE_PATH'] . '/addons/multinewsletter/functions/newsletter.inc.php');
			unlink($REX['INCLUDE_PATH'] . '/addons/multinewsletter/functions/redaxo_modules.inc.php');
			rmdir($REX['INCLUDE_PATH'] . '/addons/multinewsletter/functions/');
		}

		if(file_exists($REX['MEDIAFOLDER'] ."/addons/multinewsletter/backend.css")) {
			unlink($REX['MEDIAFOLDER'] ."/addons/multinewsletter/backend.css");
		}
		
		// Verzeichnis files/addons/multinewsletter anlegen, falls nicht existent
		if(!is_dir($REX['MEDIAFOLDER'] .'/addons/multinewsletter')) {
			mkdir($REX['MEDIAFOLDER'] .'/addons/multinewsletter');
		}

		// Dateien ins Verzeichnis files/addons/multinewsletter kopieren
		if($dh = opendir($old_config_folder)) {
			while ($file = readdir($dh)) {
				$file_uri = $old_config_folder . $file;
				if($file != '.' && $file != '..' && is_file($file_uri)) {
					copy($file_uri, $REX['MEDIAFOLDER'] .'/addons/multinewsletter/'.$file);
				}
			}
		}
	}
	
	/**
	 * Aktualisiert die Datenbank von vorhergehenden MultiNewsletter Versionen
	 * @global Array $REX
	 */
	public static function updateDatabase() {
		global $REX;

		// Tabelle rex_375_user
		$field_names_user = [];
		$query_user = "SELECT * FROM ". $REX['TABLE_PREFIX'] ."375_user LIMIT 0,1";
		$result_user = mysql_query($query_user);
		$field_user = mysql_num_fields($result_user);
		for($i = 0; $i < $field_user; $i++) {
			$field_names_user[] = mysql_field_name($result_user, $i);
		}
		// rex_375_user auf Version 1.2 aktualisieren
		if(!in_array('createip', $field_names_user)) {
			$query = "ALTER TABLE ". $REX['TABLE_PREFIX'] ."375_user ADD createip VARCHAR(16) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER createdate";
			$result = new rex_sql();
			$result->setQuery($query);
		}
		if(!in_array('activationdate', $field_names_user)) {
			$query = "ALTER TABLE ". $REX['TABLE_PREFIX'] ."375_user ADD activationdate INT(11) NULL DEFAULT NULL AFTER createip";
			$result = new rex_sql();
			$result->setQuery($query);
		}
		if(!in_array('activationip', $field_names_user)) {
			$query = "ALTER TABLE ". $REX['TABLE_PREFIX'] ."375_user ADD activationip VARCHAR(16) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER activationdate";
			$result = new rex_sql();
			$result->setQuery($query);
		}
		if(in_array('ip', $field_names_user)) {
			$query = "ALTER TABLE ". $REX['TABLE_PREFIX'] ."375_user CHANGE ip updateip VARCHAR(16) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";
			$result = new rex_sql();
			$result->setQuery($query);
		}
		if(!in_array('subscriptiontype', $field_names_user)) {
			$query = "ALTER TABLE ". $REX['TABLE_PREFIX'] ."375_user ADD subscriptiontype VARCHAR(16) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER updateip";
			$result = new rex_sql();
			$result->setQuery($query);
		}
		// rex_375_user auf Version 1.4 aktualisieren
		if(!in_array('grad', $field_names_user)) {
			$query = "ALTER TABLE ". $REX['TABLE_PREFIX'] ."375_user ADD grad VARCHAR(16) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER title";
			$result = new rex_sql();
			$result->setQuery($query);
		}
		// rex_375_user auf Version 2.0 aktualisieren
		if(in_array('id', $field_names_user)) {
			$query = "ALTER TABLE ". $REX['TABLE_PREFIX'] ."375_user CHANGE id user_id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT";
			$result = new rex_sql();
			$result->setQuery($query);
		}
		if(in_array('clang', $field_names_user)) {
			$query = "ALTER TABLE ". $REX['TABLE_PREFIX'] ."375_user CHANGE clang clang_id INT(11) NOT NULL";
			$result = new rex_sql();
			$result->setQuery($query);
		}
		if(in_array('article_id', $field_names_user)) {
			$query = "ALTER TABLE ". $REX['TABLE_PREFIX'] ."375_user DROP article_id";
			$result = new rex_sql();
			$result->setQuery($query);
		}
		if(!in_array('group_ids', $field_names_user)) {
			$query = "ALTER TABLE ". $REX['TABLE_PREFIX'] ."375_user ADD group_ids TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER status";
			$result = new rex_sql();
			$result->setQuery($query);
		}
		if(in_array('send_group', $field_names_user)) {
			$query = "ALTER TABLE ". $REX['TABLE_PREFIX'] ."375_user CHANGE send_group send_archive_id TINYINT(11) UNSIGNED NOT NULL";
			$result = new rex_sql();
			$result->setQuery($query);
		}
		if(in_array('key', $field_names_user)) {
			$query = "ALTER TABLE ". $REX['TABLE_PREFIX'] ."375_user CHANGE `key` activationkey INT(6) NOT NULL";
			$result = new rex_sql();
			$result->setQuery($query);
		}
		$result_update_firstname = new rex_sql();
		$query_update_firstname = "UPDATE ". $REX['TABLE_PREFIX'] ."375_user SET firstname = '' WHERE firstname = 'anonymous'";
		$result_update_firstname->setQuery($query_update_firstname);

		$result_update_lastname = new rex_sql();
		$query_update_lastname = "UPDATE ". $REX['TABLE_PREFIX'] ."375_user SET lastname = '' WHERE lastname = 'anonymous'";
		$result_update_lastname->setQuery($query_update_lastname);
		
		$result_update_activationip = new rex_sql();
		$query_update_activationip = "UPDATE ". $REX['TABLE_PREFIX'] ."375_user SET activationip = '' WHERE activationip = '1'";
		$result_update_activationip->setQuery($query_update_activationip);
		
		
		// Tabelle rex_375_group
		$field_names_group = [];
		$query_group = "SELECT * FROM ". $REX['TABLE_PREFIX'] ."375_group LIMIT 0,1";
		$result_group = mysql_query($query_group);
		$field_group = mysql_num_fields($result_group);
		for($i = 0; $i < $field_group; $i++) {
			$field_names_group[] = mysql_field_name($result_group, $i);
		}
		// rex_375_group auf Version 2.0 aktualisieren
		if(in_array('id', $field_names_group)) {
			$query = "ALTER TABLE ". $REX['TABLE_PREFIX'] ."375_group CHANGE id group_id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT";
			$result = new rex_sql();
			$result->setQuery($query);
		}
		if(!in_array('default_sender_email', $field_names_group)) {
			$query = "ALTER TABLE ". $REX['TABLE_PREFIX'] ."375_group ADD default_sender_email VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER name";
			$result = new rex_sql();
			$result->setQuery($query);
		}
		if(!in_array('default_sender_name', $field_names_group)) {
			$query = "ALTER TABLE ". $REX['TABLE_PREFIX'] ."375_group ADD default_sender_name VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER default_sender_email";
			$result = new rex_sql();
			$result->setQuery($query);
		}
		if(!in_array('default_article_id', $field_names_group)) {
			$query = "ALTER TABLE ". $REX['TABLE_PREFIX'] ."375_group ADD default_article_id INT(11) NULL DEFAULT NULL AFTER default_sender_name";
			$result = new rex_sql();
			$result->setQuery($query);
		}

		// Tabelle rex_375_archive
		$field_names_archive = [];
		$query_archive = "SELECT * FROM ". $REX['TABLE_PREFIX'] ."375_archive LIMIT 0,1";
		$result_archive = mysql_query($query_archive);
		$field_archive = mysql_num_fields($result_archive);
		for($i = 0; $i < $field_archive; $i++) {
			$field_names_archive[] = mysql_field_name($result_archive, $i);
		}
		// rex_375_archive auf Version 1.3 aktualisieren
		if(in_array('recipients', $field_names_archive)) {
			$query = "ALTER TABLE ". $REX['TABLE_PREFIX'] ."375_archive CHANGE recipients recipients LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";
			$result = new rex_sql();
			$result->setQuery($query);
		}
		// rex_375_archive auf Version 2.0 aktualisieren
		if(in_array('id', $field_names_archive)) {
			$query = "ALTER TABLE ". $REX['TABLE_PREFIX'] ."375_archive CHANGE id archive_id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT";
			$result = new rex_sql();
			$result->setQuery($query);
		}
		if(in_array('clang', $field_names_archive)) {
			$query = "ALTER TABLE ". $REX['TABLE_PREFIX'] ."375_archive CHANGE clang clang_id  INT(11) NOT NULL";
			$result = new rex_sql();
			$result->setQuery($query);
		}
		if(in_array('groupname', $field_names_archive)) {
			$query = "ALTER TABLE ". $REX['TABLE_PREFIX'] ."375_archive DROP groupname";
			$result = new rex_sql();
			$result->setQuery($query);
		}
		if(in_array('textbody', $field_names_archive)) {
			$query = "ALTER TABLE ". $REX['TABLE_PREFIX'] ."375_archive DROP textbody";
			$result = new rex_sql();
			$result->setQuery($query);
		}
		if(in_array('format', $field_names_archive)) {
			$query = "ALTER TABLE ". $REX['TABLE_PREFIX'] ."375_archive DROP format";
			$result = new rex_sql();
			$result->setQuery($query);
		}
		if(in_array('gid', $field_names_archive)) {
			$query = "ALTER TABLE ". $REX['TABLE_PREFIX'] ."375_archive CHANGE gid group_ids TEXT NOT NULL";
			$result = new rex_sql();
			$result->setQuery($query);
		}
		if(!in_array('sender_email', $field_names_archive)) {
			$query = "ALTER TABLE ". $REX['TABLE_PREFIX'] ."375_archive ADD sender_email VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER group_ids";
			$result = new rex_sql();
			$result->setQuery($query);
		}
		if(!in_array('sender_name', $field_names_archive)) {
			$query = "ALTER TABLE ". $REX['TABLE_PREFIX'] ."375_archive ADD sender_name VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER sender_email";
			$result = new rex_sql();
			$result->setQuery($query);
		}

		// Tabelle rex_375_user2group
		$result_u2g = new rex_sql();
		$query_u2g = "SELECT * FROM ". $REX['TABLE_PREFIX'] ."375_user2group";
		$result_u2g->setQuery($query_u2g);
		if($result_u2g) {
			$num_rows_u2g = $result_u2g->getRows();
			for($i = 0; $i < $num_rows_u2g; $i++) {
				if($result_u2g->getValue("uid") > 0) {
					$result_has_user_group = new rex_sql();
					$query_has_user_group = "SELECT group_ids FROM ". $REX['TABLE_PREFIX'] ."375_user WHERE user_id = ". $result_u2g->getValue("uid");
					$result_has_user_group->setQuery($query_has_user_group);

					$new_group_ids = $result_has_user_group->getValue('group_ids');
					if(strlen($new_group_ids) == 0) {
						$new_group_ids = "|". $result_u2g->getValue("gid") ."|";
					}
					else {
						$new_group_ids .= $result_u2g->getValue("gid") ."|";
					}
					$result_update_user = new rex_sql();
					$query_update_user = "UPDATE ". $REX['TABLE_PREFIX'] ."375_user SET group_ids = '". $new_group_ids ."' WHERE user_id = ". $result_u2g->getValue("uid");
					$result_update_user->setQuery($query_update_user);
				}

				$result_u2g->next();
			}
			$query = "DROP TABLE ". $REX['TABLE_PREFIX'] ."375_user2group";
			$result = new rex_sql();
			$result->setQuery($query);
		}
	}
}