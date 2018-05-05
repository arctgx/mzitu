<?php
namespace dao;

use lib\BaseDao;
use lib\Log;

class Pic extends BaseDao {

    const DL_STATUS_TODO = 0;
    const DL_STATUS_DONE = 1;


    public static function getModel($className='') {
        $model = parent::getModel(__CLASS__);
        $model->tableName = 'pic';
        return $model;
    }

    public function addItem($addData) {

        $sql = sprintf(
            'INSERT INTO %s (album_id, rank, url, create_time, update_time) VALUES (:album_id, :rank, :url, :create_time, :update_time)',
            $this->getTableName()
        );
        $addData['create_time'] = $addData['update_time'] = time();
        $stmt = $this->db->prepare($sql);

        $stmt->bindParam(':album_id',    $addData['album_id'],    \PDO::PARAM_INT);
        $stmt->bindParam(':rank',        $addData['rank'],        \PDO::PARAM_INT);
        $stmt->bindParam(':url',         $addData['url'],         \PDO::PARAM_STR);
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

    public function getByAlbumRank($albumID, $rank) {
        $sql = sprintf(
            'SELECT * FROM %s WHERE album_id=:album_id AND rank=:rank',
            $this->getTableName()
        );
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':album_id', $albumID, \PDO::PARAM_INT);
        $stmt->bindParam(':rank', $rank, \PDO::PARAM_INT);
        $stmt->execute();

        $ret = $stmt->fetch(\PDO::FETCH_ASSOC);
        return empty($ret) ? array() : $ret;
    }

    public function getNotDownloadPics($lastID, $reqNum) {
        $sql = sprintf(
            'SELECT * FROM %s WHERE id>:last_id AND dl_status=%d ORDER BY id ASC LIMIT :req_num',
            $this->getTableName(), self::DL_STATUS_TODO
        );

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':last_id', $lastID, \PDO::PARAM_INT);
        $stmt->bindParam(':req_num', $reqNum, \PDO::PARAM_INT);
        $stmt->execute();
        $ret = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return empty($ret) ? array() : $ret;
    }

    public function updateInfo($itemID, $updateData) {
        $canUpFields = array(
            'dl_status' => \PDO::PARAM_INT,
            'file_name' => \PDO::PARAM_STR,
            'file_size' => \PDO::PARAM_INT,
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
        $bindKeys[] = '`update_time`=:update_time';
        $updateData['update_time'] = time();
        $canUpFields['update_time'] = \PDO::PARAM_INT;

        $sql = sprintf(
            'UPDATE %s SET %s WHERE id=:id',
            $this->getTableName(), implode(',', $bindKeys)
        );
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $itemID, \PDO::PARAM_INT);
        foreach ($keys as $key) {
            $stmt->bindParam(':'.$key, $updateData[$key], $canUpFields[$key]);
        }

        $ret = $stmt->execute();
        if ($ret===false) {
            return false;
        }
        return $stmt->rowCount();
    }

}
