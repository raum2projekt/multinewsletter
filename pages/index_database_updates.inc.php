<?php
// Nur einmalig ausfuehren

$query = "SELECT subscriptiontype FROM ". $REX['TABLE_PREFIX'] ."375_user";
$result = new rex_sql();
$result -> setQuery($query);
if ($result -> getErrno() > 0) { // ERROR 1054 column not exists
	print "Datenbank wird auf Version 1.2 aktualisiert.";
	// Alte Tabelle aktualisieren
	$query = "ALTER TABLE `". $REX['TABLE_PREFIX'] ."375_user` ADD  `createip` VARCHAR( 16 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `createdate`";
	$result = new rex_sql();
	$result -> setQuery($query);

	$query = "ALTER TABLE  `". $REX['TABLE_PREFIX'] ."375_user` ADD  `activationdate` INT( 11 ) NULL DEFAULT NULL AFTER  `createip`";
	$result = new rex_sql();
	$result -> setQuery($query);

	$query = "ALTER TABLE `". $REX['TABLE_PREFIX'] ."375_user` ADD  `activationip` VARCHAR( 16 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `activationdate`";
	$result = new rex_sql();
	$result -> setQuery($query);

	$query = "ALTER TABLE `". $REX['TABLE_PREFIX'] ."375_user` CHANGE  `ip`  `updateip` VARCHAR( 16 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";
	$result = new rex_sql();
	$result -> setQuery($query);

	$query = "ALTER TABLE `". $REX['TABLE_PREFIX'] ."375_user` ADD  `subscriptiontype` VARCHAR( 16 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `updateip`";
	$result = new rex_sql();
	$result -> setQuery($query);
}

$query = "SELECT grad FROM ". $REX['TABLE_PREFIX'] ."375_user";
$result = new rex_sql();
$result -> setQuery($query);
if ($result -> getErrno() > 0) { // ERROR 1054 column not exists
	print "Datenbank wird auf Version 1.4 aktualisiert.";
	// Alte Tabelle aktualisieren
	$query = "ALTER TABLE `". $REX['TABLE_PREFIX'] ."375_user` ADD  `grad` VARCHAR( 16 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `title`";
	$result = new rex_sql();
	$result -> setQuery($query);
}
?>
