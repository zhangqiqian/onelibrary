<?php
// +----------------------------------------------------------------------
// | OneLibrary [ DO IT NOW! ]
// +----------------------------------------------------------------------
// | Copyright (c) 2014 niko All rights reserved.
// +----------------------------------------------------------------------
// | Author: zhangqiqian <43874051@qq.com>
// +----------------------------------------------------------------------

namespace Api\Controller;
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
                    $this->ajaxReturn(array('errno' => 0, 'errmsg' => 'Success to login!'));
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
            if(is_login()){
                $this->ajaxReturn(array('errno' => 0, 'errmsg' => 'Success to login!'));
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

}
