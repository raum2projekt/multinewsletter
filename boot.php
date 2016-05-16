<?php
if (rex::isBackend() && is_object(rex::getUser())) {
	rex_view::addJsFile($this->getAssetsUrl('multinewsletter.js'));
	rex_perm::register('multinewsletter[]', rex_i18n::msg('multinewsletter_addon_short_title'));
}