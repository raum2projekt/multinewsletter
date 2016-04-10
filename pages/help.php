<?php
$chapter = rex_request('chapter', 'string');

$chapterpages = array (
	'' => array(rex_i18n::msg('multinewsletter_help_chapter_readme'), 'pages/help/readme.php'),
	'faq' => array(rex_i18n::msg('multinewsletter_help_chapter_faq'), 'pages/help/faq.php'),
	'import' => array(rex_i18n::msg('multinewsletter_help_chapter_import'), 'pages/help/import.php'),
	'module' => array(rex_i18n::msg('multinewsletter_help_chapter_module'), 'pages/help/module.php'),
	'template' => array(rex_i18n::msg('multinewsletter_help_chapter_template'), 'pages/help/templates.php'),
	'updatehinweise' => array(rex_i18n::msg('multinewsletter_help_chapter_updatehinweise'), 'pages/help/updatehinweise.php'),
	'versand' => array(rex_i18n::msg('multinewsletter_help_chapter_versand'), 'pages/help/versand.php'),
	'changelog' => array(rex_i18n::msg('multinewsletter_help_chapter_changelog'), 'pages/help/changelog.php'),
);

// build chapter navigation
$chapternav = '';

foreach ($chapterpages as $chapterparam => $chapterprops) {
	if ($chapterprops[0] != '') {
		if ($chapter != $chapterparam) {
			$chapternav .= ' | <a href="'. rex_url::currentBackendPage(array('chapter' => $chapterparam)) .'">'. $chapterprops[0] .'</a>';
		} else {
			$chapternav .= ' | <a class="active" href="'. rex_url::currentBackendPage(array('chapter' => $chapterparam)) .'">'. $chapterprops[0] .'</a>';
		}
	}
}
?>
<style type="text/css">
	.panel-title a.active {
		text-decoration: underline;
	}
</style>
<div class="panel panel-edit">
	<header class="panel-heading"><div class="panel-title"><?php print ltrim($chapternav, " | "); ?></div></header>
	<div class="panel-body">
		<?php include(rex_path::addon("multinewsletter", $chapterpages[$chapter][1])); ?>
	</div>
</div>