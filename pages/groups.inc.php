<?php
// Übersichtsliste
if ($func == '') {
	/*
	 *  Liste anlegen 
	 */
  	$sql = 'SELECT group_id, name FROM '.
				$REX['TABLE_PREFIX'] .'375_group '
			.'ORDER BY name ASC';

	$list = rex_list::factory($sql, 50);
	// Spalten mit Sortierfunktion
	$list->setColumnSortable('group_id');
	$list->setColumnSortable('name');

	$imgHeader = '<a href="'. $list->getUrl(array('func' => 'add')) .'"><img src="media/metainfo_plus.gif" alt="add" title="add" /></a>';
	// Hinzufuegen Button
	$list->addColumn(
			$imgHeader, 
			'<img src="media/metainfo.gif" alt="field" title="field" />', 
			0, 
			array(
				'<th class="rex-icon">###VALUE###</th>',
				'<td class="rex-icon">###VALUE###</td>'
			)
	);

	// Edit Button unterhalb des hinzufuegen Buttons
	$list->setColumnParams(
			$imgHeader, 
			array('func' => 'edit', 'entry_id' => '###group_id###')
	);

	// Labels
	$list->setColumnLabel('group_id', $I18N->msg('multinewsletter_group_id'));	
	$list->setColumnLabel('name', $I18N->msg('multinewsletter_group_name'));	

	// Edit Funktion auf Zeileneintrag
	$list->setColumnParams('group_id', array('func' => 'edit', 'entry_id' => '###group_id###'));
	$list->setColumnParams('name', array('func' => 'edit', 'entry_id' => '###group_id###'));

	// Liste anzeigen
	$list->show();
}
// Eingabeformular
elseif ($func == 'edit' || $func == 'add') {
	$form = rex_form::factory($REX['TABLE_PREFIX'] .'375_group', $I18N->msg('multinewsletter_group'), "group_id = ". $entry_id, "post", false);

		// Gruppenname
		$field = $form->addTextField('name');
		$field->setLabel($I18N->msg('multinewsletter_group_name'));

		// Absender E-Mailadresse
		$field = $form->addTextField('default_sender_email');
		$field->setLabel($I18N->msg('multinewsletter_group_default_sender_email'));

		// Gruppenname
		$field = $form->addTextField('default_sender_name');
		$field->setLabel($I18N->msg('multinewsletter_group_default_sender_name'));

		// Artikel ID
		$field = $form->addLinkmapField('default_article_id');
		$field->setLabel($I18N->msg('multinewsletter_group_default_article_id'));

		if($func == 'edit') {
			$form->addParam('entry_id', $entry_id);
		}

		$form->show();
}
?>