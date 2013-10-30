<?php
  global $rex_array_values;

  $rex_375_file = $REX['INCLUDE_PATH'].'/addons/'.$REX['ADDON375']['addon_name'].'/functions/redaxo_modules.inc.php';
  if(file_exists($rex_375_file))
  {
    include_once($rex_375_file);
    print(rex_a375_module_input($rex_array_values));
  }
  else
    print($REX['ADDON375']['I18N']->msg('module_not_found'));
?>
