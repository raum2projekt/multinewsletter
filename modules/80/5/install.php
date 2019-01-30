<?php
$return = TRUE;
if(!rex_addon::get('yform')->isAvailable()) {
	print rex_view::error(rex_i18n::msg('d2u_helper_modules_error_yform'));
	$return = $return ? FALSE : $return;
}
return $return;