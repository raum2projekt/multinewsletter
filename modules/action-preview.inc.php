<?php
  global $rex_array_values;
  $devider = '|,|';
  
  for($i=1; $i<21; $i++)
    if(isset($REX_ACTION['VALUE'][strval($i)]) && substr($REX_ACTION['VALUE'][strval($i)], 0, 7)==':array:')
    {
      if(!isset($rex_array_values))
        $rex_array_values = array();
      $temp = substr($REX_ACTION['VALUE'][strval($i)], 7, strlen($REX_ACTION['VALUE'][strval($i)])-8);
      if(strpos($temp,$devider))
        $temp = explode($devider,$temp);
      else
        $temp = array($temp);
      
      $rex_array_values[strval($i)] = $temp;
    }
    else
    {
      if(!isset($rex_array_values[strval($i)]))
        $rex_array_values[strval($i)] = $REX_ACTION['VALUE'][strval($i)];
    }
?>
