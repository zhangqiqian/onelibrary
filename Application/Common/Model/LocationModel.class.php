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
 * Location模型
 */
class LocationModel extends MongoModel{

    protected $trueTableName = 't_location';
    protected $_idType       = self::TYPE_INT;
    protected $pk            = 'location_id';

    /* Location模型自动完成 */
    protected $_auto = array(
        array('location_id', 0, self::MODEL_INSERT),
        array('name', '', self::MODEL_INSERT),
        array('status', 0, self::MODEL_INSERT),
        array('longitude', 0.0, self::MODEL_INSERT),
        array('latitude', 0.0, self::MODEL_INSERT),
        array('radius', 0, self::MODEL_INSERT),
        array('address', "", self::MODEL_INSERT),
        array('desc', "", self::MODEL_INSERT),
        array('mtime', NOW_TIME, self::MODEL_BOTH),
        array('ctime', NOW_TIME, self::MODEL_INSERT),
    );

    /**
     * 获取所有Location
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
            unset($location['ctime']);
            unset($location['mtime']);
            $ret[] = $location;
        }
        return $ret;
    }

    /**
     * 获取Location信息
     * @param $location_id
     * @return array
     */
    public function get_location($location_id){
        $location = $this->where(array('location_id' => $location_id))->find();
        if(empty($location)){
            $location = array();
        }
        unset($location['_id']);
        unset($location['mtime']);
        unset($location['ctime']);
        return $location;
    }

    /**
     * 根据经纬度获取Location信息
     * @param $longitude
     * @param $latitude
     * @return array
     */
    public function get_locations_by_location($longitude, $latitude){
        $longitude_start = $longitude - 0.001;
        $longitude_end = $longitude + 0.001;
        $latitude_start = $latitude - 0.001;
        $latitude_end = $latitude + 0.001;

        //$map['_string'] = '{"longitude":{"$gt": '.$longitude_start.', "$lt": '.$longitude_end.'},"latitude":{"$gt": '.$latitude_start.', "$lt": '.$latitude_end.'}}';
        $map = array();
        /*$map['_complex'] = array(
            '_logic' => 'and',
            array('longitude' => array('$gt', $longitude_start)),
            array('longitude' => array('$lt', $longitude_end)),
            array('latitude' => array('$gt', $latitude_start)),
            array('latitude' => array('$lt', $latitude_end))
        );*/
        $locations = $this->where($map)->select();
        $ret = array();
        foreach ($locations as $location) {
            unset($location['_id']);
            unset($location['mtime']);
            unset($location['ctime']);
            $ret[] = $location;
        }
        return $ret;
    }

    /**
     * 添加Location
     * @param $location
     * @return array
     */
    public function insert_location($location){
        $location['ctime'] = time();
        $location['mtime'] = time();
        $ret = $this->add($location);
        return $ret;
    }

    /**
     * 更新Location
     * @param $location_id
     * @param $location
     * @return array
     */
    public function update_location($location_id, $location){
        $location['mtime'] = time();
        $ret = $this->where(array('location_id' => $location_id))->save($location);
        return $ret;
    }

    /**
     * 删除Location
     * @param $location_id
     * @return array
     */
    public function remove_location($location_id){
        $ret = $this->where(array('location_id' => $location_id))->delete();
        return $ret;
    }
}
