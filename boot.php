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