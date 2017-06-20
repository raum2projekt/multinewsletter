<?php
$messages = [];
	
// Wenn Formular schon ausgefüllt wurde
if(filter_input(INPUT_POST, 'import') != "") {
	if(empty($_FILES['newsletter_file'])) {
		$messages[] = $I18N->msg('multinewsletter_error_nofile');
	}
	else if(file_exists($_FILES['newsletter_file']['tmp_name'])) {
		require_once $REX['INCLUDE_PATH'] .'/addons/multinewsletter/classes/class.multinewsletter_csv.inc.php';

		$CSV = new CSV();
		$CSV->fImport($_FILES['newsletter_file']['tmp_name'], filesize($_FILES['newsletter_file']['tmp_name']));
		$csv_users = $CSV->data;
				
		if(!empty($csv_users) && is_array($csv_users)) {
			$fields = array(
				'email' => -1,
				'grad' => -1,
				'firstname' => -1,
				'lastname' => -1,
				'title' => -1,
				'clang' => -1,
				'clang_id' => -1,
				'status' => -1,
				'createip' => -1,
				'send_group' => -1,
				'group_ids' => -1
			);
			// Überschriften auslesen
			foreach($csv_users[0] as $id => $name) {
				$fields[$name] = $id;
			}
			// Spalte "email" muss existieren
			if($fields['email'] > -1) {
				require_once $REX['INCLUDE_PATH'] .'/addons/multinewsletter/classes/class.multinewsletter_user.inc.php';
				$multinewsletter_list = new MultinewsletterUserList(array(), $REX['TABLE_PREFIX']);
				foreach($csv_users as $csv_user) {
					if(filter_var(trim($csv_user[$fields['email']]), FILTER_VALIDATE_EMAIL) !== false) {
						$multinewsletter_user = MultinewsletterUser::initByMail($csv_user[$fields['email']], $REX['TABLE_PREFIX']);
						// Sprache
						$user_clang = 0;
						if($fields['clang'] > -1 && key_exists($csv_user[$fields['clang_id']], $REX['CLANG'])) {
							$user_clang = $csv_user[$fields['clang']];
						}
						else if($fields['clang_id'] > -1 && key_exists($csv_user[$fields['clang_id']], $REX['CLANG'])) {
							$user_clang = $csv_user[$fields['clang_id']];
						}
						else {
							if(filter_var($REX['ADDON']['multinewsletter']['settings']['default_lang'], FILTER_VALIDATE_INT, ['options' => ['default'=> 0]]) > 0) {
								// Standardsprache
								$user_clang = $REX['ADDON']['multinewsletter']['settings']['default_lang'];
							}
							else {
								// Sonst einfach erste Sprache
								$lang_ids = array_keys($REX['CLANG']);
								$user_clang = reset($lang_ids);
							}
						}
						if(filter_var($user_clang, FILTER_VALIDATE_INT) !== false) {
							// Falls ID der Sprache im CSV festgelegt wurde
							$multinewsletter_user->clang_id = filter_var($user_clang, FILTER_VALIDATE_INT);
						}
						else {
							// Falls Name der Sprach in CSV festgelegt wurde
							foreach($REX['CLANG'] as $clang_id => $clang_name) {
								if($clang_name == $user_clang) {
									$multinewsletter_user->clang_id = $clang_id;
									break;
								}
							}
						}
							
						// Akademischer Grad
						if($fields['grad'] > -1 && $csv_user[$fields['grad']] != "") {
							$multinewsletter_user->grad = $csv_user[$fields['grad']];
						}
						// Vorname
						if($fields['firstname'] > -1 && $csv_user[$fields['firstname']] != "") {
							$multinewsletter_user->firstname = trim($csv_user[$fields['firstname']]);
						}
						// Nachname
						if($fields['lastname'] > -1 && $csv_user[$fields['lastname']] != "") {
							$multinewsletter_user->lastname = trim($csv_user[$fields['lastname']]);
						}
						// Anrede
						if($fields['title'] > -1 && filter_var($csv_user[$fields['title']], FILTER_VALIDATE_INT) !== false) {
							$multinewsletter_user->title = filter_var($csv_user[$fields['title']], FILTER_VALIDATE_INT);
						}
						// Status
						if($fields['status'] > -1 && filter_var($csv_user[$fields['status']], FILTER_VALIDATE_INT) !== false) {
							$multinewsletter_user->status = filter_var($csv_user[$fields['status']], FILTER_VALIDATE_INT);
						}
						// IP Adresse (erstellt)
						if($fields['createip'] > -1 && filter_var($csv_user[$fields['createip']], FILTER_VALIDATE_IP) !== false ) {
							$multinewsletter_user->createIP = filter_var($csv_user[$fields['createip']], FILTER_VALIDATE_IP);
						}
						else {
							$multinewsletter_user->createIP = filter_input(INPUT_SERVER, 'REMOTE_ADDR');
						}
						// Erstellungsdatum
						if($multinewsletter_user->createdate == 0) {
							$multinewsletter_user->createdate = time();
						}
						// IP Adresse (update)
						$multinewsletter_user->updateIP = filter_input(INPUT_SERVER, 'REMOTE_ADDR');
						// Updatedatum
						$multinewsletter_user->updatedate = time();
						// Subscription type
						$multinewsletter_user->subscriptiontype = "import";
						// Gruppen
						$gruppen_ids = [];
						if($fields['send_group'] > -1) {
							$gruppen_ids = preg_grep('/^\s*$/s', explode("|", $csv_user[$fields['send_group']]), PREG_GREP_INVERT);
						}
						else if($fields['group_ids'] > -1) {
							$gruppen_ids = preg_grep('/^\s*$/s', explode("|", $csv_user[$fields['group_ids']]), PREG_GREP_INVERT);
						}
						foreach($gruppen_ids as $gruppen_id) {
							if(!in_array($gruppen_id, $multinewsletter_user->group_ids)) {
								$multinewsletter_user->group_ids[] = $gruppen_id;
							}
						}
					
						$multinewsletter_list->users[] = $multinewsletter_user;
					}
				}

				if(count($multinewsletter_list->users) > 0) {
					$counter = 0;
					foreach($multinewsletter_list->users as $user) {
						if(filter_input(INPUT_POST, 'import_action') == 'delete') {
							if($user->user_id > 0) {
								$user->delete();
								$counter++;
							}
						}
						else if(filter_input(INPUT_POST, 'import_action') == 'add_new') {
							if($user->user_id == 0) {
								$user->save();
								$counter++;
							}
						}
						else { // import_action == overwrite
							$user->save();
							$counter++;
						}
					}
						
					// Ergebnis ausgeben
					if(filter_input(INPUT_POST, 'import_action') == 'delete') {
						$messages[] = $I18N->msg('multinewsletter_import_success_delete', $counter);
					}
					else if(filter_input(INPUT_POST, 'import_action') == 'add_new') {
						$messages[] = $I18N->msg('multinewsletter_import_success_add', $counter);
					}
					else { // import_action == overwrite
						$messages[] = $I18N->msg('multinewsletter_import_success_overwrite', $counter);
					}
				} // Ende wenn Nutzer gefunden wurden
				else {
					$messages[] = $I18N->msg('multinewsletter_error_nothingtoimport');
				}
			} // Ende wenn "email"-feld im Import vorhanden
			else {
				$messages[] = $I18N->msg('multinewsletter_error_noemailfield');
			}
		} // Ende wenn CSV Datei keine Benutzer beinhaltete
		else {
			$messages[] = $I18N->msg('multinewsletter_error_nothingtoimport');
		}
	}
	else {
		$messages[] = $I18N->msg('multinewsletter_error_nothingtoimport');
	}
}

// Meldungen ausgeben
if(!empty($messages)) {
	echo '<p class="rex-message rex-warning"><span>';
	foreach($messages as $message) {
		echo $message .'<br />';
	}
	echo '</span></p>';
}
?>

<form action="<?php print $page_base_url; ?>" method="post" name="MULTINEWSLETTER" enctype="multipart/form-data">
	<table class="rex-table">
		<thead>
			<tr>
				<th class="rex-icon">&nbsp;</th>
				<th class="myrex_middle"><?php print $I18N->msg('multinewsletter_menu_import')?></th>
				<th class="rex-icon">&nbsp;</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td class="rex-icon">&nbsp;</td>
				<td class="myrex_middle"><a href="index.php?page=multinewsletter&subpage=help&chapter=import">
					<?php print $I18N->msg('multinewsletter_expl_import'); ?></a></td>
				<td class="rex-icon">&nbsp;</td>
			</tr>
			<tr>
				<td class="rex-icon">&nbsp;</td>
				<td class="myrex_middle">
					<?php
						if(!empty($_FILES['ec_icalfile'])) {
						}
						else {
							if(!empty($_FILES['ec_icalfile']) && empty($EC_VARS['events'])) {
								echo '<p>'.$I18N->msg('multinewsletter_import_nousersfound').'</p>';
							}
					?>
							<ul>
								<li>
									<label><?php print $I18N->msg('multinewsletter_import_csvfile'); ?></label>
									<input style="width:300px" type="file" name="newsletter_file" />
								</li>
								<li>
									<label>&nbsp;</label>
									<div class="myrex_checkbox_container">
										<input class="myrex_checkbox" type="radio" value="overwrite" name="import_action" 
											<?php if(filter_input(INPUT_POST, 'import_action') == "" || filter_input(INPUT_POST, 'import_action') == "overwrite") { print 'checked="checked"'; } ?> />
									</div>
										<label style="width:300px" for="import_overwrite"><?php print $I18N->msg('multinewsletter_import_overwrite')?></label>
								</li>
								<li>
									<label>&nbsp;</label>
									<div class="myrex_checkbox_container">
										<input class="myrex_checkbox" type="radio" value="delete" name="import_action"
											<?php if(filter_input(INPUT_POST, 'import_action') == "delete") { print 'checked="checked"'; } ?> />
									</div>
										<label style="width:300px" for="import_delete"><?php print $I18N->msg('multinewsletter_import_delete')?></label>
								</li>
								<li>
									<label>&nbsp;</label>
									<div class="myrex_checkbox_container">
										<input class="myrex_checkbox" type="radio" value="add_new" name="import_action"
											<?php if(filter_input(INPUT_POST, 'import_action') == "add_new") { print 'checked="checked"'; } ?> />
									</div>
										<label style="width:300px" for="import_add_new"><?php print $I18N->msg('multinewsletter_import_add_new')?></label>
								</li>
							</ul>
					<?php
						}
					?>
				</td>
				<td class="rex-icon">&nbsp;</td>
			</tr>
			<tr>
				<td class="rex-icon">&nbsp;</td>
				<td class="myrex_middle">
					<input type="submit" style="width:100%" class="myrex_submit" name="import" onclick="return myrex_confirm('<?php print $I18N->msg('multinewsletter_confirm_import')?>', this.form)" value="<?php print $I18N->msg('multinewsletter_button_submit_import')?>" />
				</td>
				<td class="rex-icon">&nbsp;</td>
			</tr>
		</tbody>
	</table>
</form>