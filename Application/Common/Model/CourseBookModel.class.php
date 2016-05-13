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
 * CourseBook 模型
 */
class CourseBookModel extends MongoModel{

    protected $trueTableName = 't_course_book';

    /* course book模型自动完成 */
    protected $_auto = array(
        array('course_id', 0, self::MODEL_INSERT),
        array('book_id', 0, self::MODEL_INSERT),
        array('status', 0, self::MODEL_INSERT),
        array('similarity', 0.0, self::MODEL_INSERT),
        array('mtime', NOW_TIME, self::MODEL_BOTH),
    );

    /**
     * 获取所有Course book
     * @return array
     */
    public function get_course_book_list(){
        $course_books = $this->order('similarity desc')->select();
        if(!$course_books){
            $course_books = array();
        }
        $ret = array();
        foreach ($course_books as $course_book) {
            unset($course_book['_id']);
            unset($course_book['mtime']);
            $ret[] = $course_book;
        }
        return $ret;
    }

    /**
     * 获取所有Course book
     * @param $course_id
     * @param $similarity
     * @param $limit
     * @return array
     */
    public function get_course_books($course_id, $similarity = 0, $limit = 5){
        $start_time = time() - 30 * 24 * 3600;
        $param = array(
            'course_id' => $course_id,
            'status' => 0,
            'similarity' => array(
                '$gt' => $similarity
            ),
            'mtime' => array(
                '$gt' => $start_time
            )
        );
        $course_books = $this->where($param)->order('similarity desc')->limit($limit)->select();
        if(!$course_books){
            $course_books = array();
        }
        $ret = array();
        foreach ($course_books as $course_book) {
            unset($course_book['_id']);
            $ret[] = $course_book;
        }
        return $ret;
    }

    /**
     * 获取course_book信息
     * @param $course_id
     * @param $book_id
     * @return array
     */
    public function get_course_book($course_id, $book_id){
        $course_book = $this->where(array('course_id' => $course_id, 'book_id' => $book_id))->find();
        if(empty($course_book)){
            $course_book = array();
        }
        unset($course_book['_id']);
        unset($course_book['ctime']);
        return $course_book;
    }

    /**
     * 添加Course book
     * @param $course_book
     * @return array
     */
    public function insert_course_book($course_book){
        $course_book['mtime'] = time();
        $ret = $this->add($course_book);
        return $ret;
    }

    /**
     * 更新Course book
     * @param $course_id
     * @param $book_id
     * @param $course_book
     * @return array
     */
    public function update_course_book($course_id, $book_id, $course_book){
        $course_book['mtime'] = time();
        $ret = $this->where(array('course_id' => $course_id, 'book_id' => $book_id))->save($course_book);
        return $ret;
    }

    /**
     * 删除Course book
     * @param $course_id
     * @param $book_id
     * @return bool
     */
    public function remove_course_book($course_id, $book_id){
        $ret = $this->where(array('course_id' => $course_id, 'book_id' => $book_id))->delete();
        return $ret;
    }
}
