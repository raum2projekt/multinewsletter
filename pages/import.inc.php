<?php
	$REX['ADDON375']['postget']['status'] = 'show_list';
	$REX['ADDON375']['postget']['error'] = array();
	
	if(!empty($_POST) && $REX['ADDON375']['postget']['newsletter']['import'])
	{

		if(empty($_FILES['newsletter_file']))
			$REX['ADDON375']['postget']['error'][] = $REX['ADDON375']['I18N']->msg('error_nofile');
		else
		{
			if(file_exists($_FILES['newsletter_file']['tmp_name']))
			{
				require_once($REX['INCLUDE_PATH'].'/addons/'.$REX['ADDON375']['addon_name'].'/classes/class.csv.inc.php');

				$CSV = new CSV();
				$CSV->fImport($_FILES['newsletter_file']['tmp_name'], filesize($_FILES['newsletter_file']['tmp_name']));
				$users = $CSV->data;
				
				if(!empty($users) && is_array($users))
				{
					$fieldnums = array(
						'email' => -1,
						'grad' => -1,
						'firstname' => -1,
						'lastname' => -1,
						'title' => -1,
						'clang' => -1,
						'status' => -1,
						'createip' => -1,
						'send_group' => -1
					);
					foreach($users[0] as $key => $fieldname)
						$fieldnums[$fieldname] = $key;
					
					if($fieldnums['email']>-1)
					{
						$importdata = array();
						foreach($users as $user)
						{
							if(myrex_validEmail($user[$fieldnums['email']]))
							{
								foreach($REX['CLANG'] as $k=>$v)
								{
									if($v==$user[$fieldnums['clang']])
									{
										$user[$fieldnums['clang']] = $k;
										break;
									}
								}
								
								$importdata[] = array(
									'email' => $user[$fieldnums['email']], 
									'grad' => (empty($user[$fieldnums['grad']]) ? '' : $user[$fieldnums['grad']]), 
									'firstname' => (empty($user[$fieldnums['firstname']]) ? 'anonymous' : $user[$fieldnums['firstname']]), 
									'lastname' => (empty($user[$fieldnums['lastname']]) ? 'anonymous' : $user[$fieldnums['lastname']]),
									'title' => (empty($user[$fieldnums['title']]) ? '0' : $user[$fieldnums['title']]),
									'clang' => (empty($user[$fieldnums['clang']]) ? $REX['CUR_CLANG'] : $user[$fieldnums['clang']]),
									'status' => (empty($user[$fieldnums['status']]) ? '1' : $user[$fieldnums['status']]),
									'createip' => (empty($user[$fieldnums['createip']]) ? $_SERVER['REMOTE_ADDR'] : $user[$fieldnums['createip']]),
									'createdate' => time(),
									'updateip' => $_SERVER['REMOTE_ADDR'],
									'updatedate' => time(),
									'subscriptiontype' => 'import',
									'article_id' => '0',
									'send_group' => (empty($user[$fieldnums['send_group']]) ? '0' : $user[$fieldnums['send_group']])
								);
							}
						}
						
						if(!empty($importdata))
						{
							$counter = 0;
							foreach($importdata as $user)
							{
							
								$qry_update=array();
								$qry_insert=array();

								foreach($user as $key=>$value)
								{
									if($key!='email' && $key!='createdate')
										$qry_update[$key] = "`".$key."` = '".$value."'";

									$qry_insert[$key] = "`".$key."` = '".$value."'";
								}

								$qry = "SELECT id FROM `".$REX['ADDON375']['usertable']."`
										WHERE email = '". $user['email'] ."'";
								$sql = new rex_sql();
								$sql -> setQuery($qry);
								if($REX['ADDON375']['postget']['newsletter']['import_action'] == 'delete') {
									if($sql -> getRows() > 0) {
										// Benutzer loeschen
										$sql_del = new rex_sql();
										$qry_del = "DELETE FROM `".$REX['ADDON375']['usertable']."`
												WHERE email = '". $user['email'] ."'";
										$sql_del -> setQuery($qry_del);
										// Gruppen loeschen
										$qry_del = "DELETE FROM `".$REX['ADDON375']['grouptable']."`
												WHERE uid = ". $sql -> getValue("id");
										$sql_del -> setQuery($qry);

										$counter++;
									}
								}
								else if($REX['ADDON375']['postget']['newsletter']['import_action'] == 'add_new') {
									if($sql -> getRows() == 0) {
										// Benutzer hinzufuegen
										$sql_add = new rex_sql();
										$qry_add = "INSERT INTO `". $REX['ADDON375']['usertable'] ."`
												SET ".join(", ", $qry_insert)."
												ON DUPLICATE KEY UPDATE `updatedate`='". $user['updatedate'] ."'";
										$sql_add -> setQuery($qry_add);
										// Gruppenzuordnung hinzufuegen
										if($user['send_group'] != "") {
											$qry_add_group = "INSERT INTO `".$REX['ADDON375']['u2gtable']."`
													SET uid = ". $sql_add -> getLastId() .", gid = ". $user['send_group'];
											$sql_add -> setQuery($qry_add_group);
										}

										$counter++;
									}
								}
								else  { // import_action == overwrite
									$qry_overwrite = "INSERT INTO `".$REX['ADDON375']['usertable']."`
											SET ".join(", ",$qry_insert)."
											ON DUPLICATE KEY UPDATE ". join(", ", $qry_update);
									$sql_overwrite = new rex_sql();
									$sql_overwrite -> setQuery($qry_overwrite);

									// Alte Gruppenzuweisung loeschen
									if($sql -> getRows() > 0) {
										$qry_del = "DELETE FROM `".$REX['ADDON375']['grouptable']."`
												WHERE uid = ". $sql -> getValue("id");
										$sql_del = new rex_sql();
										$sql_del -> setQuery($qry);
									}

									if($user['send_group'] != "") {
										$qry_add_group = "REPLACE INTO `".$REX['ADDON375']['u2gtable']."`
												SET uid = ". $sql_overwrite -> getLastId() .", gid = ". $user['send_group'];
										$sql_add = new rex_sql();
										$sql_add -> setQuery($qry_add_group);
									}

									$counter++;
								}
							}
							
							if($REX['ADDON375']['postget']['newsletter']['import_action'] == 'delete') {
								$REX['ADDON375']['postget']['error'][] = $REX['ADDON375']['I18N']->msg('import_success_delete', $counter);
							}
							else if($REX['ADDON375']['postget']['newsletter']['import_action'] == 'add_new') {
								$REX['ADDON375']['postget']['error'][] = $REX['ADDON375']['I18N']->msg('import_success_add', $counter);
							}
							else {
								$REX['ADDON375']['postget']['error'][] = $REX['ADDON375']['I18N']->msg('import_success_overwrite', $counter);
							}
						}
						else
							$REX['ADDON375']['postget']['error'][] = $REX['ADDON375']['I18N']->msg('error_nothingtoimport');
						
					}
					else
						$REX['ADDON375']['postget']['error'][] = $REX['ADDON375']['I18N']->msg('error_noemailfield');
				}
				else
					$REX['ADDON375']['postget']['error'][] = $REX['ADDON375']['I18N']->msg('error_nothingtoimport');
			}
			else
				$REX['ADDON375']['postget']['error'][] = $REX['ADDON375']['I18N']->msg('error_nothingtoimport');
		}
	}
	
	
/* ############################## REDAXO HEADERS ############################### */
		include $REX['INCLUDE_PATH'].'/layout/top.php';
	
		print_r(myrexvars_include_jscript($REX['INCLUDE_PATH'].'/addons/'.$REX['ADDON375']['addon_name'].'/scripts/scripts.js'));
		print_r(myrexvars_include_css($REX['INCLUDE_PATH'].'/addons/'.$REX['ADDON375']['addon_name'].'/css/backend.css'));
/* ############################## REDAXO HEADERS ############################### */
	
?>

<!-- BEGIN: CONTENT //-->
	<div class="rex-addon">
		<div id="rex-title">
			<div class="rex-title-row"><h1><?php print $REX['ADDON375']['I18N']->msg('addon_title')?></h1></div>
			<div class="rex-title-row">
<?php include('include/addons/'.$REX['ADDON375']['addon_name'].'/pages/menu.inc.php'); ?>
			</div>
		</div>

<?php
if(!empty($REX['ADDON375']['postget']['error']))
{
	echo '<p class="rex-message rex-warning"><span>';
	foreach($REX['ADDON375']['postget']['error'] as $msg)
		echo ''.$msg.'<br />';
	echo '</span></p>';
}
?>
		<p>&nbsp;</p>
		<form action="<?php print $REX['ADDON375']['thispage']?>" method="post" name="MULTINEWSLETTER" enctype="multipart/form-data">
			<table class="rex-table">
				<thead>
					<tr>
						<th class="rex-icon">&nbsp;</th>
						<th class="myrex_middle">&nbsp;</th>
						<th class="myrex_right">&nbsp;</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="rex-icon">&nbsp;</td>
						<td class="myrex_middle">
<?php
	if(!empty($_FILES['ec_icalfile']))
	{
	}
	else
	{
	// ############################# IF A NO FILE WAS UPLOADED
		if(!empty($_FILES['ec_icalfile']) && empty($EC_VARS['events']))
			echo '
							<p>'.$REX['ADDON375']['I18N']->msg('import_nousersfound').'</p>';
?>
							<ul>
								<li>
									<label><?php print $REX['ADDON375']['I18N']->msg('import_csvfile')?></label>
									<input style="width:300px" type="file" name="newsletter_file" />
								</li>
								<li>
									<label>&nbsp;</label>
									<div class="myrex_checkbox_container">
										<input class="myrex_checkbox" type="radio" value="overwrite" name="newsletter[import_action]" 
											<?php if(empty($REX['ADDON375']['postget']['newsletter']['import_action']) || $REX['ADDON375']['postget']['newsletter']['import_action'] == "overwrite") print 'checked="checked"'; ?> />
									</div>
										<label style="width:300px" for="import_overwrite"><?php print $REX['ADDON375']['I18N']->msg('import_overwrite')?></label>
								</li>
								<li>
									<label>&nbsp;</label>
									<div class="myrex_checkbox_container">
										<input class="myrex_checkbox" type="radio" value="delete" name="newsletter[import_action]"
											<?php if($REX['ADDON375']['postget']['newsletter']['import_action'] == "delete") print 'checked="checked"'; ?> />
									</div>
										<label style="width:300px" for="import_delete"><?php print $REX['ADDON375']['I18N']->msg('import_delete')?></label>
								</li>
								<li>
									<label>&nbsp;</label>
									<div class="myrex_checkbox_container">
										<input class="myrex_checkbox" type="radio" value="add_new" name="newsletter[import_action]"
											<?php if($REX['ADDON375']['postget']['newsletter']['import_action'] == "add_new") print 'checked="checked"'; ?> />
									</div>
										<label style="width:300px" for="import_add_new"><?php print $REX['ADDON375']['I18N']->msg('import_add_new')?></label>
								</li>
							</ul>
<?php
	}
?>
						</td>
						<td class="myrex_right">
							<?php print $REX['ADDON375']['I18N']->msg('expl_import')?>
						</td>
					</tr>
					<tr>
						<td class="rex-icon">&nbsp;</td>
						<td class="myrex_middle">
							<input type="submit" style="width:100%" class="myrex_submit" name="newsletter[import]" onclick="return myrex_confirm('<?php print $REX['ADDON375']['I18N']->msg('confirm_import')?>',this.form)" value="<?php print $REX['ADDON375']['I18N']->msg('button_submit_import')?>" />
						</th>
						<td class="myrex_right">&nbsp;
							
						</td>
					</tr>
				</tbody>
			</table>
		</form>
	<!-- END: ITEMS COMMENTS //-->
<?php
/* ############################## REDAXO FOOTER ############################### */
	include $REX['INCLUDE_PATH'].'/layout/bottom.php';
/* ############################## REDAXO FOOTER ############################### */
?>
	</div>
