<?php
if (rex::isBackend() && rex::getUser()) {
	rex_view::addCssFile($this->getAssetsUrl('multinewsletter.css'));
	rex_view::addCssFile($this->getAssetsUrl('qtip.css'));
	rex_view::addCssFile($this->getAssetsUrl('jquery.tag-editor.css'));
	rex_view::addCssFile($this->getAssetsUrl('jquery.dropdown.css'));

	rex_view::addJsFile($this->getAssetsUrl('multinewsletter.js'));
	rex_view::addJsFile($this->getAssetsUrl('jquery.qtip.min.js'));
	rex_view::addJsFile($this->getAssetsUrl('jquery.tag-editor.min.js'));
	rex_view::addJsFile($this->getAssetsUrl('jquery.dropdown.min.js'));	
}