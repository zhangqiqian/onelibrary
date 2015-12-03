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
 * Notification模型
 */
class NotificationModel extends MongoModel{

    protected $trueTableName = 't_notification';
    protected $_idType       = self::TYPE_INT;
    protected $pk            = 'notification_id';

    /* Notification模型自动完成 */
    protected $_auto = array(
        array('notification_id', 0, self::MODEL_INSERT),
        array('title', '', self::MODEL_INSERT),
        array('content', '', self::MODEL_INSERT),
        array('author', '', self::MODEL_INSERT),
        array('category', 0, self::MODEL_INSERT),
        array('link', '', self::MODEL_INSERT),
        array('pubdate', NOW_TIME, self::MODEL_INSERT),
        array('status', 0, self::MODEL_INSERT), //0, no handle; 1, handled.
        array('level', 0, self::MODEL_INSERT), //0, no level; 1...9
        array('tags', array(), self::MODEL_INSERT),
        array('desc', '', self::MODEL_INSERT),
        array('mtime', NOW_TIME, self::MODEL_BOTH),
        array('ctime', NOW_TIME, self::MODEL_INSERT),
    );

    /**
     * 获取所有Notification
     * @return array
     */
    public function get_notification_list(){
        $notifications = $this->order('notification_id desc')->select();
        if(!$notifications){
            $notifications = array();
        }
        $categories = C('MESSAGE_CATEGORIES');
        $ret = array();
        foreach ($notifications as $notification) {
            unset($notification['_id']);
            unset($notification['ctime']);
            unset($notification['mtime']);
            $notification['tags'] = implode(', ', $notification['tags']);
            $notification['author'] = implode(', ', $notification['author']);
            $notification['category'] = $categories[$notification['category']];
            $ret[] = $notification;
        }
        return $ret;
    }

    /**
     * 获取notification信息
     * @param $notification_id
     * @return array
     */
    public function get_notification($notification_id){
        $notification = $this->where(array('notification_id' => $notification_id))->find();
        if(empty($notification)){
            $notification = array();
        }
        unset($notification['_id']);
        unset($notification['ctime']);
        unset($notification['mtime']);
        $notification['tags'] = implode(' ', $notification['tags']);
        $notification['author'] = implode(', ', $notification['author']);
        $link = parse_url($notification['link']);
        if(isset($link['host'])){
            $notification['link_host'] = $link['scheme'].'://'.$link['host'];
        }else{
            $notification['link_host'] = 'Unknown';
            $notification['link'] = 'http://'.$notification['link'];
        }
        return $notification;
    }

    /**
     * 获取返回给APP的notification信息
     * @param $notification_id
     * @return array
     */
    public function get_notification_for_app($notification_id){
        $notification = $this->where(array('notification_id' => $notification_id))->find();
        if(empty($notification)){
            $notification = array();
        }
        unset($notification['_id']);
        unset($notification['ctime']);
        unset($notification['mtime']);
        unset($notification['desc']);
        unset($notification['status']);
        unset($notification['level']);
        $categories = C('MESSAGE_CATEGORIES');
        $notification['category'] = $categories[$notification['category']];
        $notification['tags'] = implode(', ', $notification['tags']);
        $notification['author'] = implode(', ', $notification['author']);
        $link = parse_url($notification['link']);
        if(isset($link['host'])){
            $notification['link_host'] = $link['scheme'].'://'.$link['host'];
        }else{
            $notification['link_host'] = 'Unknown';
            $notification['link'] = 'http://'.$notification['link'];
        }
        return $notification;
    }

    /**
     * 添加notification
     * @param $notification
     * @return array
     */
    public function insert_notification($notification){
        $notification['mtime'] = time();
        $notification['ctime'] = time();
        $ret = $this->add($notification);
        return $ret;
    }

    /**
     * 获取notification by category_id
     * @param $category_id
     * @return array
     */
    public function get_notifications_by_category($category_id){
        if($category_id == 0){
            return array();
        }
        $notifications = $this->field('notification_id,title')->where(array('category' => $category_id))->order('pubdate desc')->limit(10)->select();
        if(!$notifications){
            $notifications = array();
        }
        $ret = array();
        foreach ($notifications as $notification) {
            unset($notification['_id']);
            $ret[] = $notification;
        }

        return $ret;
    }

    /**
     * 更新notification
     * @param $notification_id
     * @param $notification
     * @return array
     */
    public function update_notification($notification_id, $notification){
        $notification['mtime'] = time();
        $ret = $this->where(array('notification_id' => $notification_id))->save($notification);
        return $ret;
    }

    /**
     * 删除notification
     * @param $notification_id
     * @return bool
     */
    public function remove_notification($notification_id){
        $ret = $this->where(array('notification_id' => $notification_id))->delete();
        return $ret;
    }
}
