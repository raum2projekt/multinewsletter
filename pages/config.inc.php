<?php
require_once($REX['INCLUDE_PATH'] . '/addons/multinewsletter/langpresets.inc.php');

$page = rex_request('page', 'string');
$subpage = rex_request('subpage', 'string');
$func = rex_request('func', 'string');

// save settings
if ($func == 'update') {
	$settings = (array) rex_post('settings', 'array', array());
	$langSettings = (array) rex_post('lang_settings', 'array', array());

	// Linkmap Link braucht besondere Behandlung
	$settings['link'] = intval($_POST['LINK'][1]);
	$settings['linkname'] = trim($_POST['LINK_NAME'][1]);
	$settings['link_abmeldung'] = intval($_POST['LINK'][2]);
	$settings['linkname_abmeldung'] = trim($_POST['LINK_NAME'][2]);
	$settings['default_test_article'] = intval($_POST['LINK'][3]);
	$settings['default_test_article_name'] = trim($_POST['LINK_NAME'][3]);

	// type conversion normal settings
	foreach ($REX['ADDON']['multinewsletter']['settings'] as $settings_key => $value) {
		if ($settings_key != 'lang') { // lang is extra, see below
			if (isset($settings[$settings_key])) {
				$settings[$settings_key] = multinewsletter_utils::convertVarType($value, $settings[$settings_key]);
			}
		}
	}

	// replace settings
	$REX['ADDON']['multinewsletter']['settings'] = array_merge((array) $REX['ADDON']['multinewsletter']['settings'], $settings);

	// type conversion lang settings
	foreach ($REX['CLANG'] as $clangId => $clangName) {
		if (isset($langSettings[$clangId])) {
			foreach ($langSettings[$clangId] as $settings_key => $value) {
				if (isset($langSettings[$clangId][$settings_key]) && isset($REX['ADDON']['multinewsletter']['settings']['lang'][0][$settings_key])) {
					$langSettings[$clangId][$settings_key] = multinewsletter_utils::convertVarType($REX['ADDON']['multinewsletter']['settings']['lang'][0][$settings_key], $langSettings[$clangId][$settings_key]);
				}
			}
		}
	}

	// replace lang settings
	unset($REX['ADDON']['multinewsletter']['settings']['lang']);
	$REX['ADDON']['multinewsletter']['settings']['lang'] = $langSettings;

	// update settings file
	multinewsletter_utils::updateSettingsFile();
}
?>

<div class="rex-addon-output">
	<div class="rex-form">

		<form action="index.php" method="post">
			<input type="hidden" name="page" value="multinewsletter" />
			<input type="hidden" name="subpage" value="<?php echo $subpage; ?>" />
			<input type="hidden" name="func" value="update" />

			<fieldset class="rex-form-col-1">
				<legend><?php echo $I18N->msg('multinewsletter_config_title_standards'); ?></legend>
				<div class="rex-form-wrapper slide">

					<div class="rex-form-row rex-form-element-v1">
						<p class="rex-form-text">
							<label for="sender"><?php echo $I18N->msg('multinewsletter_config_sender'); ?></label>
							<input type="text" value="<?php echo $REX['ADDON']['multinewsletter']['settings']['sender']; ?>" name="settings[sender]" class="rex-form-text" id="sender">
						</p>
					</div>

					<div class="rex-form-row rex-form-element-v1">
						<p class="rex-form-text">
							<label for="LINK_1_NAME"><?php echo $I18N->msg('multinewsletter_config_link'); ?></label>
							<input type="hidden" name="LINK[1]" id="LINK_1" value="<?php echo $REX['ADDON']['multinewsletter']['settings']['link']; ?>" />
							<input type="text" size="30" name="LINK_NAME[1]" value="<?php echo $REX['ADDON']['multinewsletter']['settings']['linkname']; ?>" id="LINK_1_NAME" readonly="readonly" class="rex-form-text"/>
							<a href="#" onclick="openLinkMap('LINK_1', '&clang=0&category_id=<?php echo $REX['ADDON']['multinewsletter']['settings']['link']; ?>');return false;">
								<img src="media/file_open.gif" width="16" height="16" alt="<?php echo $I18N->msg('multinewsletter_config_open_linkmap'); ?>" title="<?php echo $I18N->msg('multinewsletter_config_open_linkmap'); ?>" />
							</a>
							<a href="#" onclick="deleteREXLink(1);return false;">
								<img src="media/file_del.gif" width="16" height="16" title="<?php echo $I18N->msg('multinewsletter_config_remove_link'); ?>" alt="<?php echo $I18N->msg('multinewsletter_config_remove_link'); ?>" />
							</a>
						</p>
					</div>

					<div class="rex-form-row rex-form-element-v1">
						<p class="rex-form-text">
							<label for="LINK_2_NAME"><?php echo $I18N->msg('multinewsletter_config_link_abmeldung'); ?></label>
							<input type="hidden" name="LINK[2]" id="LINK_2" value="<?php echo $REX['ADDON']['multinewsletter']['settings']['link_abmeldung']; ?>" />
							<input type="text" size="30" name="LINK_NAME[2]" value="<?php echo $REX['ADDON']['multinewsletter']['settings']['linkname_abmeldung']; ?>" id="LINK_2_NAME" readonly="readonly" class="rex-form-text"/>
							<a href="#" onclick="openLinkMap('LINK_2', '&clang=0&category_id=<?php echo $REX['ADDON']['multinewsletter']['settings']['link_abmeldung']; ?>');return false;">
								<img src="media/file_open.gif" width="16" height="16" alt="<?php echo $I18N->msg('multinewsletter_config_open_linkmap'); ?>" title="<?php echo $I18N->msg('multinewsletter_config_open_linkmap'); ?>" />
							</a>
							<a href="#" onclick="deleteREXLink(2);return false;">
								<img src="media/file_del.gif" width="16" height="16" title="<?php echo $I18N->msg('multinewsletter_config_remove_link'); ?>" alt="<?php echo $I18N->msg('multinewsletter_config_remove_link'); ?>" />
							</a>
						</p>
					</div>

					<?php 
						// Fallback Sprache Auswahlfeld
						if(count($REX['CLANG']) > 1) {
					?>
					<div class="rex-form-row">
						<p class="rex-form-col-a rex-form-select">
							<label for="default_lang"><?php echo $I18N->msg('multinewsletter_config_defaultlang'); ?></label>
							<?php
								$fallback_lang_select = new rex_select();
								$fallback_lang_select->setSize(1);
								$fallback_lang_select->setName('settings[default_lang]');
								$fallback_lang_select->addOption($I18N->msg('multinewsletter_config_defaultlang_keine'),-1);
								foreach($REX['CLANG'] as $id => $str) {
									$fallback_lang_select->addOption($str, $id);
								}
								$fallback_lang_select->setSelected($REX['ADDON'][$page]['settings']['default_lang']);
								echo $fallback_lang_select->get();
							?>
						</p>
					</div>
					<?php
						}
					?>
					
					<div class="rex-form-row">
						<p class="rex-form-col-a rex-form-select">
							<label for="unsubscribe_action"><?php echo $I18N->msg('multinewsletter_config_unsubscribe_action'); ?></label>
							<?php
								// Aktion bei Abmeldung
								$unsubscribe_action_select = new rex_select();
								$unsubscribe_action_select->setSize(1);
								$unsubscribe_action_select->setName('settings[unsubscribe_action]');
								$unsubscribe_action_select->addOption($I18N->msg('multinewsletter_config_unsubscribe_action_delete'), 'delete');
								$unsubscribe_action_select->addOption($I18N->msg('multinewsletter_config_unsubscribe_action_status'), 'status_unsubscribed');
								$unsubscribe_action_select->setSelected($REX['ADDON'][$page]['settings']['unsubscribe_action']);
								echo $unsubscribe_action_select->get();
							?>
						</p>
					</div>
					
					<div class="rex-form-row rex-form-element-v1">
						<p class="rex-form-text">
							<label for="subscribe_meldung_email"><?php echo $I18N->msg('multinewsletter_config_subscribe_meldung_email'); ?></label>
							<input type="text" value="<?php echo $REX['ADDON']['multinewsletter']['settings']['subscribe_meldung_email']; ?>" name="settings[subscribe_meldung_email]" class="rex-form-text" id="subscribe_meldung_email">
						</p>
					</div>
				</div>
			</fieldset>

			<fieldset class="rex-form-col-1">
				<legend><?php echo $I18N->msg('multinewsletter_config_title_serverlimits'); ?></legend>
				<div class="rex-form-wrapper slide">
					<div class="rex-form-row rex-form-element-v1">
						<p class="rex-form-text nurtext">
							<?php echo $I18N->msg('multinewsletter_expl_config_standards'); ?>
						</p>
					</div>

					<div class="rex-form-row rex-form-element-v1">
						<p class="rex-form-text">
							<label for="max_mails"><?php echo $I18N->msg('multinewsletter_config_max_mails'); ?></label>
							<input type="text" value="<?php echo $REX['ADDON']['multinewsletter']['settings']['max_mails']; ?>" name="settings[max_mails]" class="rex-form-text" id="max_mails">
						</p>
					</div>

					<div class="rex-form-row rex-form-element-v1">
						<p class="rex-form-text">
							<label for="versandschritte_nacheinander"><?php echo $I18N->msg('multinewsletter_config_versandschritte_nacheinander'); ?></label>
							<input type="text" value="<?php echo $REX['ADDON']['multinewsletter']['settings']['versandschritte_nacheinander']; ?>" name="settings[versandschritte_nacheinander]" class="rex-form-text" id="versandschritte_nacheinander">
						</p>
					</div>

					<div class="rex-form-row rex-form-element-v1">
						<p class="rex-form-text">
							<label for="sekunden_pause"><?php echo $I18N->msg('multinewsletter_config_sekunden_pause'); ?></label>
							<input type="text" value="<?php echo $REX['ADDON']['multinewsletter']['settings']['sekunden_pause']; ?>" name="settings[sekunden_pause]" class="rex-form-text" id="sekunden_pause">
						</p>
					</div>
				</div>
			</fieldset>
			
			<fieldset class="rex-form-col-1">
				<legend><?php echo $I18N->msg('multinewsletter_config_title_testmails'); ?></legend>
				<div class="rex-form-wrapper slide">
					<div class="rex-form-row rex-form-element-v1">
						<p class="rex-form-text">
							<label for="LINK_3_NAME"><?php echo $I18N->msg('multinewsletter_config_default_test_article'); ?></label>
							<input type="hidden" name="LINK[3]" id="LINK_3" value="<?php echo $REX['ADDON']['multinewsletter']['settings']['default_test_article']; ?>" />
							<input type="text" size="30" name="LINK_NAME[3]" value="<?php echo $REX['ADDON']['multinewsletter']['settings']['default_test_article_name']; ?>" id="LINK_3_NAME" readonly="readonly" class="rex-form-text"/>
							<a href="#" onclick="openLinkMap('LINK_3', '&clang=0&category_id=<?php echo $REX['ADDON']['multinewsletter']['settings']['default_test_article']; ?>');return false;">
								<img src="media/file_open.gif" width="16" height="16" alt="<?php echo $I18N->msg('multinewsletter_config_open_linkmap'); ?>" title="<?php echo $I18N->msg('multinewsletter_config_open_linkmap'); ?>" />
							</a>
							<a href="#" onclick="deleteREXLink(3);return false;">
								<img src="media/file_del.gif" width="16" height="16" title="<?php echo $I18N->msg('multinewsletter_config_remove_link'); ?>" alt="<?php echo $I18N->msg('multinewsletter_config_remove_link'); ?>" />
							</a>
						</p>
					</div>

					<div class="rex-form-row">
						<p class="rex-form-col-a rex-form-select">
							<?php
								// Standardanrede Auswahlfeld
								$testanrede_select = new rex_select();
								$testanrede_select->setSize(1);
								$testanrede_select->setName('settings[default_test_anrede]');
								$testanrede_select->addOption($I18N->msg('multinewsletter_config_lang_title_male'),'0');
								$testanrede_select->addOption($I18N->msg('multinewsletter_config_lang_title_female'),'1');
								$testanrede_select->setAttribute('style','width:200px;');
								if(isset($REX['ADDON']['multinewsletter']['settings']['default_test_anrede'])) {
									$testanrede_select->setSelected($REX['ADDON']['multinewsletter']['settings']['default_test_anrede']);
								}
							?>
							<label for="default_test_anrede"><?php echo $I18N->msg('multinewsletter_config_default_test_anrede'); ?></label>
							<?php echo $testanrede_select->get(); ?>
						</p>
					</div>		

					<div class="rex-form-row rex-form-element-v1">
						<p class="rex-form-text">
							<label for="default_test_vorname"><?php echo $I18N->msg('multinewsletter_config_default_test_vorname'); ?></label>
							<input type="text" value="<?php echo $REX['ADDON']['multinewsletter']['settings']['default_test_vorname']; ?>" name="settings[default_test_vorname]" class="rex-form-text" id="default_test_vorname">
						</p>
					</div>

					<div class="rex-form-row rex-form-element-v1">
						<p class="rex-form-text">
							<label for="default_test_nachname"><?php echo $I18N->msg('multinewsletter_config_default_test_nachname'); ?></label>
							<input type="text" value="<?php echo $REX['ADDON']['multinewsletter']['settings']['default_test_nachname']; ?>" name="settings[default_test_nachname]" class="rex-form-text" id="default_test_nachname">
						</p>
					</div>

					<div class="rex-form-row rex-form-element-v1">
						<p class="rex-form-text">
							<label for="default_test_email"><?php echo $I18N->msg('multinewsletter_config_default_test_email'); ?></label>
							<input type="text" value="<?php echo $REX['ADDON']['multinewsletter']['settings']['default_test_email']; ?>" name="settings[default_test_email]" class="rex-form-text" id="default_test_email">
						</p>
					</div>

					<div class="rex-form-row">
						<p class="rex-form-col-a rex-form-select">
							<?php
								// Sprache für Testmail Auswahlfeld
								$sprache_select = new rex_select();
								$sprache_select->setSize(1);
								$sprache_select->setName('settings[default_test_sprache]');
								foreach($REX['CLANG'] as $langId => $langName) {
									$sprache_select->addOption($langName, $langId);
								}
								$sprache_select->setAttribute('style','width:200px;');
								if(isset($REX['ADDON']['multinewsletter']['settings']['default_test_sprache'])) {
									$sprache_select->setSelected($REX['ADDON']['multinewsletter']['settings']['default_test_sprache']);
								}
							?>
							<label for="default_test_sprache"><?php echo $I18N->msg('multinewsletter_config_default_test_sprache'); ?></label>
							<?php echo $sprache_select->get(); ?>
						</p>
					</div>		
				</div>
			</fieldset>

			<?php
				foreach ($REX['CLANG'] as $clangId => $clangName) {
			?>
			<fieldset class="rex-form-col-1">
				<legend><?php echo $I18N->msg('multinewsletter_config_langname_section');?> <?php echo $clangName; ?></legend>
				<div class="rex-form-wrapper slide">
					<a href="#" class="preset-button" data-vertical-offset="-14" data-dropdown="#dropdown-<?php echo $clangId; ?>"><?php echo $I18N->msg('multinewsletter_config_lang_presets'); ?></a>

					<div id="dropdown-<?php echo $clangId; ?>" class="dropdown dropdown-relative">
						<ul class="dropdown-menu" data-clang="<?php echo $clangId; ?>">
							<?php
								foreach ($REX['MULTINEWSLETTER_LANG_PRESETS'] as $settings_key => $value) {
									echo '<li data-langpreset-id="' . $settings_key . '"><a href="#' . $settings_key . '">' . $value['language'] . '</a></li>';
								}
							?>
						</ul>
					</div>
<!--					
					<div class="rex-form-row rex-form-element-v1">
						<p class="rex-form-text">
							<?php
								$value = '';
								if(isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]) && isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['code'])) {
									$value = $REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['code'];
								}
							?>
							<label for="lang_settings_<?php echo $clangId; ?>_code"><?php echo $I18N->msg('multinewsletter_config_lang_code'); ?></label>
							<input type="text" value="<?php echo $value; ?>" name="lang_settings[<?php echo $clangId; ?>][code]" class="rex-form-text" id="lang_settings_<?php echo $clangId; ?>_code">
						</p>
					</div>

					<div class="rex-form-row rex-form-element-v1">
						<p class="rex-form-text">
							<?php
								$value = '';
								if(isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]) && isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['language'])) {
									$value = $REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['language'];
								}
							?>
							<label for="lang_settings_<?php echo $clangId; ?>_language"><?php echo $I18N->msg('multinewsletter_config_lang_language'); ?></label>
							<input type="text" value="<?php echo $value; ?>" name="lang_settings[<?php echo $clangId; ?>][language]" class="rex-form-text" id="lang_settings_<?php echo $clangId; ?>_language">
						</p>
					</div>
-->
					<div class="rex-form-row rex-form-element-v1">
						<p class="rex-form-text">
							<?php
								$value = '';
								if(isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]) && isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['sendername'])) {
									$value = $REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['sendername'];
								}
							?>
							<label for="lang_settings_<?php echo $clangId; ?>_sendername"><?php echo $I18N->msg('multinewsletter_config_lang_sendername'); ?></label>
							<input type="text" value="<?php echo $REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['sendername']; ?>" name="lang_settings[<?php echo $clangId; ?>][sendername]" class="rex-form-text" id="lang_settings_<?php echo $clangId; ?>_sendername">
						</p>
					</div>

					<div class="rex-form-row rex-form-element-v1">
						<p class="rex-form-text nurtext">
							<b><?php echo $I18N->msg('multinewsletter_config_lang_anmeldeformular'); ?></b>
						</p>
					</div>

					<div class="rex-form-row rex-form-element-v1">
						<p class="rex-form-text">
							<?php
								$value = '';
								if(isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]) && isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['anrede'])) {
									$value = $REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['anrede'];
								}
							?>
							<label for="lang_settings_<?php echo $clangId; ?>_anrede"><?php echo $I18N->msg('multinewsletter_config_lang_anrede'); ?></label>
							<input type="text" value="<?php echo $value; ?>" name="lang_settings[<?php echo $clangId; ?>][anrede]" class="rex-form-text" id="lang_settings_<?php echo $clangId; ?>_anrede">
						</p>
					</div>

					<div class="rex-form-row">
						<p class="rex-form-col-a rex-form-select">
							<?php
								// Standardanrede Auswahlfeld
								$standardanrede_select = new rex_select();
								$standardanrede_select->setSize(1);
								$standardanrede_select->setName('lang_settings_'. $clangId .'_title');
								$standardanrede_select->addOption($I18N->msg('multinewsletter_config_lang_title_male'),'0');
								$standardanrede_select->addOption($I18N->msg('multinewsletter_config_lang_title_female'),'1');
								$standardanrede_select->setAttribute('style','width:200px;');
								if(isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]) && isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['title'])) {
									$standardanrede_select->setSelected($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['title']);
								}
							?>
							<label for="lang_settings_<?php echo $clangId; ?>_title"><?php echo $I18N->msg('multinewsletter_config_title'); ?></label>
							<?php echo $standardanrede_select->get(); ?>
						</p>
					</div>		

					<div class="rex-form-row rex-form-element-v1">
						<p class="rex-form-text">
							<?php
								$value = '';
								if(isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]) && isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['title_0'])) {
									$value = $REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['title_0'];
								}
							?>
							<label for="lang_settings_<?php echo $clangId; ?>_title_0"><?php echo $I18N->msg('multinewsletter_config_lang_title_male'); ?></label>
							<input type="text" value="<?php echo $value; ?>" name="lang_settings[<?php echo $clangId; ?>][title_0]" class="rex-form-text" id="lang_settings_<?php echo $clangId; ?>_title_0">
						</p>
					</div>

					<div class="rex-form-row rex-form-element-v1">
						<p class="rex-form-text">
							<?php
								$value = '';
								if(isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]) && isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['title_1'])) {
									$value = $REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['title_1'];
								}
							?>
							<label for="lang_settings_<?php echo $clangId; ?>_title_1"><?php echo $I18N->msg('multinewsletter_config_lang_title_female'); ?></label>
							<input type="text" value="<?php echo $value; ?>" name="lang_settings[<?php echo $clangId; ?>][title_1]" class="rex-form-text" id="lang_settings_<?php echo $clangId; ?>_title_1">
						</p>
					</div>

					<div class="rex-form-row rex-form-element-v1">
						<p class="rex-form-text">
							<?php
								$value = '';
								if(isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]) && isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['grad'])) {
									$value = $REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['grad'];
								}
							?>
							<label for="lang_settings_<?php echo $clangId; ?>_grad"><?php echo $I18N->msg('multinewsletter_config_lang_grad'); ?></label>
							<input type="text" value="<?php echo $value; ?>" name="lang_settings[<?php echo $clangId; ?>][grad]" class="rex-form-text" id="lang_settings_<?php echo $clangId; ?>_grad">
						</p>
					</div>

					<div class="rex-form-row rex-form-element-v1">
						<p class="rex-form-text">
							<?php
								$value = '';
								if(isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]) && isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['firstname'])) {
									$value = $REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['firstname'];
								}
							?>
							<label for="lang_settings_<?php echo $clangId; ?>_firstname"><?php echo $I18N->msg('multinewsletter_config_lang_firstname'); ?></label>
							<input type="text" value="<?php echo $value; ?>" name="lang_settings[<?php echo $clangId; ?>][firstname]" class="rex-form-text" id="lang_settings_<?php echo $clangId; ?>_firstname">
						</p>
					</div>

					<div class="rex-form-row rex-form-element-v1">
						<p class="rex-form-text">
							<?php
								$value = '';
								if(isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]) && isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['lastname'])) {
									$value = $REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['lastname'];
								}
							?>
							<label for="lang_settings_<?php echo $clangId; ?>_lastname"><?php echo $I18N->msg('multinewsletter_config_lang_lastname'); ?></label>
							<input type="text" value="<?php echo $value; ?>" name="lang_settings[<?php echo $clangId; ?>][lastname]" class="rex-form-text" id="lang_settings_<?php echo $clangId; ?>_lastname">
						</p>
					</div>

					<div class="rex-form-row rex-form-element-v1">
						<p class="rex-form-text">
							<?php
								$value = '';
								if(isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]) && isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['email'])) {
									$value = $REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['email'];
								}
							?>
							<label for="lang_settings_<?php echo $clangId; ?>_email"><?php echo $I18N->msg('multinewsletter_config_lang_email'); ?></label>
							<input type="text" value="<?php echo $value; ?>" name="lang_settings[<?php echo $clangId; ?>][email]" class="rex-form-text" id="lang_settings_<?php echo $clangId; ?>_email">
						</p>
					</div>

					<div class="rex-form-row rex-form-element-v1">
						<p class="rex-form-text">
							<?php
								$value = '';
								if(isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]) && isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['select_newsletter'])) {
									$value = $REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['select_newsletter'];
								}
							?>
							<label for="lang_settings_<?php echo $clangId; ?>_select_newsletter"><?php echo $I18N->msg('multinewsletter_config_lang_select_newsletter'); ?></label>
							<input type="text" value="<?php echo $value; ?>" name="lang_settings[<?php echo $clangId; ?>][select_newsletter]" class="rex-form-text" id="lang_settings_<?php echo $clangId; ?>_select_newsletter">
						</p>
					</div>

					<div class="rex-form-row rex-form-element-v1">
						<p class="rex-form-text">
							<?php
								$value = '';
								if(isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]) && isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['compulsory'])) {
									$value = $REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['compulsory'];
								}
							?>
							<label for="lang_settings_<?php echo $clangId; ?>_compulsory"><?php echo $I18N->msg('multinewsletter_config_lang_compulsory'); ?></label>
							<input type="text" value="<?php echo $REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['compulsory']; ?>" name="lang_settings[<?php echo $clangId; ?>][compulsory]" class="rex-form-text" id="lang_settings_<?php echo $clangId; ?>_compulsory">
						</p>
					</div>

					<div class="rex-form-row rex-form-element-v1">
						<p class="rex-form-text">
							<?php
								$value = '';
								if(isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]) && isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['subscribe'])) {
									$value = $REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['subscribe'];
								}
							?>
							<label for="lang_settings_<?php echo $clangId; ?>_subscribe"><?php echo $I18N->msg('multinewsletter_config_lang_subscribe'); ?></label>
							<input type="text" value="<?php echo $value; ?>" name="lang_settings[<?php echo $clangId; ?>][subscribe]" class="rex-form-text" id="lang_settings_<?php echo $clangId; ?>_subscribe">
						</p>
					</div>

					<div class="rex-form-row rex-form-element-v1">
						<p class="rex-form-text">
							<?php
								$value = '';
								if(isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]) && isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['action'])) {
									$value = $REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['action'];
								}
							?>
							<label for="lang_settings_<?php echo $clangId; ?>_action"><?php echo $I18N->msg('multinewsletter_config_lang_action'); ?></label>
							<textarea name="lang_settings[<?php echo $clangId; ?>][action]" rows="3" id="lang_settings_<?php echo $clangId; ?>_action"><?php echo stripslashes($value); ?></textarea>
						</p>
					</div>

					<div class="rex-form-row rex-form-element-v1">
						<p class="rex-form-text">
							<?php
								$value = '';
								if(isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]) && isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['safety'])) {
									$value = $REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['safety'];
								}
							?>
							<label for="lang_settings_<?php echo $clangId; ?>_safety"><?php echo $I18N->msg('multinewsletter_config_lang_safety'); ?></label>
							<textarea name="lang_settings[<?php echo $clangId; ?>][safety]" rows="3" id="lang_settings_<?php echo $clangId; ?>_safety"><?php echo stripslashes($value); ?></textarea>
						</p>
					</div>

					<div class="rex-form-row rex-form-element-v1">
						<p class="rex-form-text">
							<?php
								$value = '';
								if(isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]) && isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['status1'])) {
									$value = $REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['status1'];
								}
							?>
							<label for="lang_settings_<?php echo $clangId; ?>_status1"><?php echo $I18N->msg('multinewsletter_config_lang_status1'); ?></label>
							<input type="text" value="<?php echo $value; ?>" name="lang_settings[<?php echo $clangId; ?>][status1]" class="rex-form-text" id="lang_settings_<?php echo $clangId; ?>_status1">
						</p>
					</div>

					<div class="rex-form-row rex-form-element-v1">
						<p class="rex-form-text nurtext">
							<b><?php echo $I18N->msg('multinewsletter_config_lang_anmeldeformular_fehler'); ?></b>
						</p>
					</div>

					<div class="rex-form-row rex-form-element-v1">
						<p class="rex-form-text">
							<?php
								$value = '';
								if(isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]) && isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['no_userdata'])) {
									$value = $REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['no_userdata'];
								}
							?>
							<label for="lang_settings_<?php echo $clangId; ?>_no_userdata"><?php echo $I18N->msg('multinewsletter_config_lang_no_userdata'); ?></label>
							<textarea name="lang_settings[<?php echo $clangId; ?>][no_userdata]" rows="3" id="lang_settings_<?php echo $clangId; ?>_no_userdata"><?php echo stripslashes($value); ?></textarea>
						</p>
					</div>

					<div class="rex-form-row rex-form-element-v1">
						<p class="rex-form-text">
							<?php
								$value = '';
								if(isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]) && isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['invalid_email'])) {
									$value = $REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['invalid_email'];
								}
							?>
							<label for="lang_settings_<?php echo $clangId; ?>_invalid_email"><?php echo $I18N->msg('multinewsletter_config_lang_invalid_email'); ?></label>
							<input type="text" value="<?php echo $value; ?>" name="lang_settings[<?php echo $clangId; ?>][invalid_email]" class="rex-form-text" id="lang_settings_<?php echo $clangId; ?>_invalid_email">
						</p>
					</div>

					<div class="rex-form-row rex-form-element-v1">
						<p class="rex-form-text">
							<?php
								$value = '';
								if(isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]) && isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['invalid_firstname'])) {
									$value = $REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['invalid_firstname'];
								}
							?>
							<label for="lang_settings_<?php echo $clangId; ?>_language"><?php echo $I18N->msg('multinewsletter_config_lang_invalid_firstname'); ?></label>
							<input type="text" value="<?php echo $value; ?>" name="lang_settings[<?php echo $clangId; ?>][invalid_firstname]" class="rex-form-text" id="lang_settings_<?php echo $clangId; ?>_invalid_firstname">
						</p>
					</div>

					<div class="rex-form-row rex-form-element-v1">
						<p class="rex-form-text">
							<?php
								$value = '';
								if(isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]) && isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['invalid_lastname'])) {
									$value = $REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['invalid_lastname'];
								}
							?>
							<label for="lang_settings_<?php echo $clangId; ?>_invalid_lastname"><?php echo $I18N->msg('multinewsletter_config_lang_invalid_lastname'); ?></label>
							<input type="text" value="<?php echo $value; ?>" name="lang_settings[<?php echo $clangId; ?>][invalid_lastname]" class="rex-form-text" id="lang_settings_<?php echo $clangId; ?>_invalid_lastname">
						</p>
					</div>

					<div class="rex-form-row rex-form-element-v1">
						<p class="rex-form-text">
							<?php
								$value = '';
								if(isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]) && isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['send_error'])) {
									$value = $REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['send_error'];
								}
							?>
							<label for="lang_settings_<?php echo $clangId; ?>_send_error"><?php echo $I18N->msg('multinewsletter_config_lang_send_error'); ?></label>
							<input type="text" value="<?php echo $value; ?>" name="lang_settings[<?php echo $clangId; ?>][send_error]" class="rex-form-text" id="lang_settings_<?php echo $clangId; ?>_send_error">
						</p>
					</div>

					<div class="rex-form-row rex-form-element-v1">
						<p class="rex-form-text">
							<?php
								$value = '';
								if(isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]) && isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['software_failure'])) {
									$value = $REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['software_failure'];
								}
							?>
							<label for="lang_settings_<?php echo $clangId; ?>_software_failure"><?php echo $I18N->msg('multinewsletter_config_lang_software_failure'); ?></label>
							<input type="text" value="<?php echo $value; ?>" name="lang_settings[<?php echo $clangId; ?>][software_failure]" class="rex-form-text" id="lang_settings_<?php echo $clangId; ?>_software_failure">
						</p>
					</div>

					<div class="rex-form-row rex-form-element-v1">
						<p class="rex-form-text">
							<?php
								$value = '';
								if(isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]) && isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['nogroup_selected'])) {
									$value = $REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['nogroup_selected'];
								}
							?>
							<label for="lang_settings_<?php echo $clangId; ?>_nogroup_selected"><?php echo $I18N->msg('multinewsletter_config_lang_nogroup_selected'); ?></label>
							<textarea name="lang_settings[<?php echo $clangId; ?>][nogroup_selected]" rows="2" id="lang_settings_<?php echo $clangId; ?>_nogroup_selected"><?php echo stripslashes($value); ?></textarea>
						</p>
					</div>

					<div class="rex-form-row rex-form-element-v1">
						<p class="rex-form-text">
							<?php
								$value = '';
								if(isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]) && isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['already_subscribed'])) {
									$value = $REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['already_subscribed'];
								}
							?>
							<label for="lang_settings_<?php echo $clangId; ?>_language"><?php echo $I18N->msg('multinewsletter_config_lang_already_subscribed'); ?></label>
							<input type="text" value="<?php echo $value; ?>" name="lang_settings[<?php echo $clangId; ?>][already_subscribed]" class="rex-form-text" id="lang_settings_<?php echo $clangId; ?>_already_subscribed">
						</p>
					</div>

					<div class="rex-form-row rex-form-element-v1">
						<p class="rex-form-text">
							<?php
								$value = '';
								if(isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]) && isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['confirmation_sent'])) {
									$value = $REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['confirmation_sent'];
								}
							?>
							<label for="lang_settings_<?php echo $clangId; ?>_confirmation_sent"><?php echo $I18N->msg('multinewsletter_config_lang_confirmation_sent'); ?></label>
							<textarea name="lang_settings[<?php echo $clangId; ?>][confirmation_sent]" rows="3" id="lang_settings_<?php echo $clangId; ?>_confirmation_sent"><?php echo stripslashes($value); ?></textarea>
						</p>
					</div>

					<div class="rex-form-row rex-form-element-v1">
						<p class="rex-form-text">
							<?php
								$value = '';
								if(isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]) && isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['confirmsubject'])) {
									$value = $REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['confirmsubject'];
								}
							?>
							<label for="lang_settings_<?php echo $clangId; ?>_confirmsubject"><?php echo $I18N->msg('multinewsletter_config_lang_confirmsubject'); ?></label>
							<input type="text" value="<?php echo $value; ?>" name="lang_settings[<?php echo $clangId; ?>][confirmsubject]" class="rex-form-text" id="lang_settings_<?php echo $clangId; ?>_confirmsubject">
						</p>
					</div>

					<div class="rex-form-row rex-form-element-v1">
						<p class="rex-form-text">
							<?php
								$value = '';
								if(isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]) && isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['confirmcontent'])) {
									$value = $REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['confirmcontent'];
								}
							?>
							<label for="lang_settings_<?php echo $clangId; ?>_confirmcontent"><?php echo $I18N->msg('multinewsletter_config_lang_confirmcontent'); ?></label>
							<textarea name="lang_settings[<?php echo $clangId; ?>][confirmcontent]" rows="5" id="lang_settings_<?php echo $clangId; ?>_confirmcontent"><?php echo stripslashes($value); ?></textarea>
						</p>
					</div>

					<div class="rex-form-row rex-form-element-v1">
						<p class="rex-form-text">
							<?php
								$value = '';
								if(isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]) && isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['already_confirmed'])) {
									$value = $REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['already_confirmed'];
								}
							?>
							<label for="lang_settings_<?php echo $clangId; ?>_already_confirmed"><?php echo $I18N->msg('multinewsletter_config_lang_already_confirmed'); ?></label>
							<input type="text" value="<?php echo $value; ?>" name="lang_settings[<?php echo $clangId; ?>][already_confirmed]" class="rex-form-text" id="lang_settings_<?php echo $clangId; ?>_already_confirmed">
						</p>
					</div>

					<div class="rex-form-row rex-form-element-v1">
						<p class="rex-form-text">
							<?php
								$value = '';
								if(isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]) && isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['invalid_key'])) {
									$value = $REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['invalid_key'];
								}
							?>
							<label for="lang_settings_<?php echo $clangId; ?>_invalid_key"><?php echo $I18N->msg('multinewsletter_config_lang_invalid_key'); ?></label>
							<textarea name="lang_settings[<?php echo $clangId; ?>][invalid_key]" rows="2" id="lang_settings_<?php echo $clangId; ?>_invalid_key"><?php echo stripslashes($value); ?></textarea>
						</p>
					</div>

					<div class="rex-form-row rex-form-element-v1">
						<p class="rex-form-text">
							<?php
								$value = '';
								if(isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]) && isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['confirmation_successful'])) {
									$value = $REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['confirmation_successful'];
								}
							?>
							<label for="lang_settings_<?php echo $clangId; ?>_confirmation_successful"><?php echo $I18N->msg('multinewsletter_config_lang_confirmation_successful'); ?></label>
							<textarea name="lang_settings[<?php echo $clangId; ?>][confirmation_successful]" rows="5" id="lang_settings_<?php echo $clangId; ?>_confirmation_successful"><?php echo stripslashes($value); ?></textarea>
						</p>
					</div>

					<div class="rex-form-row rex-form-element-v1">
						<p class="rex-form-text nurtext">
							<b><?php echo $I18N->msg('multinewsletter_config_lang_abmeldeformular'); ?></b>
						</p>
					</div>

					<div class="rex-form-row rex-form-element-v1">
						<p class="rex-form-text">
							<?php
								$value = '';
								if(isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]) && isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['unsubscribe'])) {
									$value = $REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['unsubscribe'];
								}
							?>
							<label for="lang_settings_<?php echo $clangId; ?>_unsubscribe"><?php echo $I18N->msg('multinewsletter_config_lang_unsubscribe'); ?></label>
							<input type="text" value="<?php echo $value; ?>" name="lang_settings[<?php echo $clangId; ?>][unsubscribe]" class="rex-form-text" id="lang_settings_<?php echo $clangId; ?>_unsubscribe">
						</p>
					</div>

					<div class="rex-form-row rex-form-element-v1">
						<p class="rex-form-text">
							<?php
								$value = '';
								if(isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]) && isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['status0'])) {
									$value = $REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['status0'];
								}
							?>
							<label for="lang_settings_<?php echo $clangId; ?>_status0"><?php echo $I18N->msg('multinewsletter_config_lang_status0'); ?></label>
							<textarea name="lang_settings[<?php echo $clangId; ?>][status0]" rows="2" id="lang_settings_<?php echo $clangId; ?>_status0"><?php echo stripslashes($value); ?></textarea>
						</p>
					</div>

					<div class="rex-form-row rex-form-element-v1">
						<p class="rex-form-text nurtext">
							<b><?php echo $I18N->msg('multinewsletter_config_lang_abmeldeformular_fehler'); ?></b>
						</p>
					</div>

					<div class="rex-form-row rex-form-element-v1">
						<p class="rex-form-text">
							<?php
								$value = '';
								if(isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]) && isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['already_unsubscribed'])) {
									$value = $REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['already_unsubscribed'];
								}
							?>
							<label for="lang_settings_<?php echo $clangId; ?>_already_unsubscribed"><?php echo $I18N->msg('multinewsletter_config_lang_already_unsubscribed'); ?></label>
							<input type="text" value="<?php echo $value; ?>" name="lang_settings[<?php echo $clangId; ?>][already_unsubscribed]" class="rex-form-text" id="lang_settings_<?php echo $clangId; ?>_already_unsubscribed">
						</p>
					</div>

					<div class="rex-form-row rex-form-element-v1">
						<p class="rex-form-text">
							<?php
								$value = '';
								if(isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]) && isset($REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['user_not_found'])) {
									$value = $REX['ADDON']['multinewsletter']['settings']['lang'][$clangId]['user_not_found'];
								}
							?>
							<label for="lang_settings_<?php echo $clangId; ?>_user_not_found"><?php echo $I18N->msg('multinewsletter_config_lang_user_not_found'); ?></label>
							<input type="text" value="<?php echo $value; ?>" name="lang_settings[<?php echo $clangId; ?>][user_not_found]" class="rex-form-text" id="lang_settings_<?php echo $clangId; ?>_user_not_found">
						</p>
					</div>
				</div>
			</fieldset>
			<?php
				}
			?>
			<fieldset class="rex-form-col-1">
				<div class="rex-form-wrapper">
					<div class="rex-form-row rex-form-element-v2">
						<p class="rex-form-submit">
							<input style="margin-top: 5px; margin-bottom: 5px;" class="rex-form-submit" type="submit" id="sendit" name="sendit" value="<?php echo $I18N->msg('multinewsletter_config_submit'); ?>" />
						</p>
					</div>
				</div>
			</fieldset>
		</form>
	</div>
</div>

<?php
unset($homeurl_select,$mailformat_select);
?>

<style type="text/css">
div.rex-form legend {
	-webkit-box-sizing: border-box;
	-moz-box-sizing: border-box;
	box-sizing: border-box;
	width: 100%;
	cursor: pointer;
	border-bottom: 1px solid #fff;
	background: transparent url("../<?php echo $REX['MEDIA_ADDON_DIR']; ?>/multinewsletter/arrows.png") no-repeat 7px 10px;
	padding-left: 19px;
}

div.rex-form legend:hover {
	background-color: #eee;
}

div.rex-form legend.open {
	background-position: 7px -36px;
}

.rex-form-wrapper.slide {
	display: none;
}

.pipes {
	font-family: Verdana, 'Trebuchet MS', Arial, sans-serif;
}

.preset-button {
	float: right;
	margin-right: 4px;
	margin-top: -23px;
	font-weight: bold;
	border-radius: 4px;
	padding: 2px 4px;
}

.preset-button:hover,
.preset-button.dropdown-open {
	background: #ccc;
	text-decoration: none !important;
}

.preset-button:after {
    color: #08c;
    content: "↓";
    font-size: 12px;
    font-weight: bold;
    margin-left: 3px;
	vertical-align: text-top;
}

div#rex-website .dropdown a:hover {
	text-decoration: none;
}
</style>

<script type="text/javascript">
	jQuery(document).ready(function($) {
		// tag editor inputs fields
		$('input.tags').tagEditor({placeholder: "<?php echo $I18N->msg('multinewsletter_config_tag_editor_hint'); ?>"});

		// presets
		var langPresets = <?php echo json_encode($REX['MULTINEWSLETTER_LANG_PRESETS']); ?>;

		$('ul.dropdown-menu li').click(function(e) { 
			var curClang = $(this).parent().attr('data-clang');
			var curLangPresetIndex = $(this).attr('data-langpreset-id');

			$('#lang_settings_' + curClang + '_code').val(langPresets[curLangPresetIndex]['code']);
			$('#lang_settings_' + curClang + '_language').val(langPresets[curLangPresetIndex]['language']);
			$('#lang_settings_' + curClang + '_anrede').val(langPresets[curLangPresetIndex]['anrede']);
			$('#lang_settings_' + curClang + '_title_0').val(langPresets[curLangPresetIndex]['title_0']);
			$('#lang_settings_' + curClang + '_title_1').val(langPresets[curLangPresetIndex]['title_1']);
			$('#lang_settings_' + curClang + '_confirmsubject').val(langPresets[curLangPresetIndex]['confirmsubject']);
			$('#lang_settings_' + curClang + '_confirmcontent').val(langPresets[curLangPresetIndex]['confirmcontent']);
			$('#lang_settings_' + curClang + '_sendername').val(langPresets[curLangPresetIndex]['sendername']);
			$('#lang_settings_' + curClang + '_compulsory').val(langPresets[curLangPresetIndex]['compulsory']);
			$('#lang_settings_' + curClang + '_action').val(langPresets[curLangPresetIndex]['action']);
			$('#lang_settings_' + curClang + '_invalid_email').val(langPresets[curLangPresetIndex]['invalid_email']);
			$('#lang_settings_' + curClang + '_invalid_firstname').val(langPresets[curLangPresetIndex]['invalid_firstname']);
			$('#lang_settings_' + curClang + '_invalid_lastname').val(langPresets[curLangPresetIndex]['invalid_lastname']);
			$('#lang_settings_' + curClang + '_send_error').val(langPresets[curLangPresetIndex]['send_error']);
			$('#lang_settings_' + curClang + '_software_failure').val(langPresets[curLangPresetIndex]['software_failure']);
			$('#lang_settings_' + curClang + '_no_userdata').val(langPresets[curLangPresetIndex]['no_userdata']);
			$('#lang_settings_' + curClang + '_already_unsubscribed').val(langPresets[curLangPresetIndex]['already_unsubscribed']);
			$('#lang_settings_' + curClang + '_already_subscribed').val(langPresets[curLangPresetIndex]['already_subscribed']);
			$('#lang_settings_' + curClang + '_already_confirmed').val(langPresets[curLangPresetIndex]['already_confirmed']);
			$('#lang_settings_' + curClang + '_user_not_found').val(langPresets[curLangPresetIndex]['user_not_found']);
			$('#lang_settings_' + curClang + '_safety').val(langPresets[curLangPresetIndex]['safety']);
			$('#lang_settings_' + curClang + '_status0').val(langPresets[curLangPresetIndex]['status0']);
			$('#lang_settings_' + curClang + '_status1').val(langPresets[curLangPresetIndex]['status1']);
			$('#lang_settings_' + curClang + '_invalid_key').val(langPresets[curLangPresetIndex]['invalid_key']);
			$('#lang_settings_' + curClang + '_confirmation_successful').val(langPresets[curLangPresetIndex]['confirmation_successful']);
			$('#lang_settings_' + curClang + '_confirmation_sent').val(langPresets[curLangPresetIndex]['confirmation_sent']);
			$('#lang_settings_' + curClang + '_email').val(langPresets[curLangPresetIndex]['email']);
			$('#lang_settings_' + curClang + '_firstname').val(langPresets[curLangPresetIndex]['firstname']);
			$('#lang_settings_' + curClang + '_lastname').val(langPresets[curLangPresetIndex]['lastname']);
			$('#lang_settings_' + curClang + '_grad').val(langPresets[curLangPresetIndex]['grad']);
			$('#lang_settings_' + curClang + '_select_newsletter').val(langPresets[curLangPresetIndex]['select_newsletter']);
			$('#lang_settings_' + curClang + '_subscribe').val(langPresets[curLangPresetIndex]['subscribe']);
			$('#lang_settings_' + curClang + '_unsubscribe').val(langPresets[curLangPresetIndex]['unsubscribe']);
			$('#lang_settings_' + curClang + '_nogroup_selected').val(langPresets[curLangPresetIndex]['nogroup_selected']);
    	});

		// slide
		$('.rex-form-col-1 legend').click(function(e) { 
			$(this).toggleClass('open');
			$(this).next('.rex-form-wrapper.slide').slideToggle();
		});
	});
</script>