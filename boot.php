<?php

if (rex::isBackend() && is_object(rex::getUser())) {

    rex_view::addJsFile($this->getAssetsUrl('multinewsletter.js'));
    rex_perm::register('multinewsletter[]', rex_i18n::msg('multinewsletter_addon_short_title'));

    if ($this->getConfig('use_yform')) {
        $page = $this->getProperty('page');
        unset($page['subpages']['user']);

        $this->setProperty('page', $page);
    }

    if (rex_get('page', 'string') == 'multinewsletter/user') {
        rex_extension::register('REX_FORM_SAVED', function ($ep) {

            if (MultinewsletterMailchimp::isActive()) {
                $user_id = rex_get('entry_id', 'int');
                $User    = new MultinewsletterUser($user_id);
                $User->save();
            }
            return $ep->getSubject();
        });
    }

    rex_extension::register('PACKAGES_INCLUDED', function ($ep) {
    });

    rex_extension::register('REX_YFORM_SAVED', function ($ep) {
        $sql = $ep->getSubject();

        if (!($sql instanceof Exception)) {
            $action = $ep->getParam('action');

            if ($ep->getParam('table') == rex::getTablePrefix() . '375_user') {
                $User = new MultinewsletterUser($ep->getParam('id'));

                if ($action != 'update') {
                    $User->setValue('subscriptiontype', 'backend');
                }
                $User->save();
            }
        }
        return $sql;
    });
}