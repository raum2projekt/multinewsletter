<?php
// Abmeldung aus Formular holen
$unsubscribe_mail = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
if($unsubscribe_mail == "") {
	// Abmeldung aus URL holen
	$unsubscribe_mail = filter_input(INPUT_GET, 'unsubscribe', FILTER_VALIDATE_EMAIL);
}

if($REX['REDAXO']) {
	print '<p><b>Multinewsletter Abmeldung</b></p>';
	print '<p>Texte, Bezeichnungen bzw. Übersetzugen werden im <a href="index.php?page=multinewsletter&subpage=config">Multinewsletter Addon</a> verwaltet.</p>';

}
else {
	print '<div class="nl-form">';
	print '<h2>'. $REX['ADDON']['multinewsletter']['settings']['lang'][$REX['CUR_CLANG']]['unsubscribe'] .'</h2>';
	print '<br>';
	
	$showform = true;
	if($unsubscribe_mail != "") {
		require_once $REX['INCLUDE_PATH'] .'/addons/multinewsletter/classes/class.multinewsletter_user.inc.php';

		$user = MultinewsletterUser::initByMail($unsubscribe_mail, $REX['TABLE_PREFIX']);
		if($user->user_id > 0) {
			$user->delete();
			
			print "<p>". $REX['ADDON']['multinewsletter']['settings']['lang'][$REX['CUR_CLANG']]['status0'] ."</p>";
			$showform = false;
		}
		else {
			print "<p>". $REX['ADDON']['multinewsletter']['settings']['lang'][$REX['CUR_CLANG']]['user_not_found'] ."</p>";
		}
	}

	if($unsubscribe_mail == "" && filter_input(INPUT_POST, 'email') != "") {
		print "<p><b>". $REX['ADDON']['multinewsletter']['settings']['lang'][$REX['CUR_CLANG']]['invalid_email'] ."</b></p>";
	}
	
	if($showform) {
?>
		<form id="unsubscribe" class="formation" action="<?php print rex_getURL($this->article_id, $REX['CUR_CLANG']); ?>"
				method="post" name="unsubscribe">
			   <p>
				<label for="email"><?php print $REX['ADDON']['multinewsletter']['settings']['lang'][$REX['CUR_CLANG']]['email']; ?></label>
				<input type="text" class="text" name="email" value="" />
			   </p>
			   <p>
				<input type="submit" class="submit" name="unsubscribe_newsletter"
					value="<?php print $REX['ADDON']['multinewsletter']['settings']['lang'][$REX['CUR_CLANG']]['unsubscribe']; ?>" />
			</p>
		</form>
<?php
	}
	print '</div>';
}
?>