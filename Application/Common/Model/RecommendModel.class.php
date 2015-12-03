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
 * recommend模型
 */
class RecommendModel extends MongoModel{

    protected $trueTableName = 't_recommend';
    protected $_idType       = self::TYPE_INT;
    protected $pk            = 'recommend_id';

    /* recommend模型自动完成 */
    protected $_auto = array(
        array('recommend_id', 0, self::MODEL_INSERT),
        array('uid', '', self::MODEL_INSERT),
        array('location_id', 0, self::MODEL_INSERT),
        array('resource_id', '', self::MODEL_INSERT),
        array('source', 'book', self::MODEL_INSERT),
        array('similarity', 0, self::MODEL_INSERT), //0-100
        array('mtime', NOW_TIME, self::MODEL_BOTH),
        array('ctime', NOW_TIME, self::MODEL_INSERT),
    );

    /**
     * 获取recommend列表
     * @return array
     */
    public function get_recommend_list($uid, $locations, $similarity = 80, $start = 0, $limit = 10){
        if(!isset($locations)){
            $locations = array();
        }
        $locations[] = 0;
        $params = array(
            'uid' => $uid,
            'location_id' => array('in' => $locations),
            'similarity' => array('$gte' => $similarity)
        );
        $recommends = $this->where($params)->order('similarity desc')->limit($start, $limit)->select();
        if(!$recommends){
            $recommends = array();
        }
        return $recommends;
    }

    /**
     * 获取recommend信息
     * @param $recommend_id
     * @return array
     */
    public function get_recommend($recommend_id){
        $recommend = $this->where(array('recommend_id' => $recommend_id))->find();
        if(empty($recommend)){
            $recommend = array();
        }
        unset($recommend['_id']);
        unset($recommend['ctime']);
        unset($recommend['mtime']);
        return $recommend;
    }

    /**
     * 添加recommend
     * @param $recommend
     * @return array
     */
    public function insert_recommend($recommend){
        $recommend['mtime'] = time();
        $recommend['ctime'] = time();
        $ret = $this->add($recommend);
        return $ret;
    }

    /**
     * 更新recommend
     * @param $recommend_id
     * @param $recommend
     * @return array
     */
    public function update_recommend($recommend_id, $recommend){
        $recommend['mtime'] = time();
        $ret = $this->where(array('recommend_id' => $recommend_id))->save($recommend);
        return $ret;
    }

    /**
     * 删除recommend
     * @param $recommend_id
     * @return bool
     */
    public function remove_recommend($recommend_id){
        $ret = $this->where(array('recommend_id' => $recommend_id))->delete();
        return $ret;
    }
}
