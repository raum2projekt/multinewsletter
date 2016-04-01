<?php
// Gruppen
$query = 'SELECT group_id, name  '.
		'FROM '. rex::getTablePrefix() .'375_group '.
		'ORDER BY name';
$result = new rex_sql();
$result->setQuery($query);
$num_rows = $result->getRows();

$groups = array();
for($i = 0; $i < $num_rows; $i++) {
	$groups[$result->getValue("group_id")] = $result->getValue("name");
	$result->next();
}
print 'Welche Gruppen sollen vom Nutzer abonniert werden können? Wenn nur eine '
	.'Gruppe markiert ist, wird dem Nutzer keine Auswahl angeboten.<br />';
$select_feature = new rex_select(); 
$select_feature->setName('REX_INPUT_VALUE[1][]'); 
$select_feature->setMultiple(true); 
$select_feature->setSize(10);

// Daten
foreach($groups as $group_ids => $name)  {
  $select_feature->addOption($name, $group_ids); 
}

// Vorselektierung
$features_selected = preg_grep('/^\s*$/s', explode("|", "REX_VALUE[1]"), PREG_GREP_INVERT);
foreach($features_selected as $group_ids) {
  $select_feature->setSelected($group_ids); 
}

echo $select_feature->show();
?>

<p>Texte, Bezeichnungen bzw. Übersetzugen werden im <a href="index.php?page=multinewsletter&subpage=config">Multinewsletter Addon</a> verwaltet.</p>