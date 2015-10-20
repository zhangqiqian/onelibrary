<?php
// +----------------------------------------------------------------------
// | OneLibrary [ DO IT NOW! ]
// +----------------------------------------------------------------------
// | Copyright (c) 2014 niko All rights reserved.
// +----------------------------------------------------------------------
// | Author: zhangqiqian <43874051@qq.com>
// +----------------------------------------------------------------------

namespace Admin\Controller;
use Common\Model\CurriculaModel;
use User\Api\UserApi;
use Common\Model\MemberModel;

/**
 * 后台用户控制器
 * @author zhangqiqian <43874051@qq.com>
 */
class UserController extends AdminController {

    public function user_list(){
        $User   =   new UserApi();
        $users    =   $User->user_list();
        $this->assign('users', $users);
        $this->display();
    }

    public function user_add(){
        $this->display();
    }

    public function user_add_submit(){
        $username = I('username', '', 'trim');
        $password = I('password', '');
        $repassword = I('repassword', '');
        $email = I('email', '', 'trim');
        $mobile = I('mobile', '', 'trim');

        $UserApi = new UserApi();
        $user = $UserApi->get_user_by_username($username);
        if($user){
            $this->ajaxReturn(array('errno' => 400201, 'errmsg' => 'Username exists.', 'url' => '', 'location' => 'username'));
        }

        if($password != $repassword){
            $this->ajaxReturn(array('errno' => 400202, 'errmsg' => 'password is not the same', 'url' => '', 'location' => 'repassword'));
        }

        $user = $UserApi->get_user_by_email($email);
        if($user){
            $this->ajaxReturn(array('errno' => 400203, 'errmsg' => 'Email has existed.', 'url' => '', 'location' => 'email'));
        }
        if($mobile){
            $user = $UserApi->get_user_by_mobile($mobile);
            if($user){
                $this->ajaxReturn(array('errno' => 400204, 'errmsg' => 'Phone has existed.', 'url' => '', 'location' => 'mobile'));
            }
        }

        $ret = $UserApi->register($username, $password, $email, $mobile);
        if($ret > 0){
            //add member
            $user = $UserApi->get_user_by_username($username);
            $mMember = new MemberModel();
            $mMember->create_member($user['uid']);
            $this->ajaxReturn(array('errno' => 0, 'errmsg' => 'Success.', 'url' => U('User/user_list'), 'location' => ''));
        }else{
            $errmsg = $this->showRegError($ret);
            $this->ajaxReturn(array('errno' => 400205, 'errmsg' => $errmsg, 'url' => '', 'location' => ''));
        }
    }

    public function user_edit(){
        $uid = I('uid', 0, 'intval');

        $UserApi = new UserApi();
        $user = $UserApi->get_user($uid);
        $this->assign('user', $user);
        $this->display();
    }

    public function user_edit_submit(){
        $uid = I('uid', 0, 'intval');
        $username = I('username', '', 'trim');
        $mobile = I('mobile', '');
        $email = I('email', '', 'trim');
        $status = I('status', 1, 'intval');

        if(empty($uid)){
            $this->ajaxReturn(array('errno' => 400200, 'errmsg' => 'User id is invalid.', 'location' => ''));
        }

        $UserApi = new UserApi();
        $user = $UserApi->get_user_by_username($username);
        if($user && $user['uid'] != $uid){
            $this->ajaxReturn(array('errno' => 400201, 'errmsg' => 'Username exists.', 'url' => '', 'location' => 'username'));
        }
        $user = $UserApi->get_user_by_email($email);
        if($user && $user['uid'] != $uid){
            $this->ajaxReturn(array('errno' => 400202, 'errmsg' => 'Email has been existed.', 'url' => '', 'location' => 'email'));
        }
        if(!empty($mobile)){
            $user = $UserApi->get_user_by_mobile($mobile);
            if($user && $user['uid'] != $uid){
                $this->ajaxReturn(array('errno' => 400203, 'errmsg' => 'Phone has been existed.', 'url' => '', 'location' => 'mobile'));
            }
        }

        $userinfo = array(
            'username' => $username,
            'email' => $email,
            'mobile' => $mobile,
            'status' => $status,
        );

        $ret = $UserApi->update($uid, $userinfo);
        if($ret['ok'] == 1){
            $this->ajaxReturn(array('errno' => 0, 'errmsg' => 'Success.', 'url' => U('User/user_list'), 'location' => ''));
        }else{
            $this->ajaxReturn(array('errno' => 400201, 'errmsg' => 'Failure to update user.', 'url' => U('User/user_list'), 'location' => ''));
        }
    }

    public function user_del(){
        $uid = I('uid', 0, 'intval');

        $UserApi = new UserApi();
        $user = $UserApi->get_user($uid);
        $this->assign('user', $user);
        $this->display();
    }


    public function user_del_submit(){
        $uid = I('uid', 0, 'intval');
        if($uid == 1){
            $this->ajaxReturn(array('errno' => 400201, 'errmsg' => 'Admin could not be deleted.', 'location' => ''));
        }
        if(empty($uid)){
            $this->ajaxReturn(array('errno' => 400202, 'errmsg' => 'User id is invalid.', 'location' => ''));
        }

        $UserApi = new UserApi();
        $userinfo = array(
            'status' => 2, //del status
        );

        $ret = $UserApi->update($uid, $userinfo);
        if($ret['ok'] == 1){
            $this->ajaxReturn(array('errno' => 0, 'errmsg' => 'Success.', 'url' => U('User/user_list'), 'location' => ''));
        }else{
            $this->ajaxReturn(array('errno' => 400201, 'errmsg' => 'Failure to delete user.', 'url' => U('User/user_list'), 'location' => ''));
        }
    }

    public function info(){
        $uid = I('uid', 0, 'intval');

        $mMember = new MemberModel();
        $member = $mMember->get_member($uid);

        $grades = C('USER_GRADES');
        $majors = C('MAJOR_MAPPING');
        $member['grade_name'] = isset($grades[$member['grade']]) ? $grades[$member['grade']] : 'Unknown';
        $member['major_name'] = isset($majors[$member['major']]) ? $majors[$member['major']] : 'Unknown';
        $member['interests'] = implode(', ', $member['interests']);
        $member['research'] = implode(', ', $member['research']);

        $mCurricula = new CurriculaModel();
        if($member['curricula_id'] > 0){
            $curricula = $mCurricula->get_curricula($member['curricula_id']);
            $courses = array();
            for($i = 1; $i < 8; $i += 2){
                $courses[$i] = array();
                for($j = 1; $j < 8; $j++){
                    $courses[$i][$j] = array();
                }
            }
            foreach ($curricula['courses'] as $course) {
                $teacher = $mMember->get_member($course['teacher']);
                $course['teacher'] = $teacher['nickname'];
                $courses[$course['section']][$course['week']] = $course;
            }
            $curricula['courses'] = $courses;
        }else{
            $curricula = array();
        }
        $member['curricula'] = $curricula;
        $this->assign('member', $member);
        $this->display();
    }

    public function member_edit(){
        $uid = I('uid', 0, 'intval');
        $mMember = new MemberModel();
        $member = $mMember->get_member($uid);

        $member['interests'] = implode(', ', $member['interests']);
        $member['research'] = implode(', ', $member['research']);

        $grades = C('USER_GRADES');
        $this->assign('grades', $grades);

        $majors = C('MAJOR_MAPPING');
        $this->assign('majors', $majors);

        $mCurricula = new CurriculaModel();
        $result = $mCurricula->get_curriculas_by_info($member['major']);

        $curriculas = array();
        foreach ($result as $curricula) {
            $curriculas[$curricula['curricula_id']] = $curricula['name'];
        }
        $this->assign('curriculas', $curriculas);

        $this->assign('member', $member);
        $this->display();
    }

    public function member_edit_submit(){
        $uid = I('uid', 0, 'intval');
        $nickname = I('nickname', '');
        $gender = I('gender', 1, 'intval');
        $major = I('major', '', 'intval');
        $grade = I('grade', 1, 'intval');
        $interests = I('interests', '');
        $research = I('research', '');
        $curricula_id = I('curricula', 0, 'intval');

        if(empty($uid)){
            $this->ajaxReturn(array('errno' => 1, 'errmsg' => 'User id is invalid.', 'location' => ''));
        }
        if(!in_array($gender, array(0,1))){
            $this->ajaxReturn(array('errno' => 1, 'errmsg' => 'Gender value is invalid.', 'location' => 'gender'));
        }
        $grades = C('USER_GRADES');
        if(!in_array($grade, array_keys($grades))){
            $this->ajaxReturn(array('errno' => 1, 'errmsg' => 'Grade value is invalid.', 'location' => 'gender'));
        }

        $userinfo = array(
            'nickname' => $nickname,
            'gender' => $gender,
            'major' => $major,
            'grade' => $grade,
            'interests' => empty($interests) ? array() : explode(',', $interests),
            'research' => empty($research) ? array() : explode(',', $research),
            'curricula_id' => $curricula_id,
        );
        $mMember = new MemberModel();
        $ret = $mMember->update_member($uid, $userinfo);
        $this->ajaxReturn(array('errno' => 0, 'errmsg' => 'Success.', 'url' => U('User/info').'/uid/'.$uid, 'location' => ''));
    }

    /**
     * 修改密码
     * @author zhangqiqian <43874051@qq.com>
     */
    public function updatePassword(){
        $this->meta_title = '修改密码';
        $this->display();
    }

    /**
     * 修改密码提交
     * @author zhangqiqian <43874051@qq.com>
     */
    public function submitPassword($oldpassword = '', $password = '', $repassword = ''){
        if(!is_login()) {
            $this->error( '您还没有登陆',U('User/login') );
        }
        if(IS_POST) {
            //获取参数
            $uid = is_login();
            $data['password'] = $password;

            empty($oldpassword) && $this->error('请输入原密码');
            empty($data['password']) && $this->error('请输入新密码');
            empty($repassword) && $this->error('请输入确认密码');

            if($data['password'] !== $repassword){
                $this->error('您输入的新密码与确认密码不一致');
            }

            $Api = new UserApi();
            $res = $Api->updateInfo($uid, $oldpassword, $data);
            if($res['status']){
                $this->success('修改密码成功！', U('Dashboard/index'));
            }else{
                $this->error($res['info']);
            }
        }
    }

    public function add($username = '', $password = '', $repassword = '', $email = ''){
        if(IS_POST){
            /* 检测密码 */
            if($password != $repassword){
                $this->error('密码和重复密码不一致！');
            }

            /* 调用注册接口注册用户 */
            $User   =   new UserApi;
            $uid    =   $User->register($username, $password, $email);
            if(0 < $uid){ //注册成功
                $user = array('uid' => $uid, 'nickname' => $username, 'status' => 1);
                if(!M('Member')->add($user)){
                    $this->error('用户添加失败！');
                } else {
                    $this->success('用户添加成功！',U('index'));
                }
            } else { //注册失败，显示错误信息
                $this->error($this->showRegError($uid));
            }
        } else {
            $this->meta_title = '新增用户';
            $this->display();
        }
    }

    /**
     * 获取用户注册错误信息
     * @param  integer $code 错误编码
     * @return string        错误信息
     */
    private function showRegError($code = 0){
        switch ($code) {
            case -1:  $error = '用户名长度必须在16个字符以内！'; break;
            case -2:  $error = '用户名被禁止注册！'; break;
            case -3:  $error = '用户名被占用！'; break;
            case -4:  $error = '密码长度必须在6-30个字符之间！'; break;
            case -5:  $error = '邮箱格式不正确！'; break;
            case -6:  $error = '邮箱长度必须在1-32个字符之间！'; break;
            case -7:  $error = '邮箱被禁止注册！'; break;
            case -8:  $error = '邮箱被占用！'; break;
            case -9:  $error = '手机格式不正确！'; break;
            case -10: $error = '手机被禁止注册！'; break;
            case -11: $error = '手机号被占用！'; break;
            default:  $error = '未知错误';
        }
        return $error;
    }
}
