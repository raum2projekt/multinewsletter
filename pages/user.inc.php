<?php
// Übersichtsliste
if ($func == '') {
	// Anzuzeigende Nachrichten
	$messages = array();
	
	// includes
	require_once($REX['INCLUDE_PATH'] . '/addons/multinewsletter/classes/class.multinewsletter_group.inc.php');
	require_once($REX['INCLUDE_PATH'] . '/addons/multinewsletter/classes/class.multinewsletter_user.inc.php');

	// Suchkriterien in Session schreiben
 	if(!isset($_SESSION['multinewsletter'])) {
		$_SESSION['multinewsletter'] = array();
	}
 	if(!isset($_SESSION['multinewsletter']['user'])) {
		$_SESSION['multinewsletter']['user'] = array();
	}
 	// Suchbegriff
	if(filter_input(INPUT_POST, 'search_query') != "") {
		$_SESSION['multinewsletter']['user']['search_query'] = filter_input(INPUT_POST, 'search_query');
		$_SESSION['multinewsletter']['user']['pagenumber'] = 1;
	}
	else {
		$_SESSION['multinewsletter']['user']['search_query'] = "";
	}

	// Sortierung
	if(filter_input(INPUT_GET, 'orderby') != "") {
		$_SESSION['multinewsletter']['user']['orderby'] = filter_input(INPUT_GET, 'orderby');
		$_SESSION['multinewsletter']['user']['direction'] = filter_input(INPUT_GET, 'direction');
	}
	else if(!isset($_SESSION['multinewsletter']['user']['orderby']) || $_SESSION['multinewsletter']['user']['orderby'] == "") {
		$_SESSION['multinewsletter']['user']['orderby'] = "email";
	}
	if(!isset($_SESSION['multinewsletter']['user']['direction'])) {
		$_SESSION['multinewsletter']['user']['direction'] = "ASC";
	}
	
	// Anzahl anzuzeigender User
	if(filter_input(INPUT_POST, 'itemsperpage') != 0 && filter_input(INPUT_POST, 'itemsperpage', FILTER_VALIDATE_INT, ['options' => ['default'=> 0]]) > 0) {
		$_SESSION['multinewsletter']['user']['itemsperpage'] = filter_input(INPUT_POST, 'itemsperpage', FILTER_VALIDATE_INT);
	}
	else if(!isset($_SESSION['multinewsletter']['user']['itemsperpage']) || $_SESSION['multinewsletter']['user']['itemsperpage'] < 25) {
		$_SESSION['multinewsletter']['user']['itemsperpage'] = 25;
	}
	
	// Seitennummer
	if(filter_input(INPUT_POST, 'pagenumber') != 0 && filter_input(INPUT_POST, 'pagenumber', FILTER_VALIDATE_INT, ['options' => ['default'=> 0]]) > 0) {
		$_SESSION['multinewsletter']['user']['pagenumber'] = (filter_input(INPUT_POST, 'pagenumber', FILTER_VALIDATE_INT) - 1);
	}
	else if(!isset($_SESSION['multinewsletter']['user']['pagenumber']) || $_SESSION['multinewsletter']['user']['pagenumber'] < 0) {
		$_SESSION['multinewsletter']['user']['pagenumber'] = 0;		
	}

	// Gewählte Gruppe
	if(filter_input(INPUT_POST, 'showgroup') != "") {
		$_SESSION['multinewsletter']['user']['showgroup'] = filter_input(INPUT_POST, 'showgroup');
	}
	else if(!isset($_SESSION['multinewsletter']['user']['showgroup']) || $_SESSION['multinewsletter']['user']['showgroup'] == "") {
		$_SESSION['multinewsletter']['user']['showgroup'] = "all";		
	}

	// Gewählter Status
	if(filter_input(INPUT_POST, 'showstatus') >= -1 && filter_input(INPUT_POST, 'showstatus', FILTER_VALIDATE_INT) >=  -1) {
		$_SESSION['multinewsletter']['user']['showstatus'] = filter_input(INPUT_POST, 'showstatus', FILTER_VALIDATE_INT);
	}
	else if(!isset($_SESSION['multinewsletter']['user']['showstatus']) || $_SESSION['multinewsletter']['user']['showstatus'] < 0) {
		$_SESSION['multinewsletter']['user']['showstatus'] = -1;
	}
	
	// Gewählte Sprache
	if(filter_input(INPUT_POST, 'showclang') >= -1 && filter_input(INPUT_POST, 'showclang', FILTER_VALIDATE_INT) >= -1) {
		$_SESSION['multinewsletter']['user']['showclang'] = filter_input(INPUT_POST, 'showclang', FILTER_VALIDATE_INT);
	}
	else if(!isset($_SESSION['multinewsletter']['user']['showclang']) || $_SESSION['multinewsletter']['user']['showclang'] < 0) {
		$_SESSION['multinewsletter']['user']['showclang'] = -1;
	}

	// Wenn Filter zurückgesetzt wurde
	if(filter_input(INPUT_POST, 'newsletter_showall') != "" ) {
		$_SESSION['multinewsletter']['user']['search_query'] = "";
		$_SESSION['multinewsletter']['user']['showgroup'] = -1;
		$_SESSION['multinewsletter']['user']['showstatus'] = -1;
		$_SESSION['multinewsletter']['user']['showclang'] = -1;
	}

	// Aktionen gewählter oder einzelner Benutzer
	$multidelete = false;
	if(filter_input(INPUT_POST, 'newsletter_delete_items') == "X") {
		$multidelete = true;
	}
	$multistatus = filter_input(INPUT_POST, 'newsletter_item_status_all');
	$multiclang = filter_input(INPUT_POST, 'newsletter_item_clang_all');
	$multigroup = filter_input(INPUT_POST, 'addtogroup');

	$post = filter_input_array(INPUT_POST);
	$selected_users = array();
	if(isset($post['newsletter_select_item'])) {
		$selected_users = array_keys($post['newsletter_select_item']);
	}
	$form_users = array();
	if(isset($post['newsletter_item'])) {
		$form_users = $post['newsletter_item'];
	}

	if(is_array($form_users)) {
		$aktion = false;
		foreach($form_users as $user_id => $fields) {
			$user = new MultinewsletterUser($user_id, $REX['TABLE_PREFIX']);

			// Einzelaktionen
			foreach($fields as $group_ids => $value) {
				// Gewählten Benutzer löschen
				if($group_ids == 'deleteme') { 
					$user->delete();
					$aktion = true;
				}
			}
			
			// Multiselect Aktionen
			if(in_array($user_id, $selected_users)) {
				// Gewählten Benutzer löschen
				if($multidelete) { 
					$user->delete();
					$aktion = true;
				}
				else {
					// Status des gewählten Benutzers aktualisieren
					if($multistatus > -1) { 
						$user->status = $multistatus;
					}
					// Sprache des gewählten Benutzers aktualisieren
					if($multiclang > -1) { 
						$user->clang_id = $multiclang;
					}
					// Gruppe des gewählten Benutzers aktualisieren
					if($multigroup == "none") { 
						$user->group_ids = array();
					}
					else if($multigroup == "all") {
						$all_groups = MultinewsletterGroupList::getAll($REX['TABLE_PREFIX']);
						$all_group_ids = array();
						foreach($all_groups as $group) {
							$all_group_ids[] = $group->group_id;
						}
						$user->group_ids = $all_group_ids;
					}
					else if(intval($multigroup) > 0) {
						if(in_array($multigroup, $user->group_ids)) {
							continue;
						}
						else {
							$user->group_ids[] = $multigroup;
						}
					}
					$user->save();
					$aktion = true;
				}
			}
		}
		if($aktion) {
			$messages[] = $I18N->msg('multinewsletter_changes_saved');
		}
	}

	// Liste anzuzeigender User holen
	$result_list = new rex_sql();
	$query_where = "";
	$where = array();
	if($_SESSION['multinewsletter']['user']['search_query'] != "") {
		$where[] = "(email LIKE '%". $_SESSION['multinewsletter']['user']['search_query'] ."%' "
			."OR firstname LIKE '%". $_SESSION['multinewsletter']['user']['search_query'] ."%' "
			."OR lastname LIKE '%". $_SESSION['multinewsletter']['user']['search_query'] ."%')";
	}
	if(intval($_SESSION['multinewsletter']['user']['showgroup']) > 0) {
		$where[] = "group_ids LIKE '%|". $_SESSION['multinewsletter']['user']['showgroup'] ."|%'";
	}
	else if($_SESSION['multinewsletter']['user']['showgroup'] == "no") {
		$where[] = "(group_ids = '' OR group_ids IS NULL)";
	}
	if($_SESSION['multinewsletter']['user']['showstatus'] >= 0) {
		$where[] = "status = ". $_SESSION['multinewsletter']['user']['showstatus'];
	}
	if($_SESSION['multinewsletter']['user']['showclang'] >= 0) {
		$where[] = "clang_id = ". $_SESSION['multinewsletter']['user']['showclang'];
	}
	if(count($where) > 0) {
		$query_where .= " WHERE ". join(" AND ", $where) ." ";
	}
	if($_SESSION['multinewsletter']['user']['orderby']) {
		$query_where .= "ORDER BY ". $_SESSION['multinewsletter']['user']['orderby'] ." ". $_SESSION['multinewsletter']['user']['direction'];
	}
	$query_count = "SELECT COUNT(*) as counter FROM ". $REX['TABLE_PREFIX'] ."375_user ". $query_where;
	$result_list->setQuery($query_count);
	$count_users = $result_list->getValue("counter");
	
	$start = $_SESSION['multinewsletter']['user']['pagenumber'] * $_SESSION['multinewsletter']['user']['itemsperpage'];
	if($start > $count_users) {
		// Wenn die Seitenanzahl über den möglichen Seiten liegt
		$start = 0;
		$_SESSION['multinewsletter']['user']['pagenumber'] = 0;
	}
	$query_list = "SELECT user_id FROM ". $REX['TABLE_PREFIX'] ."375_user ". $query_where. " LIMIT ". $start .",". $_SESSION['multinewsletter']['user']['itemsperpage'];
	$result_list->setQuery($query_list);
	$num_rows_list = $result_list->getRows();
	
	$user_ids = array();
	for($i = 0; $i < $num_rows_list; $i++) {
		$user_ids[] = $result_list->getValue("user_id");
		$result_list->next();
	}
	
	$users = new MultinewsletterUserList($user_ids, $REX['TABLE_PREFIX']);

	// Ausgabe der Meldungen
	if(!empty($messages)) {
		echo '<p class="rex-message rex-warning"><span>';
		foreach($messages as $message) {
			echo $message .'<br />';
		}
		echo '</span></p><br />';
	}

	$newsletter_groups = MultinewsletterGroupList::getAll($REX['TABLE_PREFIX']);
?>
	<form action="<?php print $page_base_url; ?>" method="post" name="MULTINEWSLETTER">
		<table class="rex-table">
			<thead>
				<tr>
					<td class="rex-icon">&nbsp;</td>
					<td colspan="3">
						<ul style="list-style-type: none; line-height: 25px; margin: 0px">
						<?php
							$select = new rex_select();
							$select->setSize(1);
							$select->setAttribute('class','newsletter_select_small');
							$select->setName('itemsperpage');
							$numbers_per_page = array(25, 50, 100, 200, 450);
							foreach($numbers_per_page as $number) {
								$select->addOption($number .' '. $I18N->msg('multinewsletter_filter_pro_seite'), $number);
							}
							$select->setSelected($_SESSION['multinewsletter']['user']['itemsperpage']);
							$select->setAttribute("onchange","this.form.submit()");
							echo '<li class="clearfix"><label>'. $I18N->msg('multinewsletter_filter_itemsperpage') .'</label>'. $select->get() .'</li>';

							if(!empty($newsletter_groups)) {
								$groups = new rex_select();
								$groups->setSize(1);
								$groups->setAttribute('class', 'myrex_select');
								$groups->addOption($I18N->msg('multinewsletter_all_groups'),'all');
								foreach($newsletter_groups as $group) {
									$groups->addOption($group->name, $group->group_id);
								}
								$groups->addOption($I18N->msg('multinewsletter_no_groups'),'no');
								$groups->setSelected($_SESSION['multinewsletter']['user']['showgroup']);
								$groups->setAttribute("onchange","this.form.submit()");
								$groups->setName('showgroup');
								echo '<li class="clearfix"><label>'. $I18N->msg('multinewsletter_filter_groups') .'</label>'. $groups->get() .'</li>';
							}               
						?>
							<li><input type="text" name="search_query" value="<?php print htmlspecialchars(stripslashes($_SESSION['multinewsletter']['user']['search_query']),ENT_QUOTES)?>" class="myrex_search" />
								<input type="submit" name="search" value="" class="search"></li>
						</ul>
					</td>
					<td colspan="5" style="min-width: 320px">
						<ul style="list-style-type: none; line-height: 25px; margin: 0px;">
						<?php
							$select = new rex_select();
							$select->setSize(1);
							$select->setName('showstatus');
							$select->addOption($I18N->msg('multinewsletter_status_online'), 1);
							$select->addOption($I18N->msg('multinewsletter_status_offline'), 0);
							$select->addOption($I18N->msg('multinewsletter_status_unsubscribed'), 2);
							$select->addOption($I18N->msg('multinewsletter_status_all'), -1);
							$select->setSelected($_SESSION['multinewsletter']['user']['showstatus']);
							$select->setAttribute("onchange","this.form.submit()");
							echo '<li class="clearfix"><label>'. $I18N->msg('multinewsletter_filter_status') .'</label>'. $select->get() .'</li>';

							$select = new rex_select();
							$select->setSize(1);
							$select->setAttribute("onchange","this.form.submit()");
							if(count($REX['CLANG']) > 1) {
								$select->setName('showclang');
								$select->addOption($I18N->msg('multinewsletter_clang_all'), -1);
								foreach($REX['CLANG'] as $group_ids => $value) {
									$select->addOption($value,$group_ids);
								}
								$select->setSelected($_SESSION['multinewsletter']['user']['showclang']);
								echo '<li class="clearfix"><label>'.$I18N->msg('multinewsletter_filter_clang').'</label>'.$select->get().'</li>';
							}
						?>
							<li class="clearfix"><input style="width:100%;" class="myrex_submit" type="submit" name="newsletter_showall" id="newsletter_showall" value="<?php print $I18N->msg('multinewsletter_button_submit_showall'); ?>" /></li>
							<li class="clearfix"><?php print $count_users ." ". $I18N->msg('multinewsletter_users_found'); ?></li>
						</ul>
					</td>
				</tr>
				<tr>
					<th style="width:5%" class="rex-icon"><a href="<?php print $page_base_url; ?>&func=add"><img src="./media/user_plus.gif" /></a></th>
					<th style="width:20%"><a href="<?php print $page_base_url; ?>&orderby=email<?php print (($_SESSION['multinewsletter']['user']['orderby'] == 'email' && $_SESSION['multinewsletter']['user']['direction'] == 'ASC') ? '&direction=DESC' : '&direction=ASC')?>"><?php print $I18N->msg('multinewsletter_newsletter_email')?></a></th>
					<th style="width:20%"><a href="<?php print $page_base_url; ?>&orderby=firstname<?php print (($_SESSION['multinewsletter']['user']['orderby'] == 'firstname' && $_SESSION['multinewsletter']['user']['direction'] == 'ASC')  ? '&direction=DESC' : '&direction=ASC')?>"><?php print $I18N->msg('multinewsletter_newsletter_firstname')?></a></th>
					<th style="width:20%"><a href="<?php print $page_base_url; ?>&orderby=lastname<?php print (($_SESSION['multinewsletter']['user']['orderby'] == 'lastname' && $_SESSION['multinewsletter']['user']['direction'] == 'ASC')  ? '&direction=DESC' : '&direction=ASC')?>"><?php print $I18N->msg('multinewsletter_newsletter_lastname')?></a></th>
					<th style="width:5%"><a href="<?php print $page_base_url; ?>&orderby=clang_id<?php print (($_SESSION['multinewsletter']['user']['orderby'] == 'clang_id' && $_SESSION['multinewsletter']['user']['direction'] == 'ASC') ? '&direction=DESC' : '&direction=ASC')?>"><?php print $I18N->msg('multinewsletter_newsletter_clang')?></a></th>
					<th style="width:5%"><a href="<?php print $page_base_url; ?>&orderby=createdate<?php print (($_SESSION['multinewsletter']['user']['orderby'] == 'createdate' && $_SESSION['multinewsletter']['user']['direction'] == 'ASC') ? '&direction=DESC' : '&direction=ASC')?>"><?php print $I18N->msg('multinewsletter_newsletter_create')?></a></th>
					<th style="width:5%"><a href="<?php print $page_base_url; ?>&orderby=updatedate<?php print (($_SESSION['multinewsletter']['user']['orderby'] == 'updatedate' && $_SESSION['multinewsletter']['user']['direction'] == 'ASC') ? '&direction=DESC' : '&direction=ASC')?>"><?php print $I18N->msg('multinewsletter_newsletter_update')?></a></th>
					<th style="width:5%"><a href="<?php print $page_base_url; ?>&orderby=status<?php print (($_SESSION['multinewsletter']['user']['orderby'] == 'status' && $_SESSION['multinewsletter']['user']['direction'] == 'ASC') ? '&direction=DESC' : '&direction=ASC')?>"><?php print $I18N->msg('multinewsletter_newsletter_status')?></a></th>
					<th align="center" style="width:5%"><?php print $I18N->msg('multinewsletter_newsletter_delete')?></th>
				</tr>
			</thead>
			<tbody style="font-size: 0.85em;">
			<?php
				if(!empty($users->users)) {
					$status = new rex_select();
					$status->setSize(1);
					$status->setAttribute('style','width: 50px');
					$status->addOption($I18N->msg('multinewsletter_status_online'), 1);
					$status->addOption($I18N->msg('multinewsletter_status_offline'), 0);
					$status->addOption($I18N->msg('multinewsletter_status_unsubscribed'), 2);

					foreach($users->users as $user) {
						// Status je nach Nutzer setzen
						$status->resetSelected();
						$status->setName('newsletter_item['. $user->user_id .'][status]');
						$status->setSelected($user->status);
						$status->setAttribute("onchange","this.form['newsletter_select_item[". $user->user_id ."]'].checked=true");
						
						print '<tr class="myrex_normal">';
						print '<td class="rex-icon"><input type="checkbox" name="newsletter_select_item['. $user->user_id .']" value="true" style="width:auto" onclick="myrex_selectallitems(\'newsletter_select_item\',this)" /></td>';
						print '<td><a href="'. $page_base_url .'&func=edit&entry_id='.$user->user_id.'">'. htmlspecialchars($user->email).'</a></td>';
						print '<td>'. htmlspecialchars($user->firstname) .'</td>';
						print '<td>'. htmlspecialchars($user->lastname) .'</td>';
						if(isset($REX['CLANG'][$user->clang_id])) {
							print '<td>'. $REX['CLANG'][$user->clang_id] .'</td>';
						}
						else {
							print '<td></td>';
						}
						if($user->createdate > 0) {
							print '<td>'.date('d.m.Y H:i:s', $user->createdate).'</td>';
						}
						else {
							print '<td>&nbsp;</td>';
						}
						if($user->updatedate > 0) {
							print '<td>'.date('d.m.Y H:i:s', $user->updatedate).'</td>';
						}
						else {
							print '<td>&nbsp;</td>';
						}
						print '<td>'. $status->get() .'</td>';
						print '<td align="center"><input type="submit" class="myrex_submit_delete" name="newsletter_item['. $user->user_id .'][deleteme]" onclick="return myrex_confirm(\''. $I18N->msg('multinewsletter_confirm_deletethis') .'\',this.form)" value="X" /></td>';
						print '</tr>';
					}

					$status->setName('newsletter_item_status_all');
					$status->setAttribute("onchange","if(this.value>-1) myrex_deselectStatus(this.form,'newsletter_item_status',true); else myrex_deselectStatus(this.form,'newsletter_item_status',false)");
					$status->addOption($I18N->msg('multinewsletter_get_each_status'),'-1');
					$status->resetSelected();
					$status->setSelected('-1');
			?>
				<tr>
					<td valign="middle" class="rex-icon"><input class="myrex_checkbox" type="checkbox" name="newsletter_select_item_all" value="true" style="width:auto" onclick="myrex_selectallitems('newsletter_select_item', this)" /></td>
					<td valign="middle"><strong><?php print $I18N->msg('multinewsletter_edit_all_selected'); ?></strong></td>
					<td colspan="2">
					<?php
						if(!empty($newsletter_groups)) {
							$groups = new rex_select();
							$groups->setSize(1);
							$groups->setAttribute('class', 'myrex_select');
							$groups->setAttribute('style','width:100%');

							$groups->addOption($I18N->msg('multinewsletter_button_addtogroup'),'empty');
							$groups->addOption($I18N->msg('multinewsletter_remove_from_all_groups'),'none');
							foreach($newsletter_groups as $group) {
								$groups->addOption($I18N->msg('multinewsletter_add_to_group', $group->name), $group->group_id);
							}
							$groups->addOption($I18N->msg('multinewsletter_add_to_all_groups'),'all');
							$groups->setName('addtogroup');
							$groups->show();
						}
					?>
					</td>            
					<td valign="middle">
					<?php
						$select = new rex_select();
						$select->setSize(1);
						$select->setAttribute('style','width: 50px');
						$select->setName('newsletter_item_clang_all');
						$select->addOption($I18N->msg('multinewsletter_get_each_clang'),'-1');
						foreach($REX['CLANG'] as $group_ids=>$value) {
							$select->addOption($value,$group_ids);
						}
						$select->resetSelected();
						$select->setSelected('-1');
						$select->show();           
					?>
					</td>
					<td valign="middle"></td>
					<td valign="middle"></td>
					<td valign="middle"><?php print $status->get()?></td>
					<td valign="middle" align="center"><input type="submit" class="myrex_submit_delete" name="newsletter_delete_items" onclick="return myrex_confirm('<?php print $I18N->msg('multinewsletter_confirm_deleteselected'); ?>',this.form)" title="<?php print $I18N->msg('multinewsletter_button_submit_delete'); ?>" value="X" /></td>
				</tr>
			</tbody>
			<tfoot>
				<tr class="myrex_spacebelow">
					<td class="rex-icon">&nbsp;</td>
					<td colspan="3"><input type="submit" style="width:100%" class="myrex_submit" name="newsletter_save_all_items" onclick="return myrex_confirm('<?php print $I18N->msg('multinewsletter_confirm_save_all_items'); ?>',this.form)" value="<?php print $I18N->msg('multinewsletter_button_save_all_items'); ?>" /></td>
					<td colspan="5"> </td>
				</tr>
				<?php
					// check, if there are more items to show
					if($count_users > $_SESSION['multinewsletter']['user']['itemsperpage']) {
				?>
				<tr class="myrex_spacebelow">
					<td class="rex-icon">&nbsp;</td>
					<td colspan="8">
					<?php
						// show the pagination
						$temp = ceil($count_users / $_SESSION['multinewsletter']['user']['itemsperpage']);
						for($i = 0; $i < $temp; $i++) {
							if($i != $_SESSION['multinewsletter']['user']['pagenumber']) {
								echo '<input style="width:30px; margin-right: 5px;" type="submit" class="myrex_submit" name="pagenumber" value="'. strval($i + 1) .'" />';
							}
							else {
								echo '<input type="submit" class="myrex_submit" name="pagenumber" value="'. strval($i + 1) .'" style="width:30px; background-color: #2C8EC0; margin-right: 5px;" class="myrex_submit" onClick="return false;"/>';
							}
						}
					?>
					</td>
				</tr>
				<?php
					}
				?>
				<tr>
					<td class="rex-icon">&nbsp;</td>
					<td colspan="3"><input style="width:100%;" class="myrex_submit" type="submit" name="newsletter_exportusers" id="newsletter_exportusers" value="<?php print $I18N->msg('multinewsletter_button_submit_exportusers'); ?>" /></td>
					<td colspan="5"> </td>
				</tr>
			<?php
				} // ENDE Wenn Benutzer vorhanden sind
				else {
			?>
				<tr>
					<td class="rex-icon">&nbsp;</td>
					<td colspan="8"><?php print $I18N->msg('multinewsletter_no_items_found')?></td>
				</tr>
			<?php
				}
			?>
			</tbody>
		</table>
	</form>
<?php
}
// Eingabeformular
elseif ($func == 'edit' || $func == 'add') {
	$form = rex_form::factory($REX['TABLE_PREFIX'] .'375_user', $I18N->msg('multinewsletter_newsletter_userdata'), "user_id = ". $entry_id, "post", false);

		// E-Mail
		$field = $form->addTextField('email');
		$field->setLabel($I18N->msg('multinewsletter_newsletter_email'));

		// Akademischer Titel
		$field = $form->addTextField('grad');
		$field->setLabel($I18N->msg('multinewsletter_newsletter_grad'));

		// Anrede
		$field = $form->addSelectField('title');
		$field->setLabel($I18N->msg('multinewsletter_newsletter_title'));
		$select = $field->getSelect();
		$select->setSize(1);
	   	$select->addOption($I18N->msg('multinewsletter_newsletter_title0'), 0);
	   	$select->addOption($I18N->msg('multinewsletter_newsletter_title1'), 1);
		$field->setAttribute('style','width: 25%');
		
		// Vorname
		$field = $form->addTextField('firstname');
		$field->setLabel($I18N->msg('multinewsletter_newsletter_firstname'));

		// Nachname
		$field = $form->addTextField('lastname');
		$field->setLabel($I18N->msg('multinewsletter_newsletter_lastname'));

		// Sprache
		$field = $form->addSelectField('clang_id');
		$field->setLabel($I18N->msg('multinewsletter_newsletter_clang'));
		$select = $field->getSelect();
		$select->setSize(1);
		foreach($REX['CLANG'] as $clangId => $clangName) {
		   	$select->addOption($clangName, $clangId);
		}
		$field->setAttribute('style','width: 25%');
		
		// Status
		$field = $form->addSelectField('status');
		$field->setLabel($I18N->msg('multinewsletter_newsletter_status'));
		$select = $field->getSelect();
		$select->setSize(1);
	   	$select->addOption($I18N->msg('multinewsletter_status_offline'), 0);
	   	$select->addOption($I18N->msg('multinewsletter_status_online'), 1);
		$select->addOption($I18N->msg('multinewsletter_status_unsubscribed'), 2);
		$field->setAttribute('style','width: 25%');
		
		// Auswahlfeld Gruppen
		$field = $form->addSelectField('group_ids');
		$field->setLabel($I18N->msg('multinewsletter_newsletter_group'));
		$select = $field->getSelect();
		$select->setSize(5);
		$select->setMultiple(1);
		$query = 'SELECT name, group_id FROM '. $REX['TABLE_PREFIX'].'375_group ORDER BY name';
	   	$select->addSqlOptions($query);
		$field->setAttribute('style','width: 25%');
		
		if($func == 'edit') {
			// Erstellt und Aktualisiert
			$query_archive = "SELECT * FROM ". $REX['TABLE_PREFIX'] ."375_user WHERE user_id = ". $entry_id;
			$result_archive = new rex_sql();
			$result_archive->setQuery($query_archive);
			$rows_counter = $result_archive->getRows();
			if($rows_counter > 0) {
				$createdate = "-";
				if($result_archive->getValue("createdate") > 0) {
					$createdate = date("d.m.Y H:i", $result_archive->getValue("createdate"));
				}
				$form->addRawField(raw_field($I18N->msg('multinewsletter_newsletter_createdate'), $createdate));
				$form->addRawField(raw_field($I18N->msg('multinewsletter_newsletter_createip'),
						$result_archive->getValue("createip")));
				
				$activationdate = "-";
				if($result_archive->getValue("activationdate") > 0) {
					$activationdate = date("d.m.Y H:i", $result_archive->getValue("activationdate"));
				}
				$form->addRawField(raw_field($I18N->msg('multinewsletter_newsletter_activationdate'), $activationdate));
				$form->addRawField(raw_field($I18N->msg('multinewsletter_newsletter_activationip'),
						$result_archive->getValue("activationip")));

				$updatedate = "-";
				if($result_archive->getValue("updatedate") > 0) {
					$updatedate = date("d.m.Y H:i", $result_archive->getValue("updatedate"));
				}
				$form->addRawField(raw_field($I18N->msg('multinewsletter_newsletter_updatedate'), $updatedate));
				$form->addRawField(raw_field($I18N->msg('multinewsletter_newsletter_updateip'),
						$result_archive->getValue("updateip")));
				
				$form->addRawField(raw_field($I18N->msg('multinewsletter_newsletter_subscriptiontype'),
						$result_archive->getValue("subscriptiontype")));
			}
		
			$field = $form->addHiddenField('updatedate');
			$field->setValue(time());
			
			$field = $form->addHiddenField('updateip');
			$field->setValue(filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP));

			$form->addParam('entry_id', $entry_id);
		}
		else if($func == 'add') {
			$field = $form->addHiddenField('createdate');
			$field->setValue(time());
			
			$field = $form->addHiddenField('createip');
			$field->setValue(filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP));			

			$field = $form->addHiddenField('subscriptiontype');
			$field->setValue("backend");			
		}

		// Aktivierungsschlüssel
		$field = $form->addTextField('activationkey');
		$field->setLabel($I18N->msg('multinewsletter_newsletter_key'));

		$form->show();
}
else if($func == "export") {
	// Export der Benutzerdaten
	require_once($REX['INCLUDE_PATH'] . '/addons/multinewsletter/classes/class.multinewsletter_user.inc.php');

	$result_list = new rex_sql();
	$query_where = "";
	$where = array();
	if($_SESSION['multinewsletter']['user']['search_query'] != "") {
		$where[] = "(email LIKE '%". $_SESSION['multinewsletter']['user']['search_query'] ."%' "
			."OR firstname LIKE '%". $_SESSION['multinewsletter']['user']['search_query'] ."%' "
			."OR lastname LIKE '%". $_SESSION['multinewsletter']['user']['search_query'] ."%')";
	}
	if(filter_var($_SESSION['multinewsletter']['user']['showgroup'], FILTER_VALIDATE_INT) !== false) {
		$where[] = "group_ids LIKE '%|". $_SESSION['multinewsletter']['user']['showgroup'] ."|%'";
	}
	if($_SESSION['multinewsletter']['user']['showstatus'] >= 0) {
		$where[] = "status = ". $_SESSION['multinewsletter']['user']['showstatus'];
	}
	if($_SESSION['multinewsletter']['user']['showclang'] >= 0) {
		$where[] = "clang_id = ". $_SESSION['multinewsletter']['user']['showclang'];
	}
	if(count($where) > 0) {
		$query_where .= " WHERE ". join(" AND ", $where) ." ";
	}
	if($_SESSION['multinewsletter']['user']['orderby']) {
		$query_where .= "ORDER BY ". $_SESSION['multinewsletter']['user']['orderby'] ." ". $_SESSION['multinewsletter']['user']['direction'];
	}
	$start = $_SESSION['multinewsletter']['user']['pagenumber'] * $_SESSION['multinewsletter']['user']['itemsperpage'];
	$query_list = "SELECT user_id FROM ". $REX['TABLE_PREFIX'] ."375_user ". $query_where;

	$result_list->setQuery($query_list);
	$num_rows_list = $result_list->getRows();
	$user_ids = array();
	for($i = 0; $i < $num_rows_list; $i++) {
		$user_ids[] = $result_list->getValue('user_id');
		$result_list->next();
	}

	$users = new MultinewsletterUserList($user_ids, $REX['TABLE_PREFIX']);
	$users->exportCSV();
}