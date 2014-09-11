<?php
  // addon identifier
  global $REX;
  $REX['ADDON375'] = array();
  
  $REX['ADDON375']['addon_name'] = 'multinewsletter';
  $REX['ADDON375']['addon_version'] = '1.4.3';
  
  // CREATE LANG OBJ FOR THIS ADDON
  $REX['ADDON375']['I18N'] = new i18n($REX['LANG'],$REX['INCLUDE_PATH'].'/addons/'.$REX['ADDON375']['addon_name'].'/lang');
  
  // unique id
  $REX['ADDON']['rxid'][$REX['ADDON375']['addon_name']] = '375';
  // foldername
  $REX['ADDON']['page'][$REX['ADDON375']['addon_name']] = $REX['ADDON375']['addon_name'];    
  // name shown in the REDAXO main menu
  $REX['ADDON']['name'][$REX['ADDON375']['addon_name']] = $REX['ADDON375']['I18N']->msg('addon_short_title');
 // permission needed for accessing the addon
  $REX['ADDON']['perm'][$REX['ADDON375']['addon_name']] = 'multinewsletter[]';

  // add default perm for accessing the addon to user-administration
  $REX['PERM'][] = 'multinewsletter[]';
  
  $REX['ADDON375']['configfile'] = $REX['INCLUDE_PATH'].'/addons/'.$REX['ADDON375']['addon_name'].'/files/.configfile';

  $REX['ADDON375']['usertable'] = $REX['TABLE_PREFIX'].'375_user';
  $REX['ADDON375']['grouptable'] = $REX['TABLE_PREFIX'].'375_group';
  $REX['ADDON375']['u2gtable'] = $REX['TABLE_PREFIX'].'375_user2group';
  $REX['ADDON375']['archivetable'] = $REX['TABLE_PREFIX'].'375_archive';


  // Standard Config-Parameters
  $REX['ADDON375']['config'] = array();
  $REX['ADDON375']['config']['sender'] = $REX['ERROR_EMAIL'];
  $REX['ADDON375']['config']['link'] = $REX['START_ARTICLE_ID'];
  $REX['ADDON375']['config']['root'] = $REX['SERVER'];
  $REX['ADDON375']['config']['max_mails'] = '99';
  $REX['ADDON375']['config']['bcc_per_mail'] = '0';
  $REX['ADDON375']['config']['1und1'] = array();
  $REX['ADDON375']['config']['1und1']['active'] = 'yes'; // yes or no
  $REX['ADDON375']['config']['1und1']['mail_limit'] = '250'; // yes or no
  $REX['ADDON375']['config']['1und1']['time_distance'] = '305'; // yes or no
  $REX['ADDON375']['config']['format'] = 'html';
  $REX['ADDON375']['config']['confirmmail'] = '0';
  $REX['ADDON375']['config']['default_lang'] = 'en';
  
  $REX['ADDON375']['config']['default_content'] = array();
 	 foreach($REX['CLANG'] as $key=>$value) {
		// IMPORTANT: change defaults also in pages/config.inc.php
		// (additional new languages are set there)
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
?>
