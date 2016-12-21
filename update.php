<?php
$sql = rex_sql::factory();

// Datenbankengine auf Redaxo Standard umstellen
$sql->setQuery('ALTER TABLE  ' . rex::getTablePrefix() . '375_archive ENGINE = INNODB;');
$sql->setQuery('ALTER TABLE  ' . rex::getTablePrefix() . '375_group ENGINE = INNODB;');
$sql->setQuery('ALTER TABLE  ' . rex::getTablePrefix() . '375_user ENGINE = INNODB;');

// Update modules
$d2u_module_manager = new D2UModuleManager(D2UMultiNewsletterModules::getD2UMultiNewsletterModules(), "modules/", "multinewsletter");
$d2u_module_manager->autoupdate();