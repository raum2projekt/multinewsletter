<?php
/**
 * Benutzer des MultiNewsletters.
 */
class MultinewsletterUser {
	/**
	 * @var int Unique BenutzerID .
	 */
	var $user_id = 0;
	
	/**
	 * @var String Unique E-Mailadresse.
	 */
	var $email = "";
	
	/**
	 * @var String Akademischer Grad des Benutzers (z.B. Dr. oder Prof.)
	 */
	var $grad = "";
	
	/**
	 * @var String Vorname.
	 */
	var $firstname = "";
	
	/**
	 * @var String Nachname.
	 */
	var $lastname = "";

	/**
	 * @var int Anrede. 0 ist die männliche Anrede, 1 die weibliche
	 */
	var $title = 0;
	
	/**
	 * @var int Redaxo SprachID.
	 */
	var $clang_id = 0;
	
	/**
	 * @var int Status des Abonnements: 0 für inaktiv, 1 für aktiv
	 */
	var $status = 0;

	/**
	 * @var int[] Array mit ID's der abonnierten Newsletter Gruppen
	 */
	var $group_ids = array();

	/**
	 * @var boolean Steht der Benutzer in der aktuellen Warteschlange des zu
	 * sendenden Newsletters.
	 */
	var $send_archive_id = 0;

	/**
	 * @var int Unix Datum der Erstellung des Datensatzes in der Datenbank.
	 */
	var $createdate = 0;
	
	/**
	 * @var String IP Adresse von der aus der Datensatz erstellt wurde.
	 */
	var $createIP = "0.0.0.0";

	/**
	 * @var int Unixdatum der Bestätigung des Abonnements
	 */
	var $activationdate = 0;
	
	/**
	 * @var String IP Adresse von der aus die Bestätigung vorgenommen wurde.
	 */
	var $activationIP = "0.0.0.0";

	/**
	 * @var int Unixdatum der letzten Aktualisierung des Datensatzes
	 */
	var $updatedate = 0;
	
	/**
	 * @var String IP Adresse von der die letzte Aktualisierung vorgenommen wurde.
	 */
	var $updateIP = "0.0.0.0";

	/**
	 * @var String Art der Anmeldung zum Newsletter: web, import oder backend
	 */
	var $subscriptiontype = "";
	
	/**
	 * @var String 6-stelliger Anmeldeschlüssel für die Bestätigung
	 */
	var $activationkey = 0;
	
	/**
	 * @var String Tabellenpräfix von Redaxo
	 */
	var $table_prefix = "rex_";

	/**
	 * Stellt die Daten des Benutzers aus der Datenbank zusammen.
	 * @param int $user_id UserID aus der Datenbank.
	 * @param String $table_prefix Redaxo Tabellen Praefix ($REX['TABLE_PREFIX'])
	 */
	 public function __construct($user_id, $table_prefix = "rex_") {
		$this->table_prefix = $table_prefix;
		$this->user_id = $user_id;
		
		if($user_id > 0) {
			$query = "SELECT * FROM ". $this->table_prefix ."375_user "
					."WHERE user_id = ". $this->user_id ." "
					."LIMIT 0, 1";
			$result = new rex_sql();
			$result->setQuery($query);
			$num_rows = $result->getRows();

			if($num_rows > 0) {
				$this->email = trim($result->getValue("email"));
				$this->grad = stripslashes($result->getValue("grad"));
				$this->firstname = stripslashes($result->getValue("firstname"));
				$this->lastname = stripslashes($result->getValue("lastname"));
				$this->title = $result->getValue("title");
				$this->clang_id = $result->getValue("clang_id");
				$this->status = $result->getValue("status");
				$this->group_ids = preg_grep('/^\s*$/s', explode("|", $result->getValue("group_ids")), PREG_GREP_INVERT);
				$this->send_archive_id = $result->getValue("send_archive_id");
				$this->createdate = $result->getValue("createdate");
				$this->createIP = htmlspecialchars_decode($result->getValue("createip"));
				$this->activationdate = $result->getValue("activationdate");
				$this->activationIP = htmlspecialchars_decode($result->getValue("activationip"));
				$this->updatedate = $result->getValue("updatedate");
				$this->updateIP = $result->getValue("updateip");
				$this->subscriptiontype = $result->getValue("subscriptiontype");
				$this->activationkey = htmlspecialchars_decode($result->getValue("activationkey"));
			}
		}
	}
	
	/**
	 * Erstellt einen ganz neuen Nutzer.
	 * @param String $email E-Mailadresse des Nutzers
	 * @param int $title Anrede (0 = männlich, 1 = weiblich)
	 * @param String $grad Akademischer Grad des Nutzers
	 * @param String $firstname Vorname des Nutzers
	 * @param String $lastname Nachname des Nutzers
	 * @param int $clang_id Redaxo SprachID des Nutzers
	 * @param String $table_prefix Redaxo Tabellen Praefix ($REX['TABLE_PREFIX'])
	 * @return MultinewsletterUser Intialisiertes MultinewsletterUser Objekt.
	 */
	public static function factory($email, $title, $grad, $firstname, $lastname, $clang_id, $table_prefix = "rex_") {
		$user = new MultinewsletterUser(0, $table_prefix);
		$user->table_prefix = $table_prefix;
		$user->email = $email;
		$user->title = $title;
		$user->grad = $grad;
		$user->firstname = $firstname;
		$user->lastname = $lastname;
		$user->clang_id = $clang_id;
		$user->status = 1;
		$user->createdate = time();
		$user->createIP = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);
		
		return $user;
	}
	
	/**
	 * Aktiviert den Benutzer, d.h. der Activationkey wird gelöscht und der Status
	 * auf aktiv gesetzt.
	 */
	public function activate() {
		$this->activationkey = 0;
		$this->activationdate = time();
		$this->activationIP = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);
		$this->status = 1;
		$this->save();

		$this->sendAdminNoctificationMail("subscribe");
	}
	
	/**
	 * Löscht den Benutzer aus der Datenbank.
	 */
	public function delete() {
		$query = "DELETE FROM ". $this->table_prefix ."375_user WHERE user_id = ". $this->user_id;
		$result = new rex_sql();
		$result->setQuery($query);		
	}
	
	/**
	 * Holt einen neuen Benutzer anhand der E-Mailadresse aus der Datenbank.
	 * @param String $email E-Mailadresse des Nutzers
	 * @param String $table_prefix Redaxo Tabellen Praefix ($REX['TABLE_PREFIX'])
	 * @return MultinewsletterUser Intialisiertes MultinewsletterUser Objekt.
	 */
	public static function initByMail($email, $table_prefix = "rex_") {
		$user = new MultinewsletterUser(0, $table_prefix);
		$user->table_prefix = $table_prefix;
		$user->email = $email;
		
		if($user->email != "") {
			$query = "SELECT * FROM ". $user->table_prefix ."375_user "
					."WHERE email = '". trim($user->email) ."'";
			$result = new rex_sql();
			$result->setQuery($query);
			$num_rows = $result->getRows();

			if($num_rows > 0) {
				$user->user_id = $result->getValue("user_id");
				$user->grad = $result->getValue("grad");
				$user->firstname = $result->getValue("firstname");
				$user->lastname = $result->getValue("lastname");
				$user->title = $result->getValue("title");
				$user->clang_id = $result->getValue("clang_id");
				$user->status = $result->getValue("status");
				$user->group_ids = preg_grep('/^\s*$/s', explode("|", $result->getValue("group_ids")), PREG_GREP_INVERT);
				$user->send_archive_id = $result->getValue("send_archive_id");
				$user->createdate = $result->getValue("createdate");
				$user->createIP = $result->getValue("createip");
				$user->activationdate = $result->getValue("activationdate");
				$user->activationIP = $result->getValue("activationip");
				$user->updatedate = $result->getValue("updatedate");
				$user->updateIP = $result->getValue("updateip");
				$user->subscriptiontype = $result->getValue("subscriptiontype");
				$user->activationkey = $result->getValue("activationkey");
			}
			return $user;
		}
		return FALSE;
	}
	
	/**
	 * Personalisiert einen Text für die Aktivierungsmail
	 * @global mixed $REX Redaxo Variable mit Einstellungen.
	 * @param String $content Zu personalisierender Inhalt
	 * @return String Personalisierter String.
	 */
	private function personalize($content) {
		global $REX;
		$content = stripslashes($content);
		$content = str_replace( "///EMAIL///", $this->email, $content);
		$content = str_replace( "+++EMAIL+++", $this->email, $content);
		$content = str_replace( "///GRAD///", htmlspecialchars(stripslashes($this->grad), ENT_QUOTES), $content);
		$content = str_replace( "+++GRAD+++", htmlspecialchars(stripslashes($this->grad), ENT_QUOTES), $content);
		$content = str_replace( "///LASTNAME///", htmlspecialchars(stripslashes($this->lastname), ENT_QUOTES), $content);
		$content = str_replace( "+++LASTNAME+++", htmlspecialchars(stripslashes($this->lastname), ENT_QUOTES), $content);
		$content = str_replace( "///FIRSTNAME///", htmlspecialchars(stripslashes($this->firstname), ENT_QUOTES), $content);
		$content = str_replace( "+++FIRSTNAME+++", htmlspecialchars(stripslashes($this->firstname), ENT_QUOTES), $content);
		$content = str_replace( "///TITLE///", htmlspecialchars(stripslashes($REX['ADDON']['multinewsletter']['settings']['lang'][$this->clang_id]["title_". $this->title]), ENT_QUOTES), $content);
		$content = str_replace( "+++TITLE+++", htmlspecialchars(stripslashes($REX['ADDON']['multinewsletter']['settings']['lang'][$this->clang_id]["title_". $this->title]), ENT_QUOTES), $content);
		$content = preg_replace('/ {2,}/', ' ', $content);
		
		$subscribe_link = $REX['SERVER'] . trim(rex_getUrl($REX['ADDON']['multinewsletter']['settings']['link'],
			$this->clang_id, array('activationkey' => $this->activationkey, 'email' => rawurldecode($this->email))), "/");
		$content = str_replace( "///NEWSLETTERLINK///", $subscribe_link, $content);
		$content = str_replace( "+++AKTIVIERUNGSLINK+++", $subscribe_link, $content);

		return $content;
	}
	
	/**
	 * Aktualisiert den Benutzer in der Datenbank.
	 */
	public function save() {
		$groups = "";
		if(count($this->group_ids) > 0) {
			$groups = "|". implode("|", $this->group_ids) ."|";
		}
		$email = $this->email;
		if(filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
			$email = "";
		}
		$createdate = "0";
		if($this->createdate > 0) {
			$createdate = $this->createdate;
		}
		$activationdate = "0";
		if($this->activationdate > 0) {
			$activationdate = $this->activationdate;
		}
		$query = $this->table_prefix .'375_user SET '
				.'email = "'. trim($this->email) .'", '
				.'grad = "'. addslashes($this->grad) .'", '
				.'firstname = "'. addslashes($this->firstname) .'", '
				.'lastname = "'. addslashes($this->lastname) .'", '
				.'title = '. $this->title .', '
				.'clang_id = '. $this->clang_id .', '
				.'`status` = '. $this->status .', '
				.'group_ids = "'. $groups .'", '
				.'send_archive_id = '. $this->send_archive_id .', '
				.'createdate = '. $createdate .', '
				.'createip = "'. htmlspecialchars($this->createIP) .'", '
				.'activationdate = '. $activationdate .', '
				.'activationip = "'. htmlspecialchars($this->activationIP) .'", '
				.'updatedate = '. time() .', '
				.'updateip = "'. filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP) .'", '
				.'subscriptiontype = "'. $this->subscriptiontype .'", '
				.'activationkey = "'. htmlspecialchars($this->activationkey) .'" ';
		if($this->user_id == 0) {
			$query = "INSERT INTO ". $query;
		}
		else {
			$query = "UPDATE ". $query ." WHERE user_id = ". $this->user_id;
		}

		$result = new rex_sql();
		$result->setQuery($query);		
	}
	
	/**
	 * Sendet eine Mail mit Aktivierungslink an den Abonnenten
	 * @param String $sender_mail Absender der Mail
	 * @param String $sender_name Bezeichnung des Absenders der Mail
	 * @param String $subject Betreff der Mail
	 * @param String $body Inhalt der Mail
	 * @return boolean true, wenn erfolgreich versendet, sonst false
	 */
	public function sendActivationMail($sender_mail, $sender_name, $subject, $body) {
		if(!empty($body) && filter_var($this->email, FILTER_VALIDATE_EMAIL) !== false && filter_var($sender_mail, FILTER_VALIDATE_EMAIL) !== false) { 
			$mail = new rex_mailer();
			$mail->IsHTML(true);
			$mail->CharSet = "utf-8";
			$mail->From = $sender_mail;
			$mail->FromName = $sender_name;
			$mail->Sender = $sender_mail;
				
			if(trim($this->firstname) != '' && trim($this->lastname) != '') {
				$mail->AddAddress($this->email, trim($this->firstname) .' '. trim($this->lastname));
			}
			else {
				$mail->AddAddress($this->email);
			}
	
			$mail->Subject = $this->personalize($subject);
			$mail->Body = $this->personalize($body);
			return $mail->Send();
		}
		else {
			return false;
		}
	}
	
	/**
	 * Sendet eine Mail an den Admin als Hinweis, dass ein Benutzerstatus
	 * geändert wurde.
	 * @param String $type entweder "subscribe" oder "unsubscribe"
	 * @return boolean true, wenn erfolgreich versendet, sonst false
	 */
	public function sendAdminNoctificationMail($type) {
		global $REX;
		if(filter_var($REX['ADDON']['multinewsletter']['settings']['subscribe_meldung_email'], FILTER_VALIDATE_EMAIL) !== false) { 
			$mail = new rex_mailer();
			$mail->IsHTML(true);
			$mail->CharSet = "utf-8";
			$mail->From = $REX['ADDON']['multinewsletter']['settings']['sender'];
			$mail->FromName = $REX['ADDON']['multinewsletter']['settings']['lang'][$REX['CUR_CLANG']]['sendername'];
			$mail->Sender = $REX['ADDON']['multinewsletter']['settings']['sender'];
				
			$mail->AddAddress($REX['ADDON']['multinewsletter']['settings']['subscribe_meldung_email']);
	
			if($type == "subscribe") {
				$mail->Subject = "Neue Anmeldung zum Newsletter";
				$mail->Body = "Neue Anmeldung zum Newsletter: ". $this->email;
			}
			else {
				$mail->Subject = "Abmeldung vom Newsletter";
				$mail->Body = "Abmeldung vom Newsletter: ". $this->email;
			}
			return $mail->Send();
		}
		else {
			return false;
		}
	}
	
	/**
	 * Meldet den Benutzer vom Newsletter ab.
	 * @var action String mit auszuführender Aktion
	 */
	public function unsubscribe($action = "delete") {
		if($action == "delete") {
			$this->delete();
		}
		else {
			// $action = "status_unsubscribed"
			$this->status = 2;
			$this->save();
		}
		
		$this->sendAdminNoctificationMail("unsubscribe");
	}
}

/**
 * Liste Benutzer des MultiNewsletters.
 */
class MultinewsletterUserList {
	/**
	 * @var MultinewsletterUser[] Array mit Benutzerobjekten.
	 */
	var $users = array();
	
	/**
	 * @var String Tabellenpräfix von Redaxo
	 */
	var $table_prefix = "rex_";

	/**
	 * Stellt die Daten des Benutzers aus der Datenbank zusammen.
	 * @param Array $user_ids Array mit UserIds aus der Datenbank.
	 * @param String $table_prefix Redaxo Tabellen Praefix ($REX['TABLE_PREFIX'])
	 */
	 public function __construct($user_ids, $table_prefix = "rex_") {
		$this->table_prefix = $table_prefix;

		foreach($user_ids as $user_id) {
			$this->users[] = new MultinewsletterUser($user_id, $table_prefix);
		}
	}
	
	/**
	 * Exportiert die Benutzerliste als CSV und sendet das Dokument als CSV.
	 * @param String $table_prefix Redaxo Tabellen Praefix ($REX['TABLE_PREFIX'])
	 */
	public static function countAll($table_prefix = "rex_") {
		$query = "SELECT COUNT(*) as total FROM ". $table_prefix ."375_user ";
		$result = new rex_sql();
		$result->setQuery($query);

		return $result->getValue("total");
	}
	
	/**
	 * Exportiert die Benutzerliste als CSV und sendet das Dokument als CSV.
	 */
	public function exportCSV() {
		$spalten = array('email', 'grad', 'title', 'firstname', 'lastname',
			'clang_id', 'status', 'group_ids', 'createdate', 'createip',
			'activationdate', 'activationip', 'updatedate', 'updateip',
			'subscriptiontype');
		$lines = array(implode(';',$spalten));
	
		foreach($this->users as $user) {
			$groups = "";
			if(count($user->group_ids) > 0) {
				$groups = "|". implode("|", $user->group_ids) ."|";
			}
			$line = array();
			$line[] = $user->email;
			$line[] = $user->grad;
			$line[] = $user->title;
			$line[] = $user->firstname;
			$line[] = $user->lastname;
			$line[] = $user->clang_id;
			$line[] = $user->status;
			$line[] = $groups;
			$line[] = $user->createdate;
			$line[] = $user->createIP;
			$line[] = $user->activationdate;
			$line[] = $user->activationIP;
			$line[] = $user->updatedate;
			$line[] = $user->updateIP;
			$line[] = $user->subscriptiontype;

			$lines[] = implode(';', $line); 
		}

		$content = implode("\n", $lines);

		header("Cache-Control: public");
		header("Content-Description: File Transfer");
		header('Content-disposition: attachment; filename=multinewsletter_user.csv');
		header("Content-Type: application/csv");
		header("Content-Transfer-Encoding: binary");
		header('Content-Length: '. strlen($content));
		print($content);
		exit;
	}
}
