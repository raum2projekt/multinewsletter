<?php

/**
 * MultiNewsletter User Group.
 *
 * @author Tobias Krais
 */
class MultinewsletterGroup extends MultinewsletterAbstract {
    /**
     * Stellt die Daten der Gruppe aus der Datenbank zusammen.
     * @param int $id Gruppen ID aus der Datenbank.
     */
    public function __construct($id) {
        if ($id) {
            $sql = rex_sql::factory();

            $sql->setTable(rex::getTablePrefix() . '375_group');
            $sql->setWhere('id = :id', ['id' => $id]);
            $sql->select();
            $this->data = @$sql->getArray()[0];

            if ($this->data['default_article_id']) {
                $article = rex_article::get($this->data['default_article_id']);

                $this->data['default_article_name'] = $article ? $article->getName() : $this->data['default_article_id'];
            }
        }
    }

    /**
     * Löscht die Gruppe aus der Datenbank.
     */
    public function delete() {
        $sql = rex_sql::factory();
        $sql->setTable(rex::getTablePrefix() . '375_group');
        $sql->setWhere('id = :id', ['id' => $this->getId()]);
        return $sql->delete();
    }

	/**
	 * Get name groups name
	 * @return string Name
	 */
    public function getName() {
        return trim($this->getValue('name'));
    }

	/**
     * Wandelt das Objekt in einen Array um.
     * @return array Array mit den Inhalten des Gruppenobjekts
     */
    public function toArray()
    {
        $this->data['group_id'] = $this->getId();
        return $this->data;
    }
}

class MultinewsletterGroupList
{
    /**
     * Holt alle Gruppen aus der Datenbank
     * @return Array Array mit allen Gruppen Objekten der Datenbank
     */
    public static function getAll() {
        $groups = [];
        $sql    = rex_sql::factory();
        $sql->setQuery('SELECT id FROM ' . rex::getTablePrefix() . '375_group ORDER BY name');
        $num_rows = $sql->getRows();

        for ($i = 0; $i < $num_rows; $i++) {
            $groups[] = new MultinewsletterGroup($sql->getValue('id'));
            $sql->next();
        }
        return $groups;
    }

    /**
     * Holt alle Gruppen aus der Datenbank und gibt sie als Array aus
     * @return Array Array mit allen Gruppen Arrays der Datenbank
     */
    public static function getAllAsArray() {
        $groups = [];
        $sql    = rex_sql::factory();
        $sql->setQuery('SELECT id FROM ' . rex::getTablePrefix() . '375_group ORDER BY name');
        $num_rows = $sql->getRows();

        for ($i = 0; $i < $num_rows; $i++) {
            $_group   = new MultinewsletterGroup($sql->getValue('id'));
            $groups[$_group->getId()] = $_group->toArray();
            $sql->next();
        }
        return $groups;
    }
}