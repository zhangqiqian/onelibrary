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
 * UserLocation模型
 */
class UserLocationModel extends MongoModel{

    protected $trueTableName = 't_user_location';

    /* Location模型自动完成 */
    /**
     * locations = {
     *    'location_id1': {
     *         'type': 0,
     *         'count': 0,
     *         'tags': [
     *             {
     *                'tag': 'xxx',
     *                'weight': 33.4,
     *             },
     *             ...
     *         ]
     *    }
     *    ...
     * }
     * @var array
     */
    protected $_auto = array(
        array('uid', 0, self::MODEL_INSERT),
        array('status', 0, self::MODEL_INSERT), //0/1
        array('locations', array(), self::MODEL_INSERT),
        array('mtime', NOW_TIME, self::MODEL_BOTH),
    );

    /**
     * 获取所有User Locations
     * @return array
     */
    public function get_user_location_list(){
        $locations = $this->order('uid')->select();
        if(!$locations){
            $locations = array();
        }
        $ret = array();
        foreach ($locations as $location) {
            unset($location['_id']);
            $ret[] = $location;
        }
        return $ret;
    }

    /**
     * 获取Location信息
     * @param $uid
     * @return array
     */
    public function get_user_location($uid){
        $location = $this->where(array('uid' => intval($uid)))->find();
        if(empty($location)){
            $location = array();
        }
        unset($location['_id']);
        return $location;
    }

    /**
     * 添加User Location 1
     * @param $location
     * @return array
     */
    public function insert_user_location($location){
        $location['mtime'] = time();
        $ret = $this->add($location);
        return $ret;
    }

    /**
     * 添加User Location 2
     * @param $uid
     * @param $locations
     * @return array
     */
    public function add_location($uid, $locations){
        $location = array(
            'uid' => intval($uid),
            'locations' => $locations,
            'status' => 0,
            'mtime' => time(),
        );
        $ret = $this->add($location);
        return $ret;
    }

    /**
     * 更新User Location
     * @param $uid
     * @param $user_location
     * @return array
     */
    public function update_user_location($uid, $user_location){
        $user_location['mtime'] = time();
        $ret = $this->where(array('uid' => $uid))->save($user_location);
        return $ret;
    }

    /**
     * 删除User Location
     * @param $uid
     * @return array
     */
    public function remove_location($uid){
        $ret = $this->where(array('uid' => intval($uid)))->delete();
        return $ret;
    }
}
