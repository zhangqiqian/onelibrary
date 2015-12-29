<?php
// +----------------------------------------------------------------------
// | OneLibrary [ DO IT NOW! ]
// +----------------------------------------------------------------------
// | Copyright (c) 2014 niko All rights reserved.
// +----------------------------------------------------------------------
// | Author: zhangqiqian <43874051@qq.com>
// +----------------------------------------------------------------------

namespace Common\Model;
use Think\Model\MongoModel;

/**
 * UserLocationLog模型
 */
class UserLocationLogModel extends MongoModel{

    protected $trueTableName = 't_user_location_log';
    protected $_idType       = self::TYPE_INT;
    protected $pk            = 'log_id';

    /* Location模型自动完成 */
    protected $_auto = array(
        array('log_id', 0, self::MODEL_INSERT),
        array('uid', 0, self::MODEL_INSERT),
        array('longitude', 0.0, self::MODEL_INSERT),
        array('latitude', 0.0, self::MODEL_INSERT),
        array('locations', array(), self::MODEL_INSERT),
        array('mtime', NOW_TIME, self::MODEL_BOTH),
    );

    /**
     * 获取所有User Location logs
     * @return array
     */
    public function get_user_location_logs(){
        $logs = $this->order('mtime desc')->select();
        if(!$logs){
            $logs = array();
        }
        $ret = array();
        foreach ($logs as $log) {
            unset($log['_id']);
            $ret[] = $log;
        }
        return $ret;
    }

    /**
     * 获取User的Locations
     * @param $uid
     * @param $start_time
     * @param $end_time
     * @return array
     */
    public function get_location_logs_by_user($uid, $start_time = 0, $end_time = 0){
        if($start_time == 0){
            $start_time = time() - 3600;
        }
        if($end_time == 0){
            $end_time = time();
        }
        $map = array(
            'uid' => intval($uid),
            'mtime' => array(
                '$gte' => $start_time,
                '$lt' => $end_time,
            )
        );

        $logs = $this->where($map)->order('mtime desc')->select();
        if(!$logs){
            $logs = array();
        }
        $ret = array();
        foreach ($logs as $log) {
            unset($log['_id']);
            $ret[] = $log;
        }
        return $ret;
    }

    /**
     * 获取Location log信息
     * @param $log_id
     * @return array
     */
    public function get_user_location_log($log_id){
        $location_log = $this->where(array('log_id' => intval($log_id)))->find();
        if(empty($location_log)){
            $location_log = array();
        }
        unset($location_log['_id']);
        return $location_log;
    }


    /**
     * 添加User Location log
     * @param $location_log
     * @return array
     */
    public function insert_user_location_log($location_log){
        $location['mtime'] = time();
        $ret = $this->add($location_log);
        return $ret;
    }

    /**
     * 更新User Location log
     * @param $log_id
     * @param $location_log
     * @return array
     */
    public function update_user_location_log($log_id, $location_log){
        $ret = $this->where(array('log_id' => $log_id))->save($location_log);
        return $ret;
    }

    /**
     * 删除User Location log
     * @param $log_id
     * @return array
     */
    public function remove_user_location_log($log_id){
        $ret = $this->where(array('log_id' => intval($log_id)))->delete();
        return $ret;
    }

    /**
     * 删除User Location
     * @param $uid
     * @return array
     */
    public function remove_location_logs_by_user($uid){
        $ret = $this->where(array('uid' => intval($uid)))->delete();
        return $ret;
    }
}
