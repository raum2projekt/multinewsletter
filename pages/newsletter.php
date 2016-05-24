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
$form_link = filter_input_array(INPUT_POST, array('REX_INPUT_LINK'=> array('filter' => FILTER_VALIDATE_INT, 'flags' => FILTER_REQUIRE_ARRAY)));
if(!empty($form_link['REX_INPUT_LINK'])) {
	$_SESSION['multinewsletter']['newsletter']['article_id'] = $form_link['REX_INPUT_LINK'][1];
	$link_names = filter_input_array(INPUT_POST, array('REX_LINK_NAME' => array('flags' => FILTER_REQUIRE_ARRAY)));
	$_SESSION['multinewsletter']['newsletter']['article_name'] = $link_names['REX_LINK_NAME'][1];
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
foreach(rex_clang::getAll() as $rex_clang) {
	if(isset($form_sendernamen['sender_name'][$rex_clang->getId()])) {
		$_SESSION['multinewsletter']['newsletter']['sender_name'][$rex_clang->getId()] = $form_sendernamen['sender_name'][$rex_clang->getId()];
	}
	else if(!isset($_SESSION['multinewsletter']['newsletter']['sender_name'][$rex_clang->getId()])) {
		if($this->hasConfig('lang_'. $rex_clang->getId() .'_sendername')) {
			$_SESSION['multinewsletter']['newsletter']['sender_name'][$rex_clang->getId()] = $this->getConfig('lang_'. $rex_clang->getId() .'_sendername');
		}
		else {
			$_SESSION['multinewsletter']['newsletter']['sender_name'][$rex_clang->getId()] = "";
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
		$messages[] = rex_i18n::msg('multinewsletter_error_noarticle');
	}
	else {
		$temp = rex_article::get(
			$_SESSION['multinewsletter']['newsletter']['article_id'],
			$_SESSION['multinewsletter']['newsletter']['testlanguage']
		);
		if(!is_object($temp) || !$temp->isOnline()) {
			$messages[] = rex_i18n::msg('multinewsletter_error_articlenotfound',
				$_SESSION['multinewsletter']['newsletter']['article_id'],
				rex_clang::get($_SESSION['multinewsletter']['newsletter']['testlanguage'])->getName());
		}
		unset($temp);
	}

	// Testmail versenden
	if(filter_var($_SESSION['multinewsletter']['newsletter']['testemail'], FILTER_SANITIZE_EMAIL) === false) {
		$messages[] = rex_i18n::msg('multinewsletter_error_invalidemail',
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
			$messages[] = rex_i18n::msg('multinewsletter_error_senderror');
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
		$messages[] = rex_i18n::msg('multinewsletter_error_nogroupselected');
	}
		
	if(empty($messages)) {
		$offline_lang_ids = $newsletterManager->prepare($_SESSION['multinewsletter']['newsletter']['groups'],
			$_SESSION['multinewsletter']['newsletter']['article_id'],
			$this->getConfig('default_lang'));

		if(count($offline_lang_ids) > 0) {
			$offline_langs = array();
			foreach($offline_lang_ids as $clang_id) {
				$offline_langs[] = rex_clang::get($clang_id)->getName();
			}
			if(in_array($this->getConfig('default_lang'), $offline_lang_ids)) {
				$messages[] = rex_i18n::msg('multinewsletter_error_someclangsoffline', implode(", ", $offline_langs));
			}
			else {
				$messages[] = rex_i18n::msg('multinewsletter_error_someclangsdefault', implode(", ", $offline_langs));
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
	$messages[] = rex_i18n::msg('multinewsletter_error_no_phpmailer');
}

// Fehler ausgeben
foreach($messages as $msg) {
	echo rex_view::error(rex_i18n::msg($msg));
}

if(class_exists("rex_mailer")) {
?>
	<form action="<?php print rex_url::currentBackendPage(); ?>" method="post" name="MULTINEWSLETTER">
		<div class="panel panel-edit">
			<header class="panel-heading"><div class="panel-title"><?php print rex_i18n::msg('multinewsletter_menu_versand'); ?></div></header>
			<div class="panel-body">
				<fieldset>
					<legend><?php print rex_i18n::msg('multinewsletter_newsletter_send_step1'); ?></legend>
					<?php
						if($_SESSION['multinewsletter']['newsletter']['status'] > 0) {
					?>
					<dl class="rex-form-group form-group">
						<dt><label for="article_link"><?php print rex_i18n::msg('multinewsletter_newsletter_article'); ?></label></dt>
						<dd><a href="<?php print rex::getServer() . rex_getUrl($_SESSION['multinewsletter']['newsletter']['article_id'], 0); ?>" target="_blank">
									<?php print $_SESSION['multinewsletter']['newsletter']['article_name']?></a></dd>
					</dl>
					<dl class="rex-form-group form-group">
						<dt><label for="reset"></label></dt>
						<dd><input class="btn btn-reset rex-form-aligned" type="submit" name="reset" onclick="return myrex_confirm('<?php print rex_i18n::msg('multinewsletter_confirm_reset'); ?>',this.form)" value="<?php print rex_i18n::msg('multinewsletter_button_cancelall'); ?>" /></dd>
					</dl>	
					<?php
						}
						else {
					?>
					<dl class="rex-form-group form-group">
						<dt><label for="preselect_group"><?php print rex_i18n::msg('multinewsletter_newsletter_load_group'); ?></label></dt>
						<dd>
							<?php
								$groups = new rex_select();
								$groups->setSize(1);
								$groups->setAttribute('class', 'form-control');
								$groups->addOption(rex_i18n::msg('multinewsletter_newsletter_aus_einstellungen'),'0');
								foreach($newsletter_groups as $group) {
									$groups->addOption($group->name, $group->group_id);
								}
								$groups->setSelected($_SESSION['multinewsletter']['newsletter']['preselect_group']);
								$groups->setAttribute('id', 'preselect_group');
								$groups->setName('preselect_group');
								print $groups->get();
									
								$groups_array = MultinewsletterGroupList::getAllAsArray(rex::getTablePrefix());
								$sendernamen = array();
								$clang_ids = array(); // For JS some lines below
								foreach(rex_clang::getAll() as $rex_clang) {
									$sendernamen[$rex_clang->getId()] = $this->getConfig('lang_'. $rex_clang->getId() .'_sendername');
									$clang_ids[$rex_clang->getId()] = $rex_clang->getCode();
								}
								$groups_array[0] = array(
									'group_id' => '0',
									'name' => rex_i18n::msg('multinewsletter_newsletter_aus_einstellungen'),
									'default_sender_email' => $this->getConfig('sender'),
									'default_article_id' => $this->getConfig('default_test_article'),
									'default_article_name' => $this->getConfig('default_test_article_name'),
								);
							?>	
							<script type="text/javascript">
								jQuery(document).ready(function($) {
									// presets
									var groupPresets = <?php echo json_encode($groups_array); ?>;
									var langs = <?php echo json_encode($clang_ids, JSON_FORCE_OBJECT); ?>;
									var einstellungenPresets = <?php echo json_encode($sendernamen, JSON_FORCE_OBJECT); ?>;
									$('#preselect_group').change(function(e) { 
										var group_id = $(this).val();
										$('#REX_LINK_1').val(groupPresets[group_id]['default_article_id']);
										$('#REX_LINK_1_NAME').val(groupPresets[group_id]['default_article_name']);
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
						</dd>
					</dl>
					<dl class="rex-form-group form-group">
						<dt><label for="article"><?php print rex_i18n::msg('multinewsletter_newsletter_article'); ?></label></dt>
						<dd>
							<div class="input-group">
								<input class="form-control" type="text" name="REX_LINK_NAME[1]" value="<?php print stripslashes($_SESSION['multinewsletter']['newsletter']['article_name']); ?>" id="REX_LINK_1_NAME" readonly="readonly">
								<input type="hidden" name="REX_INPUT_LINK[1]" id="REX_LINK_1" value="<?php print stripslashes($_SESSION['multinewsletter']['newsletter']['article_id']); ?>">
								<span class="input-group-btn">
									<a href="#" class="btn btn-popup" onclick="openLinkMap('REX_LINK_1', '&amp;clang=<?php print $_SESSION['multinewsletter']['newsletter']['testlanguage']; ?>&amp;category_id=<?php print $_SESSION['multinewsletter']['newsletter']['article_id']; ?>');return false;" title="<?php print rex_i18n::msg('var_link_open'); ?>"><i class="rex-icon rex-icon-open-linkmap"></i></a>
									<a href="#" class="btn btn-popup" onclick="deleteREXLink(1);return false;" title="<?php print rex_i18n::msg('var_link_delete'); ?>"><i class="rex-icon rex-icon-delete-link"></i></a>
								</span>
							</div>
						</dd>
					</dl>	
					<dl class="rex-form-group form-group">
						<dt><label for="sender_email"><?php print rex_i18n::msg('multinewsletter_newsletter_email'); ?></label></dt>
						<dd><input class="form-control" id="sender_email" type="email" name="sender_email" value="<?php print $_SESSION['multinewsletter']['newsletter']['sender_email']; ?>" /></dd>
					</dl>	
					<?php
							foreach(rex_clang::getAll() as $rex_clang) {
								print '<dl class="rex-form-group form-group">';
								print '<dt><label for="sender_name_'. $rex_clang->getId() .'">'. rex_i18n::msg('multinewsletter_group_default_sender_name') .' '. $rex_clang->getName() .'</label></dt>';
								print '<dd><input class="form-control" id="sender_name_'. $rex_clang->getId() .'" type="text" name="sender_name['. $rex_clang->getId() .']" value="'. $_SESSION['multinewsletter']['newsletter']['sender_name'][$rex_clang->getId()] .'" /></dd>';
								print '</dl>';
							}
						}
					?>
				</fieldset>
				<fieldset>
					<legend><?php print rex_i18n::msg('multinewsletter_newsletter_send_step2'); ?></legend>
					<?php
						if($_SESSION['multinewsletter']['newsletter']['status'] == 0) {
					?>
					<dl class="rex-form-group form-group">
						<dt><label for="expl_testmail"></label></dt>
						<dd><?php print rex_i18n::msg('multinewsletter_expl_testmail'); ?></dd>
					</dl>	
					<dl class="rex-form-group form-group">
						<dt><label for="testemail"><?php print rex_i18n::msg('multinewsletter_newsletter_email'); ?></label></dt>
						<dd><input class="form-control" id="testemail" type="email" name="testemail" value="<?php print $_SESSION['multinewsletter']['newsletter']['testemail']; ?>" /></dd>
					</dl>	
					<dl class="rex-form-group form-group">
						<dt><label for="testtitle"><?php print rex_i18n::msg('multinewsletter_newsletter_title'); ?></label></dt>
						<dd>
							<?php
								// Standardanrede Auswahlfeld
								$standardanrede_select = new rex_select();
								$standardanrede_select->setSize(1);
								$standardanrede_select->setName('testtitle');
								$standardanrede_select->addOption(rex_i18n::msg('multinewsletter_config_lang_title_male'),'0');
								$standardanrede_select->addOption(rex_i18n::msg('multinewsletter_config_lang_title_female'),'1');
								$standardanrede_select->setAttribute('class', 'form-control');
								$standardanrede_select->setSelected($_SESSION['multinewsletter']['newsletter']['testtitle']);
								$standardanrede_select->show();
							?>
						</dd>
					</dl>	
					<dl class="rex-form-group form-group">
						<dt><label for="testgrad"><?php print rex_i18n::msg('multinewsletter_newsletter_grad'); ?></label></dt>
						<dd><input class="form-control" id="testgrad" type="text" name="testgrad" value="<?php print $_SESSION['multinewsletter']['newsletter']['testgrad']; ?>" maxlength="50" /></dd>
					</dl>	
					<dl class="rex-form-group form-group">
						<dt><label for="testfirstname"><?php print rex_i18n::msg('multinewsletter_newsletter_firstname'); ?></label></dt>
						<dd><input class="form-control" id="testfirstname" type="text" name="testfirstname" value="<?php print $_SESSION['multinewsletter']['newsletter']['testfirstname']; ?>" maxlength="255" /></dd>
					</dl>	
					<dl class="rex-form-group form-group">
						<dt><label for="testlastname"><?php print rex_i18n::msg('multinewsletter_newsletter_lastname'); ?></label></dt>
						<dd><input class="form-control" id="testlastname" type="text" name="testlastname" value="<?php print stripslashes($_SESSION['multinewsletter']['newsletter']['testlastname']); ?>" maxlength="255" /></dd>
					</dl>	
					<?php
						if(count(rex_clang::getAll()) > 1) {
					?>
					<dl class="rex-form-group form-group">
						<dt><label for="testlanguage"><?php print rex_i18n::msg('multinewsletter_newsletter_clang'); ?></label></dt>
						<dd>
							<?php
								// Sprache Auswahlfeld
								$select = new rex_select();
								$select->setSize(1);
								$select->setName('testlanguage');
								foreach(rex_clang::getAll() as $rex_clang) {
									$select->addOption($rex_clang->getName(), $rex_clang->getId());
								}
								$select->setSelected($_SESSION['multinewsletter']['newsletter']['testlanguage']);
								$select->setAttribute('class', 'form-control');
								$select->show();
							?>
						</dd>
					</dl>
					<?php
						}
						else {
							foreach(rex_clang::getAll() as $rex_clang) {
								echo '<input type="hidden" name="testlanguage" value="'. $rex_clang->getId() .'" />';
								break;
							}
						}
					?>
					<dl class="rex-form-group form-group">
						<dt><label for="sendtestmail"></label></dt>
						<dd><input class="btn btn-save rex-form-aligned" type="submit" name="sendtestmail" value="<?php print rex_i18n::msg('multinewsletter_newsletter_sendtestmail'); ?>" /></dd>
					</dl>	
					<?php
						} // ENDIF STATUS = 0
						else {
					?>
					<dl class="rex-form-group form-group">
						<dt><label for="sendtestmail_again"></label></dt>
						<dd><a href="javascript:location.reload()"><button class="btn btn-save rex-form-aligned" type="submit" name="sendtestmail" value="<?php print rex_i18n::msg('multinewsletter_newsletter_sendtestmail'); ?>"><?php print rex_i18n::msg('multinewsletter_newsletter_testmailagain'); ?></button></a></dd>
					</dl>	
					<?php
						}
					?>
				</fieldset>
				<fieldset>
					<legend><?php print rex_i18n::msg('multinewsletter_newsletter_send_step3'); ?></legend>
					<?php
						if($_SESSION['multinewsletter']['newsletter']['status'] == 1) {
					?>
					<dl class="rex-form-group form-group">
						<dt><label for="expl_testmail"></label></dt>
						<dd><?php print rex_i18n::msg('multinewsletter_expl_prepare'); ?></dd>
					</dl>	
					<dl class="rex-form-group form-group">
						<dt><label for="group[]"></label></dt>
						<dd>
							<?php
								$select = new rex_select;
								$select->setSize(5);
								$select->setMultiple(1);
								$select->setName('group[]');
								foreach($newsletter_groups as $group) {
									$select->addOption($group->name, $group->group_id);
								}
								$select->setSelected($_SESSION['multinewsletter']['newsletter']['groups']);
								$select->setAttribute('class', 'form-control');
								$select->show();
							?>
						</dd>
					</dl>
					<dl class="rex-form-group form-group">
						<dt><label for="prepare"></label></dt>
						<dd><input class="btn btn-save rex-form-aligned" type="submit" name="prepare" onclick="return myrex_confirm(\' <?php print rex_i18n::msg('multinewsletter_confirm_prepare'); ?> \',this.form)" value="<?php print rex_i18n::msg('multinewsletter_newsletter_prepare'); ?>" /></dd>
					</dl>	
					<?php
						} // ENDIF STATUS==1
						else if($_SESSION['multinewsletter']['newsletter']['status'] == 2) {
							// Leerzeile
						}
					?>
				</fieldset>
				<fieldset>
					<legend><?php print rex_i18n::msg('multinewsletter_newsletter_send_step4'); ?></legend>
					<?php
						if(($_SESSION['multinewsletter']['newsletter']['status'] == 2 || $_SESSION['multinewsletter']['newsletter']['status'] == 3) && $newsletterManager->countRemainingUsers() > 0) {
					?>
					<dl class="rex-form-group form-group">
						<dt><label for="expl_send"></label></dt>
						<dd>
						<?php
							print '<p>'. rex_i18n::msg('multinewsletter_expl_send') .'</p>';
							print '<p>'. rex_i18n::msg('multinewsletter_newsletter_2send', $newsletterManager->countRemainingUsers()) .'</p>';
							if(filter_input(INPUT_POST, 'send') != "" && $newsletterManager->countRemainingUsers() > 0) {
								print '<br /><p id="newsletter_reloadinp">'. rex_i18n::msg('multinewsletter_newsletter_reloadin')
									.'<br />(<a href="javascript:void(0)" onclick="stopreload()">'.
									rex_i18n::msg('multinewsletter_newsletter_stop_reload') .'</a>)</p>';

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
						</dd>
					</dl>
					<dl class="rex-form-group form-group">
						<dt><label for="send"></label></dt>
						<dd><input class="btn btn-save rex-form-aligned" type="submit" name="send" value="<?php print rex_i18n::msg('multinewsletter_newsletter_send'); ?>" /></dd>
					</dl>	
				<?php
					} // ENDIF STATUS==3
					else if($_SESSION['multinewsletter']['newsletter']['status'] == 3 && $newsletterManager->countRemainingUsers() == 0) {
						// Damit beim nächsten Aufruf der Seite wieder von vorn losgelegt werden kann
						$_SESSION['multinewsletter']['newsletter']['status'] = 0;
				?>
					<dl class="rex-form-group form-group">
						<dt><label for="sent"></label></dt>
						<dd><?php print rex_i18n::msg('multinewsletter_newsletter_sent'); ?></dd>
					</dl>
				<?php
					}
				?>
				</fieldset>
			</div>
		</div>
	</form>
<?php
} // if(class_exists("rex_mailer"))