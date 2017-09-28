<?php
if (rex::isBackend() && is_object(rex::getUser())) {
    rex_view::addJsFile($this->getAssetsUrl('multinewsletter.js'));
    rex_perm::register('multinewsletter[]', rex_i18n::msg('multinewsletter_addon_short_title'));

    if (rex_get('page', 'string') == 'multinewsletter/user') {
        rex_extension::register('REX_FORM_SAVED', function ($ep) {

            if (MultinewsletterMailchimp::isActive()) {
                $user_id   = rex_get('entry_id', 'int');
                $User      = new MultinewsletterUser($user_id);
                $User->save();
            }
            return $ep->getSubject();
        });
    }
}

if(rex::isBackend()) {
	rex_extension::register('CLANG_DELETED', 'rex_d2u_multinewsletter_clang_deleted');
}

/**
 * Deletes language specific configurations and objects
 * @param rex_extension_point $ep Redaxo extension point
 * @return string[] Warning message as array
 */
function rex_d2u_multinewsletter_clang_deleted(rex_extension_point $ep) {
	$warning = $ep->getSubject();
	$params = $ep->getParams();
	$clang_id = $params['id'];

	// Delete
	$users = MultinewsletterUser::getAll($clang_id, FALSE);
	foreach ($users as $user) {
		$user->clang_id = rex_clang::getStartId();
		$user->save();
	}
	// Delete Archives
	$query_lang = "DELETE FROM ". rex::getTablePrefix() ."375_archive "
		."WHERE clang_id = ". $clang_id;
	$result_lang = rex_sql::factory();
	$result_lang->setQuery($query_lang);
		
	// Delete language settings
	if(rex_config::get('multinewsletter', 'default_test_sprache') ==  $clang_id) {
		rex_config::set('multinewsletter', 'default_test_sprache', rex_clang::getStartId());
	}

	return $warning;
}