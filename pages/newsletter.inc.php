<?php
	// include usersettings file
	if(file_exists($REX['ADDON375']['configfile']))
		include_once($REX['ADDON375']['configfile']);
		
	$sql = new rex_sql;
	
	$REX['ADDON375']['postget']['error'] = array();
	$REX['ADDON375']['newsletter']['status'] = 0;

	if(!isset($REX['ADDON375']['postget']['newsletter']))
	{
		$qry = "SELECT `article_id`, `send_group` FROM `".$REX['ADDON375']['usertable']."` WHERE `article_id`<>0 LIMIT 1";
		$sql->setQuery($qry);
		$items = $sql->getArray();
		
		if(!empty($items)) {
			$items = $items[0];
			$REX['ADDON375']['postget']['newsletter'] = array(
				'article_id' => $items['article_id'],
				'group' => $items['send_group']
			);
			$REX['ADDON375']['newsletter']['status']=2;
		}
		
		if(isset($REX['ADDON375']['postget']['newsletter']['article_id']) && $REX['ADDON375']['postget']['newsletter']['article_id'] > 0) {
			$temp = OOArticle::getArticleById($REX['ADDON375']['postget']['newsletter']['article_id']);
			if(is_object($temp)) {
				$REX['ADDON375']['postget']['newsletter']['article_name'] = $temp -> getName();
			}
		}
	}
	
	if(isset($REX['ADDON375']['postget']['newsletter'])) { 
		$REX['ADDON375']['started'] = time();
		$REX['ADDON375']['maxtimeout'] = ini_get('max_execution_time');
		if($REX['ADDON375']['maxtimeout'] == 0) {
				$REX['ADDON375']['maxtimeout'] = 20;
		}

		require_once($REX['INCLUDE_PATH'].'/addons/'.$REX['ADDON375']['addon_name'].'/functions/newsletter.inc.php');

		if(isset($REX['ADDON375']['postget']['newsletter']['reset']))
		{
			rex_a375_resetRecipients(); // reset all recipients
			unset($REX['ADDON375']['postget']['newsletter']);
			$REX['ADDON375']['newsletter']['status'] = 0;
		}
		else
		{
			if(!isset($REX['ADDON375']['postget']['newsletter']['article_id']))
			{
				$REX['ADDON375']['postget']['newsletter']['article_id'] = $REX['ADDON375']['postget']['LINK']['1'];
				$REX['ADDON375']['postget']['newsletter']['article_name'] = $REX['ADDON375']['postget']['LINK_NAME']['1'];
			}
		
			if(intval($REX['ADDON375']['postget']['newsletter']['article_id'])<=0)
			{
				$REX['ADDON375']['postget']['error'][] = $REX['ADDON375']['I18N']->msg('error_noarticle');
			}
			else
			{
				// check the given parameters
		if(! isset($REX['ADDON375']['postget']['newsletter']['testlanguage'])) {
			$REX['ADDON375']['postget']['newsletter']['testlanguage'] = 0;
		}

				$temp = OOArticle::getArticleById(
									$REX['ADDON375']['postget']['newsletter']['article_id'],
									$REX['ADDON375']['postget']['newsletter']['testlanguage']
								);
		if(!is_object($temp) || !$temp->isOnline())
				{
					$REX['ADDON375']['postget']['error'][] = $REX['ADDON375']['I18N']->msg('error_articlenotfound',$REX['ADDON375']['postget']['newsletter']['article_id'],$REX['CLANG'][$REX['ADDON375']['postget']['newsletter']['testlanguage']]);
				}
				unset($temp);
			}
			
			if(empty($REX['ADDON375']['postget']['error']))
			{
	
				if(isset($REX['ADDON375']['postget']['newsletter']['sendtestmail']))
				{
					if(!myrex_validEmail($REX['ADDON375']['postget']['newsletter']['testemail']))
						$REX['ADDON375']['postget']['error'][] = $REX['ADDON375']['I18N']->msg('error_invalidemail',$REX['ADDON375']['postget']['newsletter']['testemail']);
					
					if(trim($REX['ADDON375']['postget']['newsletter']['testfirstname'])=='')
						$REX['ADDON375']['postget']['error'][] = $REX['ADDON375']['I18N']->msg('error_nofirstname');
	
					if(trim($REX['ADDON375']['postget']['newsletter']['testlastname'])=='')
						$REX['ADDON375']['postget']['error'][] = $REX['ADDON375']['I18N']->msg('error_nolastname');
						
					if(empty($REX['ADDON375']['postget']['error']))
					{
						$content = rex_a375_readArticle($REX['ADDON375']['postget']['newsletter']['article_id']);
						$newsletter_text = $content[$REX['ADDON375']['postget']['newsletter']['testlanguage']];
						$temp = rex_a375_sendnewsletter($newsletter_text,
									$REX['ADDON375']['postget']['newsletter']['testemail'],
									$REX['ADDON375']['postget']['newsletter']['testfirstname'],
									$REX['ADDON375']['postget']['newsletter']['testlastname'],
									$REX['ADDON375']['config']['default_content'][$REX['ADDON375']['postget']['newsletter']['testlanguage']]['titles'][intval($REX['ADDON375']['postget']['newsletter']['testtitle'])],
									$REX['ADDON375']['postget']['newsletter']['testlanguage'],
									$REX['ADDON375']['postget']['newsletter']['article_id'],
									false,
									$REX['ADDON375']['postget']['newsletter']['testgrad']
						);
						if(!$temp)
							$REX['ADDON375']['postget']['error'][] = $REX['ADDON375']['I18N']->msg('error_senderror');
						else
							$REX['ADDON375']['newsletter']['status'] = 1;
					}
	
				}
				else if(isset($REX['ADDON375']['postget']['newsletter']['prepare']))
				{
					rex_a375_resetRecipients(); // reset all recipients
					
					if(isset($REX['ADDON375']['postget']['newsletter']['testmailsent'])!='true')
						$REX['ADDON375']['postget']['error'][] = $REX['ADDON375']['I18N']->msg('error_testmailfirst');
					elseif(intval($REX['ADDON375']['postget']['newsletter']['group'])<=0)
					{
						$REX['ADDON375']['postget']['error'][] = $REX['ADDON375']['I18N']->msg('error_nogroupselected');
						$REX['ADDON375']['newsletter']['status'] = 1;					
					}
					else
					{
						$content = rex_a375_readArticle($REX['ADDON375']['postget']['newsletter']['article_id']);
						$lang = array();
			$nolangs = array();
						foreach($REX['CLANG'] as $k=>$v) {
							if(!empty($content[$k])) {
								$lang[] = $k;
				}
							else {
								$nolangs[] = $v;
				}
			}
					
						$qry = "UPDATE `".$REX['ADDON375']['usertable']."`
										SET `article_id`=".$REX['ADDON375']['postget']['newsletter']['article_id'].",
												`send_group` = ".$REX['ADDON375']['postget']['newsletter']['group']."
										WHERE `id` = (SELECT DISTINCT `uid` 
																	FROM ".$REX['ADDON375']['u2gtable']." 
																	WHERE `gid` = ".intval($REX['ADDON375']['postget']['newsletter']['group'])."
																 )";

						$qry = "UPDATE ".$REX['ADDON375']['u2gtable']." AS u2g, ".$REX['ADDON375']['usertable']." AS user
										SET user.article_id=".$REX['ADDON375']['postget']['newsletter']['article_id'].",
												user.send_group = ".$REX['ADDON375']['postget']['newsletter']['group']."
										WHERE user.status = '1' 
										AND user.id=u2g.uid
										AND u2g.gid = ".intval($REX['ADDON375']['postget']['newsletter']['group']);

			if($REX['ADDON375']['config']['default_lang'] == 'none' && count($nolangs) > 0) {
				$qry .= "
										AND (user.clang='".join("' OR user.clang='",$lang)."')";
			}
						$sql->setQuery($qry);
						
						$REX['ADDON375']['newsletter']['status'] = 2;

			// $lang contains default_lang because default_lang has to be online
			if($REX['ADDON375']['config']['default_lang'] != 'none' && count($nolangs) > 0 && in_array($REX['ADDON375']['config']['default_lang'],$lang)) {
				$REX['ADDON375']['postget']['error'][] = $REX['ADDON375']['I18N']->msg('error_someclangsdefault',join(', ',$nolangs));
			}
			else if(count($nolangs) > 0) {
				$REX['ADDON375']['postget']['error'][] = $REX['ADDON375']['I18N']->msg('error_someclangsoffline',join(', ',$nolangs));
			}
						
						if(intval($REX['ADDON375']['config']['bcc_per_mail'])>0)
						{
							$temp = array();
							
							foreach($content as $k=>$v)
							{
								if(strpos($v['subject'],'///FIRSTNAME///')
									 || strpos($v['subject'],'///LASTNAME///')
									 || strpos($v['subject'],'///TITLE///')
									)
									$temp[] = $REX['ADDON375']['I18N']->msg('subject');
	
								if(strpos($v['textbody'],'///FIRSTNAME///')
									 || strpos($v['textbody'],'///LASTNAME///')
									 || strpos($v['textbody'],'///TITLE///')
									)
									$temp[] = $REX['ADDON375']['I18N']->msg('textbody');
	
								if(strpos($v['htmlbody'],'///FIRSTNAME///')
									 || strpos($v['htmlbody'],'///LASTNAME///')
									 || strpos($v['htmlbody'],'///TITLE///')
									)
										$temp[] = $REX['ADDON375']['I18N']->msg('htmlbody');
							}
							$temp = array_unique($temp);
							if(count($temp)>0)
								$REX['ADDON375']['postget']['error'][] = $REX['ADDON375']['I18N']->msg('error_hasplaceholders',join(', ',$temp));
						}
					} 
				}

				if($REX['ADDON375']['newsletter']['status']=='2'
					 || isset($REX['ADDON375']['postget']['newsletter']['prepare'])
					 || isset($REX['ADDON375']['postget']['newsletter']['send'])
					)
				{
					// get an array of users that should receive the newsletter
					$t = intval($REX['ADDON375']['config']['bcc_per_mail'])>1 ? intval($REX['ADDON375']['config']['bcc_per_mail']) : 1;
					$qry = "SELECT SQL_CALC_FOUND_ROWS *
							FROM `".$REX['ADDON375']['usertable']."`
							WHERE `article_id` <> 0
							ORDER BY `clang`";

			// Are there Serverlimits?
			if($REX['ADDON375']['config']['1und1']['active'] == 'yes') {
				$sql->setQuery($qry);
				$total_users = count($sql->getArray());
				$limit_left = $total_users % $REX['ADDON375']['config']['1und1']['mail_limit'];

				if($limit_left == 0) {
					$limit_left = $REX['ADDON375']['config']['1und1']['mail_limit'];
				}

				if($limit_left > strval($REX['ADDON375']['config']['max_mails']*$t)) {
					$qry .= "
										LIMIT 0,".strval($REX['ADDON375']['config']['max_mails']*$t);
				}
				else {
					$qry .= "
										LIMIT 0,". $limit_left;
				}
			}
			// No special limits
			else {
				$qry .= "
									LIMIT 0,".strval($REX['ADDON375']['config']['max_mails']*$t);
			}

					$sql->setQuery($qry);
					$REX['ADDON375']['postget']['newsletter']['users']=$sql->getArray();

					if(!empty($REX['ADDON375']['postget']['newsletter']['users']))
					{
						$sql->setQuery("SELECT FOUND_ROWS()");
						$REX['ADDON375']['postget']['newsletter']['num_of_allusers']=$sql->getArray();
	
						$REX['ADDON375']['postget']['newsletter']['num_of_allusers'] = $REX['ADDON375']['postget']['newsletter']['num_of_allusers'][0]['FOUND_ROWS()'];
						$REX['ADDON375']['postget']['newsletter']['num_of_users'] = count($REX['ADDON375']['postget']['newsletter']['users']);
	
						// get the name of the group
						$qry = "SELECT `name` FROM `".$REX['ADDON375']['grouptable']."` 
										WHERE `id` = ".intval($REX['ADDON375']['postget']['newsletter']['users'][0]['send_group']);
						$sql->setQuery($qry); 
						$temp = $sql->getArray();
	
						$REX['ADDON375']['postget']['newsletter']['groupname'] = $temp[0]['name'];
					}
					else
					{
						$REX['ADDON375']['postget']['error'][] = $REX['ADDON375']['I18N']->msg('error_nousersingroup');
						$REX['ADDON375']['newsletter']['status'] = 1;					
					}
				}

				if(isset($REX['ADDON375']['postget']['newsletter']['send']) && !empty($REX['ADDON375']['postget']['newsletter']['users']))
				{
					$REX['ADDON375']['newsletter']['status'] = 2;
					$queries = array();
					
					// first: set up an archive entry for the newsletter
					$archive = array();
					
					// check, if the entry already exists
					$qry = "SELECT `recipients`,`clang`,`id`, `subject`, `textbody`, `htmlbody`
									FROM `".$REX['ADDON375']['archivetable']."` 
									WHERE `setupdate` = '".$REX['ADDON375']['postget']['newsletter']['users'][0]['updatedate']."'";
					$sql->setQuery($qry);
					$archive = $sql->getArray();
					
					$content = array();

					if(!empty($archive))
					{
						$temp = array();
						foreach($archive as $k=>$v)
						{
							if(strpos($v['recipients'],','))
								$v['recipients'] = explode(',',$v['recipients']);
							else
								$v['recipients'] = array();


							$temp[$v['clang']] = array(
								'oldrecipients' => $v['recipients'],
								'recipients' => array(),
								'setupdate' => $REX['ADDON375']['postget']['newsletter']['users'][0]['updatedate'],
								'clang' => $v['clang'],
								'id' => $v['id']
							);
							
							$content[$v['clang']] = array(
								'subject' => $v['subject'],
								'textbody' => base64_decode($v['textbody']),
								'htmlbody' => base64_decode($v['htmlbody'])
							);							
						}
						$archive = $temp; unset($temp);
					}
					else
					{
						$content = rex_a375_readArticle($REX['ADDON375']['postget']['newsletter']['article_id']);
					
						$archive=array();
						foreach($content as $k=>$v)
						{
							
							if(!empty($v))
							{
								$archive[$k] = array(
									'oldrecipients' => array(),
									'recipients' => array(),
									'setupdate' => $REX['ADDON375']['postget']['newsletter']['users'][0]['updatedate'],
									'clang' => $k,
									'id' => '0',
									'groupname'=> $REX['ADDON375']['postget']['newsletter']['groupname'],
									'gid'=> $REX['ADDON375']['postget']['newsletter']['users'][0]['send_group']
								);
							}
						}
					}
				 
					// now send the newsletter
					if(intval($REX['ADDON375']['config']['bcc_per_mail'])==0)
					{
						// if each recipient gets his own newsletter
						foreach($REX['ADDON375']['postget']['newsletter']['users'] as $user) {
				if(!myrex_validEmail($user["email"])) {
					$REX['ADDON375']['postget']['error'][] = $REX['ADDON375']['I18N']->msg('error_invalidemail_deleted', $user["email"]);
					$queries[] = "DELETE FROM `".$REX['ADDON375']['usertable']."` 
							WHERE `id` = ".$user['id'];
					$queries[] = "DELETE FROM`".$REX['ADDON375']['u2gtable']."` 
							WHERE `uid`= ".$user['id'];
					continue;
				}

				// Default lang fallback?
				if($REX['ADDON375']['config']['default_lang'] != "none") {
					$temp = OOArticle::getArticleById($user['article_id'], $user['clang']);
							if(!$temp->isOnline()) {
						if(is_numeric($REX['ADDON375']['config']['default_lang'])) {
							$user['clang'] = $REX['ADDON375']['config']['default_lang'];
						}
						else {
							foreach($REX['CLANG'] as $key => $value) {
								if($value == $REX['ADDON375']['config']['default_lang']) {
									$user['clang'] = $key;
								}
							}
						}
					}
				}

				if(time()-$REX['ADDON375']['started'] < $REX['ADDON375']['maxtimeout'] / 2) {
								if(!isset($content[$user['clang']]))
								{ 
									// setup a new archive entry
									$archive[$user['clang']] = array(
										'oldrecipients' => array(),
										'recipients' => array(),
										'setupdate' => $user['updatedate'],
										'clang' => $user['clang'],
										'id' => '0',
										'groupname'=> $REX['ADDON375']['postget']['newsletter']['groupname'],
										'gid'=> $user['send_group']
									);

									$temp = rex_a375_readArticle($user['article_id'],$user['clang']);

									$content[$user['clang']] = $temp[$user['clang']];
									unset($temp);
								}

								$temp = rex_a375_sendnewsletter( $content[$user['clang']],
																								 $user['email'],
																								 $user['firstname'],
																								 $user['lastname'],
																								 $REX['ADDON375']['config']['default_content'][$user['clang']]['titles'][intval($user['title'])],
																								 $user['clang'],
																								 $REX['ADDON375']['postget']['newsletter']['article_id'],
																								 false,
																								 $user['grad']
																							 );

								if($temp) {
									$queries[] = "UPDATE `".$REX['ADDON375']['usertable']."` 
																SET `article_id`=0, `send_group`=0
																WHERE	`id`='".$user['id']."'";
									
									$archive[$user['clang']]['recipients'][] = $user['email'];
									$archive[$user['clang']]['sentdate'] = time();
									$archive[$user['clang']]['sentby'] = $REX_USER->getValue("login");
									$REX['ADDON375']['postget']['newsletter']['num_of_allusers']--;
								}
							}
							else
							{
# echo "TIMEOUT ENTDECKT!<br />";
								break;
							}
						}
					}
					else
					{
						for($i=0; $i<$REX['ADDON375']['config']['max_mails']; $i++)
						{
# echo strval(time()-$REX['ADDON375']['started'])." / ".strval($REX['ADDON375']['maxtimeout']/2)."<br />";
							if( time()-$REX['ADDON375']['started'] < $REX['ADDON375']['maxtimeout']/2
									&& !empty($REX['ADDON375']['postget']['newsletter']['users'])
								)
							{ 
								$bcc = array(array_shift($REX['ADDON375']['postget']['newsletter']['users']));
								$bcc_ids = array($bcc[count($bcc)-1]['id']);
								$bcc_emails = array($bcc[count($bcc)-1]['email']);

								for($j=0; $j<count($REX['ADDON375']['postget']['newsletter']['users']); $j++)
								{
									
									if($REX['ADDON375']['postget']['newsletter']['users'][$j]['clang'] != $bcc[count($bcc)-1]['clang']
										 || count($bcc)>$REX['ADDON375']['config']['bcc_per_mail']
										 || !isset($REX['ADDON375']['postget']['newsletter']['users'][$j])
										 || empty($REX['ADDON375']['postget']['newsletter']['users'])
										)
									{
										break;
									}
									else
									{
										$bcc[] = array_shift($REX['ADDON375']['postget']['newsletter']['users']);
										$bcc_ids[] = $bcc[count($bcc)-1]['id'];
										$bcc_emails[] = $bcc[count($bcc)-1]['email'];
										$j--;
									}
								}

				// Default lang fallback?
				if($REX['ADDON375']['config']['default_lang'] != "none") {
					$temp = OOArticle::getArticleById($bcc[0]['article_id'], $bcc[0]['clang']);
							if(!$temp->isOnline()) {
						if(is_numeric($REX['ADDON375']['config']['default_lang'])) {
							$bcc[0]['clang'] = $REX['ADDON375']['config']['default_lang'];
						}
						else {
							foreach($REX['CLANG'] as $key => $value) {
								if($value == $REX['ADDON375']['config']['default_lang']) {
									$bcc[0]['clang'] = $key;
								}
							}
						}
					}
				}

								if(!isset($archive[$bcc[0]['clang']]))
								{ 
									// setup a new archive entry
									$archive[$bcc[0]['clang']] = array(
										'oldrecipients' => array(),
										'recipients' => array(),
										'setupdate' => $bcc[0]['updatedate'],
										'clang' => $bcc[0]['clang'],
										'id' => '0',
										'groupname'=> $REX['ADDON375']['postget']['newsletter']['groupname'],
										'gid'=> $bcc[0]['send_group']
									);

									$temp = rex_a375_readArticle($bcc[0]['article_id'],$bcc[0]['clang']);
									$content[$bcc[0]['clang']] = $temp[$bcc[0]['clang']];
									unset($temp);
								}

								$temp = rex_a375_sendnewsletter( $content[$bcc[0]['clang']],
																								 $REX['ADDON375']['config']['sender'],
																								 $REX['ADDON375']['config']['default_content'][$bcc[0]['clang']]['firstname'],
																								 $REX['ADDON375']['config']['default_content'][$bcc[0]['clang']]['lastname'],
																								 $REX['ADDON375']['config']['default_content'][$bcc[0]['clang']]['titles'][intval($REX['ADDON375']['config']['default_content'][$bcc[0]['clang']]['title'])],
																								 $bcc[0]['clang'],
																								 $REX['ADDON375']['postget']['newsletter']['article_id'],
																								 $bcc,
																								 $REX['ADDON375']['config']['default_content'][$bcc[0]['clang']]['grad']
																							 );
								if($temp)
								{
									$queries[] = "UPDATE `".$REX['ADDON375']['usertable']."` 
																SET `article_id`=0, `send_group`=0
																WHERE `id`='".join("' OR `id`='",$bcc_ids)."'";

									$archive[$bcc[0]['clang']]['recipients'] = array_merge($archive[$bcc[0]['clang']]['recipients'],$bcc_emails);
									$archive[$bcc[0]['clang']]['sentdate'] = time();
									$archive[$bcc[0]['clang']]['sentby'] = $REX_USER->getValue("login");

									$REX['ADDON375']['postget']['newsletter']['num_of_allusers']-=count($bcc_ids);
								}
								unset($bcc,$bcc_ids,$j,$temp);
							}
							else
							{
# echo "TIMEOUT ENTDECKT!<br />";
								break;
							}
						}
					}
					
					if(!empty($archive))
					{
						foreach($archive as $archive)
						{
							if(!empty($archive['recipients']))
							{ 
								$archive['recipients'] = array_merge($archive['recipients'],$archive['oldrecipients']);
								$archive['recipients'] = array_unique($archive['recipients']);

								if(intval($archive['id'])<=0)
								{

									$queries[] = "INSERT INTO `".$REX['ADDON375']['archivetable']."`
													SET `subject`='".$content[$archive['clang']]['subject']."',
															`textbody`='".base64_encode($content[$archive['clang']]['textbody'])."',
															`htmlbody`='".base64_encode($content[$archive['clang']]['htmlbody'])."',
															`format`='".$REX['ADDON375']['config']['format']."',
															`groupname`='".$archive['groupname']."',
															`gid`='".$archive['gid']."',
															`setupdate`='".$archive['setupdate']."',
															`clang`='".$archive['clang']."',
															`recipients`='".join(",",$archive['recipients'])."',
															`sentdate` = '".$archive['sentdate']."',
															`sentby` = '".$archive['sentby']."'";
								}
								else
								{
									$queries[] = "UPDATE `".$REX['ADDON375']['archivetable']."`
																SET `recipients`='".join(",",$archive['recipients'])."',
																		`sentdate` = '".$archive['sentdate']."',
																		`sentby` = '".$archive['sentby']."'
																WHERE `setupdate`='".$archive['setupdate']."'
																AND `clang`='".$archive['clang']."'";
								}
							}
						}														
					}
					
					if(!empty($queries))
					{
						foreach($queries as $qry)
						{
							$sql->setQuery($qry);
							# echo "SQL->".$qry."<br />";
						}
						$REX['ADDON375']['postget']['error'][] = $REX['ADDON375']['I18N']->msg('changes_saved');
					}
					
					if($REX['ADDON375']['postget']['newsletter']['num_of_allusers']<=0)
					{
						rex_a375_resetRecipients();
						unset($REX['ADDON375']['postget']['newsletter']);
						$REX['ADDON375']['newsletter']['status'] = 3;
					}
				}
			}
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
			<div class="rex-title-row"><h1><?php print $REX['ADDON375']['I18N']->msg('addon_title'); ?></h1></div>
			<div class="rex-title-row">
<?php include('include/addons/'.$REX['ADDON375']['addon_name'].'/pages/menu.inc.php'); ?>
			</div>
		</div>

<?php
if(!class_exists("rex_mailer"))
	$REX['ADDON375']['postget']['error'][] = $REX['ADDON375']['I18N']->msg('error_no_phpmailer');


if(!empty($REX['ADDON375']['postget']['error']))
{
	echo '<p class="rex-message rex-warning"><span>';
	foreach($REX['ADDON375']['postget']['error'] as $msg)
		echo ''.$msg.'<br />';
	echo '</span></p>';
}

if(class_exists("rex_mailer"))
{
?>
		<p>&nbsp;</p>
		<form action="<?php print $REX['ADDON375']['thispage']; ?>" method="post" name="MULTINEWSLETTER">
			<table class="rex-table">
				<tbody>
					<tr>
						<th class="rex-icon">&nbsp;</th>
						<th class="myrex_middle"><?php print $REX['ADDON375']['I18N']->msg('newsletter_send_step1')?></th>
						<th class="myrex_right">&nbsp;</th>
					</tr>

					<tr class="myrex_spacebelow">
						<td class="rex-icon" valign="top">&nbsp;
							
						</td>
<?php
	if($REX['ADDON375']['newsletter']['status']>0)
	{
?>
						<td class="myrex_middle">
							<ul class="myrex_form">
								<li class="clearfix">
									<label><?php print $REX['ADDON375']['I18N']->msg('newsletter_article')?></label>
									<a href="../index.php?article_id=<?php print $REX['ADDON375']['postget']['newsletter']['article_id']?>&clang=0" target="_blank"><?php print $REX['ADDON375']['postget']['newsletter']['article_name']?></a>
								</li>
							</ul>
						</td>
						<td class="myrex_right">
							<input style="width:100%" type="submit" class="myrex_submit" name="newsletter[reset]" onclick="return myrex_confirm('<?php print $REX['ADDON375']['I18N']->msg('confirm_reset')?>',this.form)" value="<?php print $REX['ADDON375']['I18N']->msg('button_reset')?>" />
						</td>
<?php
	}
	else	
	{
?>
						<td class="myrex_middle">
							<ul class="myrex_form">
							 <li class="clearfix">
									<label><?php print $REX['ADDON375']['I18N']->msg('newsletter_article')?></label>
										<input type="hidden" name="LINK[1]" id="LINK_1" value="<?php print stripslashes($REX['ADDON375']['postget']['newsletter']['article_id'])?>" />
										<input style="margin-right:0.5em" type="text" size="30" name="LINK_NAME[1]" value="<?php if(isset($REX['ADDON375']['postget']['newsletter']['article_name'])) { print stripslashes($REX['ADDON375']['postget']['newsletter']['article_name']); } ?>" id="LINK_1_NAME" readonly="readonly" />
										<a href="#" onclick="openLinkMap('LINK_1', '&clang=0&category_id=1');return false;" tabindex="24"><img src="media/file_open.gif" width="16" height="16" alt="Open Linkmap" title="Open Linkmap" /></a>
						 				<a href="#" onclick="deleteREXLink(1);return false;" tabindex="25"><img src="media/file_del.gif" width="16" height="16" title="Remove Selection" alt="Remove Selection" /></a>
								</li>
						</td>
						<td class="myrex_right">
						</td>
<?php
	}
?>
					</tr>
					
					<tr>
						<th class="rex-icon">&nbsp;</th>
						<th class="myrex_middle"><?php print $REX['ADDON375']['I18N']->msg('newsletter_send_step2')?></th>
						<th class="myrex_right">&nbsp;</th>
					</tr>
<?php
	if($REX['ADDON375']['newsletter']['status']<=0)
	{
		if(empty($REX['ADDON375']['postget']['newsletter']['testemail']))
			$REX['ADDON375']['postget']['newsletter']['testemail'] = $REX['ADDON375']['config']['sender'];
		if(empty($REX['ADDON375']['postget']['newsletter']['testfirstname']))
			$REX['ADDON375']['postget']['newsletter']['testfirstname'] = $REX['ADDON375']['config']['default_content'][$REX['CUR_CLANG']]['firstname'];
		if(empty($REX['ADDON375']['postget']['newsletter']['testlastname']))
			$REX['ADDON375']['postget']['newsletter']['testlastname'] = $REX['ADDON375']['config']['default_content'][$REX['CUR_CLANG']]['lastname'];
		if(empty($REX['ADDON375']['postget']['newsletter']['testtitle']))
			$REX['ADDON375']['postget']['newsletter']['testtitle'] =$REX['ADDON375']['config']['default_content'][$REX['CUR_CLANG']]['title'];
?>
					<tr>
						<td class="rex-icon" valign="top">&nbsp;
							
						</td>
						<td class="myrex_middle">
							<ul>
								<li class="clearfix">
									<label><?php print $REX['ADDON375']['I18N']->msg('newsletter_email')?></label>
									<input type="text" name="newsletter[testemail]" value="<?php print $REX['ADDON375']['postget']['newsletter']['testemail']?>" maxlength="255" />									
								</li>
								<li>&nbsp;</li>
								<li class="clearfix">
									<label><?php print $REX['ADDON375']['I18N']->msg('newsletter_title')?></label>
<?php
	$select = new rex_select;
	$select->setName('newsletter[testtitle]');
	$select->setAttribute('size','1');
	$select->setAttribute('class','myrex_select_small');
	foreach($REX['ADDON375']['config']['default_content'][$REX['CUR_CLANG']]['titles'] as $k=>$v)
		$select->AddOption($v,$k);
	$select->setSelected($REX['ADDON375']['postget']['newsletter']['testtitle']);
	$select->show();
		
?>
								</li>
								<li class="clearfix">
									<label><?php print $REX['ADDON375']['I18N']->msg('newsletter_grad')?></label>
									<input type="text" name="newsletter[testgrad]" value="<?php print stripslashes($REX['ADDON375']['postget']['newsletter']['testgrad'])?>" maxlength="255" />									
								</li>
                                <li class="clearfix">
									<label><?php print $REX['ADDON375']['I18N']->msg('newsletter_firstname')?></label>
									<input type="text" name="newsletter[testfirstname]" value="<?php print stripslashes($REX['ADDON375']['postget']['newsletter']['testfirstname'])?>" maxlength="255" />									
								</li>
								<li class="clearfix">
									<label><?php print $REX['ADDON375']['I18N']->msg('newsletter_lastname')?></label>
									<input type="text" name="newsletter[testlastname]" value="<?php print stripslashes($REX['ADDON375']['postget']['newsletter']['testlastname'])?>" maxlength="255" />									
								</li>
<?php
	if(count($REX['CLANG'])>1)
	{
		echo '
								<li class="clearfix">
									<label>'.$REX['ADDON375']['I18N']->msg('newsletter_language').'</label>
';
		$select = new rex_select;
		$select->setAttribute('size','1');
		$select->setName('newsletter[testlanguage]');
		foreach($REX['CLANG'] as $key=>$value)
			$select->addOption($value,$key);
		
		if(isset($REX['ADDON375']['postget']['newsletter']['testlanguage'])) {
			$select->setSelected($REX['ADDON375']['postget']['newsletter']['testlanguage']);
		}

		$select->setAttribute('class','myrex_select');
		$select->show();

		echo '
								</li>';
	}
	else
		echo '<input type="hidden" name="newsletter[testlanguage]" value="'.$REX['CUR_CLANG'].'" />';									

?>
							</ul>
						</td>
						<td class="myrex_right" rowspan="2">
							<p><?php print $REX['ADDON375']['I18N']->msg('expl_testmail')?></p>
						</td>
					</tr>
					<tr class="myrex_spacebelow">
						<td valign="middle" class="rex-icon">&nbsp;</td>
						<td class="myrex_middle">
							<input style="width:100%" type="submit" class="myrex_submit" name="newsletter[sendtestmail]" onclick="return myrex_confirm('<?php print $REX['ADDON375']['I18N']->msg('confirm_sendtestmail')?>',this.form)" value="<?php print $REX['ADDON375']['I18N']->msg('newsletter_sendtestmail')?>" />

						</td>						
					</tr>
<?php
	} // ENDIF STATUS = 0
	else
	{
?>
					<tr class="myrex_spacebelow">
						<td valign="middle" class="rex-icon">&nbsp;</td>
						<td class="myrex_middle">
							<p><?php print $REX['ADDON375']['I18N']->msg('newsletter_testmailsent')?></p>
						</td>						
						<td class="myrex_right">
<?php if($REX['ADDON375']['newsletter']['status']==1) { ?>
							<p><a href="javascript:location.reload()"><strong><?php print $REX['ADDON375']['I18N']->msg('newsletter_testmailagain')?></a></strong></p>
<?php } ?>
						</td>
					</tr>
<?php
	}
?>

					<tr>
						<th class="rex-icon">&nbsp;</th>
						<th class="myrex_middle"><?php print $REX['ADDON375']['I18N']->msg('newsletter_send_step3')?></th>
						<th class="myrex_right">&nbsp;</th>
					</tr>
<?php
	if($REX['ADDON375']['newsletter']['status']==1)
	{
?>
<input type="hidden" name="newsletter[testlanguage]" value="<?php print stripslashes($REX['ADDON375']['postget']['newsletter']['testlanguage'])?>" />
					<input type="hidden" name="newsletter[article_id]" value="<?php print stripslashes($REX['ADDON375']['postget']['newsletter']['article_id'])?>" />									
					<input type="hidden" name="newsletter[article_name]" value="<?php print stripslashes($REX['ADDON375']['postget']['newsletter']['article_name'])?>" />									
					<input type="hidden" name="newsletter[testmailsent]" value="true" />								

					<tr class="myrex_spacebelow">
						<td class="rex-icon" valign="top">&nbsp;</td>
						<td class="myrex_middle">
							<ul class="myrex_form">
								<li class="clearfix">
									<label><?php print $REX['ADDON375']['I18N']->msg('newsletter_group')?></label>
<?php
	$qry = "SELECT * FROM `".$REX['ADDON375']['grouptable']."` ORDER BY `name`";
	$sql->setQuery($qry);
	$groups = $sql->getArray();

	if(!empty($groups))
	{
		$select = new rex_select;
		$select->setAttribute('size','1');
		$select->setName('newsletter[group]');
		foreach($groups as $group)
			$select->addOption($group['name'],$group['id']);
		
		$select->setAttribute('class','myrex_select');
		$select->setSize(1);
		$select->show();
		
		echo '
								</li>
								<li class="clearfix">
									<label>&nbsp;</label>
									<input type="submit" style="width:299px" class="myrex_submit" name="newsletter[prepare]" onclick="return myrex_confirm(\''.$REX['ADDON375']['I18N']->msg('confirm_prepare').'\',this.form)" value="'.$REX['ADDON375']['I18N']->msg('newsletter_prepare').'" />';
	}
	else
	{
		echo '<input type="hidden" name="prepare[group]" value="-1" />';									
		echo '<p>'.$REX['ADDON375']['I18N']->msg('newsletter_nogroups').'</p>';
	}
?>
								</li>
							</ul>
						</td>
						<td class="myrex_right">
							<p><?php print $REX['ADDON375']['I18N']->msg('expl_prepare')?></p>
						</td>
					</tr>
<?php
	} // ENDIF STATUS==1
	elseif($REX['ADDON375']['newsletter']['status']>1)
	{
?>
					<tr class="myrex_spacebelow">
						<td valign="middle" class="rex-icon">&nbsp;</td>
						<td class="myrex_middle">
							<p><?php print $REX['ADDON375']['I18N']->msg('newsletter_prepared')?></p>
						</td>						
					</tr>
<?php
	}
?>
					<tr>
						<th class="rex-icon">&nbsp;</th>
						<th class="myrex_middle"><?php print $REX['ADDON375']['I18N']->msg('newsletter_send_step4')?></th>
						<th class="myrex_right">&nbsp;</th>
					</tr>
<?php
	if($REX['ADDON375']['newsletter']['status']==2)
	{
?>
<input type="hidden" name="newsletter[testlanguage]" value="<?php print stripslashes($REX['ADDON375']['postget']['newsletter']['testlanguage'])?>" />
					<input type="hidden" name="newsletter[article_id]" value="<?php print stripslashes($REX['ADDON375']['postget']['newsletter']['article_id'])?>" />									
					<input type="hidden" name="newsletter[article_name]" value="<?php print stripslashes($REX['ADDON375']['postget']['newsletter']['article_name'])?>" />									
					<input type="hidden" name="newsletter[prepared]" value="true" />								
					<input type="hidden" name="newsletter[status]" value="2" />								
					<tr>
						<td class="rex-icon" valign="top">&nbsp;</td>
						<td class="myrex_middle">
							<p style="font-size:1.4em"><strong><?php print $REX['ADDON375']['I18N']->msg('newsletter_2send',$REX['ADDON375']['postget']['newsletter']['num_of_allusers'],$REX['ADDON375']['postget']['newsletter']['groupname'])?></strong></p>
<?php
		if(!empty($queries))
		{
			echo '
							<p id="newsletter_reloadinp">'.$REX['ADDON375']['I18N']->msg('newsletter_reloadin').'<br />(<a href="javascript:void(0)" onclick="stopreload()">'.$REX['ADDON375']['I18N']->msg('newsletter_stop_reload').'</a>)</p>';
?>
					<input id="new_newsletter_send" name="new_newsletter[send]" value="true" type="hidden" />
<?php
		// Are there Serverlimits?
		if($REX['ADDON375']['config']['1und1']['active'] == 'yes') {
				// get an array of users that should receive the newsletter
				$qry = "SELECT SQL_CALC_FOUND_ROWS *
						FROM `".$REX['ADDON375']['usertable']."`
						WHERE `article_id`<>0";

			$sql->setQuery($qry);
			$total_users = count($sql->getArray());
			$limit_left = $total_users % $REX['ADDON375']['config']['1und1']['mail_limit'];
		}
?>
					<script type="text/javascript">
					<!--
					//<![CDATA[
				var time_left = <?php if($limit_left == 0) { print ($REX['ADDON375']['config']['1und1']['time_distance']); } else { print 3; } ?>;
				document.getElementById("newsletter_reloadin").innerHTML = time_left;

				function contdownreload()
				{
					document.getElementById("newsletter_reloadin").innerHTML = time_left;
					if(time_left > 0) {
						active = window.setTimeout("contdownreload()", 1000);
					}
					else {
						reload();
					}
					time_left = time_left - 1;
				}

				function reload()
						{
								document.getElementById("newsletter_reloadin").innerHTML="0"
								document.getElementById('newsletter_send').name = 'old_newsletter[send]';
								document.getElementById('new_newsletter_send').name = 'newsletter[send]';
								document.MULTINEWSLETTER.submit()
						}
						function stopreload()
						{
								window.clearTimeout(active);
								document.getElementById("newsletter_reloadinp").innerHTML='';
						}
				
				active = window.setTimeout("contdownreload()", 1000);
					// ]]>
					//-->
			</script>
<?php
		}
?>
						</td>
						<td class="myrex_right" rowspan="2">
							<p><?php print $REX['ADDON375']['I18N']->msg('expl_send')?></p>
							<input style="width:100%" type="submit" class="myrex_submit" name="newsletter[reset]" onclick="return myrex_confirm('<?php print $REX['ADDON375']['I18N']->msg('confirm_reset')?>',this.form)" value="<?php print $REX['ADDON375']['I18N']->msg('button_cancelall')?>" />						
						</td>
					</tr>

					<tr class="myrex_spacebelow">
						<td valign="middle" class="rex-icon">&nbsp;</td>
						<td class="myrex_middle">
							<input style="width:100%" type="submit" class="myrex_submit" id="newsletter_send" name="newsletter[send]" onclick="return myrex_confirm('<?php print $REX['ADDON375']['I18N']->msg('confirm_sendnewsletter')?>',this.form)" value="<?php print $REX['ADDON375']['I18N']->msg('newsletter_send')?>" />
						</td>						
					</tr>
<?php
	} // ENDIF STATUS==2
	elseif($REX['ADDON375']['newsletter']['status']==3)
	{
?>
					<tr>
						<td class="rex-icon" valign="top">&nbsp;</td>
						<td class="myrex_middle">
							<p style="font-size:1.4em"><strong><?php print $REX['ADDON375']['I18N']->msg('newsletter_sent')?></strong></p>
						</td>
						<td class="myrex_right">&nbsp;</td>
					</tr>
<?php
	} // ENDIF STATUS==3
?>

				</tbody>
			</table>
		</form>
<?php
} // if(class_exists("rex_mailer"))
?>
	<!-- END: OUTPUT //-->
<?php
/* ############################## REDAXO FOOTER ############################### */
	include $REX['INCLUDE_PATH'].'/layout/bottom.php';
/* ############################## REDAXO FOOTER ############################### */
?>
	</div>
