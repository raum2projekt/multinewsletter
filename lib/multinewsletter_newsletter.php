<?php

/**
 * MultiNewsletter Newletter (in der Datenbank als Newsletter Archiv).
 *
 * @author Tobias Krais
 */
class MultinewsletterNewsletter extends MultinewsletterAbstract {
    /**
     * Stellt die Daten des Archivs aus der Datenbank zusammen.
     * @param int $archive_id Archiv ID aus der Datenbank.
     */
    public function __construct($id) {
        if ($id) {
            $sql = rex_sql::factory();

            $sql->setTable(rex::getTablePrefix() . '375_archive');
            $sql->setWhere('id = :id', ['id' => $id]);
            $sql->select();
            $this->data = @$sql->getArray()[0];
        }
    }

    /**
     * Zählt die Gesamtzahl der Nutzer, die noch diesen Newsletter erhalten sollen.
     * @return int Anzahl ausstehender Newsletter User, die den Newsletter noch erhalten sollen.
     */
    public function countRemainingUsers() {
        if ($this->remaining_users == 0) {
            $query = "SELECT COUNT(*) as total FROM " . rex::getTablePrefix() . "375_sendlist "
				."WHERE archive_id = ". $this->getValue('id');
            $result = rex_sql::factory();
            $result->setQuery($query);

            return $result->getValue("total");
        }
        else {
            return $this->remaining_users;
        }
    }

    /**
     * Stellt die Daten des Archivs aus der Datenbank zusammen.
     * @param int $article_id Artikel ID aus Redaxo.
     * @param type $clang_id Sprachen ID aus Redaxo
     * @return MultinewsletterNewsletter Intialisiertes Multinewsletter Objekt.
     */
    public static function factory($article_id, $clang_id = null) {
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
	 * @param rex_article Redaxo article ID
     * @return String Personalisierter String.
     */
    public static function personalize($content, $user, $article = null) {
        return preg_replace('/ {2,}/', ' ', self::replaceVars($content, $article, $user));
    }

    public static function getUrl($id = null, $clang = null, array $params = []) {
        if (rex_addon::get('yrewrite') && rex_addon::get('yrewrite')->isAvailable()) {
            $url = rex_getUrl($id, $clang, $params);
        }
        else {
            $url = rtrim(rex::getServer(), '/') . '/' . ltrim(str_replace(['../', './'], '', rex_getUrl($id, $clang, $params)), '/');
        }
        return $url;
    }

    /**
     * Personalisiert einen String
     * @param String $content Zu personalisierender Inhalt
     * @return String Personalisierter String.
     */
    public static function replaceURLs($content) {
		$content = str_replace('href="/', 'href="'. (rex_addon::get('yrewrite')->isAvailable() ? \rex_yrewrite::getCurrentDomain()->getUrl() : rex::getServer()), $content);
		$content = str_replace('href="./', 'href="'. (rex_addon::get('yrewrite')->isAvailable() ? \rex_yrewrite::getCurrentDomain()->getUrl() : rex::getServer()), $content);
		$content = str_replace('href="../', 'href="'. (rex_addon::get('yrewrite')->isAvailable() ? \rex_yrewrite::getCurrentDomain()->getUrl() : rex::getServer()), $content);

		$content = str_replace("href='/", "href='". (rex_addon::get('yrewrite')->isAvailable() ? \rex_yrewrite::getCurrentDomain()->getUrl() : rex::getServer()), $content);
		$content = str_replace("href='./", "href='". (rex_addon::get('yrewrite')->isAvailable() ? \rex_yrewrite::getCurrentDomain()->getUrl() : rex::getServer()), $content);
		$content = str_replace("href='../", "href='". (rex_addon::get('yrewrite')->isAvailable() ? \rex_yrewrite::getCurrentDomain()->getUrl() : rex::getServer()), $content);

		$content = str_replace('src="/', 'src="'. (rex_addon::get('yrewrite')->isAvailable() ? \rex_yrewrite::getCurrentDomain()->getUrl() : rex::getServer()), $content);
		$content = str_replace('src="./', 'src="'. (rex_addon::get('yrewrite')->isAvailable() ? \rex_yrewrite::getCurrentDomain()->getUrl() : rex::getServer()), $content);
		$content = str_replace('src="../', 'src="'. (rex_addon::get('yrewrite')->isAvailable() ? \rex_yrewrite::getCurrentDomain()->getUrl() : rex::getServer()), $content);

		$content = str_replace("src='/", "src='". (rex_addon::get('yrewrite')->isAvailable() ? \rex_yrewrite::getCurrentDomain()->getUrl() : rex::getServer()), $content);
		$content = str_replace("src='./", "src='". (rex_addon::get('yrewrite')->isAvailable() ? \rex_yrewrite::getCurrentDomain()->getUrl() : rex::getServer()), $content);
		$content = str_replace("src='../", "src='". (rex_addon::get('yrewrite')->isAvailable() ? \rex_yrewrite::getCurrentDomain()->getUrl() : rex::getServer()), $content);

		$content = str_replace("src='index.php", "src='". (rex_addon::get('yrewrite')->isAvailable() ? \rex_yrewrite::getCurrentDomain()->getUrl() : rex::getServer()) .'index.php', $content);
		$content = str_replace('src="index.php', 'src="'. (rex_addon::get('yrewrite')->isAvailable() ? \rex_yrewrite::getCurrentDomain()->getUrl() : rex::getServer()) .'index.php', $content);
		
		// Correct image URLs
		$content = str_replace('&amp;', '&', $content);
		
		return $content;
    }

	/**
     * Personalisiert einen String
     * @param String $content Zu personalisierender Inhalt
	 * @param rex_article Redaxo article
     * @param MultinewsletterUser $user Empfänger der Testmail
     * @return String Personalisierter String.
     */
    public static function replaceVars($content, $newsletter_article = null, $user = null) {
        $addon = rex_addon::get("multinewsletter");
        $ulang = $user ? $user->getValue('clang_id') : rex_clang::getCurrentId();
        $user  = $user ?: new MultinewsletterUser(0);

        $replaces  = [];
        $user_keys = array_keys($user->getData());

        foreach ($user_keys as $ukey) {
            $replaces['+++' . strtoupper($ukey) . '+++'] = $user->getValue($ukey, '');
        }

        return strtr($content, rex_extension::registerPoint(
			new rex_extension_point(
				'multinewsletter.replaceVars', array_merge(
					$replaces, [
						'+++TITLE+++'				=> $addon->getConfig('lang_' . $ulang . "_title_" . $user->getValue('title')),
						'+++ABMELDELINK+++'			=> self::getUrl($addon->getConfig('link_abmeldung'), $ulang, ['unsubscribe' => $user->getValue('email')]),
						'+++AKTIVIERUNGSLINK+++'	=> self::getUrl($addon->getConfig('link'), $ulang, ['activationkey' => $user->getValue('activationkey'), 'email' => $user->getValue('email')]),
						'+++NEWSLETTERLINK+++'		=> $newsletter_article ? self::getUrl($newsletter_article->getId(), $ulang) : '',
						'+++LINK_PRIVACY_POLICY+++'	=> rex_getUrl(rex_config::get('d2u_helper', 'article_id_privacy_policy', rex_article::getSiteStartArticleId())),
						'+++LINK_IMPRESS+++'		=> rex_getUrl(rex_config::get('d2u_helper', 'article_id_impress', rex_article::getSiteStartArticleId())),
					])
				)
			)
		);
    }
	
	/**
	 * Get fallback lang settings
	 * @param int $fallback_lang
	 * @return int rex_clang fallback clang_id
	 */
    public static function getFallbackLang($fallback_lang = null) {
        $addon = rex_addon::get("multinewsletter");

        if($addon->getConfig("lang_fallback", 0) == 0 && !is_null($fallback_lang)) {
            return $fallback_lang;
		}

        if($addon->getConfig("lang_fallback", 0) == 0) {
            return null;
		}

        return rex_config::get("d2u_helper", "default_lang", $fallback_lang);
    }

    /**
     * Liest einen Redaxo Artikel aus.
     * @param type $article_id ArtikelID aus Redaxo
     * @param type $clang_id Sprachen ID aus Redaxo
     * @return boolean
     */
    private function readArticle($article_id, $clang_id) {
        $article   = rex_article::get($article_id, $clang_id);
        $article_content = new rex_article_content($article_id, $clang_id);

        if ($article instanceof rex_article && $article->isOnline()) {
            $this->setValue('article_id', $article_id);
            $this->setValue('clang_id', $clang_id);
            $this->setValue('htmlbody', $article_content->getArticleTemplate(), $article);
            $this->setValue('attachments', $article->getValue('art_newsletter_attachments'));
            $this->setValue('subject', $article->getValue('name'));
        }
    }

    /**
     * Aktualisiert den Newsletter in der Datenbank.
     */
    public function save() {
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
    private function send($User, $article = null) {
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

            $mail->Subject = $this->personalize($this->getValue('subject'), $User, $article);
			$body = MultinewsletterNewsletter::personalize($this->getValue('htmlbody'), $User, $article);
            $mail->Body = MultinewsletterNewsletter::replaceURLs($body);
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
    public function sendNewsletter($User, $article = null) {
        if ($this->send($User, $article)) {
            $recipients   = $this->getArrayValue('recipients');
            $recipients[] = $User->getValue('email');

            $this->setValue('recipients', $recipients);
            $this->setValue('sentdate', date('Y-m-d H:i:s'));
            $this->setValue('sentby', rex::getUser()->getLogin());
            $this->save();
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Sendet eine Testmail des Newsletters
     * @param MultinewsletterUser $testuser Empfänger der Testmail
     * @return boolean true, wenn erfolgreich versendet, sonst false
     */
    public function sendTestmail($testuser, $article_id)
    {
        return $this->send($testuser, rex_article::get($article_id));
    }
}