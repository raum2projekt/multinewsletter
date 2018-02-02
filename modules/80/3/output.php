<?php
// Anzuzeigende Gruppen IDs
$groups = rex_var::toArray("REX_VALUE[1]");

$addon = rex_addon::get('multinewsletter');

$showform = true;

if(filter_input(INPUT_GET, 'activationkey', FILTER_VALIDATE_INT, ['options' => ['default'=> 0]]) > 0 && filter_input(INPUT_GET, 'email', FILTER_VALIDATE_EMAIL) != "") {
	$user = MultinewsletterUser::initByMail(filter_input(INPUT_GET, 'email', FILTER_VALIDATE_EMAIL));
	if($user->activationkey == filter_input(INPUT_GET, 'activationkey', FILTER_VALIDATE_INT)) {
		print '<p>'. $addon->getConfig("lang_". rex_clang::getCurrentId() ."_confirmation_successful") .'</p>';
		$user->activate();
	}
	else if($user->activationkey == 0) {
		print '<p>'. $addon->getConfig("lang_". rex_clang::getCurrentId() ."_already_confirmed") .'</p>';
	}
	else {
		print '<p>'. $addon->getConfig("lang_". rex_clang::getCurrentId() ."_invalid_key") .'</p>';
	}
	$showform = false;		
}

$form_groups = filter_input_array(INPUT_POST, ['groups'=> ['filter' => FILTER_VALIDATE_INT, 'flags' => FILTER_REQUIRE_ARRAY]]);
$messages = [];

if(filter_input(INPUT_POST, 'submit') != "") {
	$save = true;
	// Fehlermeldungen finden
	if(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL) == "") {
		$messages[] = $addon->getConfig("lang_". rex_clang::getCurrentId() ."_invalid_email");
	}
	
	// Userobjekt deklarieren
	$user = false;
	if(count($messages) > 0) {
		print '<p><b>'. $addon->getConfig("lang_". rex_clang::getCurrentId() ."_no_userdata") .'</b></p>';
		print '<ul>';
		foreach($messages as $message) {
			print '<li><b>'. $message .'</b></li>';
		}
		print '</ul>';
		print '<br>';
		
		$save = false;
	}
	else if(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL) != "") {
		// Ist Benutzer schon in der Newslettergruppe?
		$user = MultinewsletterUser::initByMail(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL));
		if($user !== FALSE && $user->getId() > 0 && $user->status == 1) {
			$not_already_subscribed = [];
			if(count($user->group_ids) > 0 && count($form_groups['groups']) > 0) {
				foreach($form_groups['groups'] as $group_id) {
					if(!in_array($group_id, $user->group_ids)) {
						$not_already_subscribed[] = $group_id;
					}
				}
			}
			if(count($form_groups['groups']) > 0 && empty($not_already_subscribed)) {
				print '<p><b>'. $addon->getConfig("lang_". rex_clang::getCurrentId() ."_already_subscribed") .'</b></p>';
				$save = false;
			}

			$showform = false;
		}
	}
	
	if($save) {
		// Benutzer speichern
		if($user !== false) {
			$user->clang_id = rex_clang::getCurrentId();
		}
		else {
			$user = MultinewsletterUser::factory(
				filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL),
				"",
				"",
				"",
				"",
				rex_clang::getCurrentId()
			);
		}
		$user->createdate = time();
		$user->createip = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);
		$user->group_ids = $form_groups['groups'];
		$user->status = 0;
		$user->subscriptiontype = 'web';
		$user->activationkey = rand(100000, 999999);
		$user->save();

		// Aktivierungsmail senden und Hinweis ausgeben
		$user->sendActivationMail(
			$addon->getConfig('sender'),
			$addon->getConfig("lang_". rex_clang::getCurrentId() ."_sendername"),
			$addon->getConfig("lang_". rex_clang::getCurrentId() ."_confirmsubject"),
			$addon->getConfig("lang_". rex_clang::getCurrentId() ."_confirmcontent")
		);
		print '<p>'. $addon->getConfig("lang_". rex_clang::getCurrentId() ."_confirmation_sent") .'</p>';
		
		$showform = false;
	}
}


if($showform) {
	if(count($messages) == 0) {
		print '<p>'. $addon->getConfig("lang_". rex_clang::getCurrentId() ."_action") .'</p>';	
	}
?>
<div id="rex-xform" class="xform">
	<form action="<?php print rex_getUrl(rex_article::getCurrentId(), rex_clang::getCurrentId()); ?>" method="post" name="subscribe">
		<p class="formtext formlabel-email" id="xform-formular-email">
			<label class="text" for="email"><?php print $addon->getConfig("lang_". rex_clang::getCurrentId() ."_email"); ?> *</label>
			<input class="email" name="email" id="lastname" value="<?php print filter_input(INPUT_POST, 'email'); ?>" type="email" maxlength="100" required>
		</p>
		<?php
			if(count($groups) == 1) {
				foreach($groups as $group_id) {
					print '<input type="hidden" name="groups['. $group_id.']" value="'. $group_id .'" />';
				}
			}
			else if(count($groups) > 1) {
				print '<br clear="all"><p>'. $addon->getConfig("lang_". rex_clang::getCurrentId() ."_select_newsletter") .'</p>';
				
				foreach($groups as $group_id) {
					$group = new MultinewsletterGroup($group_id);
					if($group->name != "") {
						print '<p class="formcheckbox formlabel-group" id="xform-formular">';
						$checked = "";
						if(isset($form_groups[$group_id]) && $form_groups[$group_id] > 0) {
							$checked = ' checked="checked"';
						}
						print '<input class="checkbox" name="groups['. $group_id .']" id="xform-formular-'. $group_id .'" value="'. $group_id .'" type="checkbox"'. $checked .'>';
						print '<label class="checkbox" for="groups['. $group_id .']">'. $group->name .'</label>';
						print '</p>';
					}
				}
			}
		?>
		<p><?php print $addon->getConfig("lang_". rex_clang::getCurrentId() ."_compulsory"); ?></p>
		<p><?php print $addon->getConfig("lang_". rex_clang::getCurrentId() ."_safety"); ?></p>
		<p class="formsubmit formsubmit">
			<input class="submit cssclassname" name="submit" id="submit" value="<?php print $addon->getConfig("lang_". rex_clang::getCurrentId() ."_subscribe"); ?>" type="submit">
		</p>
	</form>
</div>
<?php
}
?>