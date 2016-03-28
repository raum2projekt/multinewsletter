<?php
$messages = array();

// Suchkriterien in Session schreiben
	if(!isset($_SESSION['multinewsletter'])) {
	$_SESSION['multinewsletter'] = array();
}
if(!isset($_SESSION['multinewsletter']['newsletter'])) {
	$_SESSION['multinewsletter']['newsletter'] = array();
}
if(!isset($_SESSION['multinewsletter']['newsletter']['sender_name'])) {
	$_SESSION['multinewsletter']['newsletter']['sender_name'] = array();
}

// Vorauswahl der Gruppe
if(filter_input(INPUT_POST, 'preselect_group', FILTER_VALIDATE_INT) > 0) {
	$_SESSION['multinewsletter']['newsletter']['preselect_group'] = filter_input(INPUT_POST, 'preselect_group', FILTER_VALIDATE_INT);
}
else if(!isset($_SESSION['multinewsletter']['newsletter']['preselect_group'])
	|| $_SESSION['multinewsletter']['newsletter']['preselect_group'] < 0
	|| $_SESSION['multinewsletter']['newsletter']['preselect_group'] == "") {
	$_SESSION['multinewsletter']['newsletter']['preselect_group'] = 0;
}

// Status des Sendefortschritts. Bedeutungen
$newsletterManager = new MultinewsletterNewsletterManager($this->getConfig('max_mails'), rex::getTablePrefix());
if(!isset($_SESSION['multinewsletter']['newsletter']['status'])) {
	// 0 = Aufruf des neuen Formulars
	$_SESSION['multinewsletter']['newsletter']['status'] = 0;
}
else if(filter_input(INPUT_POST, 'reset') != "") {
	// 0 = Wenn der Versand zurückgesetzt werden soll
	$newsletterManager->reset();
	$_SESSION['multinewsletter']['newsletter']['status'] = 0;
}
else if(filter_input(INPUT_POST, 'sendtestmail') != "") {
	// 1 = Testmail wurde verschickt
	// Status wird säter nur gesetzt, wenn kein Fehler beim Versand auftrat
}
else if(filter_input(INPUT_POST, 'prepare') != "") {
	// 2 = Benutzer wurden vorbereitet
	// Status wird säter nur gesetzt, wenn kein Fehler beim Vorbereiten auftrat
}
else if(filter_input(INPUT_POST, 'send') != "" || $newsletterManager->countRemainingUsers() > 0) {
	// 3 = Versand gestartet
	$_SESSION['multinewsletter']['newsletter']['status'] = 3;
}

// Ausgewählter Artikel
$form_link = filter_input_array(INPUT_POST, array('LINK'=> array('filter' => FILTER_VALIDATE_INT, 'flags' => FILTER_REQUIRE_ARRAY)));
if(!empty($form_link['LINK'])) {
	$_SESSION['multinewsletter']['newsletter']['article_id'] = $form_link['LINK'][1];
	$link_names = filter_input_array(INPUT_POST, array('LINK_NAME'=> array('flags'  => FILTER_REQUIRE_ARRAY)));
	$_SESSION['multinewsletter']['newsletter']['article_name'] = $link_names['LINK_NAME'][1];
}
else if(!isset($_SESSION['multinewsletter']['newsletter']['article_id'])) {
	$_SESSION['multinewsletter']['newsletter']['article_id'] = $this->getConfig('default_test_article');
	$_SESSION['multinewsletter']['newsletter']['article_name'] = $this->getConfig('default_test_article_name');
}

// Ausgewählter Sender E-Mail
if(filter_input(INPUT_POST, 'sender_email') != "") {
	$_SESSION['multinewsletter']['newsletter']['sender_email'] = filter_input(INPUT_POST, 'sender_email', FILTER_VALIDATE_EMAIL);
}
else if(!isset($_SESSION['multinewsletter']['newsletter']['sender_email'])) {
	$_SESSION['multinewsletter']['newsletter']['sender_email'] = $this->getConfig('sender');
}

// Ausgewählter Sender Name
$form_sendernamen = filter_input_array(INPUT_POST, array('sender_name'=> array('flags' => FILTER_REQUIRE_ARRAY)));
foreach(rex_clang::getAll() as $clang_id => $clang_value) {
	if(isset($form_sendernamen['sender_name'][$clang_id])) {
		$_SESSION['multinewsletter']['newsletter']['sender_name'][$clang_id] = $form_sendernamen['sender_name'][$clang_id];
	}
	else if(!isset($_SESSION['multinewsletter']['newsletter']['sender_name'][$clang_id])) {
		if(isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clang_id]['sendername'])) {
			$_SESSION['multinewsletter']['newsletter']['sender_name'][$clang_id] = $REX['ADDON']['multinewsletter']['settings']['lang'][$clang_id]['sendername'];
		}
		else {
			$_SESSION['multinewsletter']['newsletter']['sender_name'][$clang_id] = "";
		}
	}
}

// Testmail Empfäger E-Mail
if(filter_input(INPUT_POST, 'testemail') != "") {
	$_SESSION['multinewsletter']['newsletter']['testemail'] = filter_input(INPUT_POST, 'testemail', FILTER_VALIDATE_EMAIL);
}
else if(!isset($_SESSION['multinewsletter']['newsletter']['testemail'])) {
	$_SESSION['multinewsletter']['newsletter']['testemail'] = $this->getConfig('default_test_email');
}

// Testmail Empfäger Titel
if(filter_input(INPUT_POST, 'testtitle') != "") {
	$_SESSION['multinewsletter']['newsletter']['testtitle'] = filter_input(INPUT_POST, 'testtitle', FILTER_VALIDATE_INT);
}
else if(!isset($_SESSION['multinewsletter']['newsletter']['testtitle'])) {
	$_SESSION['multinewsletter']['newsletter']['testtitle'] = $this->getConfig('default_test_anrede');
}

// Testmail Empfäger Akademischer Grad
if(filter_input(INPUT_POST, 'testgrad') != "") {
	$_SESSION['multinewsletter']['newsletter']['testgrad'] = filter_input(INPUT_POST, 'testgrad');
}
else if(!isset($_SESSION['multinewsletter']['newsletter']['testgrad'])) {
	$_SESSION['multinewsletter']['newsletter']['testgrad'] = "";
}

// Testmail Empfäger Vorname
if(filter_input(INPUT_POST, 'testfirstname') != "") {
	$_SESSION['multinewsletter']['newsletter']['testfirstname'] = filter_input(INPUT_POST, 'testfirstname');
}
else if(!isset($_SESSION['multinewsletter']['newsletter']['testfirstname'])) {
	$_SESSION['multinewsletter']['newsletter']['testfirstname'] = $this->getConfig('default_test_vorname');
}

// Testmail Empfäger Nachname
if(filter_input(INPUT_POST, 'testlastname') != "") {
	$_SESSION['multinewsletter']['newsletter']['testlastname'] = filter_input(INPUT_POST, 'testlastname');
}
else if(!isset($_SESSION['multinewsletter']['newsletter']['testlastname'])) {
	$_SESSION['multinewsletter']['newsletter']['testlastname'] = $this->getConfig('default_test_nachname');
}

// Testmail Empfäger Sprache
if(filter_input(INPUT_POST, 'testlanguage') != "") {
	$_SESSION['multinewsletter']['newsletter']['testlanguage'] = filter_input(INPUT_POST, 'testlanguage', FILTER_VALIDATE_INT);
}
else if(!isset($_SESSION['multinewsletter']['newsletter']['testlanguage'])) {
	$_SESSION['multinewsletter']['newsletter']['testlanguage'] = $this->getConfig('default_test_sprache');
}

// Für den Versand ausgewählte Gruppen
$form_groups = filter_input_array(INPUT_POST, array('group'=> array('filter' => FILTER_VALIDATE_INT, 'flags' => FILTER_REQUIRE_ARRAY)));
if(!empty($form_groups['group'])) {
	$_SESSION['multinewsletter']['newsletter']['groups'] = $form_groups['group'];
}
else if(!isset($_SESSION['multinewsletter']['newsletter']['groups']) || !is_array($_SESSION['multinewsletter']['newsletter']['groups'])) {
	$_SESSION['multinewsletter']['newsletter']['groups'] = array($_SESSION['multinewsletter']['newsletter']['preselect_group']);
}

// Die Gruppen laden
$newsletter_groups = MultinewsletterGroupList::getAll(rex::getTablePrefix());

$time_started = time();
$maxtimeout = ini_get('max_execution_time');
if($maxtimeout == 0) {
	$maxtimeout = 20;
}
	
// Versand der Testmail
if(filter_input(INPUT_POST, 'sendtestmail') != "") {
	// Check ob Artikel gesetzt ist und online ist
	if(intval($_SESSION['multinewsletter']['newsletter']['article_id']) <= 0) {
		$messages[] = $I18N->msg('multinewsletter_error_noarticle');
	}
	else {
		$temp = OOArticle::getArticleById(
			$_SESSION['multinewsletter']['newsletter']['article_id'],
			$_SESSION['multinewsletter']['newsletter']['testlanguage']
		);
		if(!is_object($temp) || !$temp->isOnline()) {
			$messages[] = $I18N->msg('multinewsletter_error_articlenotfound',
				$_SESSION['multinewsletter']['newsletter']['article_id'],
				rex_clang::getAll()[$_SESSION['multinewsletter']['newsletter']['testlanguage']]);
		}
		unset($temp);
	}

	// Testmail versenden
	if(filter_var($_SESSION['multinewsletter']['newsletter']['testemail'], FILTER_SANITIZE_EMAIL) === false) {
		$messages[] = $I18N->msg('multinewsletter_error_invalidemail',
			$_SESSION['multinewsletter']['newsletter']['testemail']);
	}

	if(empty($messages)) {
		$testnewsletter = MultinewsletterNewsletter::factory($_SESSION['multinewsletter']['newsletter']['article_id'],
			$_SESSION['multinewsletter']['newsletter']['testlanguage'],
			rex::getTablePrefix());

		$testuser = MultinewsletterUser::factory($_SESSION['multinewsletter']['newsletter']['testemail'],
			$_SESSION['multinewsletter']['newsletter']['testtitle'],
			$_SESSION['multinewsletter']['newsletter']['testgrad'],
			$_SESSION['multinewsletter']['newsletter']['testfirstname'],
			$_SESSION['multinewsletter']['newsletter']['testlastname'],
			$_SESSION['multinewsletter']['newsletter']['testlanguage'],
			rex::getTablePrefix());

		$testnewsletter->sender_email = $_SESSION['multinewsletter']['newsletter']['sender_email'];
		$testnewsletter->sender_name = $_SESSION['multinewsletter']['newsletter']['sender_name'][$_SESSION['multinewsletter']['newsletter']['testlanguage']];
		$sendresult = $testnewsletter->sendTestmail($testuser);

		if(!$sendresult) {
			$messages[] = $I18N->msg('multinewsletter_error_senderror');
		}
		else {
			$_SESSION['multinewsletter']['newsletter']['status'] = 1;
		}
	}
}
// Adressen vorbereiten
else if(filter_input(INPUT_POST, 'prepare') != "") {
	$newsletterManager->reset();
	if($_SESSION['multinewsletter']['newsletter']['groups'][0] == 0) {
		$messages[] = $I18N->msg('multinewsletter_error_nogroupselected');
	}
		
	if(empty($messages)) {
		$offline_lang_ids = $newsletterManager->prepare($_SESSION['multinewsletter']['newsletter']['groups'],
			$_SESSION['multinewsletter']['newsletter']['article_id'],
			$this->getConfig('default_lang'));

		if(count($offline_lang_ids) > 0) {
			$offline_langs = array();
			foreach($offline_lang_ids as $clang_id) {
				$offline_langs[] = rex_clang::getAll()[$clang_id];
			}
			if(in_array($this->getConfig('default_lang'), $offline_lang_ids)) {
				$messages[] = $I18N->msg('multinewsletter_error_someclangsoffline', implode(", ", $offline_langs));
			}
			else {
				$messages[] = $I18N->msg('multinewsletter_error_someclangsdefault', implode(", ", $offline_langs));
			}
		}
		$_SESSION['multinewsletter']['newsletter']['status'] = 2;
	}
}
// Versand des Newsletters
else if(filter_input(INPUT_POST, 'send') != "") {
	$number_mails_send = $newsletterManager->countRemainingUsers() % $this->getConfig('max_mails');
	if($number_mails_send == 0) {
		$number_mails_send = $this->getConfig('max_mails');
	}
	$newsletterManager->send($number_mails_send);
	$_SESSION['multinewsletter']['newsletter']['status'] = 3;
}

if(!class_exists("rex_mailer")) {
	$messages[] = $I18N->msg('multinewsletter_error_no_phpmailer');
}

// Fehler ausgeben
if(!empty($messages)) {
	echo '<p class="rex-message rex-warning"><span>';
	foreach($messages as $msg) {
		echo ''.$msg.'<br />';
	}
	echo '</span></p><br />';
}

if(class_exists("rex_mailer")) {
?>
	<form action="<?php print $page_base_url; ?>" method="post" name="MULTINEWSLETTER">
		<table class="rex-table">
			<tbody>
				<tr>
					<th class="rex-icon">&nbsp;</th>
					<th class="myrex_middle"><?php print $I18N->msg('multinewsletter_newsletter_send_step1')?></th>
					<th class="rex-icon">&nbsp;</th>
				</tr>
				<tr class="myrex_spacebelow">
					<td class="rex-icon" valign="top">&nbsp;</td>
					<?php
						if($_SESSION['multinewsletter']['newsletter']['status'] > 0) {
					?>
					<td class="myrex_middle">
						<ul class="myrex_form">
							<li class="clearfix">
								<label><?php print $I18N->msg('multinewsletter_newsletter_article')?></label>
								<a href="<?php print rex::getServer() . rex_getUrl($_SESSION['multinewsletter']['newsletter']['article_id'], 0); ?>" target="_blank">
									<?php print $_SESSION['multinewsletter']['newsletter']['article_name']?></a>
							</li>
						</ul>
						<input style="width:45%" type="submit" class="myrex_submit" name="reset" onclick="return myrex_confirm('<?php print $I18N->msg('multinewsletter_confirm_reset')?>',this.form)" value="<?php print $I18N->msg('multinewsletter_button_cancelall')?>" />
					</td>
					<td class="rex-icon"></td>
					<?php
						}
						else {
					?>
					<td class="myrex_middle">
						<ul class="myrex_form">
							<li class="clearfix"><label><?php print $I18N->msg('multinewsletter_newsletter_load_group')?></label>
								<?php
									$groups = new rex_select();
									$groups->setSize(1);
									$groups->setAttribute('class', 'myrex_select');
									$groups->addOption($I18N->msg('multinewsletter_newsletter_aus_einstellungen'),'0');
									foreach($newsletter_groups as $group) {
										$groups->addOption($group->name, $group->group_id);
									}
									$groups->setSelected($_SESSION['multinewsletter']['newsletter']['preselect_group']);
									$groups->setAttribute('id', 'preselect_group');
									$groups->setName('preselect_group');
									print $groups->get();
									
									$groups_array = MultinewsletterGroupList::getAllAsArray(rex::getTablePrefix());
									$sendernamen = array();
									foreach(rex_clang::getAll() as $clang_id => $clang_value) {
										$sendernamen[$clang_id] = $REX['ADDON']['multinewsletter']['settings']['lang'][$clang_id]['sendername'];
									}
									$groups_array[0] = array(
										'group_id' => '0',
										'name' => $I18N->msg('multinewsletter_newsletter_aus_einstellungen'),
										'default_sender_email' => $this->getConfig('sender'),
										'default_article_id' => $this->getConfig('default_test_article'),
										'default_article_name' => $this->getConfig('default_test_article_name'),
									);
								?>	
								<script type="text/javascript">
									jQuery(document).ready(function($) {
										// presets
										var groupPresets = <?php echo json_encode($groups_array); ?>;
										var langs = <?php echo json_encode(rex_clang::getAll(), JSON_FORCE_OBJECT); ?>;
										var einstellungenPresets = <?php echo json_encode($sendernamen, JSON_FORCE_OBJECT); ?>;

										$('#preselect_group').change(function(e) { 
											var group_id = $(this).val();
											$('#LINK_1').val(groupPresets[group_id]['default_article_id']);
											$('#LINK_1_NAME').val(groupPresets[group_id]['default_article_name']);
											$('#sender_email').val(groupPresets[group_id]['default_sender_email']);
											var index;
											for (index in langs) {
												if(group_id === "0") {
													$('#sender_name_' + index).val(einstellungenPresets[index]);
												}
												else {
													$('#sender_name_' + index).val(groupPresets[group_id]['default_sender_name']);
												}
											}
										});
									});
								</script>
							</li>
						</ul>
					</td>
					<td class="rex-icon">&nbsp;</td>
				</tr>
				<tr  class="myrex_spacebelow">
					<td class="rex-icon" valign="top">&nbsp;</td>
					<td class="myrex_middle">
						<ul class="myrex_form">
							<li class="clearfix">
								<label><?php print $I18N->msg('multinewsletter_newsletter_article')?></label>
									<input type="hidden" name="LINK[1]" id="LINK_1" value="<?php print stripslashes($_SESSION['multinewsletter']['newsletter']['article_id'])?>" />
									<input style="margin-right:0.5em" type="text" size="30" name="LINK_NAME[1]" value="<?php print stripslashes($_SESSION['multinewsletter']['newsletter']['article_name']); ?>" id="LINK_1_NAME" readonly="readonly" />
									<a href="#" onclick="openLinkMap('LINK_1', '&clang=<?php print $_SESSION['multinewsletter']['newsletter']['testlanguage']; ?>&category_id=<?php print $_SESSION['multinewsletter']['newsletter']['article_id']; ?>');return false;" tabindex="24"><img src="media/file_open.gif" width="16" height="16" alt="Open Linkmap" title="Open Linkmap" /></a>
						 			<a href="#" onclick="deleteREXLink(1);return false;" tabindex="25"><img src="media/file_del.gif" width="16" height="16" title="Remove Selection" alt="Remove Selection" /></a>
							</li>
							<li class="clearfix">
								<label><?php print $I18N->msg('multinewsletter_group_default_sender_email')?></label>
									<input type="text" name="sender_email" id="sender_email" value="<?php print $_SESSION['multinewsletter']['newsletter']['sender_email']; ?>" />
							</li>
							<?php
								foreach(rex_clang::getAll() as $clang_id => $clang_value) {
									print '<li class="clearfix">';
									print '<label>'. $I18N->msg('multinewsletter_group_default_sender_name') .' '. $clang_value .'</label>';
									print '<input type="text" name="sender_name['. $clang_id .']" id="sender_name_'. $clang_id .'" value="'. $_SESSION['multinewsletter']['newsletter']['sender_name'][$clang_id] .'" />';
									print '</li>';
								}
							?>
						</ul>
					</td>
					<td class="rex-icon"></td>
					<?php
						}
					?>
				</tr>
				<tr>
					<th class="rex-icon">&nbsp;</th>
					<th class="myrex_middle"><?php print $I18N->msg('multinewsletter_newsletter_send_step2')?></th>
					<th class="rex-icon">&nbsp;</th>
				</tr>
				<?php
					if($_SESSION['multinewsletter']['newsletter']['status'] == 0) {
				?>
				<tr>
					<td class="rex-icon" valign="top">&nbsp;</td>
					<td class="myrex_middle">
						<p><?php print $I18N->msg('multinewsletter_expl_testmail')?></p>
						<ul>
							<li class="clearfix">
								<label><?php print $I18N->msg('multinewsletter_newsletter_email')?></label>
								<input type="text" name="testemail" value="<?php print $_SESSION['multinewsletter']['newsletter']['testemail']; ?>" maxlength="255" />									
							</li>
							<li>&nbsp;</li>
							<li class="clearfix">
								<label><?php print $I18N->msg('multinewsletter_newsletter_title')?></label>
								<?php
								// Standardanrede Auswahlfeld
								$standardanrede_select = new rex_select();
								$standardanrede_select->setSize(1);
								$standardanrede_select->setName('testtitle');
								$standardanrede_select->addOption($I18N->msg('multinewsletter_config_lang_title_male'),'0');
								$standardanrede_select->addOption($I18N->msg('multinewsletter_config_lang_title_female'),'1');
								$standardanrede_select->setAttribute('style','width:200px;');
								$standardanrede_select->setSelected($_SESSION['multinewsletter']['newsletter']['testtitle']);
								$standardanrede_select->show();
								?>
							</li>
							<li class="clearfix">
								<label><?php print $I18N->msg('multinewsletter_newsletter_grad')?></label>
								<input type="text" name="testgrad" value="<?php print stripslashes($_SESSION['multinewsletter']['newsletter']['testgrad']); ?>" maxlength="255" />									
							</li>
							<li class="clearfix">
								<label><?php print $I18N->msg('multinewsletter_newsletter_firstname')?></label>
								<input type="text" name="testfirstname" value="<?php print stripslashes($_SESSION['multinewsletter']['newsletter']['testfirstname']); ?>" maxlength="255" />									
							</li>
							<li class="clearfix">
								<label><?php print $I18N->msg('multinewsletter_newsletter_lastname')?></label>
								<input type="text" name="testlastname" value="<?php print stripslashes($_SESSION['multinewsletter']['newsletter']['testlastname']); ?>" maxlength="255" />									
							</li>
							<?php
								if(count(rex_clang::getAll()) > 1) {
									print '<li class="clearfix">';
									print '<label>'.$I18N->msg('multinewsletter_newsletter_clang').'</label>';
									$select = new rex_select();
									$select->setSize(1);
									$select->setName('testlanguage');
									foreach(rex_clang::getAll() as $clang_id => $clang_value) {
										$select->addOption($clang_value, $clang_id);
									}
									$select->setSelected($_SESSION['multinewsletter']['newsletter']['testlanguage']);
									$select->setAttribute('class','myrex_select');
									$select->show();
									print '</li>';
								}
								else {
									$clangs = rex_clang::getAll();
									reset($clangs);
									echo '<input type="hidden" name="testlanguage" value="'.key($clangs).'" />';
								}
							?>
						</ul>
					</td>
					<td class="rex-icon"></td>
				</tr>
				<tr class="myrex_spacebelow">
					<td valign="middle" class="rex-icon">&nbsp;</td>
					<td class="myrex_middle">
						<input style="width:45%" type="submit" class="myrex_submit" name="sendtestmail" value="<?php print $I18N->msg('multinewsletter_newsletter_sendtestmail')?>" />
					</td>
				</tr>
				<?php
					} // ENDIF STATUS = 0
					else {
				?>
				<tr class="myrex_spacebelow">
					<td valign="middle" class="rex-icon">&nbsp;</td>
					<td class="myrex_middle">
						<?php
							if($_SESSION['multinewsletter']['newsletter']['status'] == 1) {
						?>
						<p><a href="javascript:location.reload()"><strong><?php print $I18N->msg('multinewsletter_newsletter_testmailagain')?></a></strong></p>
						<?php
							}
						?>
					</td>						
					<td class="rex-icon"></td>
				</tr>
				<?php
					}
				?>
				<tr>
					<th class="rex-icon">&nbsp;</th>
					<th class="myrex_middle"><?php print $I18N->msg('multinewsletter_newsletter_send_step3')?></th>
					<th class="rex-icon">&nbsp;</th>
				</tr>
				<?php
					if($_SESSION['multinewsletter']['newsletter']['status'] == 1) {
				?>
				<tr>
					<td class="rex-icon" valign="top">&nbsp;</td>
					<td class="myrex_middle">
						<p><?php print $I18N->msg('multinewsletter_expl_prepare'); ?></p>
					<td class="rex-icon"></td>
				</tr>
				<tr>
					<td class="rex-icon" valign="top">&nbsp;</td>
					<td class="myrex_middle">
						<?php
							$select = new rex_select;
							$select->setSize(5);
							$select->setMultiple(1);
							$select->setName('group[]');
							foreach($newsletter_groups as $group) {
								$select->addOption($group->name, $group->group_id);
							}
							$select->setSelected($_SESSION['multinewsletter']['newsletter']['groups']);
							$select->setAttribute('class','myrex_select_high');
							$select->show();
						?>
					</td>
					<td class="rex-icon"></td>
				</tr>				<tr class="myrex_spacebelow">
					<td class="rex-icon" valign="top">&nbsp;</td>
					<td class="myrex_middle">
						<input type="submit" style="width:299px" class="myrex_submit" name="prepare" onclick="return myrex_confirm(\' <?php print $I18N->msg('multinewsletter_confirm_prepare'); ?> \',this.form)" value=" <?php print $I18N->msg('multinewsletter_newsletter_prepare'); ?>" />
					</td>
					<td class="rex-icon"></td>
				</tr>
				<?php
					} // ENDIF STATUS==1
					else if($_SESSION['multinewsletter']['newsletter']['status'] == 2) {
				?>
				<tr class="myrex_spacebelow">
					<td valign="middle" class="rex-icon">&nbsp;</td>
					<td class="myrex_middle"></td>						
				</tr>
				<?php
					}
				?>
				<tr>
					<th class="rex-icon">&nbsp;</th>
					<th class="myrex_middle"><?php print $I18N->msg('multinewsletter_newsletter_send_step4'); ?></th>
					<th class="rex-icon">&nbsp;</th>
				</tr>
				<?php
					if(($_SESSION['multinewsletter']['newsletter']['status'] == 2 || $_SESSION['multinewsletter']['newsletter']['status'] == 3) && $newsletterManager->countRemainingUsers() > 0) {
				?>
				<tr>
					<td class="rex-icon" valign="top">&nbsp;</td>
					<td class="myrex_middle">
						<p><?php print $I18N->msg('multinewsletter_expl_send'); ?><br /><br /></p>
						<p style="font-size: 1.4em"><strong><?php print $I18N->msg('multinewsletter_newsletter_2send', $newsletterManager->countRemainingUsers()); ?></strong></p>
						<?php
							if(filter_input(INPUT_POST, 'send') != "" && $newsletterManager->countRemainingUsers() > 0) {
								print '<br /><p id="newsletter_reloadinp">'. $I18N->msg('multinewsletter_newsletter_reloadin')
									.'<br />(<a href="javascript:void(0)" onclick="stopreload()">'.
									$I18N->msg('multinewsletter_newsletter_stop_reload') .'</a>)</p>';

								// get an array of users that should receive the newsletter
								$limit_left = $newsletterManager->countRemainingUsers() % ($this->getConfig('versandschritte_nacheinander') * $this->getConfig('max_mails'));
								$seconds_to_reload = 3;
								if($limit_left == 0) {
									$seconds_to_reload = $this->getConfig('sekunden_pause');	
								}
						?>
								<script type="text/javascript">
									var time_left = <?php print $seconds_to_reload; ?>;
									document.getElementById("newsletter_reloadin").innerHTML = time_left;

									function countdownreload() {
										document.getElementById("newsletter_reloadin").innerHTML = time_left;
										if(time_left > 0) {
											active = window.setTimeout("countdownreload()", 1000);
										}
										else {
											reload();
										}
										time_left = time_left - 1;
									}

									function reload() {
										document.getElementById("newsletter_reloadin").innerHTML="0";
										document.getElementById("send").click();
									}

									function stopreload() {
										window.clearTimeout(active);
										document.getElementById("newsletter_reloadinp").innerHTML='';
									}

									active = window.setTimeout("countdownreload()", 3000);
								</script>
						<?php
							}
						?>
					</td>
					<td class="rex-icon"></td>
				</tr>
				<tr class="myrex_spacebelow">
					<td valign="middle" class="rex-icon">&nbsp;</td>
					<td class="myrex_middle">
						<input style="width:45%" type="submit" class="myrex_submit" id="send" name="send" value="<?php print $I18N->msg('multinewsletter_newsletter_send'); ?>" />
					</td>						
				</tr>
				<?php
					} // ENDIF STATUS==3
					else if($_SESSION['multinewsletter']['newsletter']['status'] == 3 && $newsletterManager->countRemainingUsers() == 0) {
						// Damit beim nächsten Aufruf der Seite wieder von vorn losgelegt werden kann
						$_SESSION['multinewsletter']['newsletter']['status'] = 0;
				?>
				<tr>
					<td class="rex-icon" valign="top">&nbsp;</td>
					<td class="myrex_middle">
						<p style="font-size:1.4em"><strong><?php print $I18N->msg('multinewsletter_newsletter_sent'); ?></strong></p>
					</td>
					<td class="rex-icon">&nbsp;</td>
				</tr>
				<?php
					}
				?>
			</tbody>
		</table>
	</form>
<?php
} // if(class_exists("rex_mailer"))