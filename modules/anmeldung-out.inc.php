<?php
  global $rex_array_values;

  $rex_375_file = $REX['INCLUDE_PATH'].'/addons/'.$REX['ADDON375']['addon_name'].'/functions/redaxo_modules.inc.php';
  if(file_exists($rex_375_file))
  {
  
    $rex_375_devider = '|,|';
    $rex_array_values = array(
      '1'=>'REX_VALUE[1]',
      '2'=>'REX_VALUE[2]',
      '3'=>'REX_VALUE[3]',
      '4'=>'REX_VALUE[4]',
      '5'=>'REX_VALUE[5]',
      '6'=>'REX_VALUE[6]',
      '7'=>'REX_VALUE[7]',
      '8'=>'REX_VALUE[8]',
      '9'=>'REX_VALUE[9]',
      '10'=>'REX_VALUE[10]'
    );

    foreach($rex_array_values as $key => $value)
    {
      if(substr($value, 0, 7)==':array:')
      {
        $temp = substr($value, 7, strlen($value)-8);
        if(strpos($temp,$rex_375_devider))
          $temp = explode($rex_375_devider,$temp);
        else
          $temp = array($temp);
        
        $rex_array_values[$key] = $temp;
      }
      else
        $rex_array_values[$key] = $value;
    }
    unset($rex_375_devider,$key,$value,$temp);
    # print_r($rex_array_values);

    include_once($rex_375_file);
    print(rex_a375_module_output($rex_array_values));
  }
  else
    print($REX['ADDON375']['I18N']->msg('module_not_found'));
?>
