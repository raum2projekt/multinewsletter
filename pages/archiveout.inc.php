<?php
  
  if(isset($REX['ADDON375']['postget']['id']))
  {
    $qry = "SELECT * FROM `".$REX['ADDON375']['archivetable']."` WHERE `id`='".$REX['ADDON375']['postget']['id']."' LIMIT 1";
    $sql = new rex_sql;
    $sql->setQuery($qry);
    $newsletter = $sql->getArray();

    if(!empty($newsletter))
      $newsletter = $newsletter[0];
  }
  
  if(!empty($newsletter))
  {
    if(strpos($newsletter['recipients'],','))
      $newsletter['recipients'] = explode(',',$newsletter['recipients']);
    else
      $newsletter['recipients'] = array();
      
    
    $content = $newsletter[$REX['ADDON375']['postget']['view']];
    
    if($REX['ADDON375']['postget']['view']=='htmlbody')
    {
      print(base64_decode($content));
      exit;
    }
    if($REX['ADDON375']['postget']['view']=='textbody')
    {
      $content = '<textarea cols="20" rows="30" style="width:98%;height:450px;" readonly="readonly">'.base64_decode($content).'</textarea>';
    }
    elseif($REX['ADDON375']['postget']['view']=='recipients')
    {
      $content = array();
      foreach($newsletter['recipients'] as $c)
        $content[] = '<a href="mailto:'.$c.'">'.$c.'</a>';
      
      $content = join('<br />',$content);
    }
  }
  else
    $content = '<p>'.$REX['ADDON375']['I18N']->msg('archive_nocontent').'</p>';


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
    <form action="<?php print $REX['ADDON375']['thispage']?>" method="post" name="MULTINEWSLETTER">
      <table class="rex-table">
      <thead>
          <tr>
            <th class="rex-icon">&nbsp;</th>
            <th class="myrex_middle"><?php print $REX['ADDON375']['I18N']->msg('archive_output_'.$REX['ADDON375']['postget']['view'],$newsletter['subject'])?></th>
            <th class="myrex_right"><?php print $REX['ADDON375']['I18N']->msg('archive_output_details')?></th>
          </tr>
        </thead>
        <tbody>
          <tr class="myrex_spacebelow">
            <td class="rex-icon" valign="top">
              &nbsp;
            </td>
            <td class="myrex_middle">
<?php print $content?>            
            </td>
            <td class="myrex_right">
              <p>
                <strong><?php print htmlspecialchars(stripslashes($newsletter['subject']),ENT_QUOTES)?></strong>
              </p>
              <p>&nbsp;</p>
              <p>
                <strong><?php print $REX['ADDON375']['I18N']->msg('archive_sentdate')?></strong><br />
                <?php print myrexvars_formatted_date($newsletter['sentdate'],1).', '.myrexvars_formatted_date($newsletter['sentdate'],2)?>
              </p>
              <p>&nbsp;</p>
              <p>
                <strong><?php print $REX['ADDON375']['I18N']->msg('archive_recipients')?></strong>:<?php print strval(count($newsletter['recipients']))?><br />
              </p>
              <p>&nbsp;</p>
              <p>
                <strong><?php print $REX['ADDON375']['I18N']->msg('archive_language')?></strong>: <?php print $REX['CLANG'][$newsletter['clang']]?><br />
                <strong><?php print $REX['ADDON375']['I18N']->msg('archive_sentby')?></strong>: <?php print htmlspecialchars(stripslashes($newsletter['sentby']),ENT_QUOTES)?><br />
                <strong><?php print $REX['ADDON375']['I18N']->msg('archive_format')?></strong>: <?php print htmlspecialchars(stripslashes($newsletter['format']),ENT_QUOTES)?>
              </p>
              <p>&nbsp;</p>
              <p>
                <strong><?php print $REX['ADDON375']['I18N']->msg('archive_groupname')?></strong><br />
                <?php print htmlspecialchars(stripslashes($newsletter['groupname']),ENT_QUOTES)?>
              </p>
              <p>&nbsp;</p>
              <p>
                <a href="javascript:history.back()"><strong><?php print $REX['ADDON375']['I18N']->msg('archive_back')?></strong></a>
              </p>
            </td>
          </tr>
        </tbody>
      </table>
    </form>
  <!-- END: ITEMS COMMENTS //-->
<?php
/* ############################## REDAXO FOOTER ############################### */
  include $REX['INCLUDE_PATH'].'/layout/bottom.php';
/* ############################## REDAXO FOOTER ############################### */
?>  
  </div>
