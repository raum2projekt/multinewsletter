<?php
// Wenn alte Konfigurationsdatei existiert muss MultiNewsletter auf Version 2.0.0 aktualisiert werden
$old_config_file = $REX['INCLUDE_PATH'] . '/addons/multinewsletter/files/.configfile';
if(file_exists($old_config_file)) {
	// includes
	require_once($REX['INCLUDE_PATH'] . '/addons/multinewsletter/classes/class.multinewsletter_update.inc.php');
	multinewsletter_update::updateConfig();
	multinewsletter_update::updateDatabase();
}

$basedir = dirname(__FILE__);
$page = rex_request('page', 'string');
$subpage = rex_request('subpage', 'string');
$func = rex_request('func', 'string');
$entry_id = rex_request('entry_id', 'int') == "" ? 0 : rex_request('entry_id', 'int');
$page_base_url = "index.php?page=". $page ."&subpage=". $subpage;

// CSV Export muss vor erstem "print" oder "echo" erfolgen
if(filter_input(INPUT_POST, 'newsletter_exportusers')) {
	$func = "export";
	require $basedir .'/user.inc.php';
}

// Anzeigen eines Newsletters muss vor erstem "print" oder "echo" erfolgen
if(filter_input(INPUT_GET, 'shownewsletter', FILTER_VALIDATE_INT, ['options' => ['default'=> 0]]) > 0) {
	$func = "shownewsletter";
	require $basedir .'/archive.inc.php';
}

/**
 * Create a raw field
 * @param $label Sting Text im <label></label> Bereich.
 * @param $text Sting Text im Breich der rechten Spalte.
 */
function raw_field($label, $text) {
	return '<div class="rex-form-row"><p class="rex-form-col-a rex-form-text"><label>'.
			$label .'</label>'. $text .'</p></div>';
}

// layout top
require($REX['INCLUDE_PATH'] . '/layout/top.php');

$subpages = array(
	array('newsletter', $I18N -> msg('multinewsletter_menu_newsletter')),
	array('user', $I18N -> msg('multinewsletter_menu_user')),
	array('groups', $I18N -> msg('multinewsletter_menu_groups')),
	array('archive', $I18N -> msg('multinewsletter_menu_archive')),
	array('config', $I18N -> msg('multinewsletter_menu_config')),
	array('import', $I18N -> msg('multinewsletter_menu_import')),
	array('help', $I18N -> msg('multinewsletter_menu_help')),
);

// title
rex_title($REX['ADDON']['name'][$page] . ' <span style="font-size:14px; color:silver;">' . $REX['ADDON']['version'][$page] . '</span>', $subpages);

switch($subpage) {
	case "archive":
		require $basedir .'/archive.inc.php';
		break;

	case "config":
		require $basedir .'/config.inc.php';
		break;

	case "groups":
		require $basedir .'/groups.inc.php';
		break;

	case "help":
		require $basedir .'/help.inc.php';
		break;

	case "import":
		require $basedir .'/import.inc.php';
		break;

	case "newsletter":
		require $basedir .'/newsletter.inc.php';
		break;

	case "user":
		require $basedir .'/user.inc.php';
		break;

	default:
		require $basedir .'/newsletter.inc.php';
}
?>

<script type="text/javascript">
jQuery(document).ready(function($) {
	if (!jQuery.ui) {
		$('head').append('<script type="text/javascript" src="../<?php echo $REX['MEDIA_ADDON_DIR']; ?>/<?php echo rex_request('page'); ?>/jquery-ui.min.js" />');
	}

    $('.multinewsletter-tooltip[title]').qtip({
		style: {
			classes: 'qtip-default qtip-rounded qtip-shadow'
		},
		content: {
			title: '<?php echo $I18N->msg('multinewsletter_tooltip_headline'); ?>'
		}
	});

	$('.multinewsletter-info-tooltip[title]').qtip({
		style: {
			classes: 'qtip-light qtip-rounded qtip-shadow'
		},
		content: {
			title: '<?php echo $I18N->msg('multinewsletter_tooltip_info_headline'); ?>'
		}
	});
});
</script>

<?php
// layout bottom
require $REX['INCLUDE_PATH'] . '/layout/bottom.php';
