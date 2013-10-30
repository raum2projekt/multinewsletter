<?php
  global $rex_array_values;
  $rex_array_values = array();

  $devider = '|,|';

  for($i=1; $i<21; $i++)
  {
    $rex_array_values[strval($i)] = $_REQUEST['VALUE'][$i];

    if(isset($_REQUEST['VALUE'][$i]))
    {
      $rex_array_values[strval($i)] = $_REQUEST['VALUE'][$i];
      if(is_array($_REQUEST['VALUE'][$i]))
        $REX_ACTION['VALUE'][strval($i)] = ":array:".join($devider,$_REQUEST['VALUE'][$i]).":";
    }
  }
?>
