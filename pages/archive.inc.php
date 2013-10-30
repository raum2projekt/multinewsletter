<?php
$sql = new rex_sql;
$REX['ADDON375']['postget']['status'] = 'show_list';
$REX['ADDON375']['postget']['error'] = array();

// START: delete from archiv if selected
if(isset($REX['ADDON375']['postget']['newsletter_item']) || isset($REX['ADDON375']['postget']['newsletter_delete_items'])) {
	$delete_item_ids = array();

	// if only one entry should be deleted
	if(isset($REX['ADDON375']['postget']['newsletter_item'])) {
		foreach($REX['ADDON375']['postget']['newsletter_item'] as $key => $value) {
			$delete_item_ids[] = $key;
		}
	}

	// if multiple entrys should be deleted
	if(isset($REX['ADDON375']['postget']['newsletter_delete_items'])) {
		foreach($REX['ADDON375']['postget']['newsletter_select_item'] as $key => $value) {
			$delete_item_ids[] = $key;
		}
	}
	
	// deleting...
	$qry = "DELETE FROM `".$REX['ADDON375']['archivetable']."` WHERE";
	foreach($delete_item_ids as $counter => $id) {
		if($counter > 0) {
			$qry .= " OR"; 
		}
		$qry .= " `id` = ". $id;
	}
	$sql->setQuery($qry);

	$REX['ADDON375']['postget']['error'][] = count($delete_item_ids) ." ".
			$REX['ADDON375']['I18N']->msg('archive_deleted');
}
// FINISH: delete from archiv if selected

  // get the group names
  $qry = "SELECT `gid`, `groupname`, `sentdate` FROM `".$REX['ADDON375']['archivetable']."` GROUP BY `gid` ORDER BY `sentdate` DESC";
  $sql->setQuery($qry);
  $REX['ADDON375']['groups'] = $sql->getArray();

  if(!empty($REX['ADDON375']['postget']['newsletter_showall']))
  {
    unset($REX['ADDON375']['postget']['itemsperpage'],
          $REX['ADDON375']['postget']['showclang'],
          $REX['ADDON375']['postget']['newsletter_page'],
          $REX['ADDON375']['postget']['showgroup']
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
    $REX['ADDON375']['orderby'] = "`sentdate` DESC";

  if(!isset($REX['ADDON375']['postget']['showclang']))
    $REX['ADDON375']['postget']['showclang'] = -1;
    
  $REX['ADDON375']['postget']['itemsperpage'] = (intval($REX['ADDON375']['postget']['itemsperpage'])>0 ? intval($REX['ADDON375']['postget']['itemsperpage']) : 50);
  $REX['ADDON375']['postget']['showclang'] = intval($REX['ADDON375']['postget']['showclang'])>-1 ? intval($REX['ADDON375']['postget']['showclang']) : -1;
  $REX['ADDON375']['postget']['newsletter_page'] = (intval($REX['ADDON375']['postget']['newsletter_page'])>0 ? intval($REX['ADDON375']['postget']['newsletter_page'])-1 : 0);
  $REX['ADDON375']['postget']['showgroup'] = intval($REX['ADDON375']['postget']['showgroup']);
  // build sql-query    
  if(intval($REX['ADDON375']['postget']['showgroup'])>0)
    $qry = "SELECT SQL_CALC_FOUND_ROWS * FROM `".$REX['ADDON375']['archivetable']."`
            WHERE `gid`=".$REX['ADDON375']['postget']['showgroup']."";
  else
    $qry = "SELECT SQL_CALC_FOUND_ROWS * FROM `".$REX['ADDON375']['archivetable']."`
            WHERE 1";
  
  if(!empty($REX['ADDON375']['postget']['query']))
    $qry.=" AND (`subject` LIKE '%".$REX['ADDON375']['postget']['query']."%' OR `groupname` LIKE '%".$REX['ADDON375']['postget']['query']."%')"; 

  if(!empty($REX['ADDON375']['postget']['query']))
    $qry.=" AND (`subject` LIKE '%".$REX['ADDON375']['postget']['query']."%' OR `groupname` LIKE '%".$REX['ADDON375']['postget']['query']."%')"; 

  if(intval($REX['ADDON375']['postget']['showclang'])>-1)
    $qry.=" AND `clang`='".strval(intval($REX['ADDON375']['postget']['showclang']))."'"; 

  $qry.= " ORDER BY ".$REX['ADDON375']['orderby'];
  $qry.= " LIMIT ".strval($REX['ADDON375']['postget']['newsletter_page']*$REX['ADDON375']['postget']['itemsperpage']).", ".strval($REX['ADDON375']['postget']['itemsperpage']);
    
  $sql->setQuery($qry);
  $REX['ADDON375']['items']=$sql->getArray();

  $sql->setQuery("SELECT FOUND_ROWS()");
  $REX['ADDON375']['numofitems'] = $sql->getArray();
  $REX['ADDON375']['numofitems'] = intval($REX['ADDON375']['numofitems'][0]['FOUND_ROWS()']);


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
      <input type="hidden" name="newsletter[showclang]" value="<?php print strval($REX['ADDON375']['postget']['showclang'])?>" />
      <input type="hidden" name="newsletter[orderby]" value="<?php print strval($REX['ADDON375']['postget']['orderby'])?>" />
      <input type="hidden" name="newsletter[newsletter_page]" value="<?php print strval($REX['ADDON375']['postget']['newsletter_page']+1)?>" />
      <input type="hidden" name="newsletter[group]" value="<?php print strval($REX['ADDON375']['postget']['group'])?>" />

      <table class="rex-table">
      <thead>
          <tr class="myrex_spacebelow">
            <td class="rex-icon">&nbsp;</td>
            <td colspan="5">
              <label><?php print $REX['ADDON375']['I18N']->msg('newsletter_search')?></label>
              <input type="text" name="query" value="<?php print htmlspecialchars(stripslashes($REX['ADDON375']['postget']['query']),ENT_QUOTES)?>" />
              <input type="submit" class="myrex_submit_delete" name="search" value="&#0187" title="<?php print $REX['ADDON375']['I18N']->msg('newsletter_search')?>" />
            </td>
            <td>
            </td>
          </tr>
          <tr>
            <th style="width:5%" class="rex-icon">&nbsp;</th>
            <th style="width:10%"><input class="myrex_submitlink" style="font-weight:bold;" type="submit" name="orderby[sentdate<?php print ($REX['ADDON375']['postget']['orderby']=='sentdate' ? 'DESC' : '')?>]" value="<?php print $REX['ADDON375']['I18N']->msg('archive_sentdate')?>" title="<?php print $REX['ADDON375']['I18N']->msg('orderby_sentdate')?>" /></th>
            <th style="width:30%"><input class="myrex_submitlink" style="font-weight:bold;" type="submit" name="orderby[subject<?php print ($REX['ADDON375']['postget']['orderby']=='subject' ? 'DESC' : '')?>]" value="<?php print $REX['ADDON375']['I18N']->msg('archive_subject')?>" title="<?php print $REX['ADDON375']['I18N']->msg('orderby_subject')?>" /></th>
            <th style="width:20%"><input class="myrex_submitlink" style="font-weight:bold;" type="submit" name="orderby[groupname<?php print ($REX['ADDON375']['postget']['orderby']=='groupname' ? 'DESC' : '')?>]" value="<?php print $REX['ADDON375']['I18N']->msg('archive_groupname')?>" title="<?php print $REX['ADDON375']['I18N']->msg('orderby_groupname')?>" /></th>
            <th style="width:5%"><input class="myrex_submitlink" style="font-weight:bold;" type="submit" name="orderby[clang<?php print ($REX['ADDON375']['postget']['orderby']=='clang' ? 'DESC' : '')?>]" value="<?php print $REX['ADDON375']['I18N']->msg('archive_language')?>" title="<?php print $REX['ADDON375']['I18N']->msg('orderby_clang')?>" /></th>
            <th style="width:10%"><input class="myrex_submitlink" style="font-weight:bold;" type="submit" name="orderby[sentby<?php print ($REX['ADDON375']['postget']['orderby']=='sentby' ? 'DESC' : '')?>]" value="<?php print $REX['ADDON375']['I18N']->msg('archive_sentby')?>" title="<?php print $REX['ADDON375']['I18N']->msg('orderby_sentby')?>" /></th>
            <th style="width:15%">&nbsp;</th>
            <th align="center" style="width:5%"><?php print $REX['ADDON375']['I18N']->msg('newsletter_delete')?></th>
          </tr>
        </thead>
        <tbody>
<?php
  if(!empty($REX['ADDON375']['items']))
  {
    foreach($REX['ADDON375']['items'] as $item)
    {
    
      if(!isset($REX['ADDON375']['postget']['numofitems']))
        $REX['ADDON375']['postget']['numofitems'] = $item['numofitems'];

      echo '
          <tr>
            <td class="rex-icon"><input type="checkbox" name="newsletter_select_item['.$item['id'].']" value="true" style="width:auto" onclick="myrex_selectallitems(\'newsletter_select_item\',this)" /></td>
            <td><strong>'.myrexvars_formatted_date($item['sentdate'],$format=1).'</strong><br />'.myrexvars_formatted_date($item['sentdate'],$format=2).'</td>
            <td>'.htmlspecialchars(stripslashes($item['subject']),ENT_QUOTES).'</td>
            <td>'.htmlspecialchars(stripslashes($item['groupname']),ENT_QUOTES).'</td>
            <td>'.$REX['CLANG'][$item['clang']].'</td>
            <td>'.htmlspecialchars(stripslashes($item['sentby']),ENT_QUOTES).'</td>
            <td>
              <a href="?page='.$REX['ADDON375']['addon_name'].'&amp;subpage=archiveout&amp;id='.$item['id'].'&amp;view=recipients" title="'.$REX['ADDON375']['I18N']->msg('archive_recipients').'">'.$REX['ADDON375']['I18N']->msg('archive_recipients_short').'</a>&nbsp;&nbsp;
              <a href="?page='.$REX['ADDON375']['addon_name'].'&amp;subpage=archiveout&amp;id='.$item['id'].'&amp;view=textbody" title="'.$REX['ADDON375']['I18N']->msg('archive_textbody').'">'.$REX['ADDON375']['I18N']->msg('archive_textbody_short').'</a>&nbsp;&nbsp;
              <a href="?page='.$REX['ADDON375']['addon_name'].'&amp;subpage=archiveout&amp;id='.$item['id'].'&amp;view=htmlbody" target="_blank" title="'.$REX['ADDON375']['I18N']->msg('archive_htmlbody').'">'.$REX['ADDON375']['I18N']->msg('archive_htmlbody_short').'</a>&nbsp;&nbsp;
            </td>
            <td align="center"><input type="submit" class="myrex_submit_delete" name="newsletter_item['.$item['id'].'][deleteme]" onclick="return myrex_confirm(\''.$REX['ADDON375']['I18N']->msg('confirm_deletethis').'\',this.form)" value="X" /></td>
          </tr>';
    }
?>
          <tr>
            <td valign="middle" class="rex-icon"><input class="myrex_checkbox" type="checkbox" name="newsletter_select_item_all" value="true" style="width:auto" onclick="myrex_selectallitems('newsletter_select_item',this)" /></td>
            <td valign="middle" colspan="6"><strong><?php print $REX['ADDON375']['I18N']->msg('edit_all_selected')?></strong></td>
            <td valign="middle" align="center"><input type="submit" class="myrex_submit_delete" name="newsletter_delete_items" onclick="return myrex_confirm('<?php print $REX['ADDON375']['I18N']->msg('confirm_deleteselected')?>',this.form)" title="<?php print $REX['ADDON375']['I18N']->msg('button_submit_delete')?>" value="X" /></td>
          </tr>

          <tr class="myrex_spacebelow">
            <td class="rex-icon">&nbsp;</td>
            <td colspan="7">
<?php
// check, if there are more items to show
    if($REX['ADDON375']['numofitems']>$REX['ADDON375']['postget']['itemsperpage'])
    {
      // show the pagination
      $temp = ceil($REX['ADDON375']['numofitems']/$REX['ADDON375']['postget']['itemsperpage']);
      for($i=0; $i<$temp; $i++)
      {
        if($i!=$REX['ADDON375']['postget']['newsletter_page'])
          echo '<input style="width:20px" type="submit" class="myrex_submit" name="newsletter_page" value="'.strval($i+1).'" />';
        else
          echo '<span class="hilight">'.strval($i+1).'</span> ';
        
        echo '&nbsp;&nbsp;';
      }
    }
?>
            </td>
          </tr>
<?php
  }
  else
  {
?>
          <tr>
            <td class="rex-icon">&nbsp;</td>
            <td colspan="7">
              <?php print $REX['ADDON375']['I18N']->msg('no_items_found')?>
            </td>
          </tr>
<?php
  }
?>
          <tr>
            <td class="rex-icon">&nbsp</td>
            <td colspan="5">
              <ul>
<?php
  $select = new rex_select;
  $select->setSize(1);

  $select->setName('itemsperpage');
  for($i=1; $i<11; $i++)
    $select->addOption(strval($i*10).' pro Seite',strval($i*10));
  $select->setSelected($REX['ADDON375']['postget']['itemsperpage']);
  echo '<li class="clearfix"><label>'.$REX['ADDON375']['I18N']->msg('filter_itemsperpage').'</label>'.$select->get().'</li>';

  if(!empty($REX['ADDON375']['groups']))
  {
    $groups = new rex_select;
    $groups->setSize(1);
    $groups->setAttribute('class','myrex_select');
#    $groups->setAttribute('style','width:100%');

    $groups->addOption($REX['ADDON375']['I18N']->msg('all_groups'),'-1');
    foreach($REX['ADDON375']['groups'] as $group)
      $groups->addOption($group['groupname'],$group['gid']);

    $groups->setSelected($REX['ADDON375']['postget']['showgroup']);
    $groups->setName('showgroup');
    echo '<li class="clearfix"><label>'.$REX['ADDON375']['I18N']->msg('filter_groups').'</label>'.$groups->get().'</li>';
  }               


  $select = new rex_select;
  $select->setSize(1);
  $select->setAttribute('class','myrex_select_small');

  if(count($REX['CLANG'])>1)
  {
    $select->setName('showclang');
    $select->addOption($REX['ADDON375']['I18N']->msg('clang_all'),-1);
    foreach($REX['CLANG'] as $key => $value)
      $select->addOption($value,$key);
    $select->setSelected($REX['ADDON375']['postget']['showclang']);

    echo '<li class="clearfix"><label>'.$REX['ADDON375']['I18N']->msg('filter_clang').'</label>'.$select->get().'</li>';
  }
?>
              </ul>
            </td>
            <td colspan="2">
              <input style="width:100%;" class="myrex_submit" type="submit" name="newsletter_submitsearch" id="newsletter_submitsearch" value="<?php print $REX['ADDON375']['I18N']->msg('button_submit_search')?>" />
              <input style="width:100%;" class="myrex_submitlink" type="submit" name="newsletter_showall" id="newsletter_showall" value="<?php print $REX['ADDON375']['I18N']->msg('button_submit_showall')?>" />
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
