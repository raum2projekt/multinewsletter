<?php
// -------------- CONFIG 

$value_id = range(1,10);
$value_sep = '|';

// -------------- END OF CONFIG 
$post_values = filter_input_array(INPUT_POST, array('VALUE'=> array('filter' => FILTER_VALIDATE_INT, 'flags' => FILTER_REQUIRE_ARRAY)));
$values = (array) $value_id;
foreach ($values as $value_id) {
	if (!isset ($post_values['VALUE'][$value_id]) or $post_values['VALUE'][$value_id] == '') {
		continue;
	}
	$value = $post_values['VALUE'][$value_id];

	$str_value = '';

	if (is_array($value)) {
		$str_value = implode($value_sep, $value);
	}
	else {
		$str_value = $value;
	}
	$REX_ACTION['VALUE'][$value_id] = $str_value;
}