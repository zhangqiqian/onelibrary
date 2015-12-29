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
    protected $_idType       = self::TYPE_INT;
    protected $pk            = 'location_id';

    /* Location模型自动完成 */
    protected $_auto = array(
        array('location_id', 0, self::MODEL_INSERT),
        array('uid', 0, self::MODEL_INSERT),
        array('longitude', 0.0, self::MODEL_INSERT),
        array('latitude', 0.0, self::MODEL_INSERT),
        array('time', NOW_TIME, self::MODEL_BOTH),
    );

    /**
     * 获取所有User Locations
     * @return array
     */
    public function get_location_list(){
        $locations = $this->order('location_id')->select();
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
     * 获取User的Locations
     * @param $uid
     * @return array
     */
    public function get_locations_by_user($uid){
        $map = array(
            'uid' => intval($uid)
        );

        $locations = $this->where($map)->order('time desc')->select();
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
     * @param $location_id
     * @return array
     */
    public function get_user_location($location_id){
        $location = $this->where(array('location_id' => intval($location_id)))->find();
        if(empty($location)){
            $location = array();
        }
        unset($location['_id']);
        return $location;
    }

    /**
     * 根据经纬度获取Location信息
     * @param $longitude
     * @param $latitude
     * @return array
     */
    public function get_locations_by_location($longitude, $latitude){
        $longitude_start = $longitude - 0.005;
        $longitude_end = $longitude + 0.005;
        $latitude_start = $latitude - 0.005;
        $latitude_end = $latitude + 0.005;

        $map = array(
            'longitude' => array('$gt' => $longitude_start, '$lt' => $longitude_end),
            'latitude' => array('$gt' => $latitude_start, '$lt' => $latitude_end)
        );

        $locations = $this->where($map)->select();
        $ret = array();
        foreach ($locations as $location) {
            unset($location['_id']);
            $ret[] = $location;
        }
        $range = array(
            'longitude_start' => $longitude_start,
            'longitude_end' => $longitude_end,
            'latitude_start' => $latitude_start,
            'latitude_end' => $latitude_end
        );
        $ret['range'] = $range;
        return $ret;
    }

    /**
     * 添加User Location 1
     * @param $location
     * @return array
     */
    public function insert_location($location){
        $location['time'] = time();
        $ret = $this->add($location);
        return $ret;
    }

    /**
     * 添加User Location 2
     * @param $uid
     * @param $longitude
     * @param $latitude
     * @return array
     */
    public function add_location($uid, $longitude, $latitude){
        $location = array(
            'uid' => intval($uid),
            'longitude' => floatval($longitude),
            'latitude' => floatval($latitude),
            'time' => time(),
        );
        $ret = $this->add($location);
        return $ret;
    }

    /**
     * 更新User Location
     * @param $location_id
     * @param $longitude
     * @param $latitude
     * @return array
     */
    public function update_location($location_id, $uid, $longitude, $latitude){
        $location = array(
            'uid' => intval($uid),
            'longitude' => floatval($longitude),
            'latitude' => floatval($latitude),
            'time' => time(),
        );
        $ret = $this->where(array('location_id' => $location_id))->save($location);
        return $ret;
    }

    /**
     * 删除User Location
     * @param $location_id
     * @return array
     */
    public function remove_location($location_id){
        $ret = $this->where(array('location_id' => intval($location_id)))->delete();
        return $ret;
    }

    /**
     * 删除User Location
     * @param $uid
     * @return array
     */
    public function remove_location_by_user($uid){
        $ret = $this->where(array('uid' => intval($uid)))->delete();
        return $ret;
    }
}