<?php
// +----------------------------------------------------------------------
// | OneLibrary [ DO IT NOW! ]
// +----------------------------------------------------------------------
// | Copyright (c) 2014 niko All rights reserved.
// +----------------------------------------------------------------------
// | Author: zhangqiqian <43874051@qq.com>
// +----------------------------------------------------------------------

namespace User\Model;
use Think\Model\MongoModel;

/**
 * 会员模型
 */
class UserModel extends MongoModel{
	/**
	 * 数据表
	 * @var string
	 */
	protected $trueTableName = 't_user';
	protected $_idType       = self::TYPE_INT;
    protected $pk 			 = 'uid';

	/* 用户模型自动验证 */
	protected $_validate = array(
		/* 验证用户名 */
		array('username', '1,30', -1, self::EXISTS_VALIDATE, 'length'), //用户名长度不合法
		array('username', 'checkDenyMember', -2, self::EXISTS_VALIDATE, 'callback'), //用户名禁止注册
		array('username', '', -3, self::EXISTS_VALIDATE, 'unique'), //用户名被占用

		/* 验证密码 */
		array('password', '6,30', -4, self::EXISTS_VALIDATE, 'length'), //密码长度不合法

		/* 验证邮箱 */
		array('email', 'email', -5, self::EXISTS_VALIDATE), //邮箱格式不正确
		array('email', '1,32', -6, self::EXISTS_VALIDATE, 'length'), //邮箱长度不合法
		array('email', 'checkDenyEmail', -7, self::EXISTS_VALIDATE, 'callback'), //邮箱禁止注册
		array('email', '', -8, self::EXISTS_VALIDATE, 'unique'), //邮箱被占用

		/* 验证手机号码 */
		array('mobile', '//', -9, self::EXISTS_VALIDATE), //手机格式不正确 TODO:
		array('mobile', 'checkDenyMobile', -10, self::EXISTS_VALIDATE, 'callback'), //手机禁止注册
		array('mobile', '', -11, self::EXISTS_VALIDATE, 'unique'), //手机号被占用
	);

	/* 用户模型自动完成 */
	protected $_auto = array(
		array('password', 'onelibrary_md5', self::MODEL_BOTH, 'function', AUTH_KEY),
		array('status', 'getStatus', self::MODEL_BOTH, 'callback'),
		array('mtime', NOW_TIME, self::MODEL_BOTH),
        array('ctime', NOW_TIME, self::MODEL_INSERT),
        array('role', 'user'),
        array('group', 1)
	);

	/**
	 * 检测用户名是不是被禁止注册
	 * @param  string $username 用户名
	 * @return boolean          true - 未禁用，false - 禁止注册
	 */
	protected function checkDenyMember($username){
		return true; //TODO: 暂不限制，下一个版本完善
	}

	/**
	 * 检测邮箱是不是被禁止注册
	 * @param  string $email 邮箱
	 * @return boolean       true - 未禁用，false - 禁止注册
	 */
	protected function checkDenyEmail($email){
		return true; //TODO: 暂不限制，下一个版本完善
	}

	/**
	 * 检测手机是不是被禁止注册
	 * @param  string $mobile 手机
	 * @return boolean        true - 未禁用，false - 禁止注册
	 */
	protected function checkDenyMobile($mobile){
		return true; //TODO: 暂不限制，下一个版本完善
	}

	/**
	 * 根据配置指定用户状态
	 * @return integer 用户状态
	 */
	protected function getStatus(){
		return 1; //TODO: 暂不限制，下一个版本完善
	}

	/**
	 * 注册一个新用户
	 * @param  string $username 用户名
	 * @param  string $password 用户密码
	 * @param  string $email    用户邮箱
	 * @param  string $mobile   用户手机号码
	 * @return integer          注册成功-用户信息，注册失败-错误编号
	 */
	public function register($username, $password, $email, $mobile){
		$data = array(
			'username' => $username,
			'password' => $password,
			'email'    => $email,
			'mobile'   => $mobile,
		);

		/* 添加用户 */
		if($this->create($data)){
			$uid = $this->add();
			return $uid ? $uid : 0; //0-未知错误，大于0-注册成功
		} else {
			return $this->getError(); //错误详情见自动验证注释
		}
	}

	/**
	 * 用户登录认证
	 * @param  string  $username 用户名
	 * @param  string  $password 用户密码
	 * @param  integer $type     用户名类型 （1-用户名，2-邮箱，3-手机）
	 * @return integer           登录成功-用户ID，登录失败-错误编号
	 */
	public function login($username, $password, $type = 1){
		$map = array();
		switch ($type) {
			case 1:
				$map['username'] = $username;
				break;
			case 2:
				$map['email'] = $username;
				break;
			case 3:
				$map['mobile'] = $username;
				break;
			default:
				return 0; //参数错误
		}

		/* 获取用户数据 */
		$user = $this->where($map)->find();
		if(is_array($user) && $user['status'] == 1){
			/* 验证用户密码 */
			if(onelibrary_md5($password, C('DATA_AUTH_KEY')) === $user['password']){
				return $user['uid']; //登录成功，返回用户ID
			} else {
				return -2; //密码错误
			}
		} else {
			return -1; //用户不存在或被禁用
		}
	}

    public function user_list(){
        $map = array(
            'status' => array('$lte' => 1)
        );
        $users = $this->where($map)->order("uid asc")->select();
        $ret = array();
        if(!empty($users)){
            $ret = $users;
        }
        return $ret;
    }

    public function get_user($uid){
        $map = array(
            'uid' => $uid
        );
        $user = $this->where($map)->find();
        return $user;
    }

    public function get_user_by_username($username){
        $map = array(
            'username' => $username
        );
        $user = $this->where($map)->find();
        return $user;
    }

    public function get_user_by_email($email){
        $map = array(
            'email' => $email
        );
        $user = $this->where($map)->find();
        return $user;
    }

    public function get_user_by_mobile($mobile){
        $map = array(
            'mobile' => $mobile
        );
        $user = $this->where($map)->find();
        return $user;
    }

	/**
	 * 获取用户信息
	 * @param  string  $uid         用户ID或用户名
	 * @param  boolean $is_username 是否使用用户名查询
	 * @return array                用户信息
	 */
	public function info($uid, $is_username = false){
		$map = array();
		if($is_username){ //通过用户名获取
			$map['username'] = $uid;
		} else {
			$map['uid'] = $uid;
		}

		$user = $this->where($map)->find();
		if(is_array($user) && $user['status'] == 1){
			return array($user['uid'], $user['username'], $user['email'], $user['mobile']);
		} else {
			return -1; //用户不存在或被禁用
		}
	}

	/**
	 * 检测用户信息
	 * @param  string  $field  用户名
	 * @param  integer $type   用户名类型 1-用户名，2-用户邮箱，3-用户电话
	 * @return integer         错误编号
	 */
	public function checkField($field, $type = 1){
		$data = array();
		switch ($type) {
			case 1:
				$data['username'] = $field;
				break;
			case 2:
				$data['email'] = $field;
				break;
			case 3:
				$data['mobile'] = $field;
				break;
			default:
				return 0; //参数错误
		}

		return $this->create($data) ? 1 : $this->getError();
	}

	/**
	 * 更新用户登录信息
	 * @param  integer $uid 用户ID
	 * @param  array $new update fields
     * @return array
	 */
	public function update($uid, $new){
		$params = array(
			'uid' => $uid,
		);
		return $this->where($params)->save($new);
	}

	/**
	 * 更新用户信息
	 * @param int $uid 用户id
	 * @param string $password 密码，用来验证
	 * @param array $data 修改的字段数组
	 * @return true 修改成功，false 修改失败
	 * @author zhangqiqian <43874051@qq.com>
	 */
	public function updateUserFields($uid, $password, $data){
		if(empty($uid) || empty($password) || empty($data)){
			$this->error = '参数错误！';
			return false;
		}

		//更新前检查用户密码
		if(!$this->verifyUser($uid, $password)){
			$this->error = '验证出错：密码不正确！';
			return false;
		}
		
		//更新用户信息
		$data = $this->create($data);
		if($data){
			$ret = $this->where(array('uid' => intval($uid)))->save($data);
			return $ret;
		}
		return false;
	}

	/**
	 * 验证用户密码
	 * @param int $uid 用户id
	 * @param string $password_in 密码
	 * @return true 验证成功，false 验证失败
	 * @author zhangqiqian <43874051@qq.com>
	 */
	protected function verifyUser($uid, $password_in){
		//$password = $this->getFieldById($uid, 'password');
		$map = array('uid' => $uid);
		$user = $this->where($map)->find();
		if(onelibrary_md5($password_in, C('DATA_AUTH_KEY')) === $user['password']){
			return true;
		}
		return false;
	}

}
