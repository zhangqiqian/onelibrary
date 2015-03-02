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
        $matches = $this->order('mtime desc')->select();
        if(!$matches){
            $matches = array();
        }
        $ret = array();
        $grades = C('USER_GRADES');
        $majors = C('MAJOR_MAPPING');
        $priorities = C('MESSAGE_PRIORITY');
        $mMember = new MemberModel();
        $mLocation = new LocationModel();
        $mMessage = new MessageModel();
        foreach ($matches as $match) {
            unset($match['_id']);
            unset($match['ctime']);
            unset($match['mtime']);
            $match['user_grade'] = $match['user_grade'] == 0 ? 'All' : $grades[$match['user_grade']];
            $match['user_major'] = $match['user_major'] == 0 ? 'All' : $majors[$match['user_major']];

            if($match['user_gender'] == 0){
                $match['user_gender'] = 'Female';
            }elseif($match['user_gender'] == 1){
                $match['user_gender'] = 'Male';
            }else{
                $match['user_gender'] = 'All';
            }

            if($match['user_uid'] == 0){
                $match['user_name'] = 'All';
            }else{
                $member = $mMember->get_member($match['user_uid']);
                $match['user_name'] = $member['nickname'];
            }

            $match['priority'] = $priorities[$match['priority']];

            if($match['region_id'] == 0){
                $match['region_name'] = 'All';
            }else{
                $location = $mLocation->get_location($match['region_id']);
                $match['region_name'] = $location['name'];
            }

            $message = $mMessage->get_message($match['message_id']);
            $match['message'] = $message['title'];
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
        $mMessage = new MessageModel();
        $message = $mMessage->get_message($match['message_id']);
        $match['message'] = $message['title'];

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

    /**
     * 删除match by message_id
     * @param $message_id
     * @return bool
     */
    public function remove_match_by_msg_id($message_id){
        $ret = $this->where(array('message_id' => $message_id))->delete();
        return $ret;
    }
}
