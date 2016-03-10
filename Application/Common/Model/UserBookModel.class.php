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
 * Book模型
 */
class UserBookModel extends MongoModel{

    protected $trueTableName = 't_user_book';

    /* user book模型自动完成 */
    protected $_auto = array(
        array('uid', 0, self::MODEL_INSERT),
        array('book_id', 0, self::MODEL_INSERT),
        array('status', 0, self::MODEL_INSERT),
        array('similarity', 0.0, self::MODEL_INSERT),
        array('mtime', NOW_TIME, self::MODEL_BOTH),
    );

    /**
     * 获取所有User book
     * @return array
     */
    public function get_user_book_list(){
        $user_books = $this->order('similarity desc')->select();
        if(!$user_books){
            $user_books = array();
        }
        $ret = array();
        foreach ($user_books as $user_book) {
            unset($user_book['_id']);
            unset($user_book['mtime']);
            $ret[] = $user_book;
        }
        return $ret;
    }

    /**
     * 获取所有User book
     * @param $uid
     * @param $similarity
     * @param $limit
     * @return array
     */
    public function get_user_books($uid, $similarity = 0, $limit = 5){
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
        $user_books = $this->where($param)->order('similarity desc')->limit($limit)->select();
        if(!$user_books){
            $user_books = array();
        }
        $ret = array();
        foreach ($user_books as $user_book) {
            unset($user_book['_id']);
            $ret[] = $user_book;
        }
        return $ret;
    }

    /**
     * 获取user_book信息
     * @param $uid
     * @param $book_id
     * @return array
     */
    public function get_user_book($uid, $book_id){
        $user_book = $this->where(array('uid' => $uid, 'book_id' => $book_id))->find();
        if(empty($user_book)){
            $user_book = array();
        }
        unset($user_book['_id']);
        unset($user_book['ctime']);
        return $user_book;
    }

    /**
     * 添加user book
     * @param $user_book
     * @return array
     */
    public function insert_user_book($user_book){
        $user_book['mtime'] = time();
        $ret = $this->add($user_book);
        return $ret;
    }

    /**
     * 更新user book
     * @param $uid
     * @param $book_id
     * @param $user_book
     * @return array
     */
    public function update_user_book($uid, $book_id, $user_book){
        $user_book['mtime'] = time();
        $ret = $this->where(array('uid' => $uid, 'book_id' => $book_id))->save($user_book);
        return $ret;
    }

    /**
     * 删除user book
     * @param $uid
     * @param $book_id
     * @return bool
     */
    public function remove_user_book($uid, $book_id){
        $ret = $this->where(array('uid' => $uid, 'book_id' => $book_id))->delete();
        return $ret;
    }
}
