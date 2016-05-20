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
 * paper模型
 */
class PaperModel extends MongoModel{

    protected $trueTableName = 't_paper';
    protected $_idType       = self::TYPE_INT;
    protected $pk            = 'paper_id';

    /* paper模型自动完成 */
    protected $_auto = array(
        array('paper_id', 0, self::MODEL_INSERT),
        array('title', '', self::MODEL_INSERT),
        array('summary', '', self::MODEL_INSERT),
        array('author', '', self::MODEL_INSERT),
        array('journal', '', self::MODEL_INSERT),
        array('period', '', self::MODEL_INSERT),
        array('paper_type', '', self::MODEL_INSERT),
        array('link', '', self::MODEL_INSERT),
        array('project', '', self::MODEL_INSERT),
        array('source', '', self::MODEL_INSERT),
        array('institution', '', self::MODEL_INSERT),
        array('pubdate', NOW_TIME, self::MODEL_INSERT),
        array('tags', array(), self::MODEL_INSERT),
        array('keywords', array(), self::MODEL_INSERT),
        array('ctime', NOW_TIME, self::MODEL_INSERT),
    );

    /**
     * 获取所有paper
     * @return array
     */
    public function get_paper_list(){
        $papers = $this->order('paper_id desc')->select();
        if(!$papers){
            $papers = array();
        }
        $ret = array();
        foreach ($papers as $paper) {
            unset($paper['_id']);
            unset($paper['ctime']);
            $paper['keywords'] = implode(', ', $paper['keywords']);
            $ret[] = $paper;
        }
        return $ret;
    }

    /**
     * 获取paper信息
     * @param $paper_id
     * @return array
     */
    public function get_paper($paper_id){
        $paper = $this->where(array('paper_id' => $paper_id))->find();
        if(empty($paper)){
            $paper = array();
        }
        unset($paper['_id']);
        unset($paper['ctime']);
        return $paper;
    }

    /**
     * 添加paper
     * @param $paper
     * @return array
     */
    public function insert_paper($paper){
        $paper['ctime'] = time();
        $ret = $this->add($paper);
        return $ret;
    }

    /**
     * 更新paper
     * @param $paper_id
     * @param $paper
     * @return array
     */
    public function update_paper($paper_id, $paper){
        $ret = $this->where(array('paper_id' => $paper_id))->save($paper);
        return $ret;
    }

    /**
     * 删除paper
     * @param $paper_id
     * @return bool
     */
    public function remove_paper($paper_id){
        $ret = $this->where(array('paper_id' => $paper_id))->delete();
        return $ret;
    }
}
