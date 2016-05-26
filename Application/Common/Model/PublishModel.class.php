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
 * Publish模型
 */
class PublishModel extends MongoModel{

    protected $trueTableName = 't_publish';
    protected $_idType       = self::TYPE_INT;
    protected $pk            = 'publish_id';

    /* publish模型自动完成 */
    protected $_auto = array(
        array('publish_id', 0, self::MODEL_INSERT),
        array('user_uid', 0, self::MODEL_INSERT),
        array('location_id', 0, self::MODEL_INSERT),
        array('publish_time', NOW_TIME, self::MODEL_INSERT),
        array('expire_time', 0, self::MODEL_INSERT),
        array('message_id', 0, self::MODEL_INSERT),
        array('status', 0, self::MODEL_INSERT), //0:pushed; 1:received 2:opened 3:invalid
        array('priority', 0, self::MODEL_INSERT), //order by priority
        array('similarity', 0, self::MODEL_INSERT), //0-100
        array('mtime', NOW_TIME, self::MODEL_BOTH),
        array('ctime', NOW_TIME, self::MODEL_INSERT),
    );

    /**
     * 获取所有publish
     * @param array $uids
     * @param int $start
     * @param int $limit
     * @return array
     */
    public function get_publish_list($uids = array(), $start = 0, $limit = 20){
        $where = array();
        if(!empty($uids)){
            $where['user_uid'] = array('in', $uids);
        }

        if(empty($where)){
            $publishes = $this->order('publish_time desc')->limit($start.','.$limit)->select();
            $count = $this->count();
        }else{
            $publishes = $this->where($where)->order('publish_time desc')->limit($start.','.$limit)->select();
            $count = $this->where($where)->count();
        }

        if(!$publishes){
            $publishes = array();
        }
        $data = array();
        $priorities = C('MESSAGE_PRIORITY');
        $mMember = new MemberModel();
        $mLocation = new LocationModel();
        $mMessage = new MessageModel();
        foreach ($publishes as $publish) {
            unset($publish['_id']);
            unset($publish['ctime']);
            unset($publish['mtime']);

            if($publish['user_uid'] == 0){
                $publish['user_name'] = 'All';
            }else{
                $member = $mMember->get_member($publish['user_uid']);
                $publish['user_name'] = $member['nickname'];
            }

            $publish['priority'] = $priorities[$publish['priority']];

            if($publish['location_id'] == 0){
                $publish['location_name'] = 'Any';
            }else{
                $location = $mLocation->get_location($publish['location_id']);
                $publish['location_name'] = $location['name'];
            }

            if($publish['status'] == 0){
                $publish['status'] = 'Pushed';
            }elseif($publish['status'] == 1){
                $publish['status'] = 'Received';
            }else{
                $publish['status'] = 'Opened';
            }

            $message = $mMessage->get_message($publish['message_id']);
            $publish['title'] = $message['title'];
            $data[] = $publish;
        }
        $ret = array(
            'publishes' => $data,
            'count' => $count
        );
        return $ret;
    }

    /**
     * 获取所有publish by uid
     * @param $location_id
     * @param $uid
     * @return array
     */
    public function get_publishes_by_uid($location_id, $uid){
        $params = array(
            'user_uid' => $uid, //who
            'location_id' => $location_id, //where
            'publish_time' => array('lte', time()), //when
            'expire_time' => array('gte', time()), //when
            'status' => 0, //no read
        );
        $publishes = $this->where($params)->order('mtime desc')->select();
        $ret = array();
        $mMessage = new MessageModel();
        foreach ($publishes as $publish) {
            $message = $mMessage->get_message($publish['message_id']);
            $ret[] = $message;
        }
        return $ret;
    }

    /**
     * 获取所有publish by status
     * @param int $status
     * @param  int $limit
     * @return array
     */
    public function get_publishes_by_status($status = 0, $limit = 0){
        $params = array(
            'expire_time' => array('lt', time()), //when
            'status' => $status, //no read
        );

        if($limit > 0){
            $publishes = $this->where($params)->order('publish_time')->limit($limit)->select();
        }else{
            $publishes = $this->where($params)->order('publish_time')->select();
        }
        if(!$publishes){
            $publishes = array();
        }
        return $publishes;
    }

    /**
     * 获取所有publish by user features
     * $map = array(
     *       'or' => array(
     *                  array(
     *                      'user_uid' => $user['uid'],
     *                  ),
     *               array(
     *                  'user_uid' => 0,
     *                  'or' => array(
     *                      'user_grade' => array('in', array(0, $user['grade'])),
     *                      'user_major' => array('in', array(0, $user['major'])),
     *                      'user_gender' => array('in', array(0, $user['gender'])),
     *                  ),
     *              ),
     *        ),
     *       'status' => 0,
     *       'expire_time' => array('gte', $last_time),
     *       'location_id' => array('in', $location_ids)
     *  );
     * @param $locations
     * @param $user
     * @param $priority
     * @param $notification
     * @param $start
     * @param $limit
     * @return array
     */
    public function get_publishes_by_user_features($locations, $user, $priority = 1, $notification = 0, $start = 0, $limit = 10){
        $location_ids = array(0);
        foreach ($locations as $location) {
            $location_ids[] = $location['location_id'];
        }
        $now = time();

        $map['user_uid']  = array('in', array(0, $user['uid']));
        $map['location_id']  = array('in', $location_ids);
        $map['status']  = 0;
        $map['priority']  = array('$gte' => $priority);
        if($notification == 1){
            $map['similarity']  = array('$gte' => 10);
        }
        $map['publish_time']  = array('$lte' => $now);
        $map['expire_time']  = array('$gte' => $now);
        $publishes = $this->where($map)->order('priority desc, similarity desc, publish_time desc')->limit($start.','.$limit)->select();
        if(empty($publishes)){
            $publishes = array();
        }
        return $publishes;
    }

    /**
     * 获取publish信息
     * @param $publish_id
     * @return array
     */
    public function get_publish($publish_id){
        $publish = $this->where(array('publish_id' => $publish_id))->find();
        if(empty($publish)){
            $publish = array();
        }
        unset($publish['_id']);
        unset($publish['ctime']);
        unset($publish['mtime']);
        $mMessage = new MessageModel();
        $message = $mMessage->get_message($publish['message_id']);
        $publish['message'] = $message['title'];

        return $publish;
    }

    /**
     * 添加publish
     * @param $publish
     * @return array
     */
    public function insert_publish($publish){
        $publish['mtime'] = time();
        $publish['ctime'] = time();
        $ret = $this->add($publish);
        return $ret;
    }

    /**
     * 更新publish
     * @param $publish_id
     * @param $publish
     * @return array
     */
    public function update_publish($publish_id, $publish){
        $publish['mtime'] = time();
        $ret = $this->where(array('publish_id' => $publish_id))->save($publish);
        return $ret;
    }

    /**
     * 删除publish
     * @param $publish_id
     * @return bool
     */
    public function remove_publish($publish_id){
        $ret = $this->where(array('publish_id' => $publish_id))->delete();
        return $ret;
    }

    /**
     * 删除publish by message_id
     * @param $message_id
     * @return bool
     */
    public function remove_publish_by_msg_id($message_id){
        $ret = $this->where(array('message_id' => $message_id))->delete();
        return $ret;
    }

}
