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
use User\Api\UserApi;

/**
 * Member模型
 */
class MemberModel extends MongoModel{

    protected $trueTableName = 't_member';
    protected $_idType       = self::TYPE_INT;
    protected $pk            = 'uid';

    /* 用户模型自动完成 */
    protected $_auto = array(
        array('login_count', 0, self::MODEL_INSERT),
        array('last_login_time', NOW_TIME, self::MODEL_INSERT),
        array('last_login_ip', '127.0.0.1', self::MODEL_INSERT),
        array('mtime', NOW_TIME, self::MODEL_BOTH),
        array('ctime', NOW_TIME, self::MODEL_INSERT),
        array('status', 1, self::MODEL_INSERT),
        array('gender', 1, self::MODEL_INSERT),
        array('birthday', NOW_TIME, self::MODEL_INSERT),
        array('major', 0, self::MODEL_INSERT),
        array('grade', 1, self::MODEL_INSERT),
        array('interests', array(), self::MODEL_INSERT),
        array('research', array(), self::MODEL_INSERT),
        array('projects', array(), self::MODEL_INSERT),
        array('curricula_id', 0, self::MODEL_INSERT),
    );

    /**
     * 登录指定用户
     * @param  integer $uid 用户ID
     * @return boolean      true-登录成功，false-登录失败
     */
    public function login($uid){
        /* 检测是否在当前应用注册 */
        $user = $this->where(array('uid' => intval($uid)))->find();
        if(!$user){ //未注册
            /* 在当前应用中注册用户 */
        	$Api = new UserApi();
        	$info = $Api->info($uid);
            $user = $this->create(array('nickname' => $info[1], 'status' => 1));
            $user['uid'] = $uid;
            if(!$this->add($user)){
                $this->error = '前台用户信息注册失败，请重试！';
                return false;
            }
        } elseif(1 != $user['status']) {
            $this->error = '用户未激活或已禁用！'; //应用级别禁用
            return false;
        }

        /* 登录用户 */
        $this->autoLogin($user);
        return true;
    }

    /**
     * 注销当前用户
     * @return void
     */
    public function logout(){
        session('user_auth', null);
        session('user_auth_sign', null);
    }

    /**
     * 自动登录用户
     * @param  integer $user 用户信息数组
     */
    private function autoLogin($user){
        /* 更新登录信息 */
        $data = array(
            'uid'             => $user['uid'],
            'login_count'     => $user['login_count'] + 1, //array('exp', '`login_count`+1'),
            'last_login_time' => NOW_TIME,
            'last_login_ip'   => get_client_ip(0, true),
        );
        $this->save($data);

        /* 记录登录SESSION和COOKIES */
        $auth = array(
            'uid'             => $user['uid'],
            'username'        => get_username($user['uid']),
            'last_login_time' => $user['last_login_time'],
        );

        session('user_auth', $auth);
        session('user_auth_sign', data_auth_sign($auth));
    }

    /**
     * 获取所有用户
     * @return array
     */
    public function get_member_list(){
        $map['status'] = 1;
        $members = $this->where($map)->order('uid')->select();
        if(!$members){
            $members = array();
        }
        $grades = C('USER_GRADES');
        $majors = C('MAJOR_MAPPING');
        $ret = array();
        foreach ($members as $member) {
            unset($member['_id']);
            unset($member['birthday']);
            unset($member['ctime']);
            unset($member['mtime']);
            $member['grade'] = $grades[$member['grade']];
            $member['major'] = $majors[$member['major']];
            $ret[] = $member;
        }
        return $ret;
    }

    /**
     * 获取普通用户
     * @return array
     */
    public function get_members(){
        $map = array(
            'uid' => array('$ne' => 1),
            'status' => 1,
        );
        $members = $this->where($map)->order("uid")->select();
        if(!$members){
            $members = array();
        }
        $ret = array();
        foreach ($members as $member) {
            unset($member['_id']);
            unset($member['ctime']);
            $ret[] = $member;
        }
        return $ret;
    }

    /**
     * 获取普通用户
     * @return array
     */
    public function get_members_by_grade_major($grade_id = 0, $major_id = 0){
        $map = array(
            'uid' => array('$ne' => 1),
            'status' => 1,
        );
        if($grade_id > 0){
            $map['grade'] = $grade_id;
        }
        if($major_id > 0){
            $map['major'] = $major_id;
        }
        $members = $this->where($map)->order("uid")->select();
        if(!$members){
            $members = array();
        }
        $ret = array();
        foreach ($members as $member) {
            unset($member['_id']);
            unset($member['ctime']);
            $ret[] = $member;
        }
        return $ret;
    }

    /**
     * 获取普通用户
     * @param $curricula_id
     * @return array
     */
    public function get_members_by_curricula($curricula_id){
        $map = array(
            'uid' => array('$ne' => 1),
            'curricula_id' => $curricula_id,
            'status' => 1,
        );
        $members = $this->where($map)->select();
        if(!$members){
            $members = array();
        }
        $ret = array();
        foreach ($members as $member) {
            unset($member['_id']);
            unset($member['ctime']);
            $ret[] = $member;
        }
        return $ret;
    }

    /**
     * 获取所有用户
     * @return array
     */
    public function get_teacher_list(){
        $map['status'] = 1;
        $map['grade'] = array('$gte' => 3);
        $members = $this->where($map)->order('uid')->select();
        if(!$members){
            $members = array();
        }
        $ret = array();
        foreach ($members as $member) {
            unset($member['_id']);
            $ret[] = $member;
        }
        return $ret;
    }

    /**
     * 获取用户信息
     * @param $uid
     * @return array
     */
    public function get_member($uid){
        $member = $this->where(array('uid' => $uid))->find();
        if(empty($member)){
            $member = array();
        }
        unset($member['_id']);
        unset($member['ctime']);
        unset($member['mtime']);
        return $member;
    }

    /**
     * 获取用户信息
     * @param $username
     * @return array
     */
    public function get_member_by_name($username){
        $like = array('like', $username);
        $member = $this->where(array('nickname' => $like))->select();
        if(empty($member)){
            $member = array();
        }
        unset($member['_id']);
        unset($member['ctime']);
        unset($member['mtime']);
        return $member;
    }

    public function create_member($uid){
        $user = $this->where(array('uid' => intval($uid)))->find();
        if(!$user){ //未注册
            /* 在当前应用中注册用户 */
            $Api = new UserApi();
            $info = $Api->info($uid);
            $user = $this->create(array('nickname' => $info[1], 'status' => 1));
            $user['uid'] = $uid;
            if(!$this->add($user)){
                $this->error = '前台用户信息注册失败，请重试！';
                return false;
            }
        } elseif(1 != $user['status']) {
            $this->error = '用户未激活或已禁用！'; //应用级别禁用
            return false;
        }
    }

    /**
     * 更新用户
     * @param $uid
     * @param $userinfo
     * @return array
     */
    public function update_member($uid, $userinfo){
        $ret = $this->where(array('uid' => $uid))->save($userinfo);
        return $ret;
    }
}
