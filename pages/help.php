<?php

$mypage = rex_request('page','string');
$subpage = rex_request('subpage', 'string');
$chapter = rex_request('chapter', 'string');
$func = rex_request('func', 'string');

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
			$chapternav .= ' | <a href="' . rex_url::currentBackendPage() . '&amp;chapter=' . $chapterparam . '">' . $chapterprops[0] . '</a>';
		} else {
			$chapternav .= ' | <a class="active" href="' . rex_url::currentBackendPage() . '&amp;chapter=' . $chapterparam . '">' . $chapterprops[0] . '</a>';
		}
	}
}
?>
<div class="panel panel-edit">
	<header class="panel-heading"><div class="panel-title"><?php print ltrim($chapternav, " | "); ?></div></header>
	<div class="panel-body">
		<?php include(rex_path::addon("multinewsletter", $chapterpages[$chapter][1])); ?>
	</div>
</div>

<style type="text/css">
div.rex-addon-content p.rex-code {
	margin-bottom: 22px;
}

.addon-template h1 {
	font-size: 18px;
	margin-bottom: 7px;
}

#subpage-help a.rex-active {
    color: #14568A;
}

#subpage-help div.rex-addon-content {
    padding: 10px 12px;
}

#subpage-help div.rex-addon-content ul {
	margin-top: 0;
}
</style>

<script type="text/javascript">
jQuery(document).ready(function($) {
	$("#subpage-help").delegate("a", "click", function(event) {
		var host = new RegExp("/" + window.location.host + "/");

		if (!host.test(this.href)) {
			event.preventDefault();
			event.stopPropagation();

			window.open(this.href, "_blank");
		}
	});
});
</script>