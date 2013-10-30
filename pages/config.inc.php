<?php
// include usersettings file
if(file_exists($REX['ADDON375']['configfile'])) {
	// Overwrite default settings with configfile
	include_once($REX['ADDON375']['configfile']);

	// Initialize new languages if necessary
	// IMPORTANT: change defaults also in ../config.inc.php
	// (default settings are set there)
	foreach($REX['CLANG'] as $key => $value) {
		if(!isset($REX['ADDON375']['config']['default_content'][$key])) {
			$REX['ADDON375']['config']['default_content'][$key] = array(
					'title' => $REX['ADDON375']['I18N']->msg('config_default_title'),
					'titles' => array($REX['ADDON375']['I18N']->msg('config_default_title0'),$REX['ADDON375']['I18N']->msg('config_default_title1')),
					'firstname' => $REX['ADDON375']['I18N']->msg('config_default_firstname'),
					'lastname' => $REX['ADDON375']['I18N']->msg('config_default_lastname'),
					'plaintext' => '',
					'confirmsubject' => '',
					'confirm' => '',
					'sendername' => $REX['SERVERNAME']
			);
		}
	}
}
    
$sql = new rex_sql;
  
$REX['ADDON375']['postget']['error'] = array();
$REX['ADDON375']['newsletter']['status'] = 0;

if(!empty($_POST) && isset($REX['ADDON375']['postget']['config'])) {
    if(!isset($REX['ADDON375']['postget']['config']['link'])) {
		$REX['ADDON375']['postget']['config']['link'] = $REX['ADDON375']['postget']['LINK']['1'];
		$REX['ADDON375']['postget']['config']['linkname'] = $REX['ADDON375']['postget']['LINK_NAME']['1'];
    }
  
    if(!myrex_validEmail($REX['ADDON375']['postget']['config']['sender'])) {
		$REX['ADDON375']['postget']['error'][] = $REX['ADDON375']['I18N']->msg('error_invalidemail',$REX['ADDON375']['postget']['config']['sender']);
	}
    else {
		$REX['ADDON375']['config']['sender'] = $REX['ADDON375']['postget']['config']['sender'];
	}
    
    if(intval($REX['ADDON375']['postget']['config']['link'])<=0) {
		$REX['ADDON375']['postget']['error'][] = $REX['ADDON375']['I18N']->msg('error_noarticle');
	}
    else {
		$REX['ADDON375']['config']['link'] = $REX['ADDON375']['postget']['config']['link'];
	}

    if(empty($REX['ADDON375']['postget']['config']['root'])) {
		$REX['ADDON375']['postget']['error'][] = $REX['ADDON375']['I18N']->msg('error_noroot');
	}
    else {
		$REX['ADDON375']['config']['root'] = $REX['ADDON375']['postget']['config']['root'];
	}

    if(intval($REX['ADDON375']['postget']['config']['max_mails'])<=0 || intval($REX['ADDON375']['postget']['config']['max_mails'])>100) {
		$REX['ADDON375']['postget']['error'][] = $REX['ADDON375']['I18N']->msg('error_nomax_mails');
	}
    else {
		$REX['ADDON375']['config']['max_mails'] = $REX['ADDON375']['postget']['config']['max_mails'];
	}

    if(intval($REX['ADDON375']['postget']['config']['bcc_per_mail'])<0 || intval($REX['ADDON375']['postget']['config']['bcc_per_mail'])>100) {
		$REX['ADDON375']['postget']['error'][] = $REX['ADDON375']['I18N']->msg('error_nobcc');
	}
    else {
		$REX['ADDON375']['config']['bcc_per_mail'] = $REX['ADDON375']['postget']['config']['bcc_per_mail'];
	}

	$REX['ADDON375']['config']['1und1']['active'] = $REX['ADDON375']['postget']['config']['1und1']['active'];
	$REX['ADDON375']['config']['1und1']['mail_limit'] = $REX['ADDON375']['postget']['config']['1und1']['mail_limit'];
	$REX['ADDON375']['config']['1und1']['time_distance'] = $REX['ADDON375']['postget']['config']['1und1']['time_distance'];

    if(trim($REX['ADDON375']['postget']['config']['format'])=='html') {
		$REX['ADDON375']['config']['format'] = 'html';
	}
    else {
		$REX['ADDON375']['config']['format'] = 'text';
	}

    if(trim($REX['ADDON375']['postget']['config']['confirmmail'])=='0') {
		$REX['ADDON375']['config']['confirmmail'] = '0';
	}
    else {
		if(!class_exists(rex_mailer)) {
	        $REX['ADDON375']['postget']['error'][] = $REX['ADDON375']['I18N']->msg('error_confirm_without_phpmailer');
    	    $REX['ADDON375']['config']['confirmmail'] = '0';
    	}
		else {
	        $REX['ADDON375']['config']['confirmmail'] = '1';
		}
    }

	$REX['ADDON375']['config']['default_lang'] = $REX['ADDON375']['postget']['config']['default_lang'];

    $error = false;
    foreach($REX['ADDON375']['config']['default_content'] as $key=>$array) {
		foreach($array as $k=>$v) {
			if($k=='title') {
				if(intval($REX['ADDON375']['postget']['config']['default_content'][$key][$k])>=0 && intval($REX['ADDON375']['postget']['config']['default_content'][$key][$k])<=1) {
					$REX['ADDON375']['config']['default_content'][$key][$k] = strval(intval($REX['ADDON375']['postget']['config']['default_content'][$key][$k]));
				}
				else {
		            $error=true;
				}
        	}
        	elseif($k=='titles') {
				if(!empty($REX['ADDON375']['postget']['config']['default_content'][$key][$k][0])) {
		            $REX['ADDON375']['config']['default_content'][$key][$k][0] = stripslashes($REX['ADDON375']['postget']['config']['default_content'][$key][$k]['0']);
				}
		        else {
		            $error=true;
				}

          		if(!empty($REX['ADDON375']['postget']['config']['default_content'][$key][$k][1])) {
            		$REX['ADDON375']['config']['default_content'][$key][$k][1] = stripslashes($REX['ADDON375']['postget']['config']['default_content'][$key][$k]['1']);
				}
		        else {
		            $error=true;
				}
	        }
    	    else if($k=='plaintext' || $k=='confirm') { 
				$REX['ADDON375']['config']['default_content'][$key][$k] = base64_encode($REX['ADDON375']['postget']['config']['default_content'][$key][$k]);
    	    }
			else if(trim($REX['ADDON375']['postget']['config']['default_content'][$key][$k])!='') {
				$REX['ADDON375']['config']['default_content'][$key][$k] = stripslashes($REX['ADDON375']['postget']['config']['default_content'][$key][$k]);
			}
			else {
				$REX['ADDON375']['config']['default_content'][$key][$k] = '';
				$error=true;
			}
		}
    }
    if($error) {
		$REX['ADDON375']['postget']['error'][] = $REX['ADDON375']['I18N']->msg('error_default_content');
	}
    // generate the textfile
    $text = '';
    foreach($REX['ADDON375']['config'] as $key=>$value)
    {
	  if($key == '1und1') {
		$text.='
  $REX[\'ADDON375\'][\'config\'][\''.$key.'\'] = array();';
		$text.='
  $REX[\'ADDON375\'][\'config\'][\''.$key.'\'][\'active\'] = \''. $REX['ADDON375']['config'][$key]['active'] .'\';';
		$text.='
  $REX[\'ADDON375\'][\'config\'][\''.$key.'\'][\'mail_limit\'] = \''. $REX['ADDON375']['config'][$key]['mail_limit'] .'\';';
		$text.='
  $REX[\'ADDON375\'][\'config\'][\''.$key.'\'][\'time_distance\'] = \''. $REX['ADDON375']['config'][$key]['time_distance'] .'\';';
	  }
      else if($key!='default_content')
      {
        $text.='
  $REX[\'ADDON375\'][\'config\'][\''.$key.'\'] = \''.$value.'\';';
      }
      else
      {
        $text.='
  $REX[\'ADDON375\'][\'config\'][\''.$key.'\'] = array();';
        foreach($value as $clang=>$content)
        {
          $text.='
  $REX[\'ADDON375\'][\'config\'][\''.$key.'\'][\''.$clang.'\'] = array();';
  
          foreach($content as $param=>$v)
          {
            if($param=='titles')
            {
              $text.='
  $REX[\'ADDON375\'][\'config\'][\''.$key.'\'][\''.$clang.'\'][\''.$param.'\'] = array(\''.join('\',\'',$REX['ADDON375']['config']['default_content'][$clang][$param]).'\');';
            }
            else
              $text.='
  $REX[\'ADDON375\'][\'config\'][\''.$key.'\'][\''.$clang.'\'][\''.$param.'\'] = \''.$REX['ADDON375']['config']['default_content'][$clang][$param].'\';';
          }
        }
      }
    }
    $text = '<?php
'.$text.'
?>';

    $handle = fopen($REX['ADDON375']['configfile'],'w+');
    fwrite($handle,$text);
    fclose($handle);
  }
  
  if(empty($REX['ADDON375']['postget']['config']['linkname']))
  {
    $temp = OOArticle::getArticleById($REX['ADDON375']['config']['link']);
    if(is_object($temp))
    {
      $REX['ADDON375']['postget']['config']['linkname'] = $temp->getName();
    }
  }
  
/* ############################## REDAXO HEADERS ############################### */
    include $REX['INCLUDE_PATH'].'/layout/top.php';
  
    print_r(myrexvars_include_jscript($REX['INCLUDE_PATH'].'/addons/'.$REX['ADDON375']['addon_name'].'/scripts/scripts.js'));
    print_r(myrexvars_include_css($REX['INCLUDE_PATH'].'/addons/'.$REX['ADDON375']['addon_name'].'/css/backend.css'));
/* ############################## REDAXO HEADERS ############################### */
?>

<!-- BEGIN: PAGE CONTENT //-->
  <div class="rex-addon">
    <div id="rex-title">
      <div class="rex-title-row"><h1><?php print  $REX['ADDON375']['I18N']->msg('addon_title'); ?></h1></div>
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
      <table class="rex-table">
        <tbody>
          <tr>
            <th class="rex-icon">&nbsp;</th>
            <th class="myrex_middle"><?php print $REX['ADDON375']['I18N']->msg('config_title_standards')?></th>
            <th class="myrex_right">&nbsp;</th>
          </tr>

          <tr>
            <td class="rex-icon" valign="top">
              &nbsp;
            </td>
            <td class="myrex_middle">
              <ul class="myrex_form">
<?php
  echo '
                <li class="clearfix">
                  <label>'.$REX['ADDON375']['I18N']->msg('config_sender').'</label>
                  <input type="text" name="config[sender]" value="'.$REX['ADDON375']['config']['sender'].'" maxlength="255" />                  
                </li>';
                
  echo '
                <li class="clearfix">
                  <label>'.$REX['ADDON375']['I18N']->msg('config_link').'</label>
              			<input type="hidden" name="LINK[1]" id="LINK_1" value="'.stripslashes($REX['ADDON375']['config']['link']).'" />
              			<input style="margin-right:0.5em" type="text" size="30" name="LINK_NAME[1]" value="'.stripslashes($REX['ADDON375']['postget']['config']['linkname']).'" id="LINK_1_NAME" readonly="readonly" />
              			<a href="#" onclick="openLinkMap(\'LINK_1\', \'&clang=0&category_id=1\');return false;"><img src="media/file_open.gif" width="16" height="16" alt="Open Linkmap" title="Open Linkmap" /></a>
             				<a href="#" onclick="deleteREXLink(1);return false;"><img src="media/file_del.gif" width="16" height="16" title="Remove Selection" alt="Remove Selection" /></a>
                </li>';

  echo '
                <li class="clearfix">
                  <label>'.$REX['ADDON375']['I18N']->msg('config_root').'</label>
                  <input type="text" name="config[root]" value="'.$REX['ADDON375']['config']['root'].'" maxlength="255" />                  
                </li>';

  echo '
                <li class="clearfix">
                  <label>'.$REX['ADDON375']['I18N']->msg('config_max_mails').'</label>
                  <input style="width:20px" type="text" name="config[max_mails]" value="'.$REX['ADDON375']['config']['max_mails'].'" maxlength="2" />                  
                </li>';

  echo '
                <li class="clearfix">
                  <label>'.$REX['ADDON375']['I18N']->msg('config_bcc_per_mail').'</label>
                  <input style="width:20px" type="text" name="config[bcc_per_mail]" value="'.$REX['ADDON375']['config']['bcc_per_mail'].'" maxlength="2" />                  
                </li>';

	$select = new rex_select;
	$select->setAttribute('size','1');
	$select->setName('config[1und1][active]');
	$select->addOption($REX['ADDON375']['I18N']->msg('config_1und1_active_yes'),'yes');
	$select->addOption($REX['ADDON375']['I18N']->msg('config_1und1_active_no'),'no');
	$select->setSelected($REX['ADDON375']['config']['1und1']['active']);

	$select->setAttribute('class','myrex_select_small');
	
	echo '
                <li class="clearfix">
                  <label>'.$REX['ADDON375']['I18N']->msg('config_1und1_active').'</label>
                  '.$select->get().'                 
                </li>';

  echo '
                <li class="clearfix">
                  <label>&nbsp;</label>
                  <input style="width:40px" type="text" name="config[1und1][mail_limit]" value="'.$REX['ADDON375']['config']['1und1']['mail_limit'].'" maxlength="5" /> '.$REX['ADDON375']['I18N']->msg('config_1und1_maillimit').'                 
                </li>';

  echo '
                <li class="clearfix">
                  <label>&nbsp;</label>
                  <input style="width:40px" type="text" name="config[1und1][time_distance]" value="'.$REX['ADDON375']['config']['1und1']['time_distance'].'" maxlength="5" /> '.$REX['ADDON375']['I18N']->msg('config_1und1_timelimit').'
                </li>';

  $select = new rex_select;
  $select->setAttribute('size','1');
  $select->setName('config[format]');
  $select->addOption($REX['ADDON375']['I18N']->msg('config_format_html'),'html');
  $select->addOption($REX['ADDON375']['I18N']->msg('config_format_text'),'text');
  $select->setSelected($REX['ADDON375']['config']['format']);

  $select->setAttribute('class','myrex_select_small');

  echo '
                <li class="clearfix">
                  <label>'.$REX['ADDON375']['I18N']->msg('config_format').'</label>
                  '.$select->get().'                  
                </li>';

  $select = new rex_select;
  $select->setAttribute('size','1');
  $select->setName('config[confirmmail]');
  $select->addOption($REX['ADDON375']['I18N']->msg('config_confirm_yes'),'1');
  $select->addOption($REX['ADDON375']['I18N']->msg('config_confirm_no'),'0');
  $select->setSelected($REX['ADDON375']['config']['confirmmail']);

  $select->setAttribute('class','myrex_select_small');

  echo '
                <li class="clearfix">
                  <label>'.$REX['ADDON375']['I18N']->msg('config_confirmmail').'</label>
                  '.$select->get().'                  
                </li>';

	$select = new rex_select;
	$select->setAttribute('size','1');
	$select->setName('config[default_lang]');
	$select->addOption($REX['ADDON375']['I18N']->msg('config_defaultlang_keine'), 'none');
	foreach($REX['CLANG'] as $key => $value) {
		$select->addOption($value, $value);
	}
	$select->setSelected($REX['ADDON375']['config']['default_lang']);
	$select->setAttribute('class','myrex_select_small');

	echo '
                <li class="clearfix">
                  <label>'.$REX['ADDON375']['I18N']->msg('config_defaultlang').'</label>
                  '.$select->get().'                  
                </li>';
?>
              </ul>
            </td>
            <td class="myrex_right" rowspan="2">
              <?php print $REX['ADDON375']['I18N']->msg('expl_config_standards')?>            
            </td>
          </tr>
          <tr class="myrex_spacebelow">
            <td class="rex-icon" valign="top">
              &nbsp;
            </td>
            <td class="myrex_middle">
              <input style="width:100%" type="submit" class="myrex_submit" id="config_send" name="config[send]" onclick="return myrex_confirm('<?php print $REX['ADDON375']['I18N']->msg('confirm_save_all')?>',this.form)" value="<?php print $REX['ADDON375']['I18N']->msg('button_save_all')?>" />
            </td>
          </tr>
          
<?php
foreach($REX['CLANG'] as $key => $value) {
?>
          <tr>
            <th class="rex-icon">&nbsp;</th>
            <th class="myrex_middle"><?php print $REX['ADDON375']['I18N']->msg('config_title_textstandards',$value)?></th>
            <th class="myrex_right">&nbsp;</th>
          </tr>
          <tr class="myrex_spacebelow">
            <td class="rex-icon" valign="top">
              &nbsp;
            </td>
            <td class="myrex_middle">
              <ul class="myrex_form">
<?php
    foreach($REX['ADDON375']['config']['default_content'][$key] as $k=>$v) {
		if($k=='title') {
		    $select = new rex_select;
		    $select->setAttribute('size','1');
		    $select->setName('config[default_content]['.$key.'][title]');
		    $select->setAttribute('class','myrex_select_small');
		    $select->addOption($REX['ADDON375']['I18N']->msg('config_title0'),0);
		    $select->addOption($REX['ADDON375']['I18N']->msg('config_title1'),1);
		    $select->setSelected($REX['ADDON375']['config']['default_content'][$key][$k]);
		    echo '
		            <li class="clearfix">
		              <label>'.$REX['ADDON375']['I18N']->msg('config_title').'</label>
		              '.$select->get().'                  
		            </li>';
	    }
		elseif($k=='titles') {
		    echo '
		            <li class="clearfix">
		              <label>'.$REX['ADDON375']['I18N']->msg('config_title0').'</label>
		              <input type="text" name="config[default_content]['.$key.'][titles][0]" 
		                     value="'.htmlspecialchars(stripslashes($REX['ADDON375']['config']['default_content'][$key][$k][0]),ENT_QUOTES).'" maxlength="100" />                  
		            </li>';
		    echo '
		            <li class="clearfix">
		              <label>'.$REX['ADDON375']['I18N']->msg('config_title1').'</label>
		              <input type="text" name="config[default_content]['.$key.'][titles][1]" 
		                     value="'.htmlspecialchars(stripslashes($REX['ADDON375']['config']['default_content'][$key][$k][1]),ENT_QUOTES).'" maxlength="100" />                  
		            </li>';
		    echo '
		            <li>&nbsp;</li>';
		}
		elseif($k=='plaintext' || $k=='confirm') {
		    echo '
		            <li class="clearfix">
		              <label>'.$REX['ADDON375']['I18N']->msg('config_'.$k).'</label>
		              <textarea name="config[default_content]['.$key.']['.$k.']" cols="10" rows="10">'.htmlspecialchars(stripslashes(base64_decode($REX['ADDON375']['config']['default_content'][$key][$k])),ENT_QUOTES).'</textarea>
		            </li>';
      	}
      	else {
        	echo '
		            <li class="clearfix">
		              <label>'.$REX['ADDON375']['I18N']->msg('config_'.$k).'</label>
		              <input type="text" name="config[default_content]['.$key.']['.$k.']" 
		                     value="'.htmlspecialchars(stripslashes($REX['ADDON375']['config']['default_content'][$key][$k]),ENT_QUOTES).'" maxlength="255" />                  
		            </li>';	
        }
    }
?>
              </ul>
            </td>
            <td class="myrex_right" rowspan="2">
              <?php print $REX['ADDON375']['I18N']->msg('expl_config_defaulttext')?>            
            </td>
          </tr>
          <tr class="myrex_spacebelow">
            <td class="rex-icon" valign="top">
              &nbsp;
            </td>
            <td class="myrex_middle">
              <input style="width:100%" type="submit" class="myrex_submit" id="config_send" name="config[send]" onclick="return myrex_confirm('<?php print $REX['ADDON375']['I18N']->msg('confirm_save_all')?>',this.form)" value="<?php print $REX['ADDON375']['I18N']->msg('button_save_all')?>" />
            </td>
          </tr>
<?php
}
?>
        </tbody>
      </table>
    </form>
  <!-- END: OUTPUT //-->
<?php
/* ############################## REDAXO FOOTER ############################### */
  include $REX['INCLUDE_PATH'].'/layout/bottom.php';
/* ############################## REDAXO FOOTER ############################### */
?>
  </div>
