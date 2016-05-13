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
 * Preference模型
 */
class PreferenceModel extends MongoModel{
    protected $trueTableName = 't_preference';
    protected $_idType       = self::TYPE_INT;
    protected $pk            = 'pref_id';

    /* Preference模型自动完成 */
    protected $_auto = array(
        array('pref_id', 0, self::MODEL_INSERT),
        array('keyword', '', self::MODEL_INSERT),
        array('type', 1, self::MODEL_INSERT), //1: user / 2: course
        array('weight', 0.0, self::MODEL_INSERT),
        array('start_time', 0, self::MODEL_INSERT),
        array('end_time', 0, self::MODEL_INSERT),
        array('count', 0, self::MODEL_INSERT),
        array('mtime', NOW_TIME, self::MODEL_BOTH),
        array('ctime', NOW_TIME, self::MODEL_INSERT),
    );

    /**
     * 获取user所有preferences
     * @param int $type
     * @param int $start
     * @param int $limit
     * @return array
     */
    public function get_preferences($type = 1, $start = 0, $limit = 10){
        $preferences = $this->where(array('type' => $type))->order('weight desc')->limit($start.','.$limit)->select();
        if(!$preferences){
            $preferences = array();
        }
        $ret = array();
        foreach ($preferences as $preference) {
            unset($preference['_id']);
            $ret[] = $preference;
        }
        return $ret;
    }

    /**
     * 获取所有preferences
     * @return array
     */
    public function get_all_preferences(){
        $preferences = $this->select();
        if(!$preferences){
            $preferences = array();
        }
        $ret = array();
        foreach ($preferences as $preference) {
            unset($preference['_id']);
            $ret[] = $preference;
        }
        return $ret;
    }

    /**
     * 获取所有preferences
     * @param int $type
     * @return array
     */
    public function get_preferences_by_type($type = 1){
        $preferences = $this->where(array('type' => $type))->select();
        if(!$preferences){
            $preferences = array();
        }
        $ret = array();
        foreach ($preferences as $preference) {
            unset($preference['_id']);
            $ret[] = $preference;
        }
        return $ret;
    }

    /**
     * @param string $keyword
     * @param int $weight
     * @param int $type
     * @param int $start_time
     * @param int $end_time
     * @return bool
     */
    public function add_keyword($keyword, $weight, $type, $start_time, $end_time){
        $preference = array(
            'keyword' => $keyword,
            'weight' => $weight,
            'type' => $type,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'count' => 0,
            'mtime' => time(),
            'ctime' => time(),
        );
        if(!$this->add($preference)){
            $this->error = 'Failed to add preference.';
            return false;
        }
    }

    /**
     * @param array $preference
     * @return bool
     */
    public function add_preference($preference){
        if(!$this->add($preference)){
            $this->error = 'Failed to add preference.';
            return false;
        }
    }

    /**
     * get by keyword
     * @param $keyword
     * @return array
     */
    public function get_preference_by_keyword($keyword){
        $ret = $this->where(array('keyword' => $keyword))->find();
        return $ret;
    }

    /**
     * 更新
     * @param $pref_id
     * @param $preference
     * @return array
     */
    public function update_preference($pref_id, $preference){
        $preference['mtime'] = time();
        $ret = $this->where(array('pref_id' => $pref_id))->save($preference);
        return $ret;
    }

    /**
     * 更新
     * @param $keyword
     * @param $preference
     * @return array
     */
    public function update_preference_by_keyword($keyword, $preference){
        $preference['mtime'] = time();
        $ret = $this->where(array('keyword' => $keyword))->save($preference);
        return $ret;
    }


    /**
     * delete
     * @param $pref_id
     * @return array
     */
    public function delete_preference($pref_id){
        $ret = $this->where(array('pref_id' => $pref_id))->delete();
        return $ret;
    }

    /**
     * delete by keyword
     * @param $keyword
     * @return array
     */
    public function delete_preference_by_keyword($keyword){
        $ret = $this->where(array('keyword' => $keyword))->delete();
        return $ret;
    }
}
