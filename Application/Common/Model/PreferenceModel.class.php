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

    /**
     * 获取user所有preferences
     * @return array
     */
    public function get_preferences($uid, $limit = 10){
        $map['uid'] = $uid;
        $preferences = $this->where($map)->order('weight desc')->limit($limit)->select();
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
     * @param int $uid
     * @param string $keywork
     * @param int $weight
     * @return bool
     */
    public function add_preference($uid, $keywork, $weight){
        $preference = array(
            'uid' => $uid,
            'keywork' => $keywork,
            'weight' => $weight
        );
        if(!$this->add($preference)){
            $this->error = 'Failed to add preference.';
            return false;
        }
    }

    /**
     * get by keyword
     * @param $uid
     * @param $keyword
     * @return array
     */
    public function get_preference_by_keyword($uid, $keyword){
        $ret = $this->where(array('uid' => $uid, 'keyword' => $keyword))->find();
        return $ret;
    }

    /**
     * 更新
     * @param $pref_id
     * @param $preference
     * @return array
     */
    public function update_preference($pref_id, $preference){
        $ret = $this->where(array('pref_id' => $pref_id))->save($preference);
        return $ret;
    }

    /**
     * 更新
     * @param $uid
     * @param $keyword
     * @param $weight
     * @return array
     */
    public function update_preference_by_keyword($uid, $keyword, $weight){
        $ret = $this->where(array('uid' => $uid, 'keyword' => $keyword))->save(array('weight' => $weight));
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
     * @param $uid
     * @param $keyword
     * @return array
     */
    public function delete_preference_by_keyword($uid, $keyword){
        $ret = $this->where(array('uid' => $uid, 'keyword' => $keyword))->delete();
        return $ret;
    }
}
