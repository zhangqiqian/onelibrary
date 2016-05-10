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
 * PublishLog模型
 */
class PublishLogModel extends MongoModel{

    protected $trueTableName = 't_publish_log';
    protected $_idType       = self::TYPE_INT;
    protected $pk            = 'log_id';

    /* publish模型自动完成 */
    protected $_auto = array(
        array('publish_id', 0, self::MODEL_INSERT),
        array('uid', 0, self::MODEL_INSERT),
        array('location_id', 0, self::MODEL_INSERT),
        array('message_id', 0, self::MODEL_INSERT),
        array('status', 0, self::MODEL_INSERT),
        array('mtime', NOW_TIME, self::MODEL_BOTH),
        array('ctime', NOW_TIME, self::MODEL_INSERT),
    );

    /**
     * 获取所有publish log
     * @return array
     */
    public function get_publish_logs(){
        $publish_logs = $this->order('log_id desc')->select();

        if(!$publish_logs){
            $publish_logs = array();
        }
        return $publish_logs;
    }

    /**
     * 获取所有publish log by uid
     * @param $uid
     * @return array
     */
    public function get_publish_logs_by_uid($uid){
        $params = array(
            'uid' => $uid, //who
        );
        $publish_logs = $this->where($params)->order('mtime desc')->select();
        if(empty($publish_logs)){
            $publish_logs = array();
        }
        return $publish_logs;
    }


    /**
     * 添加publish log
     * @param $publish_log
     * @return array
     */
    public function insert_publish_log($publish_log){
        $publish['mtime'] = time();
        $publish['ctime'] = time();
        $ret = $this->add($publish_log);
        return $ret;
    }

    /**
     * 更新publish log
     * @param $log_id
     * @param $publish_log
     * @return array
     */
    public function update_publish_log($log_id, $publish_log){
        $publish['mtime'] = time();
        $ret = $this->where(array('log_id' => $log_id))->save($publish_log);
        return $ret;
    }

    /**
     * 删除publish log
     * @param $log_id
     * @return bool
     */
    public function remove_publish_log($log_id){
        $ret = $this->where(array('log_id' => $log_id))->delete();
        return $ret;
    }

}
