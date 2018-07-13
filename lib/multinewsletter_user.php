<?php

/**
 * Benutzer des MultiNewsletters.
 */
class MultinewsletterUser extends MultinewsletterAbstract
{

    /**
     * Stellt die Daten des Benutzers aus der Datenbank zusammen.
     * @param int $user_id UserID aus der Datenbank.
     */
    public function __construct($id, $tablePrefix = null)
    {
        if ($id) {
            $sql = rex_sql::factory();
            $dbp = $tablePrefix ?: rex::getTablePrefix();

            $sql->setTable($dbp . '375_user');
            $sql->setWhere('id = :id', ['id' => $id]);
            $sql->select();
            $this->data = @$sql->getArray()[0];
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
     * @return MultinewsletterUser Intialisiertes MultinewsletterUser Objekt.
     */
    public static function factory($email, $title, $grad, $firstname, $lastname, $clang_id, $others = [])
    {
        $user = self::initByMail($email) ?: new self(0);

        $user->setValue('email', $email);
        $user->setValue('title', $title);
        $user->setValue('grad', $grad);
        $user->setValue('firstname', $firstname);
        $user->setValue('lastname', $lastname);
        $user->setValue('clang_id', $clang_id);
        $user->setValue('status', 1);
        $user->setValue('createdate', date('Y-m-d H:i:s'));
        $user->setValue('createip', $_SERVER['REMOTE_ADDR']);

        foreach ((array) $others as $key => $value) {
            $user->setValue($key, $value);
        }
        return $user;
    }

    /**
     * Aktiviert den Benutzer, d.h. der Activationkey wird gelöscht und der Status
     * auf aktiv gesetzt.
     */
    public function activate()
    {
        $this->data['activationkey']	= "0";
        $this->data['activationdate']	= date('Y-m-d H:i:s');
        $this->data['activationip']		= $_SERVER['REMOTE_ADDR'];
        $this->data['updatedate']		= date('Y-m-d H:i:s');
        $this->data['updateip']			= $_SERVER['REMOTE_ADDR'];
        $this->data['status']			= 1;
        $this->save();

        rex_extension::registerPoint(new rex_extension_point('multinewsletter.userActivated', $this));

        $this->sendAdminNoctificationMail("subscribe");
    }

    /**
     * Löscht den Benutzer aus der Datenbank.
     */
    public function delete()
    {
        if (MultinewsletterMailchimp::isActive()) {
            $Mailchimp = MultinewsletterMailchimp::factory();

            try {
                foreach ($this->getArrayValue('group_ids') as $group_id) {
                    $Group = new MultinewsletterGroup($group_id);

                    if (strlen($Group->mailchimp_list_id)) {
                        $Mailchimp->unsubscribe($this, $Group->mailchimp_list_id);
                    }
                }
            }
            catch (MultinewsletterMailchimpException $ex) {
            }
        }

        $sql = rex_sql::factory();
        $sql->setTable(rex::getTablePrefix() . '375_user');
        $sql->setWhere('id = :id', ['id' => $this->getId()]);
        return $sql->delete();
    }

	/**
	 * Get name users name
	 * @return string Name
	 */
    public function getName() {
        return trim($this->getValue('firstname') . ' ' . $this->getValue('lastname'));
    }

    /**
     * Holt einen neuen Benutzer anhand der E-Mailadresse aus der Datenbank.
     * @param String $email E-Mailadresse des Nutzers
     * @return MultinewsletterUser Intialisiertes MultinewsletterUser Objekt.
     */
    public static function initByMail($email) {
        $sql = rex_sql::factory();
        $sql->setTable(rex::getTablePrefix() . '375_user');
        $sql->setWhere('email = :email', ['email' => trim($email)]);
        $sql->select('id');

        $User = new self(@$sql->getValue('id'));
        return $User->getId() ? $User : false;
    }

    /**
     * Personalisiert einen Text für die Aktivierungsmail
     * @param String $content Zu personalisierender Inhalt
     * @return String Personalisierter String.
     */
    private function personalize($content)
    {
        $addon = rex_addon::get("multinewsletter");

        $content = str_replace("+++EMAIL+++", $this->email, stripslashes($content));
        $content = str_replace("+++GRAD+++", htmlspecialchars(stripslashes($this->grad), ENT_QUOTES), $content);
        $content = str_replace("+++LASTNAME+++", htmlspecialchars(stripslashes($this->lastname), ENT_QUOTES), $content);
        $content = str_replace("+++FIRSTNAME+++", htmlspecialchars(stripslashes($this->firstname), ENT_QUOTES), $content);
        $content = str_replace("+++TITLE+++", htmlspecialchars(stripslashes($addon->getConfig('lang_' . $this->clang_id . "_title_" . $this->title)), ENT_QUOTES), $content);
        $content = preg_replace('/ {2,}/', ' ', $content);

        $subscribe_link = rex::getServer() . trim(trim(rex_getUrl($addon->getConfig('link'), $this->clang_id, ['activationkey' => $this->activationkey, 'email' => rawurldecode($this->email)]), "/"), "./");
        if (rex_addon::get('yrewrite')->isAvailable()) {
            // Use Yrewrite, support for Redaxo installations in subfolders: https://github.com/TobiasKrais/multinewsletter/issues/7
            $subscribe_link = rex_yrewrite::getFullUrlByArticleId($addon->getConfig('link'), $this->clang_id, ['activationkey' => $this->activationkey, 'email' => rawurldecode($this->email)]);
        }
        return str_replace("+++AKTIVIERUNGSLINK+++", $subscribe_link, $content);
    }

    /**
     * Aktualisiert den Benutzer in der Datenbank.
     */
    public function save()
    {
        // todo: throw error on wrong email
        if (filter_var($this->getValue('email'), FILTER_VALIDATE_EMAIL) === false) {
            $this->setValue('email', '');
        }
		if(!isset($this->data['createdate'])) {
			$this->setValue('createdate', $this->getValue('createdate', date('Y-m-d H:i:s')));
		}
		if(!isset($this->data['createip'])) {
			$this->setValue('createip', $this->getValue('createip', $_SERVER['REMOTE_ADDR']));
		}
		if(!isset($this->data['activationdate'])) {
			$this->setValue('activationdate', $this->getValue('activationdate', null));
		}
		if(!isset($this->data['updatedate'])) {
		    $this->setValue('updatedate', date('Y-m-d H:i:s'));
		}
		if(!isset($this->data['updateip'])) {
	        $this->setValue('updateip', $_SERVER['REMOTE_ADDR']);
		}

        if (MultinewsletterMailchimp::isActive()) {
            $Mailchimp = MultinewsletterMailchimp::factory();
            $_status   = $this->getValue('status') == 2 ? 'unsubscribed' : ($this->getValue('status') == 1 ? 'subscribed' : 'pending');

            try {
                foreach ($this->getArrayValue('group_ids') as $group_id) {
                    $Group = new MultinewsletterGroup($group_id);

                    if (strlen($Group->mailchimp_list_id)) {
                        $result = $Mailchimp->addUserToList($this, $Group->mailchimp_list_id, $_status);
                        $this->setValue('mailchimp_id', $result['id']);
                    }
                }
            }
            catch (MultinewsletterMailchimpException $ex) {
            }
        }

        $sql = rex_sql::factory();
        $sql->setTable(rex::getTablePrefix() . '375_user');
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
     * Sendet eine Mail mit Aktivierungslink an den Abonnenten
     * @param String $sender_mail Absender der Mail
     * @param String $sender_name Bezeichnung des Absenders der Mail
     * @param String $subject Betreff der Mail
     * @param String $body Inhalt der Mail
     * @return boolean true, wenn erfolgreich versendet, sonst false
     */
    public function sendActivationMail($sender_mail, $sender_name, $subject, $body)
    {
        if (!empty($body) && strlen($this->getValue('email')) && filter_var($sender_mail, FILTER_VALIDATE_EMAIL) !== false) {
            $mail = new rex_mailer();
            $mail->IsHTML(true);
            $mail->CharSet  = "utf-8";
            $mail->From     = $sender_mail;
            $mail->FromName = $sender_name;
            $mail->Sender   = $sender_mail;
            $mail->AddAddress($this->getValue('email'), $this->getName());

            $mail->Subject = $this->personalize($subject);
            $mail->Body    = rex_extension::registerPoint(new rex_extension_point('multinewsletter.preSend', $this->personalize($body), [
                'mail' => $mail,
                'user' => $this,
            ]));
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
    public function sendAdminNoctificationMail($type)
    {
        $addon = rex_addon::get('multinewsletter');

        if (filter_var($addon->getConfig('subscribe_meldung_email'), FILTER_VALIDATE_EMAIL) !== false) {
            $mail = new rex_mailer();
            $mail->IsHTML(true);
            $mail->CharSet  = "utf-8";
            $mail->From     = $addon->getConfig('sender');
            $mail->FromName = $addon->getConfig('lang_' . $this->getValue('clang_id') . "_sendername");
            $mail->Sender   = $addon->getConfig('sender');

            $mail->AddAddress($addon->getConfig('subscribe_meldung_email'));

            if ($type == "subscribe") {
                $mail->Subject = "Neue Anmeldung zum Newsletter";
                $mail->Body    = "Neue Anmeldung zum Newsletter: " . $this->getValue('email');
            }
            else {
                $mail->Subject = "Abmeldung vom Newsletter";
                $mail->Body    = "Abmeldung vom Newsletter: " . $this->getValue('email');
            }
            return $mail->Send();
        }
        else {
            return false;
        }
    }

    /**
     * Meldet den Benutzer vom Newsletter ab.
     */
    public function unsubscribe($action = "delete") {
        if ($action == "delete") {
            $this->delete();
        }
        else {
            // $action = "status_unsubscribed"
            $this->setValue('status', 2);
            $this->save();
        }

        $this->sendAdminNoctificationMail("unsubscribe");
    }
}

/**
 * Liste Benutzer des MultiNewsletters.
 */
class MultinewsletterUserList
{
    /**
     * @var MultinewsletterUser[] Array mit Benutzerobjekten.
     */
    var $users = [];

    /**
     * Stellt die Daten des Benutzers aus der Datenbank zusammen.
     * @param MultinewsletterUser[] $user_ids Array mit UserIds aus der Datenbank.
     */
    public function __construct($user_ids)
    {
        foreach ($user_ids as $id) {
            $this->users[] = new MultinewsletterUser($id);
        }
    }

    /**
     * Exportiert die Benutzerliste als CSV und sendet das Dokument als CSV.
     */
    public static function countAll()
    {
        $query  = "SELECT COUNT(*) as total FROM " . rex::getTablePrefix() . "375_user WHERE email != ''";
        $result = rex_sql::factory();
        $result->setQuery($query);

        return $result->getValue("total");
    }

    /**
     * Get all users.
     * @param boolean $ignoreInactive only online news
     * @param int $clang_id Redaxo clang id.
     * @return News[] Array with User objects.
     */
    public static function getAll($ignoreInactive = true, $clang_id = null)
    {
        $users  = [];
        $sql    = rex_sql::factory();
        $filter = ['1'];
        $params = [];

        if ($ignoreInactive) {
            $filter[] = 'email != ""';
            $filter[] = 'status = 1';
        }
        if ($clang_id > 0) {
            $where[]            = "clang_id = :clang_id";
            $params['clang_id'] = $clang_id;
        }
        $query = 'SELECT id FROM ' . rex::getTablePrefix() . "375_user WHERE " . implode(' AND ', $filter) . " ORDER BY firstname, lastname";
        $sql->setQuery($query, $params);
        $num_rows = $sql->getRows();

        for ($i = 0; $i < $num_rows; $i++) {
            $users[] = new MultinewsletterUser($sql->getValue('id'));
            $sql->next();
        }
        return $users;
    }

    /**
     * Exportiert die Benutzerliste als CSV und sendet das Dokument als CSV.
     */
    public function exportCSV()
    {
        if (count($this->users)) {
            $cols  = array_keys($this->users[0]->getData());
            $lines = [implode(';', $cols)];

            foreach ($this->users as $user) {
                $line    = array_values($user->getData());
                $lines[] = implode(';', $line);
            }

            $content = implode("\n", $lines);

            header("Cache-Control: public");
            header("Content-Description: File Transfer");
            header('Content-disposition: attachment; filename=multinewsletter_user.csv');
            header("Content-Type: application/csv");
            header("Content-Transfer-Encoding: binary");
            header('Content-Length: ' . strlen($content));
            print($content);
            exit;
        }
    }
}