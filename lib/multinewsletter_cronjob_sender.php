<?php
/**
 * Administrates background send cronjob for MultiNewsletter.
 */
class multinewsletter_cronjob_sender {
	/**
	 * @var string Name of CronJob
	 */
	 static $CRONJOB_NAME = "MultiNewsletter Sender";
	
	/**
	 * Deactivate cron job.
	 * @return boolean TRUE if successful, otherwise FALSE
	 */
	public static function activate() {
		if(\rex_addon::get('cronjob')->isAvailable() && self::isInstalled()) {
			$query = "UPDATE `". \rex::getTablePrefix() ."cronjob` SET "
				."status = 1, "
				."nexttime = '". date("Y-m-d H:i:s", strtotime("+1 min")) ."' "
				."WHERE `name` = '". self::$CRONJOB_NAME ."'";
			$sql = \rex_sql::factory();
			$sql->setQuery($query);
			return TRUE;
		}
		else {
			return FALSE;
		}
	}
	
	/**
	 * Deactivate cron job.
	 * @return boolean TRUE if successful, otherwise FALSE
	 */
	public static function deactivate() {
		if(\rex_addon::get('cronjob')->isAvailable() && self::isInstalled()) {
			$query = "UPDATE `". \rex::getTablePrefix() ."cronjob` SET status = 0 WHERE `name` = '". self::$CRONJOB_NAME ."'";
			$sql = \rex_sql::factory();
			$sql->setQuery($query);
			return TRUE;
		}
		else {
			return FALSE;
		}
	}
	
	/**
	 * Delete cron job.
	 */
	public static function delete() {
		if(\rex_addon::get('cronjob')->isAvailable()) {
			$query = "DELETE FROM `". \rex::getTablePrefix() ."cronjob` WHERE `name` = '". self::$CRONJOB_NAME ."'";
			$sql = \rex_sql::factory();
			$sql->setQuery($query);
		}
	}

	/**
	 * Install cron job. Its not activated.
	 */
	public static function install() {
		if(\rex_addon::get('cronjob')->isAvailable()) {
			$query = "INSERT INTO `". \rex::getTablePrefix() ."cronjob` (`name`, `description`, `type`, `parameters`, `interval`, `nexttime`, `environment`, `execution_moment`, `execution_start`, `status`, `createdate`, `createuser`) VALUES "
				."('". self::$CRONJOB_NAME ."', 'Sendet ausstehende Newsletter im Hintergrund. Aktiviert und deaktiviert sich automatisch.', 'rex_cronjob_phpcode', '{\"rex_cronjob_phpcode_code\":\"<?php MultinewsletterNewsletterManager::cronSend(); ?>\"}', '{\"minutes\":\"all\",\"hours\":\"all\",\"days\":\"all\",\"weekdays\":\"all\",\"months\":\"all\"}', '". date("Y-m-d H:i:s", strtotime("+5 min")) ."', '|frontend|backend|', 0, '1970-01-01 01:00:00', 0, '". date("Y-m-d H:i:s") ."', 'multinewsletter');";
			$sql = \rex_sql::factory();
			$sql->setQuery($query);
		}
	}

	/**
	 * Checks if  cron job is installed.
	 * @return boolean TRUE if Cronjob is installed, otherwise FALSE.
	 */
	public static function isInstalled() {
		if(\rex_addon::get('cronjob')->isAvailable()) {
			$query = "SELECT `name` FROM `". \rex::getTablePrefix() ."cronjob` WHERE `name` = '". self::$CRONJOB_NAME ."'";
			$sql = \rex_sql::factory();
			$sql->setQuery($query);
			if($sql->getRows() > 0) {
				return TRUE;
			}
			else {
				return FALSE;
			}
		}
	}
}