<?php
if ($I18N->hasMsg('multinewsletter_update_msg')) {
	$msg = $I18N->msg('multinewsletter_update_msg');
} else {
	$msg = 'Multinewsletter: Bitte beachten Sie die <a href="index.php?page=multinewsletter&subpage=help&chapter=updatehinweise">Update-Hinweise</a> für diese Version!';
}
echo rex_info($msg);

// Wenn alte Konfigurationsdatei existiert muss MultiNewsletter auf Version 2.0.0 aktualisiert werden
$old_config_file = $REX['INCLUDE_PATH'] . '/addons/multinewsletter/files/.configfile';
if(file_exists($old_config_file)) {
	// includes
	require_once($REX['INCLUDE_PATH'] . '/addons/multinewsletter/classes/class.multinewsletter_update.inc.php');
	multinewsletter_update::updateConfig();
	multinewsletter_update::updateDatabase();
}

$REX['ADDON']['update']['multinewsletter'] = 1;