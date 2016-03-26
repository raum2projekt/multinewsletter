<?php
$codeExample1 = file_get_contents($REX['INCLUDE_PATH']. '/addons/multinewsletter/templates/template_01.php');
?>
<div class="rex-addon-output" id="subpage-template">
	<div class="rex-addon-content">
		<div class="addon-template">
			<h1>Beispieltemplate</h1>

			<h2></h2>
			<p>Vorschau Beispielseite:</p>
			<img src="<?php echo $REX['SERVER'] . $REX['MEDIA_ADDON_DIR'] .'/multinewsletter/template/template.jpg'; ?>">
			<p><br>Code des Templates:</p>
			<?php rex_highlight_string($codeExample1); ?>
		</div>
	</div>
</div>