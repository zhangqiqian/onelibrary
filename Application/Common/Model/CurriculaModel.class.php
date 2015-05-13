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
 * Curricula模型
 */
class CurriculaModel extends MongoModel{

    protected $trueTableName = 't_curricula';
    protected $_idType       = self::TYPE_INT;
    protected $pk            = 'curricula_id';

    /* Curricula模型自动完成 */
    protected $_auto = array(
        array('curricula_id', 0, self::MODEL_INSERT),
        array('major', 0, self::MODEL_INSERT),
        array('class', 0, self::MODEL_INSERT),
        array('curricula', array(), self::MODEL_INSERT),
        array('term', 0, self::MODEL_INSERT),
        array('term_start', NOW_TIME, self::MODEL_INSERT),
        array('term_end', NOW_TIME, self::MODEL_INSERT),
        array('status', 1, self::MODEL_INSERT),
        array('desc', '', self::MODEL_INSERT),
        array('mtime', NOW_TIME, self::MODEL_BOTH),
    );

    /**
     * 获取所有Curricula
     * @return array
     */
    public function get_curricula_list(){
        $curriculas = $this->order('curricula_id desc')->select();
        if(!$curriculas){
            $curriculas = array();
        }
        $ret = array();
        foreach ($curriculas as $curricula) {
            unset($curricula['_id']);
            unset($curricula['mtime']);
            $ret[] = $curricula;
        }
        return $ret;
    }

    /**
     * 获取Curricula信息
     * @param $curricula_id
     * @return array
     */
    public function get_curricula($curricula_id){
        $curricula = $this->where(array('curricula_id' => $curricula_id))->find();
        if(empty($curricula)){
            $curricula = array();
        }
        unset($curricula['_id']);
        unset($curricula['mtime']);
        return $curricula;
    }

    /**
     * 添加curricula
     * @param $curricula
     * @return array
     */
    public function insert_curricula($curricula){
        $curricula['mtime'] = time();
        $ret = $this->add($curricula);
        return $ret;
    }

    /**
     * 更新curricula
     * @param $curricula_id
     * @param $curricula
     * @return array
     */
    public function update_curricula($curricula_id, $curricula){
        $curricula['mtime'] = time();
        $ret = $this->where(array('curricula_id' => $curricula_id))->save($curricula);
        return $ret;
    }

    /**
     * 删除curricula
     * @param $curricula_id
     * @return bool
     */
    public function remove_curricula($curricula_id){
        $ret = $this->where(array('curricula_id' => $curricula_id))->delete();
        return $ret;
    }
}
