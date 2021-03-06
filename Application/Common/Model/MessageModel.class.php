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
 * Message模型
 */
class MessageModel extends MongoModel{

    protected $trueTableName = 't_message';
    protected $_idType       = self::TYPE_INT;
    protected $pk            = 'message_id';

    /* Message模型自动完成 */
    protected $_auto = array(
        array('message_id', 0, self::MODEL_INSERT),
        array('title', '', self::MODEL_INSERT),
        array('content', '', self::MODEL_INSERT),
        array('author', array(), self::MODEL_INSERT),
        array('category', 0, self::MODEL_INSERT),
        array('link', '', self::MODEL_INSERT),
        array('pubdate', NOW_TIME, self::MODEL_INSERT),
        array('status', 0, self::MODEL_INSERT), //0, no handle; 1, handled.
        array('level', 0, self::MODEL_INSERT), //0, no level; 1...9
        array('tag_weight', array(), self::MODEL_INSERT),
        array('tags', array(), self::MODEL_INSERT),
        array('desc', '', self::MODEL_INSERT),
        array('mtime', NOW_TIME, self::MODEL_BOTH),
        array('ctime', NOW_TIME, self::MODEL_INSERT),
    );

    /**
     * 获取所有Message
     * @param string $search
     * @param int $start
     * @param int $limit
     * @return array
     */
    public function get_message_list($search = '', $start = 0, $limit = 20){
        if($search){
            $like = array('like', $search);
            $messages = $this->where(array('name' => $like))->order('message_id desc')->limit($start.','.$limit)->select();
            $count = $this->where(array('name' => $like))->count();
        }else{
            $messages = $this->order('message_id desc')->limit($start.','.$limit)->select();
            $count = $this->count();
        }

        if(!$messages){
            $messages = array();
        }
        $categories = C('MESSAGE_CATEGORIES');
        $data = array();
        foreach ($messages as $message) {
            unset($message['_id']);
            unset($message['ctime']);
            unset($message['mtime']);
            unset($message['tag_weight']);
            $message['tags'] = implode(', ', $message['tags']);
            $message['author'] = implode(', ', $message['author']);
            $message['category'] = $categories[$message['category']];
            $data[] = $message;
        }
        $ret = array(
            'messages' => $data,
            'count' => $count
        );
        return $ret;
    }

    /**
     * 获取Message信息
     * @param $message_id
     * @return array
     */
    public function get_message($message_id){
        $message = $this->where(array('message_id' => $message_id))->find();
        if(empty($message)){
            $message = array();
        }
        unset($message['_id']);
        unset($message['ctime']);
        unset($message['mtime']);
        $message['tags'] = implode(' ', $message['tags']);
        $message['author'] = implode(', ', $message['author']);
        $link = parse_url($message['link']);
        if(isset($link['host'])){
            $message['link_host'] = $link['scheme'].'://'.$link['host'];
        }else{
            $message['link_host'] = 'Unknown';
            $message['link'] = 'http://'.$message['link'];
        }
        return $message;
    }

    /**
     * 获取返回给APP的Message信息
     * @param $message_id
     * @return array
     */
    public function get_message_for_app($message_id){
        $message = $this->where(array('message_id' => $message_id))->find();
        if(empty($message)){
            $message = array();
        }
        unset($message['_id']);
        unset($message['ctime']);
        unset($message['mtime']);
        unset($message['desc']);
        unset($message['status']);
        unset($message['level']);
        unset($message['tag_weight']);
        $categories = C('MESSAGE_CATEGORIES');
        $message['category'] = $categories[$message['category']];
        $message['tags'] = implode(', ', $message['tags']);
        $message['author'] = implode(', ', $message['author']);
        $link = parse_url($message['link']);
        if(isset($link['host'])){
            $message['link_host'] = $link['scheme'].'://'.$link['host'];
        }else{
            $message['link_host'] = 'Unknown';
            $message['link'] = 'http://'.$message['link'];
        }
        return $message;
    }

    /**
     * 添加Message
     * @param $message
     * @return array
     */
    public function insert_message($message){
        $message['mtime'] = time();
        $message['ctime'] = time();
        $ret = $this->add($message);
        return $ret;
    }

    /**
     * 获取Message by category_id
     * @param $category_id
     * @return array
     */
    public function get_messages_by_category($category_id){
        $messages = $this->field('message_id,title')->where(array('category' => $category_id, 'message_id' => array('$gt' => 1)))->order('mtime desc')->limit(10)->select();
        if(!$messages){
            $messages = array();
        }
        $ret = array();
        foreach ($messages as $message) {
            unset($message['_id']);
            $ret[] = $message;
        }

        return $ret;
    }

    public function get_message_count_by_category($category_id, $start_time = 0, $end_time = 0){
        if($end_time == 0){
            $end_time = time();
        }

        $params = array(
            'category' => $category_id,
            'ctime' => array('$gte' => $start_time, '$lte' => $end_time)
        );

        $message_count = $this->where($params)->count();
        return $message_count;
    }


    /**
     * 更新Message
     * @param $message_id
     * @param $message
     * @return array
     */
    public function update_message($message_id, $message){
        $message['mtime'] = time();
        $ret = $this->where(array('message_id' => $message_id))->save($message);
        return $ret;
    }

    /**
     * 删除Message
     * @param $message_id
     * @return bool
     */
    public function remove_message($message_id){
        $ret = $this->where(array('message_id' => $message_id))->delete();
        return $ret;
    }
}
