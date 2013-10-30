<?php
  if (!function_exists('rex_a375_module_input'))
  {
    function rex_a375_module_input($rex_array_values)
    {
      global $REX;
      $return = '';
#      print_r($rex_array_values);

      $rex_375_select = new rex_select;
      $rex_375_select->setAttribute('size','1');
      $rex_375_select->setAttribute('style','width:45%;float:left');
      $rex_375_select->addOption($REX['ADDON375']['I18N']->msg('module_dontshow'),0);
      $rex_375_select->addOption($REX['ADDON375']['I18N']->msg('module_show'),1);
      $rex_375_select->addOption($REX['ADDON375']['I18N']->msg('module_required'),2);
      
      $return.='
<fieldset style="width:49%;float:left;margin-right:2%">
  <legend style="margin:0 0 1em 0;"><strong>'.$REX['ADDON375']['I18N']->msg('module_fields').'</strong></legend>';
  
  	 $return.='
  <br style="clear:both;margin-bottom:2em" />
  
  <label style="width:53%;display:block;float:left" for="VALUE6">'.$REX['ADDON375']['I18N']->msg('newsletter_grad').'</label>';

      $rex_375_select->setName('VALUE[6]');
      $rex_375_select->setId('VALUE6');
      $rex_375_select->setSelected($rex_array_values['6']);
      $return.=$rex_375_select->get();

	  $return.='
  <br style="clear:both;margin-bottom:2em" />
  
  <label style="width:53%;display:block;float:left" for="VALUE1">'.$REX['ADDON375']['I18N']->msg('newsletter_firstname').'</label>';

      $rex_375_select->setName('VALUE[1]');
      $rex_375_select->setId('VALUE1');
      $rex_375_select->setSelected($rex_array_values['1']);
      $return.=$rex_375_select->get();

      $return.='
  <br style="clear:both;margin-bottom:2em" />

  <label style="width:53%;display:block;float:left" for="VALUE2">'.$REX['ADDON375']['I18N']->msg('newsletter_lastname').'</label>';
      $rex_375_select->setName('VALUE[2]');
      $rex_375_select->setId('VALUE2');
      $rex_375_select->resetSelected();
      $rex_375_select->setSelected($rex_array_values['2']);
      $return.=$rex_375_select->get();

      $return.='
  <br style="clear:both;margin-bottom:2em" />

  <label style="width:53%;display:block;float:left" for="VALUE3">'.$REX['ADDON375']['I18N']->msg('newsletter_title').'</label>';
  
      $rex_375_select->setName('VALUE[3]');
      $rex_375_select->setId('VALUE3');
      $rex_375_select->resetSelected();
      $rex_375_select->setSelected($rex_array_values['3']);
      $return.=$rex_375_select->get();

      $return.='
  <br style="clear:both;margin-bottom:2em" />
</fieldset>

<fieldset style="width:49%;float:left;">
  <legend style="margin:0 0 1em 0;"><strong>'.$REX['ADDON375']['I18N']->msg('module_groups').'</strong></legend>';

      $rex_375_sql = new rex_sql;
      $rex_375_sql->setQuery("SELECT `name`,`id` FROM `".$REX['ADDON375']['grouptable']."` ORDER BY `name`");
      $rex_375_groups = $rex_375_sql->getArray();
      if(!empty($rex_375_groups))
      {
        if(count($rex_375_groups)>1)
        {
          $rex_375_select = new rex_select;
          $rex_375_select->setAttribute('size','7');
          $rex_375_select->setAttribute('style','width:98%');
          $rex_375_select->setAttribute('multiple','multiple');
          $rex_375_select->setName('VALUE[4][]');
          $rex_375_select->setId('VALUE4');
          // read all groups
          foreach($rex_375_groups as $group)
            $rex_375_select->addOption($group['name'],$group['id']);
        
          $rex_375_select->setSelected($rex_array_values['4']);
          $return.=$rex_375_select->get();
          
          $return.='<br style="clear:both;margin-bottom:2em" />
                    <label style="width:53%;display:block;float:left" for="VALUE5">'.$REX['ADDON375']['I18N']->msg('newsletter_groupselect').'</label>';

          $rex_375_select = new rex_select;
          $rex_375_select->setAttribute('size','1');
          $rex_375_select->setAttribute('style','width:45%;float:left');
          $rex_375_select->addOption($REX['ADDON375']['I18N']->msg('module_dontchoose'),0);
          $rex_375_select->addOption($REX['ADDON375']['I18N']->msg('module_selectbox'),1);
          $rex_375_select->addOption($REX['ADDON375']['I18N']->msg('module_checkboxes'),2);
          $rex_375_select->setName('VALUE[5]');
          $rex_375_select->setId('VALUE5');
          $rex_375_select->setSelected($rex_array_values['5']);
          $return.=$rex_375_select->get();
          
          $return.='<br style="clear:both;margin-bottom:2em" /><p>'.$REX['ADDON375']['I18N']->msg('newsletter_groupselect_expl').'</p><br style="clear:both;margin-bottom:1em" />';
        }
        else
        {
          $return.='<input type="hidden" name="VALUE[4][0]" value="'.$rex_375_groups[0]['id'].'" />
                    <p>'.$REX['ADDON375']['I18N']->msg('module_groupset',$rex_375_groups[0]['name']).'</p>';
        }
      }
      else
        $return.='<input type="hidden" name="VALUE[4]" value="" /><p>'.$REX['ADDON375']['I18N']->msg('module_redaxo_output_nogroups').'</p>';
    
      $return.='
</fieldset>
<hr style="clear:both;" />';

      return $return;

    }
  }
  
  if (!function_exists('rex_a375_module_output'))
  {
    function rex_a375_module_output($rex_array_values)
    {
      global $REX;
      
      // get the configration settings
      if(file_exists($REX['ADDON375']['configfile']))
        include_once($REX['ADDON375']['configfile']);
    
      // get the groupnames
      $rex_375_sql = new rex_sql;
      $rex_375_sql->setQuery("SELECT `name`,`id` FROM `".$REX['ADDON375']['grouptable']."` ORDER BY `name`");
      $rex_375_groups = $rex_375_sql->getArray();
      
      if(is_array($rex_array_values['4']))
      { 
        // generate grouplist from the selected and the existing groups
        $t = array();
        foreach($rex_375_groups as $group)
          if(in_array($group['id'],$rex_array_values['4']))
            $t[] = $group;
        $rex_array_values['4'] = $t; unset($t);
      }
      else
        $rex_array_values['4'] = array();
      
      if(empty($rex_array_values['4']) && count($rex_375_groups)==1)
        $rex_array_values['4'] = $rex_375_groups;
      
      if($REX['REDAXO'])
      {
        $return.= '<p>'.$REX['ADDON375']['I18N']->msg('module_redaxo_output').'</p>';
        
        if(count($rex_array_values['4'])<=0)
          $return.= '<p><strong>'.$REX['ADDON375']['I18N']->msg('module_redaxo_output_nogroupsselected').'</strong></p>';
        elseif(count($rex_375_groups)<=0)
          $return.= '<p><strong>'.$REX['ADDON375']['I18N']->msg('module_redaxo_output_nogroups').'</strong></p>';
        else
          foreach($rex_array_values['4'] as $group)
            $return.= '<p>'.$REX['ADDON375']['I18N']->msg('module_redaxo_output_groups',$group['name']).'</p>';
      
        if(!class_exists(rex_mailer) && intval($REX['ADDON375']['config']['confirmmail'])==1)
        { 
          $return.= '<p><strong>'.$REX['ADDON375']['I18N']->msg('module_redaxo_nophpmailer').'</strong></p>';
        }
      }
      elseif( count($rex_375_groups)>0
              && count($rex_array_values['4'])>0
              && !(!class_exists(rex_mailer) && intval($REX['ADDON375']['config']['confirmmail'])==1)
            )
      { 
        $showform = true;
        
        /* $rex_array_values contains all the given parameters
           6 : Titel / Grad (0 = dont show, 1 = show, 2 = required)
		   1 : First name (0 = dont show, 1 = show, 2 = required)
           2 : Last name (0 = dont show, 1 = show, 2 = required)
           3 : Title (0 = dont show, 1 = show, 2 = required)
           4 : Groups (Array with group ids)
           5 : Groupselect (0 = dont choose, 1 = as a selectbox, 2 = via chackboxes)
        */
        
        require_once($REX['INCLUDE_PATH'].'/addons/'.$REX['ADDON375']['addon_name'].'/functions/basics.inc.php');

        if(!empty($_GET['key']) || !empty($_GET['unsubscribe']) || !empty($_POST))
        {
         // check the parameters that has been send

          require_once($REX['INCLUDE_PATH'].'/addons/'.$REX['ADDON375']['addon_name'].'/functions/newsletter.inc.php');
          
          $REX['ADDON375']['postget'] = myrexvars_read_postget_parameters();
          
          if(!empty($REX['ADDON375']['postget']['key']))
          {
            $temp_key = rawurldecode($REX['ADDON375']['postget']['key']);
            $temp_key = explode(',',$temp_key);
            
            // confirm the user
            $user = array(
              'email' => trim($temp_key[0]),
              'key' => trim($temp_key[1])
            );
    
            $messages = rex_a375_confirm($user);
            $showform = false;
          }
          elseif(!empty($_GET['unsubscribe']))
          {  
            $user = array(
              'email' => rawurldecode($_GET['unsubscribe'])
            );
            
            $messages = rex_a375_unsubscribe($user);
            
            $showform=false;
            
            
          }
          else if(myrex_validEmail($REX['ADDON375']['postget']['rex_375']['email']))
          {
            $messages = array('error'=>array(),'msg'=>array());
            
            $user = array();
            $user['email'] = $REX['ADDON375']['postget']['rex_375']['email'];

            if(!empty($REX['ADDON375']['postget']['rex_375']['subscribe']))
            {
              // if the user is about to subscribe to a newsletter
              
			  if($rex_array_values['6']=='2' && trim($REX['ADDON375']['postget']['rex_375']['grad'])=='')
                $messages['error'][] = 'invalid_grad';
              else
                $user['grad'] = $REX['ADDON375']['postget']['rex_375']['grad'];
			  
			  if($rex_array_values['1']=='2' && trim($REX['ADDON375']['postget']['rex_375']['firstname'])=='')
                $messages['error'][] = 'invalid_firstname';
              else
                $user['firstname'] = $REX['ADDON375']['postget']['rex_375']['firstname'];
              
              if($rex_array_values['2']=='2' && trim($REX['ADDON375']['postget']['rex_375']['lastname'])=='')
                $messages['error'][] = 'invalid_lastname';
              else
                $user['lastname'] = $REX['ADDON375']['postget']['rex_375']['lastname'];

              if($rex_array_values['3']=='2' && !(intval($REX['ADDON375']['postget']['rex_375'])>=0 && intval($REX['ADDON375']['postget']['rex_375']['title'])<=1))
                $messages['error'][] = 'invalid_lastname';
              else
               $user['title'] = $REX['ADDON375']['postget']['rex_375']['title'];
                
              if(empty($REX['ADDON375']['postget']['rex_375']['groups']))
                $messages['error'][] = 'nogroup_selected';
              else
              { 
                $t = array();
                foreach($REX['ADDON375']['postget']['rex_375']['groups'] as $gid)
                {
                  if(intval($gid)>0)
                    $t[] = intval($gid);
                }
                if(!empty($t))
                  $user['groups'] = $t;
                else
                  $messages['error'][] = 'nogroup_selected';
                
                unset($t);
              }
            }
            
            if(empty($messages['error']))
            {
              if(!empty($REX['ADDON375']['postget']['rex_375']['subscribe']))
                $messages = rex_a375_subscribe($user);
              elseif(!empty($REX['ADDON375']['postget']['rex_375']['unsubscribe']))
                $messages = rex_a375_unsubscribe($user);
              
              $showform = false;
            }
          }
          else {
            $messages['error'][] = 'invalid_email';
		  }
        }
        
        $return .= '<div id="rex_375_multinewsletter">';

        // Print out the confirm-/subscribe-/unsubscribe- messages - if they exist
        if(!empty($messages['msg']))
        {
          $return.= '<div class="rex_375_msg">';
          foreach($messages['msg'] as $r)
            $return.='<p>'.myrex_clang_msg($r).'</p>';
          $return.='</div>';
        }
        
        // Print out the error messages - if they exist
        if(!empty($messages['error']))
        {
          $return.= '<div class="rex_375_error">';
          foreach($messages['error'] as $r)
            $return.='<p>'.myrex_clang_msg($r).'</p>';
          $return.='</div>';
        }
        
        // print out the form
        if($showform)
        {
			//  ##### NEU: Session wieder zum Senden freigeben ##### 
			$_SESSION['newsletter_gesendet']='';
		  $return.='<p>'. myrex_clang_msg('action') .'</p>';
          $return.='<form action="'.rex_getUrl($REX_ARTICLE_ID,$REX['CUR_CLANG']).'" method="post" name="rex_375_multinewsletter">';
          $return.='<div class="xform"><table border="0" cellspacing="0" cellpadding="4px" width="100%">';
          
          // Title
          if(intval($rex_array_values['3'])>0)
          {
            $return.='<tr><td><label for="rex_375_title">'.myrex_clang_msg('title').(intval($rex_array_values['3'])==2 ? '*' : '').'</label></td><td>';

            $rex_375_select = new rex_select;
            $rex_375_select->setName('rex_375[title]');
            $rex_375_select->setAttribute('id','rex_375_title');
            $rex_375_select->setAttribute('size',1);
            $rex_375_select->setAttribute('class','rex_375_select');
            $rex_375_select->setAttribute('style','border: 1px solid #D1D1D1;');
            $rex_375_select->addOption(myrex_clang_msg('title0'),'0');
            $rex_375_select->addOption(myrex_clang_msg('title1'),'1');
            $rex_375_select->setSelected($REX['ADDON375']['postget']['rex_375']['title']);
            $return.=$rex_375_select->get().'</td></tr>';
          }
          
          // Titel / Grad
          if(intval($rex_array_values['6'])>0)
          {
            $return.='<tr><td><label for="rex_375_grad">'.myrex_clang_msg('grad').(intval($rex_array_values['6'])==2 ? '*' : '').'</label></td>';
            $return.='<td><input class="rex_375_text" type="text" name="rex_375[grad]" id="rex_375_grad" value="'.stripslashes($REX['ADDON375']['postget']['rex_375']['grad']).'" maxlength="255" style="border: 1px solid #D1D1D1;" /></td></tr>';
          } 
 
 		  // First name
          if(intval($rex_array_values['1'])>0)
          {
            $return.='<tr><td><label for="rex_375_firstname">'.myrex_clang_msg('firstname').(intval($rex_array_values['1'])==2 ? '*' : '').'</label></td>';
            $return.='<td><input class="rex_375_text" type="text" name="rex_375[firstname]" id="rex_375_firstname" value="'.stripslashes($REX['ADDON375']['postget']['rex_375']['firstname']).'" maxlength="255" style="border: 1px solid #D1D1D1;" /></td></tr>';
          }
          
          // Last name
          if(intval($rex_array_values['2'])>0)
          {
            $return.='<tr><td><label for="rex_375_lastname">'.myrex_clang_msg('lastname').(intval($rex_array_values['2'])==2 ? '*' : '').'</label></td>';
            $return.='<td><input class="rex_375_text" type="text" name="rex_375[lastname]" id="rex_375_lastname" value="'.stripslashes($REX['ADDON375']['postget']['rex_375']['lastname']).'" maxlength="255" style="border: 1px solid #D1D1D1;" /></td></tr>';
          }

          // E-Mail
          $return.='<tr><td><label for="rex_375_email">'.myrex_clang_msg('email').'&nbsp;*</label></td>';
          $return.='<td><input class="rex_375_text" type="text" name="rex_375[email]" id="rex_375_email" value="'.stripslashes($REX['ADDON375']['postget']['rex_375']['email']).'" maxlength="255" style="border: 1px solid #D1D1D1;" /></td></tr>';
          
          $hidden_groupfields = '';
          // Groups
          
          if(intval($rex_array_values['5'])==0 || count($rex_array_values['4'])==1)
          {
            // no option to chose
            foreach($rex_array_values['4'] as $group)
              $hidden_groupfields.='<input type="hidden" name="rex_375[groups]['.$group['id'].']" value="'.$group['id'].'" />';
          }
          elseif(intval($rex_array_values['5'])==1)
          {
            // selectbox
            $rex_375_select = new rex_select;
            $rex_375_select->setName('rex_375[groups][]');
            $rex_375_select->setAttribute('id','rex_375_groups');
            $rex_375_select->setAttribute('size',1);
            $rex_375_select->setAttribute('class','rex_375_select');
            $rex_375_select->addOption(myrex_clang_msg('select_group'),0);
            foreach($rex_array_values['4'] as $group)
              $rex_375_select->addOption($group['name'],$group['id']);
            $rex_375_select->setSelected($REX['ADDON375']['postget']['rex_375']['groups']);
            $return.=$rex_375_select->get();
          }
          elseif(intval($rex_array_values['5'])==2)
          {
            // checkboxes
            foreach($rex_array_values['4'] as $group)
            {
              if(isset($REX['ADDON375']['postget']['rex_375']['groups'][$group['id']]))
                $t = ' checked="checked"';
                
              $return.='<tr><td>';
              $return.=$hidden_groupfields; unset($hidden_groupfields);
              $return.='<input class="rex_375_checkbox" type="checkbox" name="rex_375[groups]['.$group['id'].']" id="rex_375_group'.$group['id'].'" value="'.$group['id'].'"'.$t.' style="border: 1px solid #D1D1D1;" /></td>';
              $return.='<td><label class="rex_375_checkbox" for="rex_375_group'.$group['id'].'">'.htmlspecialchars(stripslashes($group['name']),ENT_QUOTES).'</label></td>';
              $return.='</tr>';
            }
          }

          // Subscribe-Button
          $return.='<tr><td>&nbsp;</td><td>'.$hidden_groupfields.'<input class="rex_375_subscribe formsubmit" type="submit" name="rex_375[subscribe]" id="rex_375_subscribe" value="'.myrex_clang_msg('subscribe').'" style="border: 1px solid #D1D1D1;" /></td></tr>';

          // Unsubscribe-Button
//          $return.='<tr><td>&nbsp;</td><td><input class="rex_375_unsubscribe formsubmit" type="submit" name="rex_375[unsubscribe]" id="rex_375_unsubscribe" value="'.myrex_clang_msg('unsubscribe').'" style="border: 1px solid #D1D1D1;" /></td></tr>';

          $return.='</table></div>';
          $return.='</form>';

		  $return.='<br /><p>'.myrex_clang_msg('compulsory').'</p>';
//		  $return.='<p>'.myrex_clang_msg('safety').'</p>';

        }

        $return .= '</div>';
        
      }
      return $return;
    }
  }
  
?>
