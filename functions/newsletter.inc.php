<?php

if (!function_exists('rex_a375_sendnewsletter'))
{
	function rex_a375_sendnewsletter( $content,
			$to_email='',
			$to_firstname='',
			$to_lastname='',
			$to_title='',
			$clang='0',
			$article_id='0',
			$bcc=false,
			$to_grad='') {

		$return = false;		 

		if(!empty($content) && myrex_validEmail($to_email))
		{ 
			global $REX;
			
			// prepare the rex_mailer Class
			$mail = new rex_mailer();
			if($REX['ADDON375']['config']['format']=='html')
				$mail->IsHTML(true);
			else
				$mail->IsHTML(false);
						
			$mail->CharSet = "utf-8";

			$mail->From = $REX['ADDON375']['config']['sender'];
			$mail->FromName = $REX['ADDON375']['config']['default_content'][$clang]['sendername'];
			$mail->Sender = $REX['ADDON375']['config']['sender'];
			
			if(is_array($bcc) && !empty($bcc))
			{
				// setup the mail for multiple recipients
				foreach($bcc as $b)
					$mail->AddBCC($b['email']);
				
				$mail->AddAddress($REX['ADDON375']['config']['sender'],$REX['ADDON375']['config']['default_content'][$clang]['sendername']);
/*
				$to_title = $REX['ADDON375']['config']['default_content'][$clang]['titles'][intval($REX['ADDON375']['config']['default_content'][$clang]['title'])];
				$to_firstname = $REX['ADDON375']['config']['default_content'][$clang]['firstname'];
				$to_lastname = $REX['ADDON375']['config']['default_content'][$clang]['lastname'];
*/
				$to_title = '';
				$to_firstname = '';
				$to_lastname = '';
				$to_grad = '';

				$link = rtrim($REX['ADDON375']['config']['root'], "/") . str_replace("//", "/", "/". rex_getUrl($REX['ADDON375']['config']['link'],$clang));
			}
			else
			{
				// setup the mail for a single recipient
				$temp_name = '';
				
				if(strtolower($to_firstname)!='anonymous' && trim($to_firstname)!='')
					$temp_name.=trim($to_firstname);
					
				if(strtolower($to_lastname)!='anonymous' && trim($to_lastname)!='')
					$temp_name.=' '.trim($to_lastname);

				if($to_title == '') {
					$to_title = $REX['ADDON375']['config']['default_content'][$clang]['titles'][intval($REX['ADDON375']['config']['default_content'][$clang]['title'])];
				}
				
				if(trim($temp_name)!='')
					$mail->AddAddress($to_email,$to_firstname.' '.$to_lastname);
				else
					$mail->AddAddress($to_email);

				$link = rtrim($REX['ADDON375']['config']['root'], "/") . str_replace("//", "/", "/". rex_getUrl($REX['ADDON375']['config']['link'],$clang,array('unsubscribe'=>rawurlencode($to_email))));
			}
			
			if(intval($article_id)<=0)
				$article_id = $REX['ADDON375']['config']['link'];
				
			$newsletterlink = rtrim($REX['ADDON375']['config']['root'], "/") . str_replace("//", "/", "/". rex_getUrl($article_id, $clang)) ;
			
			$mail->Subject = rex_a375_personalize($content['subject'],$to_email,$to_firstname,$to_lastname,$to_title,$link,$newsletterlink,false,$to_grad);
			if($REX['ADDON375']['config']['format']=='html')
			{
				$mail->Body = rex_a375_personalize($content['htmlbody'],$to_email,$to_firstname,$to_lastname,$to_title,$link,$newsletterlink,true,$to_grad);
				$mail->AltBody = rex_a375_personalize($content['textbody'],$to_email,$to_firstname,$to_lastname,$to_title,$link,$newsletterlink,false,$to_grad);
			}
			else
				$mail->Body = rex_a375_personalize($content['textbody'],$to_email,$to_firstname,$to_lastname,$to_title,$link,$newsletterlink,false,$to_grad);

			$return = $mail->Send();
#			print $mail->ErrorInfo;
#			print_r($mail);
#			echo "<br />-----------------------------------------------<br />";
#			$return = true;
		}
		return $return;
	}
}

if (!function_exists('rex_a375_readArticle'))
{
	function rex_a375_readArticle($article_id = 0, $clang = -1)
	{
		global $REX;
		$content = array();
		
		if(intval($article_id) > 0)
		{
			if(intval($clang) < 0)
				$clang = $REX['CLANG'];
			else
				$clang = array($clang=>'lang');
				
			foreach($clang as $key => $value)
			{
				$save_gg = $REX['GG'];
				$save_rex = $REX['REDAXO'];
	
				$REX['GG'] = true;					// Artikel aus Cache fürs Frontend
				$REX['REDAXO'] = false;

				$temp = OOArticle::getArticleById($article_id, $key);

				$art = new rex_article($article_id, $key);

				if($temp instanceof OOArticle && $temp -> isOnline()) {
					$text = $art -> getArticleTemplate();

					// cut the content after the </html>-Tag and read the cutted end to put it
					// into the text-version
					$text = explode('</html>',$text);
					$text_body = $text[1];
					$html_body = $text[0].'</html>';
					
					$plaintext = '';
					preg_match('/\<\!-- PLAINTEXTMAIL.*?([\s\S]*?)\/\/--\>/m', $text_body,$plaintext);
					if(!empty($plaintext))
						$plaintext = trim($plaintext[1]);
					
					if($plaintext=='')
					{
						$plaintext = '';
						preg_match('/\<\!-- DEFAULTTEXTMAIL.*?([\s\S]*?)\/\/--\>/m', $text_body,$plaintext);
						if(!empty($plaintext))
							$plaintext = trim($plaintext[1]);
					}
			
					if(empty($plaintext))
					{
						// if no plaintext is found, send the standard text
						// if it's not set, try to generate the text from the htmlbody
						if(empty($REX['ADDON375']['config']['default_content'][$key]['plaintext']))
							$plaintext = myrexvars_plaintext($html_body);
						else
							$plaintext = base64_decode($REX['ADDON375']['config']['default_content'][$key]['plaintext']);
						
					}
					$content[$key] = array(
						'htmlbody' => $html_body,
						'textbody' => $plaintext,
						'subject' => $art->getValue('name')
					);
					unset($text,$temp_body,$html_body,$plaintext);
				}
				else
					$content[$key] = false;
				$REX['GG'] = $save_gg;
				$REX['REDAXO'] = $save_rex;
			}

			unset($save_gg,$save_rex,$art,$key,$value);
		}
		return $content;
	}
}

if (!function_exists('rex_a375_personalize'))
{
	function rex_a375_personalize($content='',$to_email=false,$to_firstname='',$to_lastname='',$to_title='',$link='',$newsletterlink='',$html=false,$to_grad='')
	{
		if($html)
		{
			$content = str_replace( "///EMAIL///",$to_email,$content);
			$content = str_replace( "///GRAD///",htmlspecialchars(stripslashes($to_grad),ENT_QUOTES),$content);
			$content = str_replace( "///LASTNAME///",htmlspecialchars(stripslashes($to_lastname),ENT_QUOTES),$content);
			$content = str_replace( "///FIRSTNAME///",htmlspecialchars(stripslashes($to_firstname),ENT_QUOTES),$content);
			$content = str_replace( "///TITLE///",htmlspecialchars(stripslashes($to_title),ENT_QUOTES),$content);
	 //	$content = str_replace( "///LINK///",'<a href="'.$link.'" target="_blank">'.htmlspecialchars(stripslashes($link),ENT_QUOTES).'</a>',$content);
			$content = str_replace( "///LINK///",$link,$content);
			$content = str_replace( "///NEWSLETTERLINK///",$newsletterlink,$content);
		
//		$content = str_replace( "///LINK///",html_entity_decode(stripslashes($link),ENT_QUOTES),$content); 
//	$content = str_replace( "///NEWSLETTERLINK///",html_entity_decode(stripslashes($newsletterlink),ENT_QUOTES),$content);
		}
		else
		{
			$content = str_replace( "///EMAIL///",$to_email,$content);
			$content = str_replace( "///GRAD///",stripslashes($to_grad),$content);
			$content = str_replace( "///LASTNAME///",stripslashes($to_lastname),$content);
			$content = str_replace( "///FIRSTNAME///",stripslashes($to_firstname),$content);
			$content = str_replace( "///TITLE///",stripslashes($to_title),$content);
	 //	 $content = str_replace( "///LINK///",stripslashes($link),$content);
		 // $content = str_replace( "///LINK///",$link,$content);
		//	$content = str_replace( "///NEWSLETTERLINK///",stripslashes($newsletterlink),$content);
		$content = str_replace( "///LINK///",$link,$content);
			$content = str_replace( "///NEWSLETTERLINK///",$newsletterlink,$content);
		
		
		}
		$content = str_replace("	 ", " ", $content);
		$content = str_replace("	", " ", $content);

			return $content;
	}
}

if (!function_exists('rex_a375_resetRecipients'))
{
	function rex_a375_resetRecipients()
	{
		global $REX;
		
		$sql = new rex_sql;
		$sql->setQuery("UPDATE `".$REX['ADDON375']['usertable']."` SET `article_id`=0, `send_group`=0, `updatedate`=".time());
	}
}

if (!function_exists('rex_a375_export_userlist'))
{
	function rex_a375_export_userlist($userlist=false)
	{
		if(is_array($userlist) && !empty($userlist))
		{
			global $REX;
			$return = '';

			$fieldnames = array();
			foreach($userlist[0] as $key=>$value)
				if($key=='email' || $key=='firstname' || $key=='lastname' || $key=='title' || $key=='clang' || $key=='status' || $key=='createip' || $key=='grad')
					$fieldnames[]='"'.$key.'"';
			
			$lines = array(join(';',$fieldnames));
			unset($fieldnames,$key,$value); 
			
			foreach($userlist as $user)
			{
				$t_line = array();
				
				foreach($user as $key=>$value)
					if($key=='email' || $key=='firstname' || $key=='lastname' || $key=='title' || $key=='clang' || $key=='status' || $key=='createip' || $key=='grad')
					{
						if($key=='clang')
							$value = $REX['CLANG'][$value];
							
						$t_line[]='"'.ereg_replace('"','""',$value).'"';
					}

				$lines[] = join(';',$t_line); 
			}

			$lines = join("\n",$lines);

			$return = $lines;
		}
		return $return;
	}
}


if (!function_exists('rex_a375_subscribe'))
{
		function rex_a375_subscribe($userdata=false)
		{
			$return = array('error'=>array(),'msg'=>array());

			if(!empty($userdata) && !empty($userdata['email']))
			{
				global $REX;

				if(file_exists($REX['ADDON375']['configfile']))
					include_once($REX['ADDON375']['configfile']);
				if(myrex_validEmail($userdata['email']))
				{
					$sql = new rex_sql;

			// Nur nach Datensaetzen suchen, die auch als aktive Benutzer angemeldet sind (status = 0)
					$qry = "SELECT `status`,`clang` FROM `".$REX['ADDON375']['usertable']."` 
									WHERE `email`='".$userdata['email']." AND `status` = 1'
									LIMIT 1";
					$sql->setQuery($qry);

					if($sql->getRows() == 0)
					{
				if(file_exists($REX['INCLUDE_PATH'].'/addons/'.$REX['ADDON375']['addon_name'].'/functions/basics.inc.php'))
				{
					require_once($REX['INCLUDE_PATH'].'/addons/'.$REX['ADDON375']['addon_name'].'/functions/basics.inc.php');

					if(file_exists($REX['ADDON375']['configfile']))
						include_once($REX['ADDON375']['configfile']);
					
					if(empty($userdata['groups']) || !is_array($userdata['groups']))
						$return['error'][] = 'nogroup_selected';
					elseif(myrex_validEmail($userdata['email']))
					{ 
						$userdata['grad'] = empty($userdata['grad']) ? '' : stripslashes($userdata['grad']);
						$userdata['firstname'] = empty($userdata['firstname']) ? 'anonymous' : stripslashes($userdata['firstname']);
						$userdata['lastname'] = empty($userdata['lastname']) ? 'anonymous' : stripslashes($userdata['lastname']);
						$userdata['title'] = intval($userdata['title'])<0 || intval($userdata['title'])>1 ? '0' : strval(intval($userdata['title']));
						$userdata['clang'] = intval($userdata['clang'])<0 || intval($userdata['clang'])>count($REX['CLANG']) ? $REX['CUR_CLANG'] : strval(intval($REX['CUR_CLANG']));
						$userdata['status'] = '0';
						$userdata['createip'] = $_SERVER['REMOTE_ADDR'];
						$userdata['key'] = myrex_randomStr(6);
						
						$qry = '';

						// if the user has to confirm the subscription, send an email
						if(intval($REX['ADDON375']['config']['confirmmail'])==1) {
							//  ##### NEU: nur, wenn noch nicht gesendet ##### 
						 	if($_SESSION['newsletter_gesendet']!='1') {
								// set correct title from config file
								$userdata['title'] = $REX['ADDON375']['config']['default_content'][$userdata['clang']]['titles'][$userdata['title']];

								// send the email
								$subscribelink = rtrim($REX['ADDON375']['config']['root'], "/") . str_replace("//", "/", "/". rex_getURL($REX['ADDON375']['config']['link'],$userdata['clang'],array('key'=>rawurlencode($userdata['email'].','.$userdata['key']))));

								$content = rex_a375_personalize(base64_decode($REX['ADDON375']['config']['default_content'][$userdata['clang']]['confirm']),
																								$userdata['email'],
																								$userdata['firstname'],
																								$userdata['lastname'],
																								$userdata['title'],
																								$subscribelink = str_replace("&amp;", "&", $subscribelink),
																								$subscribelink,
																								false,
																								$userdata['grad']);
							
								$mail = new rex_mailer();
								$mail->IsHTML(false);
								$mail->From = $REX['ADDON375']['config']['sender'];
								$mail->FromName = $REX['ADDON375']['config']['default_content'][$userdata['clang']]['sendername'];
								$mail->AddAddress($userdata['email']);
								$mail->Body = $content;
								$mail->Subject = stripslashes($REX['ADDON375']['config']['default_content'][$userdata['clang']]['confirmsubject']);
							
								if(!$mail->send())
								{
									$return['error'][] = 'could_not_send';
								}
								else {
									//  ##### NEU: Session-Wert setzen, um reload zu verhindern ##### 
									$_SESSION['newsletter_gesendet']='1';
									$return['msg'][] = 'confirmation_sent';
								}
							}
							// ##### NEU: Meldung, wenn bereits versendet ##### 
							else
							{         
								$return['error'][] = 'already_confirmed';
								$return['msg'][] = 'confirmation_sent';   
							}
						}
						else
						{
							$userdata['status'] = '1';
							$userdata['key'] = '';

							$return['msg'][] = 'status1';
						}
						
						if(!empty($userdata) && empty($return['error']))
						{
							$qry = "INSERT INTO `".$REX['ADDON375']['usertable']."`
											SET `email` = '".$userdata['email']."',
													`grad` = '".$userdata['grad']."',
													`firstname` = '".$userdata['firstname']."',
													`lastname` = '".$userdata['lastname']."',
													`title` = '".$userdata['title']."',
													`clang` = '".$userdata['clang']."',
													`status` = '".$userdata['status']."',
													`createip` = '". $_SERVER['REMOTE_ADDR'] ."',
													`createdate` = '".time()."',
													`article_id` = '0',
													`send_group` = '0',
													`subscriptiontype` = 'web',
													`key`= '".$userdata['key']."'
											ON DUPLICATE KEY UPDATE
													`grad` = '".$userdata['grad']."',
													`firstname` = '".$userdata['firstname']."',
													`lastname` = '".$userdata['lastname']."',
													`title` = '".$userdata['title']."',
													`clang` = '".$userdata['clang']."',
													`status` = '".$userdata['status']."',
													`updateip` = '". $_SERVER['REMOTE_ADDR'] ."',
													`updatedate` = '". time() ."',
													`article_id` = '0',
													`send_group` = '0',
													`subscriptiontype` = 'web',
													`key`= '".$userdata['key']."'
										 ";
							$sql = new rex_sql;
							$sql->setQuery($qry);
							
						 $userdata['id'] = $sql->getLastId();
							if(intval($userdata['id'])>0)
							{
								$queries = array();
								$queries[] = "DELETE FROM `".$REX['ADDON375']['u2gtable']."` WHERE `uid`='".$userdata['id']."'";
								foreach($userdata['groups'] as $g)
									$queries[] = "INSERT INTO `".$REX['ADDON375']['u2gtable']."` SET `uid`='".$userdata['id']."', `gid`='".$g."'";
								
								foreach($queries as $qry)
								 $sql->setQuery($qry);
							}
							else
								$return['error'][] = 'software_failure';
						}
					}
					else
						$return['error'][] = 'invalid_email';
				}
			}
					else
							$return['error'][] = 'already_subscribed';
				}
				else
					$return['error'][] = 'software_failure';
			}
			else
				$return['error'][] = 'no_userdata';
				
			return $return;
		}
	}

	if (!function_exists('rex_a375_unsubscribe'))
	{
		function rex_a375_unsubscribe($userdata=false)
		{
			$return = array('error'=>array(),'msg'=>array());

			if(!empty($userdata) && !empty($userdata['email']))
			{
				global $REX;
				
				if(file_exists($REX['INCLUDE_PATH'].'/addons/'.$REX['ADDON375']['addon_name'].'/functions/basics.inc.php'))
				{
					require_once($REX['INCLUDE_PATH'].'/addons/'.$REX['ADDON375']['addon_name'].'/functions/basics.inc.php');

					if(file_exists($REX['ADDON375']['configfile']))
						include_once($REX['ADDON375']['configfile']);
					if(myrex_validEmail($userdata['email']))
					{
						$sql = new rex_sql;

						$qry = "SELECT `status`,`clang` FROM `".$REX['ADDON375']['usertable']."` 
										WHERE `email`='".$userdata['email']."'
										LIMIT 1";
						$sql->setQuery($qry);

						if($sql->getRows()>0)
						{
							$status = $sql->getValue('status');
							
							if(intval($status)==0)
								$return['error'][] = 'already_unsubscribed';
							else
							{
/*
 * Ab Version 1.1.7 wird keine Bestaetigungsmail fuer die Abmeldung mehr verschickt
								$userdata['status'] = '1';
								$userdata['key'] =	myrex_randomStr(6);
								$userdata['clang'] = $sql->getValue('clang');
								
								// if the user has to confirm the subscription, send an email
								if(intval($REX['ADDON375']['config']['confirmmail'])==1)
								{ 
									// send the email
									$subscribelink = rtrim($REX['ADDON375']['config']['root'], "/") . str_replace("//", "/", "/". rex_getURL($REX['ADDON375']['config']['link'],$userdata['clang'],array('key'=>rawurlencode($userdata['email'].','.$userdata['key']))); 
									$content = rex_a375_personalize(base64_decode($REX['ADDON375']['config']['default_content'][$userdata['clang']]['confirm']),
																									$userdata['email'],
																									$userdata['firstname'],
																									$userdata['lastname'],
																									$userdata['title'],
																									$subscribelink = str_replace("&amp;", "&", $subscribelink),
																									$subscribelink,
																									false);
									
					$mail = new rex_mailer();
									$mail->IsHTML(false);
									$mail->From = $REX['ADDON375']['config']['sender'];
									$mail->FromName = $REX['ADDON375']['config']['default_content'][$userdata['clang']]['sendername'];
									$mail->AddAddress($userdata['email']);
									$mail->Body = $content;
								 $mail->Subject = stripslashes($REX['ADDON375']['config']['default_content'][$userdata['clang']]['confirmsubject']);
	
									if(!$mail->send())
									{
										$return['error'][] = 'could_not_send';
									}
									else
										$return['msg'][] = 'confirmation_sent';
									
								}
								else
								{
*/
									$userdata['status'] = '0';
									$userdata['key'] = '';

									$return['msg'][] = 'status0';
/*
								}
*/
							
								if(!empty($userdata) && empty($return['error']))
								{
									$qry = "UPDATE `".$REX['ADDON375']['usertable']."`
													SET `status` = '".$userdata['status']."',
															`updatedate` = '".time()."',
															`updateip` = '". $_SERVER['REMOTE_ADDR'] ."',
															`article_id` = '0',
															`send_group` = '0',
													WHERE `email`='".$userdata['email']."'";
									$sql = new rex_sql;
									$sql->setQuery($qry);
								}
							}
						}
						else
							$return['error'][] = 'user_not_found';
					}
					else
						$return['error'][] = 'invalid_email';
				}
				else
					$return['error'][] = 'software_failure';
			}
			else
				$return['error'][] = 'no_userdata';
				
			return $return;
		}
	}



	if (!function_exists('rex_a375_confirm'))
	{
		function rex_a375_confirm($userdata=false)
		{
			$return = array('error'=>array(),'msg'=>array());

			if(!empty($userdata) && !empty($userdata['email']))
			{
				global $REX;
				
				if(file_exists($REX['INCLUDE_PATH'].'/addons/'.$REX['ADDON375']['addon_name'].'/functions/basics.inc.php'))
				{
					require_once($REX['INCLUDE_PATH'].'/addons/'.$REX['ADDON375']['addon_name'].'/functions/basics.inc.php');

					if(file_exists($REX['ADDON375']['configfile']))
						include_once($REX['ADDON375']['configfile']);
					if(myrex_validEmail($userdata['email']))
					{
						$sql = new rex_sql;

						$qry = "SELECT `status`, `key`, `id` FROM `".$REX['ADDON375']['usertable']."` 
										WHERE `email`='".$userdata['email']."'
										LIMIT 1";
						$sql->setQuery($qry);

						if($sql->getRows()>0)
						{
							$key = $sql->getValue('key');
							$userdata['id'] = $sql->getValue('id');
														
							if(empty($key))
							{
								$return['error'][] = 'already_confirmed';
								$return['error'][] = 'status'.$sql->getValue('status');
							}
							elseif($key != $userdata['key'])
							{
								$return['error'][] = 'invalid_key';
							}
							else
							{
								$user['status'] = $sql->getValue('status');
								$user['status'] = intval($user['status'])==1 ? $userdata['status']=0 : $userdata['status']=1;
								
								$qry = "UPDATE `".$REX['ADDON375']['usertable']."`
												SET `status` = '".$userdata['status']."',
								`activationdate`='".time()."',
								`activationip` = '". $_SERVER['REMOTE_ADDR'] ."',
								`key`='',
								`article_id`='0',
								`send_group`='0'
												WHERE `email`='".$userdata['email']."' AND `key`='".$userdata['key']."'
												LIMIT 1";
								$sql->setQuery($qry);
								$return['msg'][] = 'confirmation_successful';
								$return['msg'][] = 'status'.$userdata['status'];

								if($userdata['status']=='0')
								{
									$sql->setQuery("DELETE FROM `".$REX['ADDON375']['u2gtable']."` WHERE `uid`='".$userdata['id']."'");
								}
							}
						}
						else
							$return['error'] = 'user_not_found';
					}
					else
						$return['error'][] = '﻿invalid_email';
				}
				else
					$return['error'][] = 'software_failure';
			}
			else
				$return['error'][] = 'no_userdata';
				
			return $return;
		}
	}
?>
