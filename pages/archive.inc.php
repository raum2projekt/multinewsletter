<?php
// Übersichtsliste
if ($func == '') {
	/*
	 *  Liste anlegen 
	 */
  	$sql = 'SELECT archive_id, subject, clang_id, FROM_UNIXTIME(sentdate) as sentdate FROM '.
				$REX['TABLE_PREFIX'] .'375_archive '
			.'ORDER BY sentdate DESC';

	$list = rex_list::factory($sql, 50);
	// Spalten mit Sortierfunktion
	$list->setColumnSortable('archive_id');
	$list->setColumnSortable('subject');
	$list->setColumnSortable('clang_id');
	$list->setColumnSortable('sentdate');

	$imgHeader = '<a href="'. $list->getUrl(array('func' => 'add')) .'"><img src="media/metainfo_plus.gif" alt="add" title="add" /></a>';

	// Edit Button unterhalb des hinzufuegen Buttons
	$list->setColumnParams(
			$imgHeader, 
			array('func' => 'edit', 'entry_id' => '###group_id###')
	);

	// Labels
	$list->setColumnLabel('archive_id', $I18N->msg('multinewsletter_group_id'));	
	$list->setColumnLabel('subject', $I18N->msg('multinewsletter_archive_subject'));	
	$list->setColumnLabel('clang_id', $I18N->msg('multinewsletter_newsletter_clang'));	
	$list->setColumnLabel('sentdate', $I18N->msg('multinewsletter_archive_sentdate'));	

	// Edit Funktion auf Zeileneintrag
	$list->setColumnParams('archive_id', array('func' => 'edit', 'entry_id' => '###archive_id###'));
	$list->setColumnParams('subject', array('func' => 'edit', 'entry_id' => '###archive_id###'));

	// Liste anzeigen
	$list->show();
}
// Eingabeformular
elseif ($func == 'edit') {
	$form = rex_form::factory($REX['TABLE_PREFIX'] .'375_archive', $I18N->msg('multinewsletter_menu_archive'), "archive_id = ". $entry_id, "post", false);

		$query_archive = "SELECT * FROM ". $REX['TABLE_PREFIX'] ."375_archive "
			."WHERE archive_id = ". $entry_id;
		$result_archive = new rex_sql();
		$result_archive->setQuery($query_archive);

		// Sprach ID
		$form->addRawField(raw_field($I18N->msg('multinewsletter_archive_language'),
			$REX['CLANG'][$result_archive->getValue("clang_id")]));

		// Betreff
		$form->addRawField(raw_field($I18N->msg('multinewsletter_archive_subject'),
			$result_archive->getValue("subject")));

		// Inhalt
		$form->addRawField(raw_field($I18N->msg('multinewsletter_archive_htmlbody'),
			'<a href="'. $page_base_url .'&shownewsletter='. $entry_id .'" target="_blank">'. $I18N->msg('multinewsletter_archive_output_details') .'</a>'));

		// Empfänger
		$recipients = preg_grep('/^\s*$/s', explode(",", $result_archive->getValue("recipients")), PREG_GREP_INVERT);
		$recipients_html = '<div style="font-size: 0.75em; float: right; width: 590px; max-height: 400px; overflow:auto;"><table><tr>';
		foreach($recipients as $key => $recipient) {
			$recipients_html .= "<td>". strtolower($recipient) ."</td>";
			if($key > 1 && $key % 3 == 2) {
				$recipients_html .= "</tr><tr>";				
			}
		}
		$recipients_html .= "</tr></table></div>";
		$form->addRawField(raw_field($I18N->msg('multinewsletter_archive_recipients'),
			$recipients_html));

		// E-Mailadresse Absender
		$form->addRawField(raw_field($I18N->msg('multinewsletter_group_default_sender_email'),
			$result_archive->getValue("sender_email")));

		// Empfänger Gruppen
		$form->addRawField(raw_field($I18N->msg('multinewsletter_archive_groupname'),
			$result_archive->getValue("group_ids")));

		// Name Absender
		$form->addRawField(raw_field($I18N->msg('multinewsletter_group_default_sender_name'),
			$result_archive->getValue("sender_name")));

		// Erstellungsdatum
		$form->addRawField(raw_field($I18N->msg('multinewsletter_newsletter_createdate'),
					date("d.m.Y H:i", $result_archive->getValue("setupdate"))));

		// Sendedatum
		$form->addRawField(raw_field($I18N->msg('multinewsletter_archive_sentdate'),
					date("d.m.Y H:i", $result_archive->getValue("sentdate"))));

		// Redaxo Benutzer vom Versand
		$form->addRawField(raw_field($I18N->msg('multinewsletter_archive_redaxo_sender'),
			$result_archive->getValue("sender_name")));

		if($func == 'edit') {
			$form->addParam('entry_id', $entry_id);
		}

		$form->show();

		print '<style type="text/css">'
		.'#rex_375_archive_Archiv_save {visibility:hidden}'
		.'#rex_375_archive_Archiv_apply {visibility:hidden}'
		.'</style>';
}
elseif ($func == 'shownewsletter') {
	$query_archive = "SELECT * FROM ". $REX['TABLE_PREFIX'] ."375_archive "
		."WHERE archive_id = ". filter_input(INPUT_GET, 'shownewsletter', FILTER_VALIDATE_INT);
	$result_archive = new rex_sql();
	$result_archive->setQuery($query_archive);

	print base64_decode($result_archive->getValue("htmlbody"));
	exit;
}