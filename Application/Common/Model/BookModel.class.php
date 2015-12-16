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
class BookModel extends MongoModel{

    protected $trueTableName = 't_book';
    protected $_idType       = self::TYPE_INT;
    protected $pk            = 'book_id';

    /* book模型自动完成 */
    protected $_auto = array(
        array('book_id', 0, self::MODEL_INSERT),
        array('title', '', self::MODEL_INSERT),
        array('summary', '', self::MODEL_INSERT),
        array('author', '', self::MODEL_INSERT),
        array('isbn', '', self::MODEL_INSERT),
        array('publisher', '', self::MODEL_INSERT),
        array('classno', '', self::MODEL_INSERT),
        array('pubdate', '', self::MODEL_INSERT),
        array('tags', array(), self::MODEL_INSERT),
        array('subject', array(), self::MODEL_INSERT),
        array('mtime', NOW_TIME, self::MODEL_BOTH),
        array('ctime', NOW_TIME, self::MODEL_INSERT),
    );

    /**
     * 获取所有book
     * @return array
     */
    public function get_book_list(){
        $books = $this->order('book_id desc')->select();
        if(!$books){
            $books = array();
        }
        $ret = array();
        foreach ($books as $book) {
            unset($book['_id']);
            unset($book['ctime']);
            unset($book['mtime']);
            $book['subject'] = implode(', ', $book['subject']);
            $ret[] = $book;
        }
        return $ret;
    }

    /**
     * 获取book信息
     * @param $book_id
     * @return array
     */
    public function get_book($book_id){
        $book = $this->where(array('book_id' => $book_id))->find();
        if(empty($book)){
            $book = array();
        }
        unset($book['_id']);
        unset($book['ctime']);
        unset($book['mtime']);
        $book['subject'] = implode(' ', $book['subject']);
        return $book;
    }

    /**
     * 添加book
     * @param $book
     * @return array
     */
    public function insert_book($book){
        $book['mtime'] = time();
        $book['ctime'] = time();
        $ret = $this->add($book);
        return $ret;
    }

    /**
     * 更新book
     * @param $book_id
     * @param $book
     * @return array
     */
    public function update_book($book_id, $book){
        $book['mtime'] = time();
        $ret = $this->where(array('book_id' => $book_id))->save($book);
        return $ret;
    }

    /**
     * 删除book
     * @param $book_id
     * @return bool
     */
    public function remove_book($book_id){
        $ret = $this->where(array('book_id' => $book_id))->delete();
        return $ret;
    }
}
