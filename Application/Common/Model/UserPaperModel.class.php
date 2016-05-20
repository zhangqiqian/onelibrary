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
 * Paper模型
 */
class UserPaperModel extends MongoModel{

    protected $trueTableName = 't_user_paper';

    /* user paper模型自动完成 */
    protected $_auto = array(
        array('uid', 0, self::MODEL_INSERT),
        array('paper_id', 0, self::MODEL_INSERT),
        array('status', 0, self::MODEL_INSERT),
        array('similarity', 0.0, self::MODEL_INSERT),
        array('mtime', NOW_TIME, self::MODEL_BOTH),
    );

    /**
     * 获取所有User paper
     * @return array
     */
    public function get_user_paper_list(){
        $user_papers = $this->order('similarity desc')->select();
        if(!$user_papers){
            $user_papers = array();
        }
        $ret = array();
        foreach ($user_papers as $user_paper) {
            unset($user_paper['_id']);
            unset($user_paper['mtime']);
            $ret[] = $user_paper;
        }
        return $ret;
    }

    /**
     * 获取所有User paper
     * @param $uid
     * @param $similarity
     * @param $limit
     * @return array
     */
    public function get_user_papers($uid, $similarity = 0, $limit = 5){
        $start_time = time() - 30 * 24 * 3600;
        $param = array(
            'uid' => $uid,
            'status' => 0,
            'similarity' => array(
                '$gt' => $similarity
            ),
            'mtime' => array(
                '$gt' => $start_time
            )
        );
        $user_papers = $this->where($param)->order('similarity desc')->limit($limit)->select();
        if(!$user_papers){
            $user_papers = array();
        }
        $ret = array();
        foreach ($user_papers as $user_paper) {
            unset($user_paper['_id']);
            $ret[] = $user_paper;
        }
        return $ret;
    }

    /**
     * 获取user_paper信息
     * @param $uid
     * @param $paper_id
     * @return array
     */
    public function get_user_paper($uid, $paper_id){
        $user_paper = $this->where(array('uid' => $uid, 'paper_id' => $paper_id))->find();
        if(empty($user_paper)){
            $user_paper = array();
        }
        unset($user_paper['_id']);
        unset($user_paper['ctime']);
        return $user_paper;
    }

    /**
     * 添加user paper
     * @param $user_paper
     * @return array
     */
    public function insert_user_paper($user_paper){
        $user_paper['mtime'] = time();
        $ret = $this->add($user_paper);
        return $ret;
    }

    /**
     * 更新user paper
     * @param $uid
     * @param $paper_id
     * @param $user_paper
     * @return array
     */
    public function update_user_paper($uid, $paper_id, $user_paper){
        $user_paper['mtime'] = time();
        $ret = $this->where(array('uid' => $uid, 'paper_id' => $paper_id))->save($user_paper);
        return $ret;
    }

    /**
     * 删除user paper
     * @param $uid
     * @param $paper_id
     * @return bool
     */
    public function remove_user_paper($uid, $paper_id){
        $ret = $this->where(array('uid' => $uid, 'paper_id' => $paper_id))->delete();
        return $ret;
    }
}
