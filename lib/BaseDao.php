<?php

namespace lib;

class BaseDao {

    protected $db = null;

    protected $tableName = '';

    protected static $_models = array();

    protected function __construct() {
        // disallow new instance
        $this->db = DBManager::getDB();
        if ($this->db === false) {
            throw new Exception("can not connect to db", 1);
        }
    }

    protected function __clone() {
        // disallow clone
    }

    public static function getModel($className = __CLASS__) {
        if (!isset(self::$_models[$className])) {
            self::$_models[$className] = new $className();
        }
        return self::$_models[$className];
    }

    public function getTableName() {
        return $this->tableName;
    }

    public function getItemByID($ID) {
        $sql = sprintf('SELECT * FROM %s WHERE id = :id', $this->getTableName());
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $ID, \PDO::PARAM_INT);
        $stmt->execute();
        $ret = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $ret;
    }
}
