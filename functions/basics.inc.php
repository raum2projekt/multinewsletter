<?php

if (!function_exists('myrexvars_read_postget_parameters'))
{
  /* Reads all parameters transmitted via POST or GET and puts them into an array */
  function myrexvars_read_postget_parameters()
  {
    $array = array();
    
    foreach($_GET as $k=>$v)
      if(!empty($v))
        $array[$k] = $v;
  
    foreach($_POST as $k=>$v)
      if(!empty($v))
        $array[$k] = $v;

    return $array;
  }
}

if (!function_exists('myrexvars_formatted_date'))
{
  function myrexvars_formatted_date($timestamp,$format=0) {
    // returns a formatted date-string
    if(intval($timestamp)>0) {
      $MONTHS = array("Januar","Februar","März","April","Mai","Juni","Juli","August","September","Oktober","November","Dezember");
      if($format==1) {
        // Gibt ein kurzes Datum aus
        return date("j.n.Y", $timestamp);
      } else if($format==2) {
        return date("H:i", $timestamp);
      } else if($format==3) {
        // Gibt ein kurzes Datum aus
        return date("d.m.y", $timestamp);
      } else if($format==4) {
        // System-datum ausgeben
        return date("Y-m-d H:i:00", $timestamp);
      }else {
        // Gibt ein langes Datum aus.
        $temp = $MONTHS[intval(date("n", $timestamp))-1];
        
        return date("j. ", $timestamp).$temp.date(" Y", $timestamp);
      }
    }
    else return $timestamp;
  }
}

if (!function_exists('myrexvars_date2time'))
{
  function myrexvars_date2time($date) {
    /* Tries to return a formatted date into a UNIX timestamp */
       
    // Read the date and try to seperate the single date-parameters day, month and year
    if(strpos($date,".")) $date = explode(".",$date);
    else if(strpos($date,"-")) $date = explode("-",$date);
    else if(strpos($date,"/")) $date = explode("/",$date);
    else if(intval($date)==0) $date = array(time());
    else $date = array(intval($date));
    
    $finaldate = array('day'=>0,'month'=>0,'year'=>0);
    
    if(count($date)>1)
    {
      if((strlen($date[0])<=2) && ($date[0]<=31)) $finaldate['day'] = intval($date[0]);
      else if(strlen($date[0])==4)
      {
        $finaldate['year']  = intval($date[0]);
        if(isset($date[1])) $finaldate['month'] = intval($date[1]);
        if(isset($date[2])) $finaldate['day']   = intval($date[2]);
      }
      
      if(!$finaldate['month'])
      {
        if((strlen($date[1])<=2) && ($date[1]<=12)) $finaldate['month'] = intval($date[1]);
        else if((strlen($date[1])<=2) && ($date[1]>12))
        {
          if((strlen($date[0])<=2) && ($date[0]<=12)) $finaldate['month'] = intval($date[0]);
          else if(isset($date[2]))
          {
            if((strlen($date[2])<=2) && ($date[2]<=12)) $finaldate['month'] = intval($date[2]);
          }

          $finaldate['day'] = intval($date[1]);
        }
        if(isset($date[2]))
        {
          if(strlen($date[2])==4) $finaldate['year'] = intval($date[2]);
          else if(strlen($date[2])==2) $finaldate['year'] = 2000+intval($date[2]);
          else if($finaldate['year']==0) {
            if(intval($date[2])>50) $finaldate['year'] = date("Y",1900);
            else $finaldate['year'] = date("Y",2000);
            $finaldate['year'] = substr($finaldate['year'],0,strlen($finaldate['year'])-strlen($date[2])).$date[2];
            $finaldate['year'] = intval($finaldate['year']);
          }
        }
      }

      if($finaldate['day']<=0)   $finaldate['day']   = intval(date("j",time()));
      if($finaldate['month']<=0) $finaldate['month'] = intval(date("m",time()));
      if(($finaldate['year']<=1970) || ($finaldate['year']>2030))  $finaldate['year']  = intval(date("Y",time()));
      
      $date = mktime(0,0,0,$finaldate['month'],$finaldate['day'],$finaldate['year']);
      unset($finaldate);
    }
    else if(count($date)<=1) $date = intval($date[0]); 
    // If the date-array has only one field, it probably is already a UNIX timestamp
  
    return $date;
  }
}

if (!function_exists('myrexvars_alphanumeric_string'))
{
  function myrexvars_alphanumeric_string($string,$exeptions=false) {
    // returns an alphanumeric string
    $string = ereg_replace("[ÀÁÂÃÅ]","A",$string);
    $string = ereg_replace("[ÄÆ]","AE",$string);
    $string = ereg_replace("[Ç]","C",$string);
    $string = ereg_replace("[ÈÉË]","E",$string);
    $string = ereg_replace("[ÌÍÎÏ]","I",$string);
    $string = ereg_replace("[Ğ]","D",$string);
    $string = ereg_replace("[Ñ]","N",$string);
    $string = ereg_replace("[ÒÓÔÕ]","O",$string);
    $string = ereg_replace("[ÖØ]","OE",$string);
    $string = ereg_replace("[ÙÚÛ]","U",$string);
    $string = ereg_replace("[Ü]","UE",$string);
    $string = ereg_replace("[İ]","Y",$string);
    $string = ereg_replace("[Ş]","TH",$string);

    $string = ereg_replace("[ß]","ss",$string);

    $string = ereg_replace("[àáâå]","a",$string);
    $string = ereg_replace("[äæ]","ae",$string);
    $string = ereg_replace("[ç]","c",$string);
    $string = ereg_replace("[èéêë]","e",$string);
    $string = ereg_replace("[ìí]","i",$string);
    $string = ereg_replace("[ğ]","d",$string);
    $string = ereg_replace("[ñ]","n",$string);
    $string = ereg_replace("[óòôõ]","o",$string);
    $string = ereg_replace("[öø]","oe",$string);
    $string = ereg_replace("[ùúû]","u",$string);
    $string = ereg_replace("[ü]","ue",$string);
    $string = ereg_replace("[ıÿ]","y",$string);
    $string = ereg_replace("[ş]","th",$string);
    
    if($exeptions) $exeptions = quotemeta($exeptions);
    $exeptions = ereg_replace("\-","",$exeptions);
    $exeptions = ereg_replace(" ","\ ",$exeptions);
    
    $exeptions = "[^a-zA-Z0-9".$exeptions."-]";
    $string = ereg_replace($exeptions,"",$string);
    
    return $string;
  }
}

if (!function_exists('myrexvars_cleanupstring'))
{
  function myrexvars_cleanupstring($data,$html=true,$striptagmarks=true) {
    // Returns clean strings with only an allowed number of characters
    
    global $REX;
    
    $data = stripslashes($data);
    $data = " ".$data;
    
    while(strpos($data,'"')) {
      $stringpos = strpos($data,'"');
      if($stringpos>0)             $ord_left  = ord(substr($data,$stringpos-1,1)); else $ord_left=false;
      if($stringpos<strlen($data)) $ord_right = ord(substr($data,$stringpos+2,1)); else $ord_right=false;
      
      if($ord_left && (
       ($ord_left>=48 && $ord_left<=57) ||
       ($ord_left>=65 && $ord_left<=90) ||
       ($ord_left>=97 && $ord_left<=122) ||
       ($ord_left>=192 && $ord_left<=255) ) ) $data = substr_replace($data,"«",$stringpos,1);
      else if($ord_right && (
       ($ord_right>=48 && $ord_right<=57) ||
       ($ord_right>=65 && $ord_right<=90) ||
       ($ord_right>=97 && $ord_right<=122) ||
       ($ord_right>=192 && $ord_right<=255) ) ) $data = substr_replace($data,"»",$stringpos,1);
      else $data = substr_replace($data,"«",$stringpos,1);
    }
    
    while(strpos($data,"'")) {
      $stringpos = strpos($data,"'");

      if($stringpos>0)             $ord_left  = ord(substr($data,$stringpos-1,1)); else $ord_left=false;
      if($stringpos<strlen($data)) $ord_right = ord(substr($data,$stringpos+2,1)); else $ord_right=false;
      
      if($ord_left && (
       ($ord_left>=48 && $ord_left<=57) ||
       ($ord_left>=65 && $ord_left<=90) ||
       ($ord_left>=97 && $ord_left<=122) ||
       ($ord_left>=192 && $ord_left<=255) ) &&
       ($ord_right==20) ) $data = substr_replace($data,"‹",$stringpos,1);
      else if($ord_right && (
       ($ord_right>=48 && $ord_right<=57) ||
       ($ord_right>=65 && $ord_right<=90) ||
       ($ord_right>=97 && $ord_right<=122) ||
       ($ord_right>=192 && $ord_right<=255) )  &&
       ($ord_left==20) ) $data = substr_replace($data,"›",$stringpos,1);
      else $data = substr_replace($data,"´",$stringpos,1);

    }

    $data = trim($data);
    $data = nl2br($data);
    
    if($striptagmarks) {
      $data = ereg_replace("<","‹",$data);
      $data = ereg_replace(">","›",$data);
      $data = ereg_replace("‹br /›","<br />",$data);
    }

    $data = ereg_replace("[^a-zA-Z0-9»«›‹!%'\(\)\*\+,-\./:;_<>\?£§©®ÀÁÂÃÄÅÆÇÈÉËÌÍÎÏĞÑÒÓÔÕÖØÙÚÛÜİŞßàáâãäåæçèéêëìíğñóòôõöøùúûüışÿ|&´ ]","",$data);
    $data = ereg_replace('<br />',chr(10),$data);
    if($html) {
      $data = htmlentities($data);
      $data = nl2br($data);
      
      if(isset($SI_VARS['allowed_htmltags']))
        $allowed_tags = $SI_VARS['allowed_htmltags'];
      else
        $allowed_tags = array();
        
      foreach($allowed_tags as $tag) {
        while(strpos(" ".strtolower($data),"&lt;".$tag)) {
          $start_b = strpos(strtolower($data),"&lt;".$tag);
          $end_b = strpos(strtolower($data),"&gt;",$start_b);
          
          $temp = substr($data,$start_b,$end_b-$start_b);
          if(strpos(" ".$temp,"&raquo;")) {
            $anzahl = substr_count($temp,"&raquo;");
            $laenge = strlen($temp);
            $temp = ereg_replace("&raquo;",'"',$temp);
            $temp = ereg_replace("&laquo;",'"',$temp);
            $data = substr_replace($data, $temp, $start_b, $laenge);
          } else $anzahl=0;
          $data = substr_replace($data, "<".$tag, $start_b, 4+strlen($tag));
          $data = substr_replace($data, ">", $end_b-3-$anzahl*2*6, 4);
          
        }
        $data = ereg_replace("&lt;/".$tag."&gt;","</".$tag.">",$data);
      }
    }
    
    if(strpos($REX['LANG'],'utf8'))
      $data = utf8_encode($data);
      
    return trim($data);
  }
}

if (!function_exists('myrexvars_truncate'))
{
  function myrexvars_truncate($string, $maxwords = 80, $etc = '...')
  {
    // returns a rex_a160_truncated string

    // delete spaces and linebreaks at the ends of the string
 	  $string = trim($string);
  		
  	// insert a space before </p>
  	// Otherwise the last word of one paragraph and the first word of the next one will be
    // recognized as one word
  	$string = str_replace("</p>", " </p>", $string);
  		
  	// same with <br />
  	$string = str_replace("<br>", "<br />", $string);
  	$string = str_replace("<br />", " <br />", $string);
  		
  	$output = "";
  	$words = explode(" ",$string);
  	$wordsCount = count($words);

  	if ($wordsCount < $maxwords) {
      $etc = '';
      $wEnd = $wordsCount;
    }	else  $wEnd = $maxwords;
  			
  	for ($w=0;$w<$wEnd;$w++) {
  		$output .= $words[$w]." ";
  	}
  		
  	// again: delete spaces and linebreaks at the ends
  	$output = trim($output);
  	
	  $newString = $etc;

  	if(strpos(' '.$string,'p>')) {
      $isCloseParagraph = substr($output,-4);
  	  $newString.= '</p>';
  	}
    
  	if ($isCloseParagraph == '</p>') {
      $output = substr_replace($output,$newString,-4);
    } else {
  		$output .= $newString;
  	}
    return($output);
  }
}


if (!function_exists('myrexvars_gotoPage'))
{
  function myrexvars_gotoPage($url) {
    /* Jumps to a new page with empty POST GET and FILE arrays - prevends duplicate data submitting */
    $_POST  = array();
    $_GET   = array();
    $_FILES = array();
    
    header("Location: ".ereg_replace("&amp;","&",$url));
  }
}

if (!function_exists('myrexvars_include_jscript'))
{
  function myrexvars_include_jscript($filename='')
  {
    $return = '';
    if(file_exists($filename) && filesize($filename)>0)
    {
      $handle = fopen($filename,"r");
      $data = fread($handle,filesize($filename));
      fclose($handle);
      
      $return = '
<script type="text/javascript" language="JavaScript">
<!--
//<![CDATA[
'.$data.'
// ]]>
--> 
</script>
      ';
    }
    unset($handle,$filename);
    return $return;
  }
}
if (!function_exists('myrexvars_include_css'))
{
  function myrexvars_include_css($filename='')
  {
    $return = '';
    if(file_exists($filename) && filesize($filename)>0)
    {
      $handle = fopen($filename,"r");
      $data = fread($handle,filesize($filename));
      fclose($handle);
      
      $return = '
<style type="text/css">
<!--
'.$data.'
--> 
</style>
      ';
    }
    unset($handle,$filename);
    return $return;
  }
}

if (!function_exists('myrexvars_queryvalues'))
{
  function myrexvars_queryvalues($fields=false)
  {
    if(is_array($fields) && count($fields)>0)
    {
      $return = array();
      foreach($fields as $key=>$value)
        $return[] = "`".$key."`='".$value."'";
      
      $return = " SET ".join(",",$return);
    }
    else
      $return = "";
    
    return $return;
  }
}

if (!function_exists('myrexvars_plaintext'))
{
  function myrexvars_plaintext($text,$utf8=true)
  {
    if(!empty($text))
    {
      // erase the html-code
      $text = ereg_replace('><','>      <',$text);
      $text = strip_tags($text);
      $text = stripslashes($text);

      if($utf8) 
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
      else
        $text = html_entity_decode($text, ENT_QUOTES);


      // delete operating chars
      for($i=1; $i<32; $i++)
        $text = ereg_replace(chr($i),' ',$text);
      
      // add correct double quotes
      $text = preg_replace('/"(.*)"/U', '»\1«', $text);
      // add correct single quotes
      $text = preg_replace("/'(.*)'/U", '‚\1’', $text);
      
      // delete double spaces
      $text = preg_replace('/\s{2,}/sm',' ',$text,PREG_SET_ORDER);
      
      $text = trim($text);
    }
    return $text;
  }
}

if (!function_exists('myrexvars_checkMetaField'))
{
  function myrexvars_checkMetaField($fieldname='')
  {
    $return = false;
    if(trim($fieldname)!='')
    {
      global $REX;

      $qry = "SELECT `".$fieldname."` FROM `".$REX['TABLE_PREFIX']."article` LIMIT 1";
      $sql = new rex_sql;
      $sql->setQuery($qry);
      $array = $sql->getArray();
      if(isset($array[0][$fieldname]))
        $return = true;
    }
    
    return $return;
  }
}

if (!function_exists('myrex_validEmail')) {
	function myrex_validEmail($email_to_validate = '') {
		if(filter_var($email_to_validate, FILTER_VALIDATE_EMAIL) === false) {
			return false;
		}
		else {
			return true;
		}
	}
}

if (!function_exists('myrex_randomStr'))
{
  function myrex_randomStr($length)
  {
    $set = array("a","A","b","B","c","C","d","D","e","E","f","F","g","G","h","H","i","I","j","J","k","K","l","L","m","M","n","N","o","O","p","P","q","Q","r","R","s","S","t","T","u","U","v","V","w","W","x","X","y","Y","z","Z","1","2","3","4","5","6","7","8","9","0");
    $str = "";

    for($i=1;$i<=$length;$i++)
    {
	    $ch = rand(0, count($set)-1);
	    $str .= $set[$ch];
	  }
    return $str;
  }
}

if (!function_exists('myrex_setup_clang'))
{
  function myrex_setup_clang($clang=0)
  {
    global $REX;
    
    $clang=strval($clang);

    if(empty($REX['ADDON375']['CLANG'][$clang]))
    {
      $filename = $REX['INCLUDE_PATH'].'/addons/'.$REX['ADDON375']['addon_name'].'/files/clang'.$clang.'.lang';
      if(file_exists($filename))
      {
        $lines = file($filename);
        $temp = array();
        foreach($lines as $line)
        {
          if(strpos($line, '=') !== false)
          {
            $key = trim(substr($line,0,strpos($line,'=')));
            $value = trim(substr($line,strpos($line,'=')+1));
            $temp[$key] = $value;
          }
        }
        if(!empty($temp))
        {
          if(empty($REX['ADDON375']['CLANG']))
            $REX['ADDON375']['CLANG'] = array();
          
          $REX['ADDON375']['CLANG'][$clang] = $temp;
        }
      }
    }
  }
}

if (!function_exists('myrex_clang_msg')) {
	function myrex_clang_msg($msg='',$clang=-1) {
		$return = $msg;

		if(!empty($msg)) {
			global $REX;
      
			if(intval($clang) < 0) {
				$clang = $REX['CUR_CLANG'];
			}

			if(empty($REX['ADDON375']['CLANG'][$clang])) {
				myrex_setup_clang($clang);
			}

			if(!empty($REX['ADDON375']['CLANG'][$clang][$msg])) {
				$return = $REX['ADDON375']['CLANG'][$clang][$msg];
			}
		}
		return $return;
	}
}

?>
