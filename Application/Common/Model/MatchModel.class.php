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
 * Match模型
 */
class MatchModel extends MongoModel{

    protected $trueTableName = 't_match';
    protected $_idType       = self::TYPE_INT;
    protected $pk            = 'match_id';

    /* match模型自动完成 */
    protected $_auto = array(
        array('match_id', 0, self::MODEL_INSERT),
        array('user_uid', 0, self::MODEL_INSERT),
        array('user_grade', 0, self::MODEL_INSERT),
        array('user_major', 0, self::MODEL_INSERT),
        array('user_gender', 2, self::MODEL_INSERT), //0: female, 1: male, 2: all
        array('region_id', 0, self::MODEL_INSERT),
        array('datetime', '', self::MODEL_INSERT),
        array('message_id', 0, self::MODEL_INSERT),
        array('status', 0, self::MODEL_INSERT), //0, no read; 1, read
        array('priority', 0, self::MODEL_INSERT), //order by priority
        array('mtime', NOW_TIME, self::MODEL_BOTH),
        array('ctime', NOW_TIME, self::MODEL_INSERT),
    );

    /**
     * 获取所有match
     * @return array
     */
    public function get_match_list(){
        $matches = $this->order('match_id')->select();
        if(!$matches){
            $matches = array();
        }
        $ret = array();
        foreach ($matches as $match) {
            unset($match['_id']);
            unset($match['ctime']);
            unset($match['mtime']);
            $ret[] = $match;
        }
        return $ret;
    }

    /**
     * 获取match信息
     * @param $match_id
     * @return array
     */
    public function get_match($match_id){
        $match = $this->where(array('match_id' => $match_id))->find();
        if(empty($match)){
            $match = array();
        }
        unset($match['_id']);
        unset($match['ctime']);
        unset($match['mtime']);
        $match['tags'] = implode(', ', $match['tags']);
        return $match;
    }

    /**
     * 添加match
     * @param $match
     * @return array
     */
    public function insert_match($match){
        $match['mtime'] = time();
        $match['ctime'] = time();
        $ret = $this->add($match);
        return $ret;
    }

    /**
     * 更新match
     * @param $match_id
     * @param $match
     * @return array
     */
    public function update_match($match_id, $match){
        $match['mtime'] = time();
        $ret = $this->where(array('match_id' => $match_id))->save($match);
        return $ret;
    }

    /**
     * 删除match
     * @param $match_id
     * @return bool
     */
    public function remove_match($match_id){
        $ret = $this->where(array('match_id' => $match_id))->delete();
        return $ret;
    }
}
