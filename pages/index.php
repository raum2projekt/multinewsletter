<?php
echo rex_view::title($this->i18n('multinewsletter_addon_short_title'));

rex_be_controller::includeCurrentPageSubPath();
?>
<script type="text/javascript">
jQuery(document).ready(function($) {
	if (!jQuery.ui) {
		$('head').append('<script type="text/javascript" src="../<?php echo rex_path::addonAssets(rex_request('page')); ?>/jquery-ui.min.js" />');
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