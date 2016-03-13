<?php
// +----------------------------------------------------------------------
// | OneLibrary [ DO IT NOW! ]
// +----------------------------------------------------------------------
// | Copyright (c) 2014 niko All rights reserved.
// +----------------------------------------------------------------------
// | Author: zhangqiqian <43874051@qq.com>
// +----------------------------------------------------------------------

namespace Api\Controller;
use Common\Model\PublishModel;
use User\Api\UserApi;
use Think\Controller;

/**
 * API首页控制器
 * @author zhangqiqian <43874051@qq.com>
 */
class PublicController extends Controller {

    /**
     * api用户登录
     * @author zhangqiqian <43874051@qq.com>
     */
    public function login($username = null, $password = null, $verify = null){
        if(IS_POST){
            /* 检测验证码*/
            /*if(!check_verify($verify)){
                $this->error('验证码输入错误！');
            }*/
            /* 调用登录接口登录 */
            $User = new UserApi;
            $uid = $User->login($username, $password);
            if(0 < $uid){ //UC登录成功
                /* 登录用户 */
                $Member = D('Member');
                if($Member->login($uid)){ //登录用户
                    $this->ajaxReturn(array('errno' => 0, 'errmsg' => 'Success to login!', 'uid' => $uid));
                } else {
                    $this->ajaxReturn(array('errno' => 1, 'errmsg' => $Member->getError()));
                }

            } else { //登录失败
                switch($uid) {
                    case -1: $error = '用户不存在或被禁用！'; break; //系统级别禁用
                    case -2: $error = '密码错误！'; break;
                    default: $error = '未知错误！'; break; // 0-接口参数错误（调试阶段使用）
                }
                $this->ajaxReturn(array('errno' => 1, 'errmsg' => $error));
            }
        } else {
            $uid = is_login();
            if($uid > 0){
                $this->ajaxReturn(array('errno' => 0, 'errmsg' => 'Success to login!', 'uid' => $uid));
            }else{
                $this->ajaxReturn(array('errno' => 1, 'errmsg' => 'Please sign in.'));
            }
        }
    }

    /* 退出登录 */
    public function logout(){
        if(is_login()){
            D('Member')->logout();
        }
        $this->ajaxReturn(array('errno' => 0, 'errmsg' => 'Success to logout!'));
    }

    /* 验证码，用于登录和注册 */
    public function verify(){
        $verify = new \Think\Verify();
        $verify->entry(1);
    }

    /**
     * @param string $username
     * @param string $password
     * @param string $repassword
     * @param string $email
     */
    public function register($username = '', $password = '', $repassword = '', $email = ''){
        if(!C('USER_ALLOW_REGISTER')){
            $this->ajaxReturn(array('errno' => 1, 'errmsg' => '注册已关闭'));
        }
        if(IS_POST){ //注册用户
            /* 检测密码 */
            if($password != $repassword){
                $this->ajaxReturn(array('errno' => 1, 'errmsg' => '密码和重复密码不一致'));
            }

            /* 调用注册接口注册用户 */
            $User = new UserApi;
            $uid = $User->register($username, $password, $email);
            if(0 < $uid){ //注册成功
                $publish = array(
                    'user_uid' => $uid,
                    'location_id' => 0,
                    'publish_time' => time(),
                    'expire_time' => time() + 24 * 3600,
                    'message_id' => 1,
                    'status' => 0,
                    'priority' => 3,
                    'similarity' => 100
                );
                $mPublish = new PublishModel();
                $mPublish->insert_publish($publish);
                $this->ajaxReturn(array('errno' => 0, 'errmsg' => 'Success to sign up'));
            } else { //注册失败，显示错误信息
                $this->ajaxReturn(array('errno' => 1, 'errmsg' => $this->showRegError($uid)));
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
}
