<?php
/**
 * Administrates background send cronjob for MultiNewsletter.
 */
class multinewsletter_cronjob_cleanup {
	/**
	 * @var string Name of CronJob
	 */
	 static $CRONJOB_NAME = "MultiNewsletter CleanUp";
	
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
	 * Install cron job. Its also activated.
	 */
	public static function install() {
		if(\rex_addon::get('cronjob')->isAvailable()) {
			$query = "INSERT INTO `". \rex::getTablePrefix() ."cronjob` (`name`, `description`, `type`, `parameters`, `interval`, `nexttime`, `environment`, `execution_moment`, `execution_start`, `status`, `createdate`, `createuser`) VALUES "
				."('". self::$CRONJOB_NAME ."', 'Ersetzt Empfängeradressen in Archiven die älter als 4 Wochen sind. Außerdem werden nicht aktivierte Abonnenten nach 4 Wochen gelöscht.', 'rex_cronjob_phpcode', '{\"rex_cronjob_phpcode_code\":\"<?php MultinewsletterNewsletterManager::autoCleanup(); ?>\"}', '{\"minutes\":[0],\"hours\":[0],\"days\":\"all\",\"weekdays\":[1],\"months\":\"all\"}', '". date("Y-m-d H:i:s", strtotime("+5 min")) ."', '|frontend|backend|', 0, '1970-01-01 01:00:00', 1, '". date("Y-m-d H:i:s") ."', 'multinewsletter');";
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