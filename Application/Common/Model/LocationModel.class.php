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
        array('location_type', 0, self::MODEL_INSERT),
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
     * @param string $search
     * @param int $start
     * @param int $limit
     * @return array
     */
    public function get_location_list($search = '', $start = 0, $limit = 20){

        if($search){
            $like = array('like', $search);
            $locations = $this->where(array('name' => $like))->order('location_id asc')->limit($start.','.$limit)->select();
            $total = $this->where(array('name' => $like))->count();
        }else{
            $locations = $this->order('location_id asc')->limit($start.','.$limit)->select();
            $total = $this->count();
        }

        if(!$locations){
            $locations = array();
        }
        $location_types = C('LOCATION_TYPE_MAPPING');
        $data = array();
        foreach ($locations as $location) {
            unset($location['_id']);
            unset($location['ctime']);
            unset($location['mtime']);
            $location_type = isset($location['location_type']) ? $location['location_type'] : 0;
            if(!isset($location['location_type'])){
                $location['location_type'] = $location_type;
            }
            $location['location_type_name'] = $location_types[$location_type];
            $data[] = $location;
        }

        $ret = array(
            'locations' => $data,
            'count' => $total
        );
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
        $location_types = C('LOCATION_TYPE_MAPPING');
        $location_type = isset($location['location_type']) ? $location['location_type'] : 0;
        if(!isset($location['location_type'])){
            $location['location_type'] = $location_type;
        }
        $location['location_type_name'] = $location_types[$location_type];
        unset($location['_id']);
        unset($location['mtime']);
        unset($location['ctime']);
        return $location;
    }

    public function get_all_locations(){
        $locations = $this->select();
        if(empty($locations)){
            $locations = array();
        }
        return $locations;
    }

    public function get_locations_by_type($types = array()){
        if(empty($types)){
            $locations = $this->select();
        }else{
            $locations = $this->where(array('location_type' => array('$in' => $types)))->select();
        }
        if(empty($locations)){
            $locations = array();
        }
        return $locations;
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
            unset($location['mtime']);
            unset($location['ctime']);
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
