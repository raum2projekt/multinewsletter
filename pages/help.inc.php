<?php
/* ############################## REDAXO HEADERS ############################### */
    include $REX['INCLUDE_PATH'].'/layout/top.php';
  
    print_r(myrexvars_include_jscript($REX['INCLUDE_PATH'].'/addons/'.$REX['ADDON375']['addon_name'].'/scripts/scripts.js'));
    print_r(myrexvars_include_css($REX['INCLUDE_PATH'].'/addons/'.$REX['ADDON375']['addon_name'].'/css/backend.css'));
/* ############################## REDAXO HEADERS ############################### */
?>

<!-- BEGIN: CONTENT //-->
  <div class="rex-addon">
    <div id="rex-title">
      <div class="rex-title-row"><h1><?php print $REX['ADDON375']['I18N']->msg('addon_title')?></h1></div>
      <div class="rex-title-row">
<?php include('include/addons/'.$REX['ADDON375']['addon_name'].'/pages/menu.inc.php'); ?>
      </div>
    </div>
    <p>&nbsp;</p>
    <table class="rex-table">
      <tbody>
<?php
  if(file_exists($REX['INCLUDE_PATH'].'/addons/'.$REX['ADDON375']['addon_name'].'/lang/help_'.$REX['LANG'].'.html'))
    readfile($REX['INCLUDE_PATH'].'/addons/'.$REX['ADDON375']['addon_name'].'/lang/help_'.$REX['LANG'].'.html');
?>
      </tbody>
    </table>
<?php
/* ############################## REDAXO FOOTER ############################### */
  include $REX['INCLUDE_PATH'].'/layout/bottom.php';
/* ############################## REDAXO FOOTER ############################### */
?>
  </div>
