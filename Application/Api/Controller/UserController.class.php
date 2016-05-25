<?php
// +----------------------------------------------------------------------
// | OneLibrary [ DO IT NOW! ]
// +----------------------------------------------------------------------
// | Copyright (c) 2014 niko All rights reserved.
// +----------------------------------------------------------------------
// | Author: zhangqiqian <43874051@qq.com>
// +----------------------------------------------------------------------

namespace Api\Controller;
use Common\Model\CurriculaModel;
use Common\Model\MemberModel;
use User\Api\UserApi;

/**
 * API用户控制器
 * @author zhangqiqian <43874051@qq.com>
 */
class UserController extends ApiController {

    /**
     * @param string $username
     * @param string $password
     * @param string $repassword
     * @param string $email
     * @param string $verify
     */
    public function register($username = '', $password = '', $repassword = '', $email = '', $verify = ''){
        if(!C('USER_ALLOW_REGISTER')){
            $this->ajaxReturn(array('errno' => 1, 'errmsg' => '注册已关闭'));
        }
        if(IS_POST){ //注册用户
            /* 检测验证码 */
            /*if(!check_verify($verify)){
                $this->ajaxReturn(array('errno' => 1, 'errmsg' => '验证码输入错误'));
            }*/

            /* 检测密码 */
            if($password != $repassword){
                $this->ajaxReturn(array('errno' => 1, 'errmsg' => '密码和重复密码不一致'));
            }

            /* 调用注册接口注册用户 */
            $User = new UserApi;
            $uid = $User->register($username, $password, $email);
            if(0 < $uid){ //注册成功
                //TODO: 发送验证邮件
                $this->ajaxReturn(array('errno' => 1, 'errmsg' => 'Success to sign up'));
            } else { //注册失败，显示错误信息
                $this->ajaxReturn(array('errno' => 1, 'errmsg' => $this->showRegError($uid)));
            }
        }
    }

    /**
     * 修改昵称提交
     * @author zhangqiqian <43874051@qq.com>
     */
    public function update_nickname(){
        //获取参数
        $nickname = I('post.nickname');
        $password = I('post.password');
        empty($nickname) && $this->ajaxReturn(array('errno' => 1, 'errmsg' => '请输入昵称'));
        empty($password) && $this->ajaxReturn(array('errno' => 1, 'errmsg' => '请输入密码'));

        //密码验证
        $User   =   new UserApi();
        $uid    =   $User->login(UID, $password, 4);
        ($uid == -2) && $this->ajaxReturn(array('errno' => 1, 'errmsg' => '密码不正确'));

        $Member =   D('Member');
        $data   =   $Member->create(array('nickname'=>$nickname));
        if(!$data){
            $this->ajaxReturn(array('errno' => 1, 'errmsg' => $Member->getError()));
        }

        $res = $Member->where(array('uid'=>$uid))->save($data);

        if($res){
            $user               =   session('user_auth');
            $user['username']   =   $data['nickname'];
            session('user_auth', $user);
            session('user_auth_sign', data_auth_sign($user));
            $this->ajaxReturn(array('errno' => 0, 'errmsg' => '修改昵称成功！'));
        }else{
            $this->ajaxReturn(array('errno' => 1, 'errmsg' => '修改昵称失败！'));
        }
    }

    /**
     * 修改密码提交
     * @author zhangqiqian <43874051@qq.com>
     */
    public function update_password(){
        if(!is_login()){
            $this->ajaxReturn(array('errno' => 1, 'errmsg' => '您还没有登录'));
        }
        $oldpassword = I('oldpassword', '', '');
        $password = I('password', '', '');
        $repassword = I('repassword', '', '');

        if(IS_POST){
            //获取参数
            $uid = is_login();
            $data['password'] = $password;

            empty($oldpassword) && $this->ajaxReturn(array('errno' => 1, 'errmsg' => '请输入原密码'));
            empty($data['password']) && $this->ajaxReturn(array('errno' => 1, 'errmsg' => '请输入新密码'));
            empty($repassword) && $this->ajaxReturn(array('errno' => 1, 'errmsg' => '请输入确认密码'));

            if($data['password'] !== $repassword){
                $this->ajaxReturn(array('errno' => 1, 'errmsg' => '您输入的新密码与确认密码不一致'));
            }

            $Api = new UserApi();
            $res = $Api->updateInfo($uid, $oldpassword, $data);
            if($res['status']){
                $this->ajaxReturn(array('errno' => 0, 'errmsg' => '修改密码成功'));
            }else{
                $this->ajaxReturn(array('errno' => 1, 'errmsg' => $res['info']));
            }
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

    /**
     * 获取用户信息
     * @author zhangqiqian <43874051@qq.com>
     */
    public function get_profile(){
        $uid = is_login();

        $mMember = new MemberModel();
        $ret = $mMember->get_member($uid);
        $profile_info = array();
        if($ret){
            $profile_info['grade'] = $ret['grade'];
            $profile_info['major'] = $ret['major'];
            $profile_info['research'] = implode(',', $ret['research']);
            $profile_info['interest'] = implode(',', $ret['interests']);
            $profile_info['project'] = implode(',', $ret['projects']);
            $profile_info['curricula_id'] = $ret['curricula_id'];
        }
        $this->ajaxReturn(array('errno' => 0, 'result' => $profile_info));
    }

    /**
     * 修改用户信息
     * @author zhangqiqian <43874051@qq.com>
     */
    public function update_profile(){
        $grade = I('grade', 0, 'intval');
        $major = I('major', 0, 'intval');
        $research = I('research', '', 'trim');
        $interest = I('interest', '', 'trim');
        $project = I('project', '', 'trim');
        $curricula_id = I('curricula', 0, 'intval');

        $uid = is_login();
        $grades = C('USER_GRADES');
        if(!in_array($grade, array_keys($grades))){
            $this->ajaxReturn(array('errno' => 1, 'errmsg' => 'Grade value is invalid.', 'location' => 'gender'));
        }

        $interests = array();
        if (!empty($interest)){
            $interest_arr = explode(',', $interest);
            foreach ($interest_arr as $interest_str) {
                $interests[] = trim($interest_str);
            }
        }

        $researches = array();
        if (!empty($research)){
            $research_arr = explode(',', $research);
            foreach ($research_arr as $research_str) {
                $researches[] = trim($research_str);
            }
        }

        $projects = array();
        if (!empty($project)){
            $project_arr = explode(',', $project);
            foreach ($project_arr as $project_str) {
                $projects[] = trim($project_str);
            }
        }

        $userinfo = array(
            'major' => $major,
            'grade' => $grade,
            'interests' => $interests,
            'research' => $researches,
            'projects' => $projects,
            'curricula_id' => $curricula_id,
            'mtime' => time()
        );
        $mMember = new MemberModel();
        $ret = $mMember->update_member($uid, $userinfo);
        if ($ret['ok'] == 1){
            $this->ajaxReturn(array('errno' => 0, 'errmsg' => 'Success.'));
        }else{
            $this->ajaxReturn(array('errno' => 1, 'errmsg' => 'Failure.'));
        }
    }

    public function get_profile_options(){
        $mCurricula = new CurriculaModel();
        $curriculas = $mCurricula->get_all_curriculas();

        $data = array();
        $majors = C('MAJOR_MAPPING');
        foreach ($majors as $key => $major) {
            $data[$key] = array();
        }
        foreach ($curriculas as $curricula) {
            if($curricula['courses']){
                if(isset($data[$curricula['major']])){
                    $data[$curricula['major']][$curricula['curricula_id']] = array(
                        'id' => $curricula['curricula_id'],
                        'name' => $curricula['name']
                    );
                }else{
                    $data[$curricula['major']] = array(
                        $curricula['curricula_id'] => array(
                            'id' => $curricula['curricula_id'],
                            'name' => $curricula['name']
                        )
                    );
                }
            }
        }
        $major_curricula_list = array();
        foreach ($data as $major_id => $curricula_list) {
            $curricula_list[0] = array(
                'id' => 0,
                'name' => '无课程'
            );
            $major_curricula_list[] = array(
                'major_id' => $major_id,
                'curriculas' => array_values($curricula_list)
            );
        }

        $grades = C('USER_GRADES');
        $new_grades = array();
        foreach ($grades as $id => $grade) {
            if(in_array($id, array(1, 2, 7))){
                $new_grades[] = array(
                    'id' => $id,
                    'name' => $grade,
                );
            }
        }

        $majors = C('MAJOR_MAPPING');
        $new_majors = array();
        foreach ($majors as $id => $major) {
            $new_majors[] = array(
                'id' => $id,
                'name' => $major,
            );
        }

        $this->ajaxReturn(
            array(
                'errno' => 0,
                'result' => array(
                    'grades' => $new_grades,
                    'majors' => $new_majors,
                    'curriculas' => $major_curricula_list,
                )
            )
        );
    }
}
