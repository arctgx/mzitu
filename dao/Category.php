<?php
namespace dao;

use lib\BaseDao;

class Category extends BaseDao
{
    public static function getModel($className='') {
        $model = parent::getModel(__CLASS__);
        $model->tableName = 'category';
        return $model;
    }

    public function getAllCategory() {
        $sql = sprintf('SELECT * FROM %s', $this->getTableName());
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $ret = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $ret;
    }
}
