<?php

/**
 * MultiNewsletter Newletter (in der Datenbank als Newsletter Archiv).
 *
 * @author Tobias Krais
 */
class MultinewsletterNewsletter extends MultinewsletterAbstract
{
    /**
     * Stellt die Daten des Archivs aus der Datenbank zusammen.
     * @param int $archive_id Archiv ID aus der Datenbank.
     */
    public function __construct($id)
    {
        if ($id) {
            $sql = rex_sql::factory();

            $sql->setTable(rex::getTablePrefix() . '375_archive');
            $sql->setWhere('id = :id', ['id' => $id]);
            $sql->select();
            $this->data = @$sql->getArray()[0];
        }
    }

    /**
     * Stellt die Daten des Archivs aus der Datenbank zusammen.
     * @param int $article_id Artikel ID aus Redaxo.
     * @param type $clang_id Sprachen ID aus Redaxo
     * @return MultinewsletterNewsletter Intialisiertes Multinewsletter Objekt.
     */
    public static function factory($article_id, $clang_id = null)
    {
        // init Mailbody und Betreff
        $newsletter = new self(0);
        $newsletter->readArticle($article_id, $clang_id);

        return $newsletter;
    }

    /**
     * Löscht das Archiv aus der Datenbank.
     */
    public function delete()
    {
        $sql = rex_sql::factory();
        $sql->setTable(rex::getTablePrefix() . '375_archive');
        $sql->setWhere('id = :id', ['id' => $this->getId()]);
        return $sql->delete();
    }

    /**
     * Personalisiert einen String
     * @param String $content Zu personalisierender Inhalt
     * @param MultinewsletterUser $user Empfänger der Testmail
     * @return String Personalisierter String.
     */
    public static function personalize($content, $User, $clang_id = null)
    {
        return preg_replace('/ {2,}/', ' ', self::replaceVars($content, null, $User));
    }

    public static function getUrl($id = null, $clang = null, array $params = [])
    {
        if (rex_addon::get('yrewrite') && rex_addon::get('yrewrite')->isAvailable()) {
            $url = rex_getUrl($id, $clang, $params);
        }
        else {
            $url = rtrim(rex::getServer(), '/') . '/' . ltrim(str_replace(['../', './'], '', rex_getUrl($id, $clang, $params)), '/');
        }
        return $url;
    }

    public static function replaceVars($content, $newsletter_article = null, $User = null)
    {
        $addon = rex_addon::get("multinewsletter");
        $User  = $User ?: new MultinewsletterUser(0);
        $ulang = $User->getValue('clang_id');

        $replaces  = [];
        $user_keys = array_keys($User->getData());

        foreach ($user_keys as $ukey) {
            $replaces['+++' . strtoupper($ukey) . '+++'] = $User->getValue($ukey, '');
        }

        return stripslashes(strtr($content, rex_extension::registerPoint(new rex_extension_point('multinewsletter.replaceVars', array_merge($replaces, [
            '+++TITLE+++'            => htmlspecialchars($addon->getConfig('lang_' . $ulang . "_title_" . $User->getValue('title')), ENT_QUOTES),
            '+++ABMELDELINK+++'      => self::getUrl($addon->getConfig('link_abmeldung'), $ulang, ['unsubscribe' => $User->getValue('email')]),
            '+++AKTIVIERUNGSLINK+++' => self::getUrl($addon->getConfig('link'), $ulang, ['activationkey' => $User->getValue('activationkey'), 'email' => $email]),
            '+++NEWSLETTERLINK+++'   => $newsletter_article ? self::getUrl($newsletter_article->getId(), $ulang) : '',
        ])))));
    }

    /**
     * Liest einen Redaxo Artikel aus.
     * @param type $article_id ArtikelID aus Redaxo
     * @param type $clang_id Sprachen ID aus Redaxo
     * @return boolean
     */
    private function readArticle($article_id, $clang_id)
    {
        $article         = rex_article::get($article_id, $clang_id);
        $article_content = new rex_article_content($article_id, $clang_id);

        if ($article instanceof rex_article && $article->isOnline()) {
            $this->setValue('clang_id', $clang_id);
            $this->setValue('htmlbody', $article_content->getArticleTemplate(), $article);
            $this->setValue('attachments', $article->getValue('art_newsletter_attachments'));
            $this->setValue('subject', $article->getValue('name'));
        }
    }

    /**
     * Aktualisiert den Newsletter in der Datenbank.
     */
    public function save()
    {
        $this->setValue('setupdate', $this->getValue('setupdate', date('Y-m-d H:i:s')));

        $sql = rex_sql::factory();
        $sql->setTable(rex::getTablePrefix() . '375_archive');
        $sql->setValues($this->data);

        if ($this->getId()) {
            $sql->setWhere('id = :id', [':id' => $this->getId()]);
            $sql->update();
        }
        else {
            $sql->insert();
            $id = $sql->getLastId();
            $this->setValue('id', $id);
        }
        return $this;
    }

    /**
     * Sendet eine Mail des Newsletters an übergebenen Nutzer
     * @param MultinewsletterUser $user Empfänger der Mail
     * @return boolean true, wenn erfolgreich versendet, sonst false
     */
    private function send($User)
    {
        if (strlen($this->getValue('htmlbody')) && strlen($User->getValue('email'))) {
            $addon       = rex_addon::get("multinewsletter");
            $attachments = strlen($this->getValue('attachments')) ? array_filter(explode(',', $this->getValue('attachments'))) : [];

            $mail = new rex_mailer();
            $mail->IsHTML(true);
            $mail->CharSet  = "utf-8";
            $mail->From     = trim($this->getValue('sender_email'));
            $mail->FromName = $this->getValue('sender_name');
            $mail->Sender   = trim($this->getValue('sender_email'));
            $mail->AddAddress($User->getValue('email'), $User->getName());

            if ($addon->getConfig('use_smtp')) {
                $mail->Mailer     = 'smtp';
                $mail->Host       = $addon->getConfig('smtp_host');
                $mail->Port       = $addon->getConfig('smtp_port');
                $mail->SMTPSecure = $addon->getConfig('smtp_crypt');
                $mail->SMTPAuth   = $addon->getConfig('smtp_auth');
                $mail->Username   = $addon->getConfig('smtp_user');
                $mail->Password   = $addon->getConfig('smtp_password');
                // set bcc
                $mail->clearBCCs();
                $bccs = strlen($addon->getConfig('smtp_bcc')) ? explode(',', $addon->getConfig('smtp_bcc')) : [];

                foreach ($bccs as $bcc) {
                    $mail->addBCC($bcc);
                }
            }

            foreach ($attachments as $attachment) {
                $media = rex_media::get($attachment);
                $mail->addAttachment(rex_path::media($attachment), $media ? $media->getTitle() : '');
            }

            $mail->Subject = $this->personalize($this->getValue('subject'), $User);
            $mail->Body    = $this->personalize($this->getValue('htmlbody'), $User, $this->getValue('clang_id'));
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
    public function sendNewsletter($User)
    {
        if ($this->send($User)) {
            $recipients   = $this->getArrayValue('recipients');
            $recipients[] = $User->getValue('email');

            $this->setValue('recipients', $recipients);
            $this->setValue('sentdate', date('Y-m-d H:i:s'));
            $this->setValue('sentby', rex::getUser()->getLogin());
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
    public function sendTestmail($testuser)
    {
        return $this->send($testuser);
    }
}

/**
 * MultiNewsletter Newletter (noch zu versenden)
 *
 * @author Tobias Krais
 */
class MultinewsletterNewsletterManager
{
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
     */
    public function __construct($numberMails = 0)
    {
        $this->initArchivesToSend();
        $this->initRecipients($numberMails);
    }

    /**
     * Initialisiert die Newsletter Archive, die zum Versand ausstehen.
     */
    private function initArchivesToSend()
    {
        $query  = "SELECT send_archive_id FROM " . rex::getTablePrefix() . "375_user " . "WHERE send_archive_id > 0 " . "GROUP BY send_archive_id";
        $result = rex_sql::factory();
        $result->setQuery($query);
        $num_rows = $result->getRows();

        for ($i = 0; $num_rows > $i; $i++) {
            $archive_id                  = @$result->getValue('send_archive_id');
            $this->archives[$archive_id] = new MultinewsletterNewsletter($archive_id);
            $result->next();
        }
    }

    /**
     * Initialisiert die Newsletter Empfänger, die zum Versand ausstehen.
     * @param int $numberMails Anzahl der Mails für den nächsten Versandschritt.
     */
    private function initRecipients($numberMails = 0)
    {
        $query = "SELECT id FROM " . rex::getTablePrefix() . "375_user " . "WHERE send_archive_id > 0 " . "ORDER BY email";
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
     * Zählt die Gesamtzahl der Nutzer, die noch einen Newsletter erhalten
     * @return int Anzahl ausstehender Newsletter User, die den Newsletter noch erhalten sollen.
     */
    public function countRemainingUsers()
    {
        if ($this->remaining_users == 0) {
            $query  = "SELECT COUNT(*) as total FROM " . rex::getTablePrefix() . "375_user " . "WHERE send_archive_id > 0 ";
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
    public function prepare($group_ids, $article_id, $fallback_clang_id, array $recipient_ids = [], $attachments = '')
    {
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
            $Newsletter = MultinewsletterNewsletter::factory($article_id, $clang_id);
            if (!strlen($Newsletter->getValue('htmlbody'))) {
                $offline_lang_ids[] = $clang_id;
            }
            else {
                $Newsletter->setValue('attachments', $attachments);
                $Newsletter->setValue('group_ids', $group_ids);
                $Newsletter->setValue('sender_email', $_SESSION['multinewsletter']['newsletter']['sender_email']);
                $Newsletter->setValue('sender_name', $_SESSION['multinewsletter']['newsletter']['sender_name'][$_SESSION['multinewsletter']['newsletter']['testlanguage']]);
                $Newsletter->save();

                $this->archives[$Newsletter->getId()] = $Newsletter;
            }
        }

        // Abonnenten zum Senden hinzufügen
        $where_offline_langs = [];
        foreach ($offline_lang_ids as $offline_lang_id) {
            $where_offline_langs[] = "clang_id = " . $offline_lang_id;
        }
        foreach ($this->archives as $archive_id => $Newsletter) {
            $n_lang_id = $Newsletter->getValue('clang_id');

            if (!in_array($n_lang_id, $offline_lang_ids)) {
                $query_add_users = "UPDATE " . rex::getTablePrefix() . "375_user " . "SET send_archive_id = " . $archive_id . " " . "WHERE (" . implode(" OR ", $where_groups) . ") " . "AND (clang_id = " . $n_lang_id;
                if ($n_lang_id == $fallback_clang_id && count($where_offline_langs) > 0) {
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
     * Setzt die zu versendenden Newsletter zurück.
     */
    public function reset()
    {
        // Benutzer zurücksetzen
        $query_user  = "UPDATE " . rex::getTablePrefix() . "375_user " . "SET send_archive_id = NULL";
        $result_user = rex_sql::factory();
        $result_user->setQuery($query_user);

        // Archive, die bisher keine Empfänger hatten auch löschen
        $query_archive  = "DELETE FROM " . rex::getTablePrefix() . "375_archive " . "WHERE sentdate = '' OR sentdate IS NULL";
        $result_archive = rex_sql::factory();
        $result_archive->setQuery($query_archive);
    }

    /**
     * Veranlasst das Senden der nächsten Trange von Mails.
     * @param int $numberMails Anzahl von Mails die raus sollen.
     * @return boolean true, wenn erfolgreich versendet, sonst false
     */
    public function send($numberMails)
    {
        if ($numberMails > $this->countRemainingUsers()) {
            $numberMails = $this->countRemainingUsers();
        }

        while ($numberMails > 0) {
            $Recipient  = $this->recipients[$numberMails - 1];
            $Newsletter = $this->archives[$Recipient->getValue('send_archive_id')];

            if ($Newsletter->sendNewsletter($Recipient) == false) {
                $Recipient->setValue('send_archive_id', 0);
                $Recipient->save();
                return $Recipient->getValue('email');
            }

            // Speichern, dass der Benutzer nicht mehr zum Versand aussteht
            $Recipient->setValue('send_archive_id', 0);
            $Recipient->save();

            $this->last_send_users[] = $Recipient;
            $numberMails--;
        }
        return true;
    }
}