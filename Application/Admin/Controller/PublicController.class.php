<?php
// +----------------------------------------------------------------------
// | OneLibrary [ DO IT NOW! ]
// +----------------------------------------------------------------------
// | Copyright (c) 2014 niko All rights reserved.
// +----------------------------------------------------------------------
// | Author: zhangqiqian <43874051@qq.com>
// +----------------------------------------------------------------------

namespace Admin\Controller;
use User\Api\UserApi;
use Think\Controller;

/**
 * 后台首页控制器
 * @author zhangqiqian <43874051@qq.com>
 */
class PublicController extends Controller {

    /**
     * 后台用户登录
     * @param string $username
     * @param string $password
     * @param string $verify
     * @author zhangqiqian <43874051@qq.com>
     */
    public function login($username = null, $password = null, $verify = null){
        if(IS_POST){
            /* 检测验证码 */
            if(!check_verify($verify)){
                $this->error('验证码输入错误！');
            }
            /* 调用登录接口登录 */
            $User = new UserApi;
            $uid = $User->login($username, $password); //检验用户名和密码是否一致，返回用户的UID
            if(0 < $uid){ //登录成功
                /* 登录用户 */
                $Member = D('Member');
                if($Member->login($uid)){ //登录用户，将auth加入session
                    //跳转到登录前页面
                    $this->success('Success to login!', U('Dashboard/index'));
                } else {
                    $this->error($Member->getError());
                }
            } else { //登录失败
                switch($uid) {
                    case -1: $error = '用户不存在或被禁用！'; break; //系统级别禁用
                    case -2: $error = '密码错误！'; break;
                    default: $error = '未知错误！'; break; // 0-接口参数错误（调试阶段使用）
                }
                $this->error($error);
            }
        } else {
            $this->display();
        }
    }

    /* 退出登录 */
    public function logout(){
        if(is_login()){
            D('Member')->logout();
            $this->success('Success to logout!', U('login'));
        } else {
            $this->redirect('login');
        }
    }

    /* 验证码，用于登录和注册 */
    public function verify(){
        $verify = new \Think\Verify();
        $verify->entry(1);
    }

}
