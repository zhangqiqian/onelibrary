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
        array('expire_time', '', self::MODEL_INSERT),
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
        $matches = $this->order('match_id desc')->select();
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
     * 获取所有match by uid
     * @param $region_id
     * @param $uid
     * @return array
     */
    public function get_matches_by_uid($region_id, $uid){
        $params = array(
            'uid' => $uid, //who
            'region_id' => $region_id, //where
            'expire_time' => array('$gte', time()), //when
            'status' => 0, //no read
        );
        $matches = $this->where($params)->order('mtime desc')->select();
        $ret = array();
        $mMessage = new MessageModel();
        foreach ($matches as $match) {
            $message = $mMessage->get_message($match['message_id']);
            $ret[] = $message;
        }
        return $ret;
    }

    /**
     * 获取所有match by user features
     * {"$or":[
     *      {"$and" : [
     *                  {"user_uid": 1},
     *                  {"region_id": {"$in": [0,1]}},
     *                  {"status" : 0},
     *                  {"expire_time" : {"$gte" : time()}}
     *               ]
     *      },
     *      {"$and" : [
     *                  {"$and" : [
     *                              {"user_uid": 0},
     *                              {"region_id": {"$in": [0,1]}},
     *                              {"status" : 0},
     *                              {"expire_time" : {"$gte" : time()}}
     *                      ]
     *                  },
     *                  {"$or" : [
     *                              {"user_grade": {"$in": [0,1]}},
     *                              {"user_major": {"$in": [0,1]}},
     *                              {"user_gender": {"$in": [0,1]}}
     *                      ]
     *                  }
     *              ]
     *      ]}
     * }
     * @param $locations
     * @param $user
     * @param $last_message_id
     * @param $last_time
     * @param $start
     * @param $limit
     * @return array
     */
    public function get_matches_by_user_features($locations, $user, $last_message_id = 0, $last_time = 0, $start = 0, $limit = 10){
        $region_ids = array(0);
        foreach ($locations as $location) {
            $region_ids[] = $location['location_id'];
        }

        $last_time = $last_time == 0 ? time() : $last_time;

        $where['user_uid']  = array('in', array(0, $user['uid']));
        $where['user_grade']  = array('in', array(0, $user['grade']));
        $where['user_major']  = array('in', array(0, $user['major']));
        $where['user_gender']  = array('in', array(0, $user['gender']));
        $where['_logic'] = 'or';

        $map['_complex'] = $where;
        $map['status']  = 0;
        $map['message_id']  = array('gt', $last_message_id);
        $map['expire_time']  = array('gte', $last_time);
        $map['region_id']  = array('in', $region_ids);

        $matches = $this->where($map)->order('mtime desc')->limit($start.','.$limit)->select();
        if(empty($matches)){
            $matches = array();
        }
        return $matches;
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
