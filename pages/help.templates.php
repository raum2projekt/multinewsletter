<fieldset>
	<legend>Beispieltemplate</legend>
	<p>Vorschau Beispielseite:</p>
	<img src="<?php echo rex_url::addonAssets("multinewsletter", 'template/template.jpg'); ?>">
	<p><br>Code des Templates:</p>
	<?php print rex_string::highlight(file_get_contents(rex_path::addon("multinewsletter") . 'templates/template_01.php')); ?>
</fieldset>