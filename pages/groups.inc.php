<?php
  $sql = new rex_sql;
  $REX['ADDON375']['postget']['status'] = 'show_list';
  $REX['ADDON375']['postget']['error'] = array();
  
  if(!empty($_POST))
  {
    
    if(!empty($REX['ADDON375']['postget']['newsletter_select_item']))
    {
      $queries=array();
      
      foreach($REX['ADDON375']['postget']['newsletter_select_item'] as $id => $item)
      { 
        if(isset($REX['ADDON375']['postget']['newsletter_delete_items']))
        { 
          if(intval($id)>0)
          {
            $queries[] = "DELETE FROM `".$REX['ADDON375']['grouptable']."` WHERE `id`='".$id."'";
            $queries[] = "DELETE FROM `".$REX['ADDON375']['u2gtable']."` WHERE `gid`='".$id."'";
          }
        }
        elseif(!empty($REX['ADDON375']['postget']['newsletter_item'][$id]))
        {
          if(trim($REX['ADDON375']['postget']['newsletter_item'][$id]['name'])!='')
          {
            $qry = "SELECT `id`, `name` FROM `".$REX['ADDON375']['grouptable']."`
                    WHERE `name`='".$REX['ADDON375']['postget']['newsletter_item'][$id]['name']."'
                    AND `id`!='".$id."'";
            $sql->setQuery($qry);
            if($sql->getRows()>0)
              $REX['ADDON375']['postget']['error'][] = $REX['ADDON375']['I18N']->msg('error_nameused',$REX['ADDON375']['postget']['newsletter_item'][$id]['name']);
            else
            {
              if(intval($id)==0)
                $queries[] = "INSERT INTO `".$REX['ADDON375']['grouptable']."`
                              SET `name`='".$REX['ADDON375']['postget']['newsletter_item'][$id]['name']."',
                                  `createdate`='".time()."',
                                  `updatedate`='".time()."'";
              else
                $queries[] = "UPDATE `".$REX['ADDON375']['grouptable']."`
                              SET `name`='".$REX['ADDON375']['postget']['newsletter_item'][$id]['name']."',
                                  `updatedate`='".time()."'
                              WHERE `id`='".$id."'";
            }
          }
          else
          {
            if(intval($id)>0)
              $REX['ADDON375']['postget']['error'][] = $REX['ADDON375']['I18N']->msg('error_nogroupname',$id);
            else
              $REX['ADDON375']['postget']['error'][] = $REX['ADDON375']['I18N']->msg('error_nonewgroupname');
          }
        }
      }
      
      if(!empty($queries) && empty($REX['ADDON375']['postget']['error']))
      {
        foreach($queries as $qry)
        {
          # print_r($qry);
          $sql->setQuery($qry);
        }
        $REX['ADDON375']['postget']['error'][] = $REX['ADDON375']['I18N']->msg('changes_saved');
      }

    }
  }
  
  if(!empty($REX['ADDON375']['postget']['newsletter_showall']))
  {
    unset($REX['ADDON375']['postget']['itemsperpage'],
          $REX['ADDON375']['postget']['showstatus'],
          $REX['ADDON375']['postget']['newsletter_page']
         );
  }

  // generate the ORDER BY parameter for the mysql-query
  if(!empty($REX['ADDON375']['postget']['orderby']))
  {
    foreach($REX['ADDON375']['postget']['orderby'] as $key=>$value)
    {
      $REX['ADDON375']['postget']['orderby'] = $key; break;
    }
    
    if(strpos($REX['ADDON375']['postget']['orderby'],'DESC'))
      $REX['ADDON375']['orderby'] = "`".ereg_replace('DESC','',$REX['ADDON375']['postget']['orderby'])."` DESC";
    else
      $REX['ADDON375']['orderby'] = "`".trim($REX['ADDON375']['postget']['orderby'])."`";
  }
  else
    $REX['ADDON375']['orderby'] = "`name`";

  $REX['ADDON375']['postget']['itemsperpage'] = (intval($REX['ADDON375']['postget']['itemsperpage'])>0 ? intval($REX['ADDON375']['postget']['itemsperpage']) : 20);
  $REX['ADDON375']['postget']['showstatus'] = (intval($REX['ADDON375']['postget']['showstatus'])<-1 || intval($REX['ADDON375']['postget']['showstatus'])>2 || !isset($REX['ADDON375']['postget']['showstatus']) ? -1 : intval($REX['ADDON375']['postget']['showstatus']));
  $REX['ADDON375']['postget']['newsletter_page'] = (intval($REX['ADDON375']['postget']['newsletter_page'])>0 ? intval($REX['ADDON375']['postget']['newsletter_page'])-1 : 0);
  
  if($REX['ADDON375']['postget']['status'] == 'show_list')
  {
    $qry = "SELECT SQL_CALC_FOUND_ROWS * FROM `".$REX['ADDON375']['grouptable']."`
            WHERE 1";
  
    $qry.= (intval($REX['ADDON375']['postget']['showstatus'])>-1 ? " AND `status`='".$REX['ADDON375']['postget']['showstatus']."'" : "");
    
    if(!empty($REX['ADDON375']['postget']['query']))
      $qry.=" AND (`name` LIKE '%".$REX['ADDON375']['postget']['query']."%')"; 

    $qry.= " ORDER BY ".$REX['ADDON375']['orderby'];
    $qry.= " LIMIT ".strval($REX['ADDON375']['postget']['newsletter_page']*$REX['ADDON375']['postget']['itemsperpage']).", ".strval($REX['ADDON375']['postget']['itemsperpage']);
      
    $sql->setQuery($qry);
    $REX['ADDON375']['items']=$sql->getArray();
  
    $sql->setQuery("SELECT FOUND_ROWS()");
    $REX['ADDON375']['numofitems'] = $sql->getArray();
    $REX['ADDON375']['numofitems'] = intval($REX['ADDON375']['numofitems'][0]['FOUND_ROWS()']);
  }
  
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

<?php
if(!empty($REX['ADDON375']['postget']['error']))
{
  echo '<p class="rex-message rex-warning"><span>';
  foreach($REX['ADDON375']['postget']['error'] as $msg)
    echo ''.$msg.'<br />';
  echo '</span></p>';
}
?>
    <p>&nbsp;</p>
    <form action="<?php print $REX['ADDON375']['thispage']?>" method="post" name="MULTINEWSLETTER">
      <input type="hidden" name="newsletter[itemsperpage]" value="<?php print strval($REX['ADDON375']['postget']['itemsperpage'])?>" />
      <input type="hidden" name="newsletter[showstatus]" value="<?php print strval($REX['ADDON375']['postget']['showstatus'])?>" />
      <input type="hidden" name="newsletter[orderby]" value="<?php print strval($REX['ADDON375']['postget']['orderby'])?>" />
      <input type="hidden" name="newsletter[newsletter_page]" value="<?php print strval($REX['ADDON375']['postget']['newsletter_page']+1)?>" />

      <table class="rex-table">
        <thead>
          <tr>
            <th class="rex-icon">&nbsp;</th>
            <th class="myrex_middle"><input class="myrex_submitlink" style="font-weight:bold;" type="submit" name="orderby[name<?php print ($REX['ADDON375']['postget']['orderby']=='name' ? 'DESC' : '')?>]" value="<?php print $REX['ADDON375']['I18N']->msg('newsletter_group')?> (ID)" title="<?php print $REX['ADDON375']['I18N']->msg('orderby_name')?>" /></th>
            <th class="myrex_right">&nbsp;</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td class="rex-icon"><input id="newsletter_select_item0" type="checkbox" name="newsletter_select_item[0]" value="true" style="width:auto" /></td>
            <td class="myrex_middle">
              <ul>
                <li class="clearfix">
                  <label><?php print $REX['ADDON375']['I18N']->msg('new_groupname')?></label>
                  <input id="newsletter_item0" type="text" name="newsletter_item[0][name]" value=""
                         onkeydown="document.getElementById('newsletter_select_item0').checked=true" />
                </li>
              </ul>
            </td>
            <td class="myrex_right" rowspan="<?php print strval($REX['ADDON375']['numofitems']+3)?>">&nbsp;</td>
          </tr>
<?php
  if(!empty($REX['ADDON375']['items']))
  {
    foreach($REX['ADDON375']['items'] as $item)
    {

      echo '
          <input type="hidden" name="newsletter_item['.$item['id'].'][id]" value="'.$item['id'].'" />
          <input type="hidden" name="newsletter_item['.$item['id'].'][createdate]" value="'.$item['createdate'].'" />
          <input type="hidden" name="newsletter_item['.$item['id'].'][updatedate]" value="'.$item['updatedate'].'" />

          <tr class="myrex_'.($item['status']==0 ? 'normal' : 'orange').'"">
            <td class="rex-icon"><input id="newsletter_select_item'.$item['id'].'" type="checkbox" name="newsletter_select_item['.$item['id'].']" value="true" style="width:auto" onclick="myrex_selectallitems(\'newsletter_select_item\',this)" /></td>
            <td class="myrex_middle">
              <a href="javascript:void(0)" onclick="document.getElementById(\'newsletter_item'.$item['id'].'\').style.display=(document.getElementById(\'newsletter_item'.$item['id'].'\').style.display==\'none\' ? \'block\' : \'none\')">'.htmlspecialchars(stripslashes($item['name']),ENT_QUOTES).' ('. $item['id'] .')</a><br />
              <input id="newsletter_item'.$item['id'].'" style="display:none" type="text" 
                     name="newsletter_item['.$item['id'].'][name]" value="'.htmlspecialchars(stripslashes($item['name']),ENT_QUOTES).'"
                     onkeydown="document.getElementById(\'newsletter_select_item'.$item['id'].'\').checked=true" />
            </td>
          </tr>';
    }
?>
          <tr>
            <td valign="middle" class="rex-icon"><input class="myrex_checkbox" type="checkbox" id="newsletter_select_item_all" name="newsletter_select_item_all" value="true" style="width:auto" onclick="myrex_selectallitems('newsletter_select_item',this)" /></td>
            <td class="myrex_middle"><label for="newsletter_select_item_all"><strong><?php print $REX['ADDON375']['I18N']->msg('edit_all_selected')?></strong></label></td>
          </tr>
          <tr class="myrex_spacebelow">
            <td class="rex-icon">&nbsp;</td>
            <td class="myrex_middle">
              <input type="submit" style="width:40%;margin:0 5% 0 0;" class="myrex_submit" name="newsletter_save_all_items" onclick="return myrex_confirm('<?php print $REX['ADDON375']['I18N']->msg('confirm_save_all_items')?>',this.form)" value="<?php print $REX['ADDON375']['I18N']->msg('button_save_all_items')?>" />
              <input type="submit" style="width:40%" class="myrex_submit" name="newsletter_delete_items" onclick="return myrex_confirm('<?php print $REX['ADDON375']['I18N']->msg('confirm_deleteselected')?>',this.form)" title="<?php print $REX['ADDON375']['I18N']->msg('button_submit_delete')?>" value="<?php print $REX['ADDON375']['I18N']->msg('button_submit_delete')?>" />
            </td>
          </tr>
<?php
  }
?>

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
