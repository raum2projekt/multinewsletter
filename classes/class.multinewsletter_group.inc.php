<?php
/**
 * MultiNewsletter User Group.
 *
 * @author Tobias Krais
 */
class MultinewsletterGroup {
	/**
	 * @var int Unique Gruppen ID .
	 */
	var $group_id = 0;
	
	/**
	 * @var String Name der Gruppe.
	 */
	var $name = "";
	
	/**
	 * @var String Von den Einstellungen abweichende Absender E-Mailadresse.
	 */
	var $default_sender_email = "";
	
	/**
	 * @var String Von den Einstellungen abweichender Absendername.
	 */
	var $default_sender_name = "";
	
	/**
	 * @var int ArtikelID bei der die Linkmap in den Versandeinstellungen öffnet.
	 */
	var $default_article_id = 0;

	/**
	 * @var String Name des Artikels.
	 */
	var $default_article_name = "";
	
	/**
	 * @var int Unix Datum der Erstellung des Datensatzes in der Datenbank.
	 */
	var $createdate = 0;

	/**
	 * @var int Unixdatum der letzten Aktualisierung des Datensatzes
	 */
	var $updatedate = 0;

	/**
	 * @var String Tabellenpräfix von Redaxo
	 */
	var $table_prefix = "rex_";

	/**
	 * Stellt die Daten der Gruppe aus der Datenbank zusammen.
	 * @param int $group_id Gruppen ID aus der Datenbank.
	 * @param String $table_prefix Redaxo Tabellen Praefix ($REX['TABLE_PREFIX'])
	 */
	 public function __construct($group_id, $table_prefix = "rex_") {
		$this->table_prefix = $table_prefix;
		$this->group_id = $group_id;
		
		$query = "SELECT * FROM ". $this->table_prefix ."375_group "
				."WHERE group_id = ". $this->group_id ." "
				."LIMIT 0, 1";
		$result = new rex_sql();
		$result->setQuery($query);
		$num_rows = $result->getRows();

		if ($num_rows > 0) {
			$this->name = $result->getValue("name");
			$this->default_sender_email = $result->getValue("default_sender_email");
			$this->default_sender_name = $result->getValue("default_sender_name");
			$this->default_article_id = $result->getValue("default_article_id");
			if($this->default_article_id > 0) {
					$article = OOArticle::getArticleById($this->default_article_id, 0);
					if($article instanceof OOArticle) {
						$this->default_article_name = $article->getValue("name");
					}
					else {
						$this->default_article_name = $this->default_article_id;
					}
			}
			$this->createdate = $result->getValue("createdate");
			$this->updatedate = $result->getValue("updatedate");
		}
	}
	
	/**
	 * Löscht die Gruppe aus der Datenbank.
	 */
	public function delete() {
		$query = "DELETE FROM ". $this->table_prefix ."375_group WHERE group_id = ". $this->group_id;
		$result = new rex_sql();
		$result->setQuery($query);		
	}

	/**
	 * Wandelt das Objekt in einen Array um.
	 * @return array Array mit den Inhalten des Gruppenobjekts
	 */
	public function toArray() {
		$group = array(
			'group_id' => $this->group_id,
			'name' => $this->name,
			'default_sender_email' => $this->default_sender_email,
			'default_sender_name' => $this->default_sender_name,
			'default_article_id' => $this->default_article_id,
			'default_article_name' => $this->default_article_name,
			'createdate' => $this->createdate,
			'updatedate' => $this->updatedate,
		);

		return $group;
	}
}

class MultinewsletterGroupList {
	/**
	 * Holt alle Gruppen aus der Datenbank
	 * @param String $table_prefix Redaxo Tabellen Praefix ($REX['TABLE_PREFIX'])
	 * @return Array Array mit allen Gruppen Objekten der Datenbank
	 */
	public static function getAll($table_prefix = "rex_") {
		$query = "SELECT group_id FROM ". $table_prefix ."375_group "
			."ORDER BY name";
		$result = new rex_sql();
		$result->setQuery($query);
		$num_rows = $result->getRows();
		
		$groups = array();
		for($i = 0; $i < $num_rows; $i++) {
			$groups[] = new MultinewsletterGroup($result->getValue('group_id'), $table_prefix);
			$result->next();
		}
		return $groups;
	}

	/**
	 * Holt alle Gruppen aus der Datenbank und gibt sie als Array aus
	 * @param String $table_prefix Redaxo Tabellen Praefix ($REX['TABLE_PREFIX'])
	 * @return Array Array mit allen Gruppen Arrays der Datenbank
	 */
	public static function getAllAsArray($table_prefix = "rex_") {
		$query = "SELECT group_id FROM ". $table_prefix ."375_group "
			."ORDER BY name";
		$result = new rex_sql();
		$result->setQuery($query);
		$num_rows = $result->getRows();
		
		$groups = array();
		for($i = 0; $i < $num_rows; $i++) {
			$group = new MultinewsletterGroup($result->getValue('group_id'), $table_prefix);
			$result->next();
		
			$groups[$group->group_id] = $group->toArray();
		}
		return $groups;
	}
}
