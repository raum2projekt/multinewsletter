<?php
$hide_subscription_form = 1;

if($REX['REDAXO']) {
	echo '<p>Multinewsletter Abmeldung</p>';
}
else {
	$style = ' style="color:#ba333f;"'; # Style for a wrong parameter-field
     
	if(!function_exists( 'add_newsletter_user')):
		function add_newsletter_user($vorname, $name, $email) {
			global $REX;
			$sql = new rex_sql();

			$qry = "SELECT `id` FROM `".$REX['TABLE_PREFIX']."375_user`
					WHERE `email` = '".$email ."'";
        	$sql -> setQuery($qry);
			if($sql -> getRows() > 0) {
				$userid = $sql->getValue('id');
				$qry = "UPDATE `".$REX['TABLE_PREFIX']."375_user`
						SET `firstname`='".$vorname . "', `name`='". $name ."',
                        	`status`='1', `updatedate`='".time()."'
                   		WHERE `email` = '". $email ."'";
          		$sql->setQuery($qry);
        	}
        	else {
          		$qry = "INSERT INTO `".$REX['TABLE_PREFIX']."375_user`
						SET `firstname`='". $vorname . "', `lastname`='". $name ."',
                        	`clang` = '". $REX['CUR_CLANG'] ."', `email` = '". $email ."' ,
                        	`status`='1', `updatedate` = '".time()."'";
				$sql->setQuery($qry);
        	}
        	     
			return $sql->getError() == '';
      	}
	endif;
     
     
      if (!function_exists( 'valid_email')):
      function valid_email( $email) {
		if(filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
			return false;
		}
		else {
			return true;
		}
      }
      endif;
     
     
      if (!function_exists( 'remove_newsletter_user')):
      function remove_newsletter_user( $email) {
        global $REX;
       
        $qry = "UPDATE `".$REX['TABLE_PREFIX']."375_user`
                   SET `status`='0' WHERE `email` = '". $email ."'";
        $sql = new rex_sql();
        $sql->setQuery($qry);
     
        return $sql->getError() == '';
      }
      endif;
     
      //------------------------------> Formularauswerten
      $warningstyles = array('surname'=>'','lastname'=>'','email'=>'','signoff_email'=>'');
     
     
      if ( !empty($_POST['subscribe']) && $_POST['subscribe']=='true')
      {
     
        if($_POST['newsletter_firstname']!='' && $_POST['newsletter_name']!='' && valid_email($_POST['newsletter_email']))
          $result = add_newsletter_user($_POST['newsletter_firstname'],$_POST['newsletter_name'],$_POST['newsletter_email']);
        else
          $result = false;
     
        if($_POST['newsletter_firstname']=='')
          $warningstyles['surname'] = $style;
        if($_POST['newsletter_name']=='')
          $warningstyles['lastname'] = $style;
        if(!valid_email($_POST['newsletter_email']))
          $warningstyles['email'] = $style;
         
        if ( $result === true)
        {
           $message .= "REX_VALUE[1]";
           $hide_subscription_form = true;
        } else {
           $message .= "REX_VALUE[2]";
           $hide_subscription_form = false;
        }
      }
      else if( !empty($_POST['subscribe']) && $_POST['subscribe']=='false')
      {
        if(valid_email($_POST['newsletter_email']))
          $result = remove_newsletter_user( $_POST['newsletter_email']);
        else
        {
          $warningstyles['signoff_email'] = $style;
        }
       
        if ($result === true)
        {
          $message .= "REX_VALUE[3]";
          $hide_unsubscription_form = true;
        }
        else
        {
          $message .= "REX_VALUE[4]";
          $hide_unsubscription_form = false;
        }
      }
     
     
      //------------------------------> Formular
      ?>
    <?php
    if($hide_unsubscription_form)
    {   if ($message!="")
       {
       echo '
          <div class="nl-form">
          <h2>REX_VALUE[6]</h2>
          <p class="warning">'. $message .'</p>
          </div>';
          }
    }
    ?>


    <?php if(!$hide_subscription_form) {

      } // ENDIF $hide_subscription_form
      if(!$hide_unsubscription_form) { ?>
      <div class="nl-form">
        <h2>REX_VALUE[6]</h2>
        <form id="abmelden" class="formation" action="<?php print rex_getURL($this->article_id,$REX['CUR_CLANG']); ?>"
              method="post" name="sign_newsletter">
         
          <?php
          if ($message != '') {
          echo '
          <p class="warning">'. $message .'</p>';
          }
          ?>

         
          <input type="hidden" name="article_id" value="REX_ARTICLE_ID"/>
          <input type="hidden" name="clang" value="REX_CLANG_ID"/>
          <input type="hidden" name="subscribe" value="false"/>

            <p>
              <label for="newsletter_email2">E-Mail</label>
              <input type="text" class="text" name="newsletter_email"
                     id="newsletter_email2" value="<?php echo $_POST['newsletter_email']; ?>" />
            </p>
            <p>
              <input type="submit" class="submit" name="unsubscribe_newsletter"
                     value="REX_VALUE[12]" />
            </p>
        </form>
      </div>
    <?php
      }
    }
    unset($message);
    ?> 
