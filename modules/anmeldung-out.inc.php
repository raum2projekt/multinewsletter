<?php
require_once $REX['INCLUDE_PATH'] .'/addons/multinewsletter/classes/class.multinewsletter_user.inc.php';
require_once $REX['INCLUDE_PATH'] .'/addons/multinewsletter/classes/class.multinewsletter_group.inc.php';

// Anzuzeigende Gruppen IDs
$groups = preg_grep('/^\s*$/s', explode("|", "REX_VALUE[1]"), PREG_GREP_INVERT);

$showform = true;

if(filter_input(INPUT_GET, 'activationkey', FILTER_VALIDATE_INT) > 0 && filter_input(INPUT_GET, 'email', FILTER_VALIDATE_EMAIL) != "") {
	$user = MultinewsletterUser::initByMail(filter_input(INPUT_GET, 'email', FILTER_VALIDATE_EMAIL), $REX['TABLE_PREFIX']);
	if($user->activationkey == filter_input(INPUT_GET, 'activationkey', FILTER_VALIDATE_INT)) {
		print '<p>'. $REX['ADDON']['multinewsletter']['settings']['lang'][$REX['CUR_CLANG']]['confirmation_successful'] .'</p>';
		$user->activate();
	}
	else if($user->activationkey == 0) {
		print '<p>'. $REX['ADDON']['multinewsletter']['settings']['lang'][$REX['CUR_CLANG']]['already_confirmed'] .'</p>';
	}
	else {
		print '<p>'. $REX['ADDON']['multinewsletter']['settings']['lang'][$REX['CUR_CLANG']]['invalid_key'] .'</p>';
	}
	$showform = false;		
}

$form_groups = filter_input_array(INPUT_POST, array('groups'=> array('filter' => FILTER_VALIDATE_INT, 'flags' => FILTER_REQUIRE_ARRAY)));
$messages = array();

if(filter_input(INPUT_POST, 'submit') != "") {
	$save = true;
	// Fehlermeldungen finden
	if(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL) == "") {
		$messages[] = $REX['ADDON']['multinewsletter']['settings']['lang'][$REX['CUR_CLANG']]['invalid_email'];
	}
	if(filter_input(INPUT_POST, 'firstname') == "" || strlen(filter_input(INPUT_POST, 'firstname')) > 30) {
		$messages[] = $REX['ADDON']['multinewsletter']['settings']['lang'][$REX['CUR_CLANG']]['invalid_firstname'];
	}
	if(filter_input(INPUT_POST, 'lastname') == "" || strlen(filter_input(INPUT_POST, 'lastname')) > 30) {
		$messages[] = $REX['ADDON']['multinewsletter']['settings']['lang'][$REX['CUR_CLANG']]['invalid_lastname'];
	}
	if(count($form_groups['groups']) == 0) {
		$messages[] = $REX['ADDON']['multinewsletter']['settings']['lang'][$REX['CUR_CLANG']]['nogroup_selected'];
	}
	
	// Userobjekt deklarieren
	$user = false;
	if(count($messages) > 0) {
		print '<p><b>'. $REX['ADDON']['multinewsletter']['settings']['lang'][$REX['CUR_CLANG']]['no_userdata'] .'</b></p>';
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
		$user = MultinewsletterUser::initByMail(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL), $REX['TABLE_PREFIX']);
		if($user->user_id > 0 && $user->status == 1) {
			$not_already_subscribed = array();
			if(count($user->group_ids) > 0 && count($form_groups['groups']) > 0) {
				foreach($form_groups['groups'] as $group_id) {
					if(!in_array($group_id, $user->group_ids)) {
						$not_already_subscribed[] = $group_id;
					}
				}
			}
			if(count($form_groups['groups']) > 0 && empty($not_already_subscribed)) {
				print '<p><b>'. $REX['ADDON']['multinewsletter']['settings']['lang'][$REX['CUR_CLANG']]['already_subscribed'] .'</b></p>';
				$save = false;
			}

			$showform = false;
		}
	}
	
	if($save) {
		// Benutzer speichern
		if($user !== false) {
			$user->title = filter_input(INPUT_POST, 'anrede', FILTER_VALIDATE_INT);
			$user->firstname = filter_input(INPUT_POST, 'firstname');
			$user->lastname = filter_input(INPUT_POST, 'lastname');
			$user->clang_id = $REX['CUR_CLANG'];
		}
		else {
			$user = MultinewsletterUser::factory(
				filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL),
				filter_input(INPUT_POST, 'anrede', FILTER_VALIDATE_INT),
				filter_input(INPUT_POST, 'grad'),
				filter_input(INPUT_POST, 'firstname'),
				filter_input(INPUT_POST, 'lastname'),
				$REX['CUR_CLANG'],
				$REX['TABLE_PREFIX']
			);
		}
		$user->createdate = time();
		$user->createIP = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);
		$user->group_ids = $form_groups['groups'];
		$user->status = 0;
		$user->subscriptiontype = 'web';
		$user->activationkey = rand(100000, 999999);
		$user->save();

		// Aktivierungsmail senden und Hinweis ausgeben
		$user->sendActivationMail(
			$REX['ADDON']['multinewsletter']['settings']['sender'],
			$REX['ADDON']['multinewsletter']['settings']['lang'][$REX['CUR_CLANG']]['sendername'],
			$REX['ADDON']['multinewsletter']['settings']['lang'][$REX['CUR_CLANG']]['confirmsubject'],
			$REX['ADDON']['multinewsletter']['settings']['lang'][$REX['CUR_CLANG']]['confirmcontent']
		);
		print '<p>'. $REX['ADDON']['multinewsletter']['settings']['lang'][$REX['CUR_CLANG']]['confirmation_sent'] .'</p>';
		
		$showform = false;
	}
}


if($showform) {
	if(count($messages) == 0) {
		print '<p>'. $REX['ADDON']['multinewsletter']['settings']['lang'][$REX['CUR_CLANG']]['action'] .'</p>';	
	}
?>
<div id="rex-xform" class="xform">
	<form action="<?php print rex_getUrl($this->getArticleId(), $REX['CUR_CLANG']); ?>" method="post" name="subscribe" class="rex-xform">
		<p class="formselect formlabel-anrede" id="xform-formular-anrede">
			<label class="select" for="anrede"><?php print $REX['ADDON']['multinewsletter']['settings']['lang'][$REX['CUR_CLANG']]['anrede']; ?></label>
			<select class="select" id="anrede" name="anrede" size="1">
				<option value="0"><?php print $REX['ADDON']['multinewsletter']['settings']['lang'][$REX['CUR_CLANG']]['title_0']; ?></option>
				<?php
					$selected = "";
					if(filter_input(INPUT_POST, 'anrede', FILTER_VALIDATE_INT) == 1) {
						$selected = ' selected';
					}
				?>
				<option value="1" <?php print $selected; ?>><?php print $REX['ADDON']['multinewsletter']['settings']['lang'][$REX['CUR_CLANG']]['title_1']; ?></option>
			</select>
		</p>
		<p class="formtext formlabel-grad" id="xform-formular-grad">
			<label class="text" for="grad"><?php print $REX['ADDON']['multinewsletter']['settings']['lang'][$REX['CUR_CLANG']]['grad']; ?></label>
			<input class="text" name="grad" id="grad" value="<?php print filter_input(INPUT_POST, 'grad'); ?>" type="text" maxlength="15">
		</p>
		<p class="formtext formlabel-firstname" id="xform-formular-firstname">
			<label class="text" for="firstname"><?php print $REX['ADDON']['multinewsletter']['settings']['lang'][$REX['CUR_CLANG']]['firstname']; ?> *</label>
			<input class="text" name="firstname" id="firstname" value="<?php print filter_input(INPUT_POST, 'firstname'); ?>" type="text" maxlength="30" required>
		</p>
		<p class="formtext formlabel-lastname" id="xform-formular-lastname">
			<label class="text" for="lastname"><?php print $REX['ADDON']['multinewsletter']['settings']['lang'][$REX['CUR_CLANG']]['lastname']; ?> *</label>
			<input class="text" name="lastname" id="lastname" value="<?php print filter_input(INPUT_POST, 'lastname'); ?>" type="text" maxlength="30" required>
		</p>
		<p class="formtext formlabel-email" id="xform-formular-email">
			<label class="text" for="email"><?php print $REX['ADDON']['multinewsletter']['settings']['lang'][$REX['CUR_CLANG']]['email']; ?> *</label>
			<input class="email" name="email" id="lastname" value="<?php print filter_input(INPUT_POST, 'email'); ?>" type="text" maxlength="100" required>
		</p>
		<?php
			if(count($groups) == 1) {
				foreach($groups as $group_id) {
					print '<input type="hidden" name="groups['. $group_id.']" value="'. $group_id .'" />';
				}
			}
			else if(count($groups) > 1) {
				print '<br clear="all"><p>'. $REX['ADDON']['multinewsletter']['settings']['lang'][$REX['CUR_CLANG']]['select_newsletter'] .'</p>';
				
				foreach($groups as $group_id) {
					$group = new MultinewsletterGroup($group_id, $REX['TABLE_PREFIX']);
					print '<p class="formcheckbox formlabel-group" id="xform-formular">';
					$checked = "";
					if(isset($form_groups[$group->group_id]) && $form_groups[$group->group_id] > 0) {
						$checked = ' checked="checked"';
					}
					print '<input class="checkbox" name="groups['. $group->group_id .']" id="xform-formular-'. $group->group_id .'" value="'. $group->group_id .'" type="checkbox"'. $checked .'>';
					print '<label class="checkbox" for="groups['. $group->group_id .']">'. $group->name .'</label>';
					print '</p>';
				}
			}
		?>
		<p><?php print $REX['ADDON']['multinewsletter']['settings']['lang'][$REX['CUR_CLANG']]['compulsory']; ?></p>
		<p><?php print $REX['ADDON']['multinewsletter']['settings']['lang'][$REX['CUR_CLANG']]['safety']; ?></p>
		<p class="formsubmit formsubmit">
			<input class="submit cssclassname" name="submit" id="submit" value="<?php print $REX['ADDON']['multinewsletter']['settings']['lang'][$REX['CUR_CLANG']]['subscribe']; ?>" type="submit">
		</p>
	</form>
</div>
<?php
}
?>
