<?php
/**
 * MultiNewsletter Newletter (in der Datenbank als Newsletter Archiv).
 *
 * @author Tobias Krais
 */
class MultinewsletterNewsletter {
	/**
	 * @var int Unique ArchivID .
	 */
	var $archive_id = 0;
	
	/**
	 * @var int Sprache des Newsletters (jede Sprache bekommt einen eigenen
	 * Eintrag in der Datenbank
	 */
	var $clang_id = 0;
	
	/**
	 * @var String Betreff des Newsletters
	 */
	var $subject = "";
	
	/**
	 * @var String Body des Newsletters (HTML)
	 */
	var $htmlbody = "";
	
	/**
	 * @var String[] E-Mailadressen der Empfänger des Newsletters.
	 */
	var $recipients = array();

	/**
	 * @var int[] IDs der Gruppen an die der Newsletter versandt wurde.
	 */
	var $group_ids = array();

	/**
	 * @var String E-Mailadresse des Absenders
	 */
	var $sender_email = "";

	/**
	 * @var String Name des Absenders
	 */
	var $sender_name = "";

	/**
	 * @var int UNIX Timestamp mit dem Zeitstempel, wann der Newsletter erstellt
	 * wurde.
	 */
	var $setupdate = 0;

	/**
	 * @var int UNIX Timestamp mit dem Zeitstempel, wann der Newsletter endgültig
	 * versandt wurde.
	 */
	var $sentdate = 0;

	/**
	 * @var int Redaxo Benutzername des Nutzers, der den Newsletter versendet hat.
	 */
	var $sentby = "";

	/**
	 * Stellt die Daten des Archivs aus der Datenbank zusammen.
	 * @param int $archive_id Archiv ID aus der Datenbank.
	 */
	 public function __construct($archive_id) {
		$this->archive_id = $archive_id;
		
		if($archive_id > 0) {
			$query = "SELECT * FROM ". rex::getTablePrefix() ."375_archive "
					."WHERE archive_id = ". $this->archive_id ." "
					."LIMIT 0, 1";
			$result = rex_sql::factory();
			$result->setQuery($query);
			$num_rows = $result->getRows();

			if ($num_rows > 0) {
				$this->clang_id = $result->getValue("clang_id");
				$this->subject = $result->getValue("subject");
				$this->htmlbody = base64_decode($result->getValue("htmlbody"));
				$this->recipients = preg_grep('/^\s*$/s', explode(",", $result->getValue("recipients")), PREG_GREP_INVERT);
				$this->group_ids = preg_grep('/^\s*$/s', explode("|", $result->getValue("group_ids")), PREG_GREP_INVERT);
				$this->sender_email = $result->getValue("sender_email");
				$this->sender_name = $result->getValue("sender_name");
				$this->setupdate = $result->getValue("setupdate");
				$this->sentdate = $result->getValue("sentdate");
				$this->sentby = $result->getValue("sentby");
			}
		}
	}
	
	/**
	 * Stellt die Daten des Archivs aus der Datenbank zusammen.
	 * @param int $article_id Artikel ID aus Redaxo.
	 * @param type $clang_id Sprachen ID aus Redaxo
	 * @return MultinewsletterNewsletter Intialisiertes Multinewsletter Objekt.
	 */
	public static function factory($article_id, $clang_id) {
		$newsletter = new MultinewsletterNewsletter(0, rex::getTablePrefix());
		
		// init Mailbody und Betreff
		$newsletter->readArticle($article_id, $clang_id);
		
		return $newsletter;
	}
	
	/**
	 * Löscht das Archiv aus der Datenbank.
	 */
	public function delete() {
		$query = "DELETE FROM ". rex::getTablePrefix() ."375_group WHERE archive_id = ". $this->archive_id;
		$result = rex_sql::factory();
		$result->setQuery($query);		
	}
	
	/**
	 * Liest einen Redaxo Artikel aus.
	 * @param type $article_id ArtikelID aus Redaxo
	 * @param type $clang_id Sprachen ID aus Redaxo
	 * @return boolean
	 */
	private function readArticle($article_id, $clang_id) {
		$article = rex_article::get($article_id, $clang_id);
		$article_content = new rex_article_content($article_id, $clang_id);

		if($article instanceof rex_article && $article->isOnline()) {
			// Link zur Onlineversion des Newsletters setzen
			$newsletter_link = rex::getServer() . rex_getUrl($article_id, $clang_id);
			$content = str_replace("+++NEWSLETTERLINK+++", $newsletter_link, $article_content->getArticleTemplate());
			
			$this->clang_id = $clang_id;
			$this->htmlbody = $content;
			$this->subject = $article->getValue('name');
		}
	}

	/**
	 * Aktualisiert den Newsletter in der Datenbank.
	 */
	public function save() {
		$groups = "";
		if(count($this->group_ids) > 0) {
			$groups = "|". implode("|", $this->group_ids) ."|";
		}
		$recipients = "";
		if(count($this->recipients) > 0) {
			$recipients = implode(",", $this->recipients);
		}
		if($this->setupdate == 0) {
			$this->setupdate = time();
		}
		$query = rex::getTablePrefix() ."375_archive SET "
				."clang_id = '". $this->clang_id ."', "
				."subject = '". htmlspecialchars($this->subject) ."', "
				."htmlbody = '". base64_encode($this->htmlbody) ."', "
				."group_ids = '". $groups ."', "
				."recipients = '". $recipients ."', "
				."sender_email = '". $this->sender_email ."', "
				."sender_name = '". htmlspecialchars($this->sender_name) ."', "
				."setupdate = ". $this->setupdate .", "
				."sentdate = ". $this->sentdate .", "
				."sentby = '". $this->sentby ."'";
		if($this->archive_id == 0) {
			$query = "INSERT INTO ". $query;
		}
		else {
			$query = "UPDATE ". $query ." WHERE archive_id = ". $this->archive_id;
		}
		$result = rex_sql::factory();
		$result->setQuery($query);
		
		if($this->archive_id == 0) {
			$this->archive_id = $result->getLastId();
		}
	}
	
	/**
	 * Sendet eine Mail des Newsletters an übergebenen Nutzer
	 * @param MultinewsletterUser $user Empfänger der Mail
	 * @return boolean true, wenn erfolgreich versendet, sonst false
	 */
	private function send($user) {
		if(!empty($this->htmlbody) && filter_var($user->email, FILTER_VALIDATE_EMAIL) !== false) { 
			$mail = new rex_mailer();
			$mail->IsHTML(true);
			$mail->CharSet = "utf-8";
			$mail->From = trim($this->sender_email);
			$mail->FromName = $this->sender_name;
			$mail->Sender = trim($this->sender_email);
				
			if(trim($user->firstname) != '' && trim($user->lastname) != '') {
				$mail->AddAddress(trim($user->email), trim($user->firstname) .' '. trim($user->lastname));
			}
			else {
				$mail->AddAddress(trim($user->email));
			}
	
			$mail->Subject = $this->personalize($this->subject, $user);
			$mail->Body = $this->personalize($this->htmlbody, $user);
			return $mail->Send();
		}
		else {
			return false;
		}
	}

	/**
	 * Sendet eine Mail des Newsletters an übergebenen Nutzer und fügt ihn zu
	 * den gesendeten Empfängern hinzu.
	 * @param MultinewsletterUser $user Empfänger der Mail
	 * @return boolean true, wenn erfolgreich versendet, sonst false
	 */
	public function sendNewsletter($user) {
		if($this->send($user)) {
			$this->recipients[] = $user->email;
			$this->sentdate = time();
			$this->sentby = rex::getUser()->getLogin();
			$this->save();
			return true;
		}

		return false;
	}
	
	/**
	 * Sendet eine Testmail des Newsletters
	 * @param MultinewsletterUser $testuser Empfänger der Testmail
	 * @return boolean true, wenn erfolgreich versendet, sonst false
	 */
	public function sendTestmail($testuser) {
		return $this->send($testuser);
	}

	/**
	 * Personalisiert einen String
	 * @param String $content Zu personalisierender Inhalt
	 * @param MultinewsletterUser $user Empfänger der Testmail
	 * @return String Personalisierter String.
	 */
	private function personalize($content, $user) {
		$addon = rex_addon::get("multinewsletter");

		$content = str_replace("+++EMAIL+++", $user->email, $content);
		$content = str_replace("+++GRAD+++", htmlspecialchars(stripslashes($user->grad), ENT_QUOTES), $content);
		$content = str_replace("+++LASTNAME+++", htmlspecialchars(stripslashes($user->lastname), ENT_QUOTES), $content);
		$content = str_replace("+++FIRSTNAME+++", htmlspecialchars(stripslashes($user->firstname), ENT_QUOTES), $content);
		$content = str_replace("+++TITLE+++", htmlspecialchars(stripslashes($addon->getConfig('lang_'. $user->clang_id ."_title_". $user->title)), ENT_QUOTES), $content);
		$content = preg_replace('/ {2,}/', ' ', $content);
		
		$unsubscribe_link = rex::getServer() . rex_getUrl($addon->getConfig('link_abmeldung'), $this->clang_id, array('unsubscribe' => $user->email));
		return str_replace("+++ABMELDELINK+++", $unsubscribe_link, $content);
	}
}

/**
 * MultiNewsletter Newletter (noch zu versenden)
 *
 * @author Tobias Krais
 */
class MultinewsletterNewsletterManager {
	/**
	 * @var Array Archiv Objekte des Newsletters. ACHTUNG: der Index im Array
	 * muss die Archiv ID sein.
	 */
	var $archives = array();
	
	/**
	 * @var Array Empfänger des Newsletters.
	 */
	var $recipients = array();

	/**
	 * @var int Anzahl ausstehender Newsletter Mails
	 */
	var $remaining_users = 0;

	/**
	 * Stellt die Daten des Newsletters aus einem Archiv zusammen.
	 * @param int $numberMails Anzahl der Mails für den nächsten Versandschritt.
	 */
	public function __construct($numberMails = 0) {
		$this->initArchivesToSend();
		$this->initRecipients($numberMails);
	}
	
	/**
	 * Initialisiert die Newsletter Archive, die zum Versand ausstehen.
	 */
	private function initArchivesToSend() {
		$query = "SELECT send_archive_id FROM ". rex::getTablePrefix() ."375_user "
			."WHERE send_archive_id > 0 "
			."GROUP BY send_archive_id";
		$result = rex_sql::factory();
		$result->setQuery($query);		
		$num_rows = $result->getRows();

		for($i = 0; $num_rows > $i; $i++) {
			$archiv_id = $result->getValue('send_archive_id');
			$this->archives[$archiv_id] = new MultinewsletterNewsletter($archiv_id);
			$result->next();
		}
	}
	
	/**
	 * Initialisiert die Newsletter Empfänger, die zum Versand ausstehen.
	 * @param int $numberMails Anzahl der Mails für den nächsten Versandschritt.
	 */
	private function initRecipients($numberMails = 0) {
		$query = "SELECT user_id FROM ". rex::getTablePrefix() ."375_user "
			."WHERE send_archive_id > 0 ";
		if($numberMails > 0) {
			$query .= "LIMIT 0, ". $numberMails;
		}
		$result = rex_sql::factory();
		$result->setQuery($query);		
		$num_rows = $result->getRows();

		for($i = 0; $num_rows > $i; $i++) {
			$this->recipients[] = new MultinewsletterUser($result->getValue('user_id'));
			$result->next();
		}
	}
	
	/**
	 * Zählt die Gesamtzahl der Nutzer, die noch einen Newsletter erhalten
	 * @return int Anzahl ausstehender Newsletter User, die den Newsletter noch erhalten sollen.
	 */
	public function countRemainingUsers() {
		if($this->remaining_users == 0) {
			$query = "SELECT COUNT(*) as total FROM ". rex::getTablePrefix() ."375_user "
				."WHERE send_archive_id > 0 ";
			$result = rex_sql::factory();
			$result->setQuery($query);		

			return $result->getValue("total");
		}
		else {
			return $this->remaining_users;
		}
	}
	
	/**
	 * Bereitet den Versand des Newsletters vor.
	 * @param Array $group_ids Array mit den GruppenIDs der vorzubereitenden Gruppen.
	 * @param int $article_id ID des zu versendenden Redaxo Artikels
	 * @param int $fallback_clang_id ID der Sprache, die verwendet werden soll,
	 * wenn der Artikel offline ist.
	 * @return Array Array mit den Sprach IDs, die Offline sind und durch die
	 * Fallback Sprache ersetzt wurden.
	 */
	public function prepare($group_ids, $article_id, $fallback_clang_id) {
		$offline_lang_ids = array();
		
		$clang_ids = array();
		// Welche Sprachen sprechen die Nutzer der vorzubereitenden Gruppen?
		$where_groups = array();
		foreach($group_ids as $group_id) {
			$where_groups[] = "group_ids LIKE '%|". $group_id ."|%'";
		}
		$query = "SELECT clang_id FROM ". rex::getTablePrefix() ."375_user "
			."WHERE ". implode(" OR ", $where_groups) ." GROUP BY clang_id";

		$result = rex_sql::factory();
		$result->setQuery($query);
		$num_rows = $result->getRows();
		for($i = 0; $num_rows > $i; $i++) {
			$clang_ids[] = $result->getValue('clang_id');
			$result->next();
		}

		// Newsletter Artikel auslesen
		foreach($clang_ids as $clang_id) {
			$newsletter = MultinewsletterNewsletter::factory($article_id, $clang_id);
			if($newsletter->htmlbody == "") {
				$offline_lang_ids[] = $clang_id;
			}
			else {
				$newsletter->group_ids = $group_ids;
				$newsletter->sender_email = $_SESSION['multinewsletter']['newsletter']['sender_email'];
				$newsletter->sender_name = $_SESSION['multinewsletter']['newsletter']['sender_name'][$_SESSION['multinewsletter']['newsletter']['testlanguage']];
				$newsletter->save();
				$this->archives[$newsletter->archive_id] = $newsletter;
			}
		}

		// Abonnenten zum Senden hinzufügen
		$where_offline_langs = array();
		foreach($offline_lang_ids as $offline_lang_id) {
			$where_offline_langs[] = "clang_id = ". $offline_lang_id;		
		}
		foreach($this->archives as $archive_id => $newsletter) {
			if(!in_array($newsletter->clang_id, $offline_lang_ids)) {
				$query_add_users = "UPDATE ". rex::getTablePrefix() ."375_user "
					."SET send_archive_id = ". $archive_id ." "
					."WHERE (". implode(" OR ", $where_groups) .") "
						."AND (clang_id = ". $newsletter->clang_id;
				if($newsletter->clang_id == $fallback_clang_id && count($where_offline_langs) > 0) {
					$query_add_users .= " OR ". implode(" OR ", $where_offline_langs);
				}
				$query_add_users .= ") AND status = 1";
				$result_add_users = rex_sql::factory();
				$result_add_users->setQuery($query_add_users);
			}
		}
		
		return $offline_lang_ids;
	}
	
	/**
	 * Setzt die zu versendenden Newsletter zurück.
	 */
	public function reset() {
		// Benutzer zurücksetzen
		$query_user = "UPDATE ". rex::getTablePrefix() ."375_user "
			."SET send_archive_id = NULL";
		$result_user = rex_sql::factory();
		$result_user->setQuery($query_user);
		
		// Archive, die bisher keine Empfänger hatten auch löschen
		$query_archive = "DELETE FROM ". rex::getTablePrefix() ."375_archive "
			."WHERE sentdate = 0";
		$result_archive = rex_sql::factory();
		$result_archive->setQuery($query_archive);
	}
	
	/**
	 * Veranlasst das Senden der nächsten Trange von Mails.
	 * @param int $numberMails Anzahl von Mails die raus sollen.
	 * @return boolean true, wenn erfolgreich versendet, sonst false
	 */
	public function send($numberMails) {
		if($numberMails > $this->countRemainingUsers()) {
			$numberMails = $this->countRemainingUsers();
		}
		
		while($numberMails > 0) {
			$recipient = $this->recipients[$numberMails - 1];
			$newsletter = $this->archives[$recipient->send_archive_id];
			if($newsletter->sendNewsletter($recipient) == false) {
				return false;
			}
			
			// Speichern, dass der Benutzer nicht mehr zum Versand aussteht
			$recipient->send_archive_id = 0;
			$recipient->save();

			$numberMails--;
		}
		
		return true;
	}
}