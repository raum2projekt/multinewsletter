<?php

/**
 * MultiNewsletter Newletter (noch zu versenden)
 *
 * @author Tobias Krais
 */
class MultinewsletterNewsletterManager {
    /**
     * @var MultinewsletterNewsletter[] Archiv Objekte des Newsletters. ACHTUNG: der Index im Array
     * muss die Archiv ID sein.
     */
    var $archives = [];

    /**
     * @var MultinewsletterUser[] Empfänger des Newsletters.
     */
    var $recipients = [];

    /**
     * @var boolean $autosend_only TRUE if only autosend archives and receipients
	 * should be managed.
     */
    var $autosend_only = FALSE;

    /**
     * @var MultinewsletterUser[] Users an die der Newsletter zuletzt versand wurde.
     */
    var $last_send_users = [];

    /**
     * @var int Anzahl ausstehender Newsletter Mails
     */
    var $remaining_users = 0;

    /**
     * Stellt die Daten des Newsletters aus einem Archiv zusammen.
     * @param int $numberMails Anzahl der Mails für den nächsten Versandschritt.
	 * @param boolean $autosend_only Init only reciepients with autosend option.
     */
    public function __construct($numberMails = 0, $autosend_only = FALSE) {
		$this->autosend_only = $autosend_only;
        $this->initArchivesToSend();
        $this->initRecipients($numberMails);
    }

    /**
     * Versendet einen Newsletter sofort.
     * @param int[] $group_ids Array mit den GruppenIDs der vorzubereitenden Gruppen.
     * @param int $article_id ID des zu versendenden Redaxo Artikels
     * @param int $fallback_clang_id ID der Sprache, die verwendet werden soll,
     * wenn der Artikel offline ist.
     * @param int[] $recipient_ids IDs of receipients that should be added to send list
     * @param string $attachments Attachment list, comma separated
     * @return TRUE if successful started, otherwise FALSE
     */
	public static function autosend($group_ids, $article_id, $fallback_clang_id, array $recipient_ids = [], $attachments = '') {
		$newsletterManager = MultinewsletterNewsletterManager::factory();
		$newsletterManager->autosend_only = TRUE;
		$newsletterManager->prepare($group_ids, $article_id, $fallback_clang_id, $recipient_ids, $attachments);
		
		if(multinewsletter_cronjob_sender::isInstalled() && count($newsletterManager->archives) > 0) {
			// Activate CronJob
			multinewsletter_cronjob_sender::activate();
			return TRUE;
		}
		else {
			// Send it all right now - or try it at least
			$sendresult = $newsletterManager->send(count($newsletterManager->recipients));
			// Send final admin notification
			foreach($newsletterManager->archives as $archive) {
				if($archive->countRemainingUsers() == 0) {
					$subject = 'Versand Newsletter abgeschlossen';
					$body = 'Der Versand das folgenden Newsletters wurde abgeschlossen:<br>'
						. $archive->getValue('subject') ."<br><br>"
						. "Der Versand per ConJob war nicht möglich und wurde daher ohne Berücksichtigung möglicher Serverlimits auf ein mal durchgeführt. Bitte installieren Sie das CronJob Addon und aktivieren Sie außerdem den MutliNewsletter Sender Cronjob über die Einstellungen des MultiNewsletters.<br><br>"
						. "Fehler gab es beim Versand an folgende Nutzer:<br>"
						. $archive->getValue('recipients_failure');
					$newsletterManager->sendAdminNotification($subject, $body);
					// Unset archive
					unset($this->archives[$archive->getValue('id')]);
				}
			}
		}
	}
	
    /**
     * Sends next step of newletters in send list.
     */
	public static function cronSend() {
		// Calculate maximum mails per CronJob step (every 5 minutes)
		$numberMails = round(rex_config::get('multinewsletter', 'max_mails') * rex_config::get('multinewsletter', 'versandschritte_nacheinander') * 3600 / rex_config::get('multinewsletter', 'sekunden_pause') / 12);
		$newsletterManager = new MultinewsletterNewsletterManager($numberMails, TRUE);
		$sendresult = $newsletterManager->send($numberMails);
		if($sendresult !== TRUE) {
			$messages[] = rex_i18n::msg('multinewsletter_error_send_incorrect_user') .' '. $sendresult;
		}

		// Send final admin notification
		foreach($newsletterManager->archives as $archive) {
			if($archive->countRemainingUsers() == 0) {
				$subject = 'Versand Newsletter abgeschlossen';
				$body = 'Der automatisierte Versand das folgenden Newsletters wurde abgeschlossen:<br>'
					. $archive->getValue('subject') ."<br><br>"
					. "Fehler gab es beim Versand an folgende Nutzer:<br>"
					. $archive->getValue('recipients_failure');
				$newsletterManager->sendAdminNotification($subject, $body);
				// Unset archive
				unset($this->archives[$archive->getValue('id')]);
			}
		}
		
		// Deactivate CronJob
		if(count($newsletterManager->archives) == 0) {
			multinewsletter_cronjob_sender::deactivate();
		}
	}

	/**
	 * Creates a blank, uninitialized MultinewsletterNewsletterManager object.
	 * @return MultinewsletterNewsletterManager Empty MultinewsletterNewsletterManager object.
	 */
	public static function factory() {
		$manager = new MultinewsletterNewsletterManager();
		$manager->archives = [];
		$manager->recipients = [];
		return $manager;
	}
	
    /**
     * Initialisiert die Newsletter Archive, die zum Versand ausstehen.
     */
    private function initArchivesToSend() {
        $query = "SELECT archive_id FROM ". rex::getTablePrefix() ."375_sendlist "
			.($this->autosend_only ? "WHERE autosend = 1 ": "")
			."GROUP BY archive_id";
        $result = rex_sql::factory();
        $result->setQuery($query);
        $num_rows = $result->getRows();

        for ($i = 0; $num_rows > $i; $i++) {
            $archive_id = $result->getValue('archive_id');
            $this->archives[$archive_id] = new MultinewsletterNewsletter($archive_id);
            $result->next();
        }
    }

    /**
     * Initialisiert die Newsletter Empfänger, die zum Versand ausstehen.
     * @param int $numberMails Anzahl der Mails für den nächsten Versandschritt.
     */
    private function initRecipients($numberMails = 0) {
        $query = "SELECT id FROM " . rex::getTablePrefix() . "375_sendlist AS sendlist "
			. "LEFT JOIN " . rex::getTablePrefix() . "375_user AS users "
				. "ON sendlist.user_id = users.id "
			.($this->autosend_only ? "WHERE autosend = 1 ": "")
			. "ORDER BY archive_id, email";
        if ($numberMails > 0) {
            $query .= " LIMIT 0, " . $numberMails;
        }

		$result = rex_sql::factory();
        $result->setQuery($query);
        $num_rows = $result->getRows();
        for ($i = 0; $num_rows > $i; $i++) {
            $this->recipients[] = new MultinewsletterUser($result->getValue('id'));
            $result->next();
        }
    }

    /**
     * Zählt die Gesamtzahl der Nutzer, die noch einen Newsletter erhalten.
     * @return int Anzahl ausstehender Newsletter User, die den Newsletter noch erhalten sollen.
     */
    public function countRemainingUsers() {
        if ($this->remaining_users == 0) {
            $query = "SELECT COUNT(*) as total FROM " . rex::getTablePrefix() . "375_sendlist"
				.($this->autosend_only ? " WHERE autosend = 1" : "");
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
     * @param int[] $group_ids Array mit den GruppenIDs der vorzubereitenden Gruppen.
     * @param int $article_id ID des zu versendenden Redaxo Artikels
     * @param int $fallback_clang_id ID der Sprache, die verwendet werden soll,
     * wenn der Artikel offline ist.
     * @param int[] $recipient_ids IDs of receipients that should be added to send list
     * @param string $attachments Attachment list, comma separated
     * @return int[] Array mit den Sprach IDs, die Offline sind und durch die
     * Fallback Sprache ersetzt wurden.
     */
    public function prepare($group_ids, $article_id, $fallback_clang_id, array $recipient_ids = [], $attachments = '') {
        $offline_lang_ids = [];

        $clang_ids = [];
        // Welche Sprachen sprechen die Nutzer der vorzubereitenden Gruppen?
        $where_groups = [];
        foreach ($group_ids as $group_id) {
            $where_groups[] = "
                group_ids = '" . $group_id . "' OR 
                group_ids LIKE '" . $group_id . "|%' OR 
                group_ids LIKE '%|" . $group_id . "' OR 
                group_ids LIKE '%|" . $group_id . "|%' OR 
                group_ids LIKE '" . $group_id . ",%' OR 
                group_ids LIKE '%," . $group_id . "' OR 
                group_ids LIKE '%," . $group_id . ",%' 
            ";
        }
        if (count($recipient_ids)) {
            $where_groups[] = 'id IN(' . implode(',', $recipient_ids) . ')';
        }
        $query = "SELECT clang_id FROM " . rex::getTablePrefix() . "375_user " . "WHERE " . implode(" OR ", $where_groups) . " GROUP BY clang_id";

        $result = rex_sql::factory();
        $result->setQuery($query);
        $num_rows = $result->getRows();
        for ($i = 0; $num_rows > $i; $i++) {
            $clang_ids[] = $result->getValue('clang_id');
            $result->next();
        }

        // Newsletter Artikel auslesen
        foreach ($clang_ids as $clang_id) {
            $newsletter = MultinewsletterNewsletter::factory($article_id, $clang_id);
            if (!strlen($newsletter->getValue('htmlbody'))) {
                $offline_lang_ids[] = $clang_id;
            }
            else {
                $newsletter->setValue('attachments', $attachments);
                $newsletter->setValue('group_ids', $group_ids);
                $newsletter->setValue('sender_email', $_SESSION['multinewsletter']['newsletter']['sender_email']);
                $newsletter->setValue('sender_name', $_SESSION['multinewsletter']['newsletter']['sender_name'][$_SESSION['multinewsletter']['newsletter']['testlanguage']]);
                $newsletter->save();

                $this->archives[$newsletter->getId()] = $newsletter;
            }
        }

        // Abonnenten zum Senden hinzufügen
        $where_offline_langs = [];
        foreach ($offline_lang_ids as $offline_lang_id) {
            $where_offline_langs[] = "clang_id = " . $offline_lang_id;
        }
        foreach ($this->archives as $archive_id => $newsletter) {
            $newsletter_lang_id = $newsletter->getValue('clang_id');

            if (!in_array($newsletter_lang_id, $offline_lang_ids)) {
                $query_add_users = "INSERT INTO `" . rex::getTablePrefix() . "375_sendlist` (`archive_id`, `user_id`, `autosend`) "
					. "SELECT ". $archive_id ." AS archive_id, `id`, ". ($this->autosend_only ? 1 : 0) ." AS autosend "
						. "FROM `" . rex::getTablePrefix() . "375_user` WHERE (" . implode(" OR ", $where_groups) . ") " . "AND (clang_id = " . $newsletter_lang_id;
                if ($newsletter_lang_id == $fallback_clang_id && count($where_offline_langs) > 0) {
                    $query_add_users .= " OR " . implode(" OR ", $where_offline_langs);
                }
                $query_add_users  .= ") AND status = 1 AND email != ''";
                $result_add_users = rex_sql::factory();
                $result_add_users->setQuery($query_add_users);
            }
        }

        return $offline_lang_ids;
    }

    /**
     * Setzt die zu versendenden Newsletter zurück. Dabei werden auch noch nicht
	 * versendete Archive gelöscht.
     */
    public function reset() {
        // Benutzer zurücksetzen
        $query_user  = "TRUNCATE " . rex::getTablePrefix() . "375_sendlist";
        $result_user = rex_sql::factory();
        $result_user->setQuery($query_user);
		$this->recipients = [];

        // Archive, die bisher keine Empfänger hatten auch löschen
        $query_archive  = "DELETE FROM " . rex::getTablePrefix() . "375_archive " . "WHERE sentdate = '' OR sentdate IS NULL";
        $result_archive = rex_sql::factory();
        $result_archive->setQuery($query_archive);
		$this->archives = [];

		$this->remaining_users = 0;
    }

    /**
     * Veranlasst das Senden der nächsten Trange von Mails.
     * @param int $numberMails Anzahl von Mails die raus sollen.
     * @return boolean TRUE, wenn erfolgreich versendet, otherwise list of
	 * failed email addresses.
     */
    public function send($numberMails) {
        if ($numberMails > $this->countRemainingUsers()) {
            $numberMails = $this->countRemainingUsers();
        }

	    $result = rex_sql::factory();
		$failure_mails = [];
        while ($numberMails > 0) {
            $recipient  = $this->recipients[$numberMails - 1];
			$archive_id = $recipient->getSendlistArchiveIDs($this->autosend_only);
            $newsletter = $this->archives[$archive_id[0]];

            if ($newsletter->sendNewsletter($recipient, rex_article::get($newsletter->getValue('article_id'))) == FALSE) {
				$result->setQuery("DELETE FROM ". rex::getTablePrefix() ."375_sendlist WHERE user_id = ". $recipient->getId() ." AND archive_id = ". $newsletter->getId());
                $failure_mails[] = $recipient->getValue('email');
            }

            // Speichern, dass der Benutzer nicht mehr zum Versand aussteht
			$result->setQuery("DELETE FROM ". rex::getTablePrefix() ."375_sendlist WHERE user_id = ". $recipient->getId() ." AND archive_id = ". $newsletter->getId());

            $this->last_send_users[] = $recipient;
            $numberMails--;
        }
		
		$archive = new MultinewsletterNewsletter($archive_id);
		$archive->setValue('recipients_failure', implode(', ', array_merge($archive->getArrayValue('recipients_failure'), $failure_mails)));
		$archive->save();
		
        return true;
    }
	
	/**
     * Sends a email to MutliNewsletter admin, if given in settings.
     * @param string $subject Message subject
     * @param string $body Message body
     * @return boolean TRUE if successfully sent, otherwise FALSE
     */
    private function sendAdminNotification($subject, $body) {
		if($multinewsletter->getConfig('admin_email', '') != '') {
			$multinewsletter = rex_addon::get("multinewsletter");

			$mail = new rex_mailer();
			$mail->IsHTML(true);
			$mail->CharSet = "utf-8";
			$mail->From = $multinewsletter->getConfig('sender');
			$mail->FromName = "MultiNewsletter Manager";
			$mail->Sender = $multinewsletter->getConfig('sender');
			$mail->AddAddress($multinewsletter->getConfig('admin_email'));

			if ($multinewsletter->getConfig('use_smtp')) {
				$mail->Mailer = 'smtp';
				$mail->Host = $multinewsletter->getConfig('smtp_host');
				$mail->Port = $multinewsletter->getConfig('smtp_port');
				$mail->SMTPSecure = $multinewsletter->getConfig('smtp_crypt');
				$mail->SMTPAuth = $multinewsletter->getConfig('smtp_auth');
				$mail->Username = $multinewsletter->getConfig('smtp_user');
				$mail->Password = $multinewsletter->getConfig('smtp_password');
			}

			$mail->Subject = $subject;
			$mail->Body = $body;
			return $mail->Send();
		}
    }
}