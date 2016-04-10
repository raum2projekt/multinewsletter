<?php
$sql = rex_sql::factory();

// Datenbankengine auf Redaxo Standard umstellen
$sql->setQuery('ALTER TABLE  ' . rex::getTablePrefix() . '375_archive ENGINE = INNODB;');
$sql->setQuery('ALTER TABLE  ' . rex::getTablePrefix() . '375_group ENGINE = INNODB;');
$sql->setQuery('ALTER TABLE  ' . rex::getTablePrefix() . '375_user ENGINE = INNODB;');