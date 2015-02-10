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
        array('longitude', 0, self::MODEL_INSERT),
        array('latitude', 0, self::MODEL_INSERT),
        array('city', "", self::MODEL_INSERT),
        array('country', "", self::MODEL_INSERT),
        array('country_code', "", self::MODEL_INSERT),
        array('region', "", self::MODEL_INSERT),
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
        $grades = C('USER_GRADES');
        $ret = array();
        foreach ($locations as $location) {
            unset($location['_id']);
            unset($location['ctime']);
            unset($location['mtime']);
            $location['grade'] = $grades[$location['grade']];
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
     * 添加Location
     * @param $location
     * @return array
     */
    public function insert_location($location){
        $ret = $this->save($location);
        return $ret;
    }

    /**
     * 更新Location
     * @param $location_id
     * @param $location
     * @return array
     */
    public function update_location($location_id, $location){
        $ret = $this->where(array('location_id' => $location_id))->save($location);
        return $ret;
    }

    /**
     * 删除Location
     * @param $location_id
     * @return array
     */
    public function remove_location($location_id){
        //$location['mtime'] = time();
        $ret = $this->where(array('location_id' => $location_id))->delete();
        return $ret;
    }
}
