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
 * Region模型
 */
class RegionModel extends MongoModel{

    protected $trueTableName = 't_region';
    protected $_idType       = self::TYPE_INT;
    protected $pk            = 'region_id';

    /* Region模型自动完成 */
    protected $_auto = array(
        array('region_id', 0, self::MODEL_INSERT),
        array('name', '', self::MODEL_INSERT),
        array('status', 0, self::MODEL_INSERT),
        array('locations', array(), self::MODEL_INSERT),
        array('desc', "", self::MODEL_INSERT),
        array('mtime', NOW_TIME, self::MODEL_BOTH),
        array('ctime', NOW_TIME, self::MODEL_INSERT),
    );

    /**
     * 获取所有Region
     * @return array
     */
    public function get_region_list(){
        $regions = $this->order('region_id')->select();
        if(!$regions){
            $regions = array();
        }
        $ret = array();
        foreach ($regions as $region) {
            unset($region['_id']);
            unset($region['ctime']);
            unset($region['mtime']);
            $ret[] = $region;
        }
        return $ret;
    }

    /**
     * 获取Region信息
     * @param $region_id
     * @return array
     */
    public function get_region($region_id){
        $region = $this->where(array('region_id' => $region_id))->find();
        if(empty($region)){
            $region = array();
        }
        unset($region['_id']);
        unset($region['mtime']);
        unset($region['ctime']);
        return $region;
    }

    /**
     * 添加Region
     * @param $region
     * @return array
     */
    public function insert_region($region){
        $ret = $this->save($region);
        return $ret;
    }

    /**
     * 更新Region
     * @param $region_id
     * @param $region
     * @return array
     */
    public function update_region($region_id, $region){
        $ret = $this->where(array('region_id' => $region_id))->save($region);
        return $ret;
    }

    /**
     * 删除Region
     * @param $region_id
     * @return array
     */
    public function remove_region($region_id){
        $ret = $this->where(array('region_id' => $region_id))->delete();
        return $ret;
    }
}
