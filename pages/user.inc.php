<?php
  $sql = new rex_sql;
  $REX['ADDON375']['postget']['status'] = 'show_list';
  $REX['ADDON375']['postget']['error'] = array();
  
  // get the group names
  $qry = "SELECT `name`,`id` FROM `".$REX['ADDON375']['grouptable']."` ORDER BY `name`";
  $sql->setQuery($qry);
  $REX['ADDON375']['groups'] = $sql->getArray();
  

  if(!empty($_POST))
  { 
    $queries = array();

    if(!empty($REX['ADDON375']['postget']['add_new_user']))
    {
      $REX['ADDON375']['postget']['newsletter']['user'] = array(
        'id' => 0,
        'email' => '',
        'grad' => '',
		'firstname' => '',
        'lastname' => '',
        'title' => '1',
        'status' => '1',
        'clang' => $REX['CUR_CLANG'],
        'article_id' => 0,
        'send_group' => 0,
        'createdate' => time(),
        'createip' => $_SERVER['REMOTE_ADDR'],
		'subscriptiontype' => 'backend',
        'groups' => array()
      );
    
      $REX['ADDON375']['postget']['status'] = 'edit_user';
    }
    else
    {
      require_once($REX['INCLUDE_PATH'].'/addons/'.$REX['ADDON375']['addon_name'].'/functions/newsletter.inc.php');

      // if several user have to be edited
      $delete = false;
      if(is_array($REX['ADDON375']['postget']['newsletter_item']))
      {
        foreach($REX['ADDON375']['postget']['newsletter_item'] as $id => $item)
        {
          if(!empty($REX['ADDON375']['postget']['newsletter_item'][$id]['deleteme']))
          { 
            $queries[] = "DELETE FROM `".$REX['ADDON375']['usertable']."` WHERE `id` = '".$id."'";
            $queries[] = "DELETE FROM `".$REX['ADDON375']['u2gtable']."` WHERE `uid` = '".$id."'";
            $delete=true;
          }
        }
      }
      
      if(!empty($REX['ADDON375']['postget']['newsletter_select_item']) && !$delete)
      {
        foreach($REX['ADDON375']['postget']['newsletter_select_item'] as $id => $item)
        { 
          if(!empty($REX['ADDON375']['postget']['newsletter_item'][$id]))
          {
            if(!empty($REX['ADDON375']['postget']['newsletter_edit'][$id]))
            { 
              // if a particular user is edited, check the given parameters
              
              if(!myrex_validEmail($REX['ADDON375']['postget']['newsletter_item'][$id]['email']))
                $REX['ADDON375']['postget']['error'][] = $REX['ADDON375']['I18N']->msg('error_invalidemail',$REX['ADDON375']['postget']['newsletter_item'][$id]['email']);
              else
              {
                $qry = "SELECT `id`,`firstname`,`lastname` FROM `".$REX['ADDON375']['usertable']."`
                        WHERE `email`='".$REX['ADDON375']['postget']['newsletter_item'][$id]['email']."'
                        AND `id`!='".$id."'";
                $sql->setQuery($qry);
                if($sql->getRows()>0)
                  $REX['ADDON375']['postget']['error'][] = $REX['ADDON375']['I18N']->msg('error_emailused',$sql->getValue('firstname').' '.$sql->getValue('lastname'));
              }
    
              if(trim($REX['ADDON375']['postget']['newsletter_item'][$id]['firstname'])=='')
                $REX['ADDON375']['postget']['error'][] = $REX['ADDON375']['I18N']->msg('error_nofirstname');
        
              if(trim($REX['ADDON375']['postget']['newsletter_item'][$id]['lastname'])=='')
                $REX['ADDON375']['postget']['error'][] = $REX['ADDON375']['I18N']->msg('error_nolastname');
                
              if(empty($REX['ADDON375']['postget']['error']) && intval($id)>0)
              {
                // UPDATE GROUP2USER-TABLE
                $queries[] = "DELETE FROM `".$REX['ADDON375']['u2gtable']."` WHERE `uid`='".$id."'";

                if(!empty($REX['ADDON375']['postget']['newsletter_item'][$id]['groups']))
                {
                  foreach($REX['ADDON375']['postget']['newsletter_item'][$id]['groups'] as $group)
                  { 
                    $queries[] = "REPLACE INTO `".$REX['ADDON375']['u2gtable']."` SET `uid`='".$id."', `gid`='".$group."'";
                  }
                }
              }

              if(!empty($REX['ADDON375']['postget']['error']))
                $REX['ADDON375']['postget']['status'] = 'edit_user';
              $REX['ADDON375']['postget']['newsletter']['user'] = $REX['ADDON375']['postget']['newsletter_item'][$id];
              
              $qry = array();
              foreach($REX['ADDON375']['postget']['newsletter_item'][$id] as $key => $value)
              {
                if($key=='updatedate')
					$qry[$key] = "`".$key."` = '".time()."'";
                else if($key=='updateip')
					$qry[$key] = "`".$key."` = '".$_SERVER['REMOTE_ADDR']."'";
                else if($key!='groups')
					$qry[$key] = "`".$key."` = '".$value."'";
              }
			
			  if($REX['ADDON375']['postget']['newsletter']['user']['id'] > 0) {
				// edit existing entry
              	$queries[] = "REPLACE INTO `".$REX['ADDON375']['usertable']."` SET ".join(", ",$qry);
			  }
			  else {
				// insert new entry and insert groups
              	$qry = array();
             	foreach($REX['ADDON375']['postget']['newsletter_item'][$id] as $key => $value) {
                	if($key=='updatedate')
                  		$qry[$key] = "`".$key."` = '".time()."'";
                	else if($key != 'groups' && $key != 'id')
                  		$qry[$key] = "`".$key."` = '".$value."'";
              	}

				$query = "INSERT INTO `".$REX['ADDON375']['usertable']."` SET ".join(", ",$qry);
				$sql->setQuery($query);
				$REX['ADDON375']['postget']['newsletter']['user']['id'] = $sql->getLastId();
                if(!empty($REX['ADDON375']['postget']['newsletter_item'][$id]['groups']))
                {
                  foreach($REX['ADDON375']['postget']['newsletter_item'][$id]['groups'] as $group)
                  { 
                    $queries[] = "REPLACE INTO `".$REX['ADDON375']['u2gtable']."` SET `uid`='".
						$REX['ADDON375']['postget']['newsletter']['user']['id']."', `gid`='". $group ."'";
                  }
                }
			  }
              
            }
            elseif(!empty($REX['ADDON375']['postget']['newsletter_delete_items']))
            { 
              $queries[] = "DELETE FROM `".$REX['ADDON375']['usertable']."` WHERE `id` = '".$id."'";
              $queries[] = "DELETE FROM `".$REX['ADDON375']['u2gtable']."` WHERE `uid` = '".$id."'";
            }
            else
            {
              // otherwise check the general settings of the user list
              
              // general setting for the status?
              if(isset($REX['ADDON375']['postget']['newsletter_item_status_all'])
                 && intval($REX['ADDON375']['postget']['newsletter_item_status_all'])>-1)
              {
                $REX['ADDON375']['postget']['newsletter_item'][$id]['status'] = $REX['ADDON375']['postget']['newsletter_item_status_all'];
              }
              else
                $REX['ADDON375']['postget']['newsletter_item'][$id]['status'] = $REX['ADDON375']['postget']['newsletter_item_status'][$id]['status'];
                
              // general setting for the languages?
              if(isset($REX['ADDON375']['postget']['newsletter_item_clang_all'])
                 && intval($REX['ADDON375']['postget']['newsletter_item_clang_all'])>-1)
              {
                $REX['ADDON375']['postget']['newsletter_item'][$id]['clang'] = $REX['ADDON375']['postget']['newsletter_item_clang_all'];
              }

              if(!empty($REX['ADDON375']['postget']['addtogroup']))
              {
                if($REX['ADDON375']['postget']['addtogroup']=='all')
                {
                  foreach($REX['ADDON375']['groups'] as $group)
                    $queries[] = "REPLACE INTO `".$REX['ADDON375']['u2gtable']."` SET `gid`='".$group['id']."', `uid`='".$id."'";
                }
                else if($REX['ADDON375']['postget']['addtogroup']=='none')
                {
                  $queries[] = "DELETE FROM `".$REX['ADDON375']['u2gtable']."` WHERE `uid`='".$id."'";
                }
                else if(intval($REX['ADDON375']['postget']['addtogroup'])>0)
                {
                  $queries[] = "REPLACE INTO `".$REX['ADDON375']['u2gtable']."` SET `gid`='".intval($REX['ADDON375']['postget']['addtogroup'])."', `uid`='".$id."'";
                }
              }
              
              $qry = array();
              foreach($REX['ADDON375']['postget']['newsletter_item'][$id] as $key => $value)
              {
                if($key=='updatedate')
                  $qry[$key] = "`".$key."` = '".time()."'";
                else if($key!='groups')
                  $qry[$key] = "`".$key."` = '".$value."'";
              }
              $queries[] = "REPLACE INTO `".$REX['ADDON375']['usertable']."` SET ".join(", ",$qry);
            }
          }
        }
      }
      
      if(!empty($queries) && empty($REX['ADDON375']['postget']['error']))
      {
//        rex_a375_resetRecipients(); // reset any setups for newsletter
        foreach($queries as $qry)
        {
#            print_r($qry);
          $sql->setQuery($qry);
        }
        $REX['ADDON375']['postget']['error'][] = $REX['ADDON375']['I18N']->msg('changes_saved');
      }
    }

	// check if a user is edited or whether its a new one
	$keys = array();
	if(is_array($REX['ADDON375']['postget']['newsletter_edit'])) {
		$keys = array_keys($REX['ADDON375']['postget']['newsletter_edit']);
	}

    // check the user that is edited, omit new users
    if(!empty($REX['ADDON375']['postget']['newsletter_edit']) && (!empty($keys) && $keys[0] > 0))
    {
      $REX['ADDON375']['postget']['newsletter']['user'] = -1;
      foreach($REX['ADDON375']['postget']['newsletter_edit'] as $key=>$value)
      {
        $REX['ADDON375']['postget']['newsletter']['user'] = intval($key);
        break;
      }

      if($REX['ADDON375']['postget']['newsletter']['user']>0)
      {
        $qry = "SELECT * FROM `".$REX['ADDON375']['usertable']."` 
                WHERE `id` = '".$REX['ADDON375']['postget']['newsletter']['user']."'
                LIMIT 1";
        $sql->setQuery($qry);
        $REX['ADDON375']['postget']['newsletter']['user'] = $sql->getArray();

        if(!empty($REX['ADDON375']['postget']['newsletter']['user']))
        {
          $REX['ADDON375']['postget']['newsletter']['user'] = $REX['ADDON375']['postget']['newsletter']['user'][0];
          $qry = "SELECT gid FROM ".$REX['ADDON375']['u2gtable']."
                  WHERE uid='".$REX['ADDON375']['postget']['newsletter']['user']['id']."'
                  GROUP BY gid";
          $sql->setQuery($qry);

          $REX['ADDON375']['postget']['newsletter']['user']['groups'] = array();
          foreach($sql->getArray() as $group)
            $REX['ADDON375']['postget']['newsletter']['user']['groups'][] = intval($group['gid']);

          $REX['ADDON375']['postget']['status'] = 'edit_user';
        }
        else
        {
          $REX['ADDON375']['postget']['error'][] = $REX['ADDON375']['I18N']->msg('error_usernotfound');
          $REX['ADDON375']['newsletter']['status'] = 'show_list';          
        }
      }
    }
  }
  
  if(!empty($REX['ADDON375']['postget']['newsletter_showall']))
  {
    unset($REX['ADDON375']['postget']['itemsperpage'],
          $REX['ADDON375']['postget']['showstatus'],
          $REX['ADDON375']['postget']['showclang'],
          $REX['ADDON375']['postget']['newsletter_page'],
          $REX['ADDON375']['postget']['createip'],
          $REX['ADDON375']['postget']['showgroup'],
          $REX['ADDON375']['postget']['query']
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
    $REX['ADDON375']['orderby'] = "`lastname`";

	if(!isset($REX['ADDON375']['postget']['showclang']) && !isset($REX['ADDON375']['postget']['newsletter_submitsearch'])) {
		$REX['ADDON375']['postget']['showclang'] = -1;
	}
	else if(!isset($REX['ADDON375']['postget']['showclang']) && isset($REX['ADDON375']['postget']['newsletter_submitsearch']) ) {
		// In case clang is 0
		$REX['ADDON375']['postget']['showclang'] = 0;
	}
    
  $REX['ADDON375']['postget']['itemsperpage'] = (intval($REX['ADDON375']['postget']['itemsperpage'])>0 ? intval($REX['ADDON375']['postget']['itemsperpage']) : 50);
  $REX['ADDON375']['postget']['showstatus'] = (intval($REX['ADDON375']['postget']['showstatus'])<-1 || intval($REX['ADDON375']['postget']['showstatus'])>2 || !isset($REX['ADDON375']['postget']['showstatus']) ? -1 : intval($REX['ADDON375']['postget']['showstatus']));
  $REX['ADDON375']['postget']['showclang'] = intval($REX['ADDON375']['postget']['showclang'])>-1 ? intval($REX['ADDON375']['postget']['showclang']) : -1;
  $REX['ADDON375']['postget']['newsletter_page'] = (intval($REX['ADDON375']['postget']['newsletter_page'])>0 ? intval($REX['ADDON375']['postget']['newsletter_page'])-1 : 0);
  $REX['ADDON375']['postget']['createip'] = trim($REX['ADDON375']['postget']['createip']);
  $REX['ADDON375']['postget']['showgroup'] = intval($REX['ADDON375']['postget']['showgroup']);
  
  if($REX['ADDON375']['postget']['status'] == 'show_list')
  {
    // build sql-query    
    if($REX['ADDON375']['postget']['showgroup']>0)
      $qry = "SELECT SQL_CALC_FOUND_ROWS * FROM `".$REX['ADDON375']['usertable']."`, `".$REX['ADDON375']['u2gtable']."`
              WHERE `gid`=".$REX['ADDON375']['postget']['showgroup']." AND `uid`=`id`";
    else
      $qry = "SELECT SQL_CALC_FOUND_ROWS * FROM `".$REX['ADDON375']['usertable']."`
              WHERE 1";
  
    $qry.= (intval($REX['ADDON375']['postget']['showstatus'])>-1 ? " AND `status`='".$REX['ADDON375']['postget']['showstatus']."'" : "");
    $qry.= ($REX['ADDON375']['postget']['createip']!='' ? " AND `createip`='".$REX['ADDON375']['postget']['createip']."'" : "");
    
    if(!empty($REX['ADDON375']['postget']['query']))
      $qry.=" AND (`email` LIKE '%".$REX['ADDON375']['postget']['query']."%' OR `firstname` LIKE '%".$REX['ADDON375']['postget']['query']."%' OR `lastname` LIKE '%".$REX['ADDON375']['postget']['query']."%')"; 

    if(intval($REX['ADDON375']['postget']['showclang'])>-1)
      $qry.=" AND `clang`='".strval(intval($REX['ADDON375']['postget']['showclang']))."'"; 

    $qry.= " ORDER BY ".$REX['ADDON375']['orderby'];
    
    if(empty($REX['ADDON375']['postget']['newsletter_exportusers']))
      $qry.= " LIMIT ".strval($REX['ADDON375']['postget']['newsletter_page']*$REX['ADDON375']['postget']['itemsperpage']).", ".strval($REX['ADDON375']['postget']['itemsperpage']);
      
    $sql->setQuery($qry);
    $REX['ADDON375']['items']=$sql->getArray();
  
    $sql->setQuery("SELECT FOUND_ROWS()");
    $REX['ADDON375']['numofitems'] = $sql->getArray();
    $REX['ADDON375']['numofitems'] = intval($REX['ADDON375']['numofitems'][0]['FOUND_ROWS()']);
  }
  
  if(!empty($_POST) && !empty($REX['ADDON375']['postget']['newsletter_exportusers']))
  {
    $userdata = rex_a375_export_userlist($REX['ADDON375']['items']);

    header("Cache-Control: public");
    header("Content-Description: File Transfer");
    header('Content-disposition: attachment; filename=multinewsletter_user.csv');
    header("Content-Type: application/csv");
    header("Content-Transfer-Encoding: binary");
    header('Content-Length: '.strlen($userdata));
    print($userdata); 
    exit;
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
      <input type="hidden" name="newsletter[showclang]" value="<?php print strval($REX['ADDON375']['postget']['showclang'])?>" />
      <input type="hidden" name="newsletter[orderby]" value="<?php print strval($REX['ADDON375']['postget']['orderby'])?>" />
      <input type="hidden" name="newsletter[newsletter_page]" value="<?php print strval($REX['ADDON375']['postget']['newsletter_page']+1)?>" />
      <input type="hidden" name="newsletter[group]" value="<?php print strval($REX['ADDON375']['postget']['group'])?>" />

      <table class="rex-table">
<?php

  if($REX['ADDON375']['postget']['status'] == 'show_list') {
?>
        <thead>
          <tr class="myrex_spacebelow">
            <td class="rex-icon">&nbsp;</td>
            <td colspan="2">
              <label><?php print $REX['ADDON375']['I18N']->msg('newsletter_search')?></label>
              <input type="text" name="query" value="<?php print htmlspecialchars(stripslashes($REX['ADDON375']['postget']['query']),ENT_QUOTES)?>" style="width: 150px" />
              <input type="submit" class="myrex_submit_delete" name="search" value="&#0187" title="<?php print $REX['ADDON375']['I18N']->msg('newsletter_search')?>" />
            </td>
            <td colspan="5">
              <input class="myrex_submitlink" style="font-weight:bold;" type="submit" name="add_new_user" value="<?php print $REX['ADDON375']['I18N']->msg('newsletter_newuser')?>" />
            </td>
            <td>
            </td>
          </tr>
          <tr>
            <th style="width:5%" class="rex-icon">&nbsp;</th>
            <th style="width:20%"><input class="myrex_submitlink" style="font-weight:bold;" type="submit" name="orderby[email<?php print ($REX['ADDON375']['postget']['orderby']=='email' ? 'DESC' : '')?>]" value="<?php print $REX['ADDON375']['I18N']->msg('newsletter_email')?>" title="<?php print $REX['ADDON375']['I18N']->msg('orderby_email')?>" /></th>
            <th style="width:20%"><input class="myrex_submitlink" style="font-weight:bold;" type="submit" name="orderby[firstname<?php print ($REX['ADDON375']['postget']['orderby']=='firstname' ? 'DESC' : '')?>]" value="<?php print $REX['ADDON375']['I18N']->msg('newsletter_firstname')?>" title="<?php print $REX['ADDON375']['I18N']->msg('orderby_firstname')?>" /></th>
            <th style="width:20%"><input class="myrex_submitlink" style="font-weight:bold;" type="submit" name="orderby[lastname<?php print ($REX['ADDON375']['postget']['orderby']=='lastname' ? 'DESC' : '')?>]" value="<?php print $REX['ADDON375']['I18N']->msg('newsletter_lastname')?>" title="<?php print $REX['ADDON375']['I18N']->msg('orderby_lastname')?>" /></th>
            <th style="width:5%"><input class="myrex_submitlink" style="font-weight:bold;" type="submit" name="orderby[clang<?php print ($REX['ADDON375']['postget']['orderby']=='clang' ? 'DESC' : '')?>]" value="<?php print $REX['ADDON375']['I18N']->msg('newsletter_language')?>" title="<?php print $REX['ADDON375']['I18N']->msg('orderby_clang')?>" /></th>
            <th style="width:5%"><input class="myrex_submitlink" style="font-weight:bold;" type="submit" name="orderby[clang<?php print ($REX['ADDON375']['postget']['orderby']=='createdate' ? 'DESC' : '')?>]" value="<?php print $REX['ADDON375']['I18N']->msg('newsletter_createdate')?>" title="<?php print $REX['ADDON375']['I18N']->msg('orderby_createdate')?>" /></th>
            <th style="width:5%"><input class="myrex_submitlink" style="font-weight:bold;" type="submit" name="orderby[clang<?php print ($REX['ADDON375']['postget']['orderby']=='updatedate' ? 'DESC' : '')?>]" value="<?php print $REX['ADDON375']['I18N']->msg('newsletter_updatedate')?>" title="<?php print $REX['ADDON375']['I18N']->msg('orderby_updatedate')?>" /></th>
            <th style="width:5%"><input class="myrex_submitlink" style="font-weight:bold;" type="submit" name="orderby[status<?php print ($REX['ADDON375']['postget']['orderby']=='status' ? 'DESC' : '')?>]" value="<?php print $REX['ADDON375']['I18N']->msg('newsletter_status')?>" title="<?php print $REX['ADDON375']['I18N']->msg('orderby_status')?>" /></th>
            <th align="center" style="width:5%"><?php print $REX['ADDON375']['I18N']->msg('newsletter_delete')?></th>
          </tr>
        </thead>
        <tbody style="font-size: 0.85em;">
<?php
  if(!empty($REX['ADDON375']['items']))
  {
    
    $status = new rex_select;
    $status->setSize(1);
    $status->setAttribute('style','width: 50px');
    $status->addOption($REX['ADDON375']['I18N']->msg('status_online'),'1');
    $status->addOption($REX['ADDON375']['I18N']->msg('status_offline'),'0');
    
    foreach($REX['ADDON375']['items'] as $item)
    {
    
      if(!isset($REX['ADDON375']['postget']['numofitems']))
        $REX['ADDON375']['postget']['numofitems'] = $item['numofitems'];

      $status->resetSelected();
      $status->setName('newsletter_item_status['.$item['id'].'][status]');
      $status->setSelected($item['status']);
      $status->setAttribute("onchange","this.form['newsletter_select_item[".$item['id']."]'].checked=true");
  #    print_r($comment);
      echo '
          <input type="hidden" name="newsletter_item['.$item['id'].'][id]" value="'.$item['id'].'" />
          <input type="hidden" name="newsletter_item['.$item['id'].'][email]" value="'.$item['email'].'" />
          <input type="hidden" name="newsletter_item['.$item['id'].'][firstname]" value="'.$item['firstname'].'" />
          <input type="hidden" name="newsletter_item['.$item['id'].'][lastname]" value="'.$item['lastname'].'" />
          <input type="hidden" name="newsletter_item['.$item['id'].'][title]" value="'.$item['title'].'" />
          <input type="hidden" name="newsletter_item['.$item['id'].'][clang]" value="'.$item['clang'].'" />
          <input type="hidden" name="newsletter_item['.$item['id'].'][article_id]" value="'.$item['article_id'].'" />
          <input type="hidden" name="newsletter_item['.$item['id'].'][send_group]" value="'.$item['send_group'].'" />
          <input type="hidden" name="newsletter_item['.$item['id'].'][createdate]" value="'.$item['createdate'].'" />
          <input type="hidden" name="newsletter_item['.$item['id'].'][createip]" value="'.$item['createip'].'" />
          <input type="hidden" name="newsletter_item['.$item['id'].'][updatedate]" value="'.$item['updatedate'].'" />

          <tr class="myrex_'.($item['status']==1 ? 'normal' : 'orange').'"">
            <td class="rex-icon"><input type="checkbox" name="newsletter_select_item['.$item['id'].']" value="true" style="width:auto" onclick="myrex_selectallitems(\'newsletter_select_item\',this)" /></td>
            <td><input class="myrex_submitlink" type="submit" name="newsletter_edit['.$item['id'].']" value="'.htmlspecialchars($item['email']).'" /></td>
            <td>'.htmlspecialchars($item['firstname']).'</td>
            <td>'.htmlspecialchars($item['lastname']).'</td>
            <td>'.htmlspecialchars($REX['CLANG'][$item['clang']]).'</td>';
	  if($item['createdate'] > 0)
		echo '<td>'.date('d.m.Y H:i:s', $item['createdate']).'</td>';
	  else
		echo '<td>&nbsp;</td>';
	  if($item['updatedate'] > 0)
		echo'<td>'.date('d.m.Y H:i:s', $item['updatedate']).'</td>';
	  else
		echo '<td>&nbsp;</td>';

	  echo '<td>'.$status->get().'</td>
            <td align="center"><input type="submit" class="myrex_submit_delete" name="newsletter_item['.$item['id'].'][deleteme]" onclick="return myrex_confirm(\''.$REX['ADDON375']['I18N']->msg('confirm_deletethis').'\',this.form)" value="X" /></td>
          </tr>';
    }
  
    $status->setName('newsletter_item_status_all');
    $status->setAttribute("onchange","if(this.value>-1) myrex_deselectStatus(this.form,'newsletter_item_status',true); else myrex_deselectStatus(this.form,'newsletter_item_status',false)");
    $status->addOption($REX['ADDON375']['I18N']->msg('get_each_status'),'-1');
    $status->resetSelected();
    $status->setSelected('-1');
    
?>
          <tr>
            <td valign="middle" class="rex-icon"><input class="myrex_checkbox" type="checkbox" name="newsletter_select_item_all" value="true" style="width:auto" onclick="myrex_selectallitems('newsletter_select_item',this)" /></td>
            <td valign="middle"><strong><?php print $REX['ADDON375']['I18N']->msg('edit_all_selected')?></strong></td>
            <td colspan="2">
<?php
  if(!empty($REX['ADDON375']['groups']))
  {
    $groups = new rex_select;
    $groups->setSize(1);
    $groups->setAttribute('class','myrex_select');
    $groups->setAttribute('style','width:100%');

    $groups->addOption($REX['ADDON375']['I18N']->msg('button_addtogroup'),'empty');
    $groups->addOption('----------------------------','empty');
    $groups->addOption($REX['ADDON375']['I18N']->msg('add_to_all_groups'),'all');
    $groups->addOption($REX['ADDON375']['I18N']->msg('remove_from_all_groups'),'none');
    $groups->addOption('----------------------------','empty');
    foreach($REX['ADDON375']['groups'] as $group)
      $groups->addOption($REX['ADDON375']['I18N']->msg('add_to_group',$group['name']),$group['id']);

    $groups->setName('addtogroup');
    $groups->show();
  }               
?>
            </td>            
            <td valign="middle">
<?php

    $select = new rex_select;
    $select->setSize(1);
    $select->setAttribute('style','width: 50px');
    $select->setName('newsletter_item_clang_all');
  
    $select->addOption($REX['ADDON375']['I18N']->msg('get_each_clang'),'-1');
    foreach($REX['CLANG'] as $key=>$value)
      $select->addOption($value,$key);

    $select->resetSelected();
    $select->setSelected('-1');

    $select->show();
               
?>
            
            </td>
            <td valign="middle"></td>
            <td valign="middle"></td>
            <td valign="middle"><?php print $status->get()?></td>
            <td valign="middle" align="center"><input type="submit" class="myrex_submit_delete" name="newsletter_delete_items" onclick="return myrex_confirm('<?php print $REX['ADDON375']['I18N']->msg('confirm_deleteselected')?>',this.form)" title="<?php print $REX['ADDON375']['I18N']->msg('button_submit_delete')?>" value="X" /></td>
          </tr>
		</tbody>
		<tfoot>
          <tr class="myrex_spacebelow">
            <td class="rex-icon">&nbsp;</td>
            <td colspan="8">
              <input type="submit" style="width:100%" class="myrex_submit" name="newsletter_save_all_items" onclick="return myrex_confirm('<?php print $REX['ADDON375']['I18N']->msg('confirm_save_all_items')?>',this.form)" value="<?php print $REX['ADDON375']['I18N']->msg('button_save_all_items')?>" />
            </td>
          </tr>
            

          <tr class="myrex_spacebelow">
            <td class="rex-icon">&nbsp;</td>
            <td colspan="5">
<?php
// check, if there are more items to show
    if($REX['ADDON375']['numofitems']>$REX['ADDON375']['postget']['itemsperpage']) {
      // show the pagination
      $temp = ceil($REX['ADDON375']['numofitems']/$REX['ADDON375']['postget']['itemsperpage']);
      for($i=0; $i<$temp; $i++)
      {
        if($i!=$REX['ADDON375']['postget']['newsletter_page'])
          echo '<input style="width:30px; margin-right: 5px;" type="submit" class="myrex_submit" name="newsletter_page" value="'.strval($i+1).'" />';
        else
          echo '<input type="submit" class="myrex_submit" name="newsletter_page" value="'.strval($i+1).'" 
				style="width:30px; border: 1px solid red; background-color: #FAA; margin-right: 5px;" class="myrex_submit" onClick="return false;"/>';
      }
    }
?>
            </td>
            <td colspan="4">
              <input style="width:100%;" class="myrex_submit" type="submit" name="newsletter_exportusers" id="newsletter_exportusers" value="<?php print $REX['ADDON375']['I18N']->msg('button_submit_exportusers')?>" />
            </td>
          </tr>
<?php
  }
  else
  {
?>
          <tr>
            <td class="rex-icon">&nbsp;</td>
            <td colspan="9">
              <?php print $REX['ADDON375']['I18N']->msg('no_items_found')?>
            </td>
          </tr>
<?php
  }
?>
          <tr>
            <td class="rex-icon">&nbsp;</td>
            <td colspan="3">
              <ul style="list-style-type: none; line-height: 25px;">
<?php
  $select = new rex_select;
  $select->setSize(1);
  $select->setAttribute('class','newsletter_select_small');

  $select->setName('itemsperpage');
  for($i=1; $i<11; $i++)
    $select->addOption(strval($i*25).' pro Seite',strval($i*25));
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
      $groups->addOption($group['name'],$group['id']);

    $groups->setSelected($REX['ADDON375']['postget']['showgroup']);
    $groups->setName('showgroup');
    echo '<li class="clearfix"><label>'.$REX['ADDON375']['I18N']->msg('filter_groups').'</label>'.$groups->get().'</li>';
  }               

  $select->setName('showstatus');
  $select->addOption($REX['ADDON375']['I18N']->msg('status_online'),'1');
  $select->addOption($REX['ADDON375']['I18N']->msg('status_offline'),'0');
  $select->addOption($REX['ADDON375']['I18N']->msg('status_all'),'-1');
  $select->setSelected($REX['ADDON375']['postget']['showstatus']);
  echo '<li class="clearfix"><label>'.$REX['ADDON375']['I18N']->msg('filter_status').'</label>'.$select->get().'</li>';

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
            <td colspan="4">
              <input style="width:100%;" class="myrex_submit" type="submit" name="newsletter_submitsearch" id="newsletter_submitsearch" value="<?php print $REX['ADDON375']['I18N']->msg('button_submit_search')?>" />
			  <br />
              <input style="width:100%;" class="myrex_submit" type="submit" name="newsletter_showall" id="newsletter_showall" value="<?php print $REX['ADDON375']['I18N']->msg('button_submit_showall')?>" />
            </td>
          </tr>
        </tbody>
<?php
  } // ENDIF STATUS == 'SHOW_LIST'
  else
  { // SHOW THE SINGLE EDIT-FIELDS
?>
        <thead>
          <tr>
            <th class="rex-icon">&nbsp;</th>
            <th class="myrex_middle"><?php print $REX['ADDON375']['I18N']->msg('newsletter_userdata')?></th>
            <th class="myrex_right"><?php print $REX['ADDON375']['I18N']->msg('newsletter_group')?></th>
          </tr>
        </thead>
        <tbody>
          <tr class="myrex_spacebelow">
            <td class="rex-icon">&nbsp;</td>
            <td class="myrex_middle">
              <ul>
<?php
  echo '
                <input type="hidden" name="newsletter_select_item['.$REX['ADDON375']['postget']['newsletter']['user']['id'].']" value="true" />
                <input type="hidden" name="newsletter_edit['.$REX['ADDON375']['postget']['newsletter']['user']['id'].']" value="true" />';
  $counter = 1;

  foreach($REX['ADDON375']['postget']['newsletter']['user'] as $key=>$value)
  {
    $temp_name = 'newsletter_item['.$REX['ADDON375']['postget']['newsletter']['user']['id'].']['.$key.']';
    
    if($key=='id' || $key=='article_id' || $key=='send_group')
    {
      echo '    <input id="myrex_'.strval($counter).'" type="hidden" name="'.$temp_name.'" value="'.htmlspecialchars(stripslashes($value),ENT_QUOTES).'" />';
    }
    elseif($key!='groups')
    {
      if($key=='status')
      {
        $status = new rex_select;
        $status->setSize(1);
        $status->setAttribute('class','myrex_select_small');
        $status->addOption($REX['ADDON375']['I18N']->msg('status_online'),'1');
        $status->addOption($REX['ADDON375']['I18N']->msg('status_offline'),'0');
        $status->setName($temp_name);
        $status->setSelected($value);
        $temp_out = $status->get();
      }
      elseif($key=='clang')
      {
        $status = new rex_select;
        $status->setSize(1);
        $status->setAttribute('class','myrex_select_small');
        foreach($REX['CLANG'] as $id=>$name)
          $status->addOption($name,$id);
          
        $status->setName($temp_name);
        $status->resetSelected();
        $status->setSelected($value);
        $temp_out = $status->get();
      }
      elseif($key=='title')
      {
        $status = new rex_select;
        $status->setSize(1);
        $status->setAttribute('class','myrex_select_small');
        $status->addOption($REX['ADDON375']['I18N']->msg('newsletter_title0'),'0');
        $status->addOption($REX['ADDON375']['I18N']->msg('newsletter_title1'),'1');
        $status->setName($temp_name);
        $status->resetSelected();
        $status->setSelected($value);
        $temp_out = $status->get();
      }
      elseif($key == 'createip' || $key == 'activationdate' || $key == 'activationip' || $key == 'updateip' || $key == 'subscriptiontype' || $key=='createdate' || $key=='updatedate') {
		// Folgende Felder werden ausgegraut, da sie nicht editiert werden duerfen
		$formatted_value = '';
			if(($key == 'activationdate' || $key=='createdate' || $key=='updatedate') && $value > 0) {
				// Datum formatieren, wenn vorhanden, wenn nicht wird es unten normal ausgegeben
				$formatted_value = date('d.m.Y H:i:s', $value);
			}
			else if ($value != '0') {
				// Normal ausgeben
				$formatted_value = htmlspecialchars(stripslashes($value), ENT_QUOTES);
			}
	        $temp_out = '<input id="myrex_'.strval($counter).'" type="text" name="'.
					$temp_name.'" value="'. $formatted_value .'" disabled />';
		// Da ausgegraute Felder bei der Uebertragung nicht ausgewertet werden, hier nochmals als hidden input
      	echo '    <input id="myrex_'.strval($counter).'" type="hidden" name="'.$temp_name.'" value="'.htmlspecialchars(stripslashes($value),ENT_QUOTES).'" />';

	  }
      else {
        $temp_out = '<input id="myrex_'.strval($counter).'" type="text" name="'.$temp_name.'" value="'.htmlspecialchars(stripslashes($value),ENT_QUOTES).'" />';
	  }

      echo '
                <li class="clearfix">
                  <label for="myrex_'.strval($counter).'">'.$REX['ADDON375']['I18N']->msg('newsletter_'.$key).'</label>
                  '.$temp_out.'
                </li>';
    }
    $counter++;
  }
?>
              </ul>
            </td>
            <td class="myrex_right" rowspan="2">
<?php
  if(!empty($REX['ADDON375']['groups']))
  {
    $groups = new rex_select;
    $groups->setSize(10);
    $groups->setAttribute('class','myrex_select_high');
    $groups->setAttribute('multiple','multiple');
    foreach($REX['ADDON375']['groups'] as $group)
      $groups->addOption($group['name'],$group['id']);

    $groups->setName('newsletter_item['.$REX['ADDON375']['postget']['newsletter']['user']['id'].'][groups][]');
    $groups->resetSelected();
    $groups->setSelected($REX['ADDON375']['postget']['newsletter']['user']['groups']);
    $groups->show();
    
    echo '
      <a href="javascript:void(0)" onclick="myrex_selectalloptions(\'newsletter_item['.$REX['ADDON375']['postget']['newsletter']['user']['id'].'][groups][]\',this,1)">'.$REX['ADDON375']['I18N']->msg('select_all').'</a>&nbsp;&nbsp;&nbsp;
      <a href="javascript:void(0)" onclick="myrex_selectalloptions(\'newsletter_item['.$REX['ADDON375']['postget']['newsletter']['user']['id'].'][groups][]\',this,0)">'.$REX['ADDON375']['I18N']->msg('select_none').'</a>
    ';
  } 
?>              
            </td>
          </tr>
          <tr class="myrex_spacebelow">
            <td class="rex-icon">&nbsp;</td>
            <td class="myrex_middle">
              <input style="width:100%;" class="myrex_submit" type="submit" name="newsletter_submit" id="newsletter_submit" value="<?php print $REX['ADDON375']['I18N']->msg('button_submit')?>" />
            </td>
          </tr>
        </tbody>
<?php
  } // ENDIF
?>
      </table>
    </form>
  <!-- END: ITEMS COMMENTS //-->
<?php
/* ############################## REDAXO FOOTER ############################### */
  include $REX['INCLUDE_PATH'].'/layout/bottom.php';
/* ############################## REDAXO FOOTER ############################### */
?>
  </div>
