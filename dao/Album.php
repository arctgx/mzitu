<?php
namespace dao;

use lib\BaseDao;
use lib\Log;

class Album extends BaseDao {

    const P_STAT_TODO = 0;
    const P_STAT_DONE = 1;

    const I_STAT_TODO = 0;
    const I_STAT_DONE = 1;

    public static function getModel($className='') {
        $model = parent::getModel(__CLASS__);
        $model->tableName = 'album';
        return $model;
    }

    public function getByAlbumID($albumID) {
        $sql = sprintf('SELECT * FROM %s WHERE album_id = :album_id', $this->getTableName());
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':album_id', $albumID, \PDO::PARAM_INT);
        $stmt->execute();
        $ret = $stmt->fetch(\PDO::FETCH_ASSOC);
        return empty($ret) ? array() : $ret;
    }

    public function addItem($addData) {
        $sql = sprintf(
            'INSERT INTO %s (album_id, title, category_id, create_at, create_time, update_time) '
            . 'VALUES (:album_id, :title, :category_id, :create_at, :create_time, :update_time)',
            $this->getTableName()
        );
        $stmt = $this->db->prepare($sql);

        if (!isset($addData['create_at'])) {
            $addData['create_at'] = 0;
        }
        if (!isset($addData['title'])) {
            $addData['title'] = '';
        }
        $addData['create_time'] = $addData['update_time'] = time();

        $stmt->bindParam(':album_id',    $addData['album_id'],    \PDO::PARAM_INT);
        $stmt->bindParam(':title',       $addData['title'],       \PDO::PARAM_STR);
        $stmt->bindParam(':category_id', $addData['category_id'], \PDO::PARAM_INT);
        $stmt->bindParam(':create_at',   $addData['create_at'],   \PDO::PARAM_INT);
        $stmt->bindParam(':create_time', $addData['create_time'], \PDO::PARAM_INT);
        $stmt->bindParam(':update_time', $addData['update_time'], \PDO::PARAM_INT);

        $ret = $stmt->execute();
        if ($ret) {
            return $this->db->lastInsertId();
        } else {
            $strLog = sprintf('mysql error! error code [%s] error info [%s]', $stmt->errorCode(), json_encode($stmt->errorInfo()));
            Log::output($strLog);
            return false;
        }
    }

    public function getNotProcessd($lastID, $reqNum = 100) {
        $sql = sprintf(
            'SELECT * FROM %s WHERE id > :last_id AND info_status=%d ORDER BY id ASC LIMIT :req_num',
            $this->getTableName(), self::I_STAT_TODO
        );
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':last_id', $lastID, \PDO::PARAM_INT);
        $stmt->bindParam(':req_num', $reqNum, \PDO::PARAM_INT);
        $stmt->execute();
        $ret = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        // printf("sql %s\n", $sql);
        // var_dump($ret);
        return empty($ret) ? array() : $ret;
    }

    public function getNotProcessdPicAlbums($lastID, $reqNum = 100) {
        $sql = sprintf(
            'SELECT * FROM %s WHERE id > :last_id AND process_status=%d ORDER BY id ASC LIMIT :req_num',
            $this->getTableName(), self::P_STAT_TODO
        );
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':last_id', $lastID, \PDO::PARAM_INT);
        $stmt->bindParam(':req_num', $reqNum, \PDO::PARAM_INT);
        $stmt->execute();
        $ret = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        // printf("sql %s\n", $sql);
        // var_dump($ret);
        return empty($ret) ? array() : $ret;
    }

    public function updateInfo($itemID, $updateData) {
        $canUpFields = array(
            'title'          => \PDO::PARAM_STR,
            'create_at'      => \PDO::PARAM_INT,
            'total_pic'      => \PDO::PARAM_INT,
            'process_status' => \PDO::PARAM_INT,
            'info_status'    => \PDO::PARAM_INT,
        );

        $keys = $bindKeys = array();
        foreach ($updateData as $key => $value) {
            if (isset($canUpFields[$key])) {
                $keys[] = $key;
                $bindKeys[] = '`'.$key.'`'.'=:'.$key;
            }
        }
        if (empty($keys)) {
            return true;
        }
        $keys[] = 'update_time';
        $bindKeys[] = 'update_time=:update_time';
        $updateData['update_time'] = time();
        $canUpFields['update_time'] = \PDO::PARAM_INT;

        $sql = sprintf(
            'UPDATE %s SET %s WHERE id=:id',
            $this->getTableName(),
            implode(', ', $bindKeys)
        );
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $itemID, \PDO::PARAM_INT);
        foreach ($keys as $oneKey) {
            $stmt->bindParam(':'.$oneKey, $updateData[$oneKey], $canUpFields[$oneKey]);
        }
        $ret = $stmt->execute();
        if ($ret===false) {
            return false;
        }
        return $stmt->rowCount();
    }
}
