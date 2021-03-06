<?php
namespace User\Api;
use User\Model\UserModel;

class UserApi extends Api{
    /**
     * 构造方法，实例化操作模型
     */
    protected function _init(){
        $this->model = new UserModel();
    }

    /**
     * 注册一个新用户
     * @param  string $username 用户名
     * @param  string $password 用户密码
     * @param  string $email    用户邮箱
     * @param  string $mobile   用户手机号码
     * @return integer          注册成功-用户信息，注册失败-错误编号
     */
    public function register($username, $password, $email, $mobile = ''){
        return $this->model->register($username, $password, $email, $mobile);
    }

    /**
     * 用户登录认证
     * @param  string  $username 用户名
     * @param  string  $password 用户密码
     * @param  integer $type     用户名类型 （1-用户名，2-邮箱，3-手机）
     * @return integer           登录成功-用户ID，登录失败-错误编号
     */
    public function login($username, $password, $type = 1){
        return $this->model->login($username, $password, $type);
    }

    /**
     * 获取用户信息
     * @param  string  $uid         用户ID或用户名
     * @param  boolean $is_username 是否使用用户名查询
     * @return array                用户信息
     */
    public function info($uid, $is_username = false){
        return $this->model->info($uid, $is_username);
    }

    /**
     * 获取用户信息 by uid
     * @param  int  $uid         用户ID
     * @return array                用户信息
     */
    public function get_user($uid){
        return $this->model->get_user($uid);
    }

    /**
     * 获取用户信息 by username
     * @param  string  $username    用户名
     * @return array                用户信息
     */
    public function get_user_by_username($username){
        return $this->model->get_user_by_username($username);
    }

    /**
     * 获取用户信息 by email
     * @param  string  $email         用户email
     * @return array                用户信息
     */
    public function get_user_by_email($email){
        return $this->model->get_user_by_email($email);
    }

    /**
     * 获取用户信息 by mobile
     * @param  string  $mobile     mobile
     * @return array                用户信息
     */
    public function get_user_by_mobile($mobile){
        return $this->model->get_user_by_mobile($mobile);
    }


    /**
     * 获取all用户信息
     * @param string $search
     * @param int $start
     * @param int $limit
     * @return array all用户信息
     */
    public function user_list($search = '', $start = 0, $limit = 20){
        return $this->model->user_list($search, $start, $limit);
    }

    /**
     * update用户信息
     * @param  int  $uid         用户ID
     * @param  array $data      update fields
     */
    public function update($uid, $data){
        return $this->model->update($uid, $data);
    }

    /**
     * 检测用户名
     * @param  string  $username  用户名
     * @return integer         错误编号
     */
    public function checkUsername($username){
        return $this->model->checkField($username, 1);
    }

    /**
     * 检测邮箱
     * @param  string  $email  邮箱
     * @return integer         错误编号
     */
    public function checkEmail($email){
        return $this->model->checkField($email, 2);
    }

    /**
     * 检测手机
     * @param  string  $mobile  手机
     * @return integer         错误编号
     */
    public function checkMobile($mobile){
        return $this->model->checkField($mobile, 3);
    }

    /**
     * 更新用户信息
     * @param int $uid 用户id
     * @param string $password 密码，用来验证
     * @param array $data 修改的字段数组
     * @return true 修改成功，false 修改失败
     * @author zhangqiqian <43874051@qq.com>
     */
    public function updateInfo($uid, $password, $data){
        if($this->model->updateUserFields($uid, $password, $data) !== false){
            $return['status'] = true;
        }else{
            $return['status'] = false;
            $return['info'] = $this->model->getError();
        }
        return $return;
    }

}
