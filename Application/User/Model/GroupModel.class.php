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
 * User Group模型
 */
class GroupModel extends MongoModel{
	/**
	 * 数据表
	 * @var string
	 */
	protected $trueTableName = 't_group';
	protected $_idType       = self::TYPE_INT;
    protected $pk 			 = 'group_id';

	/* group模型自动验证 */
	protected $_validate = array(
		/* 验证group name */
		array('group_name', '1,30', -1, self::EXISTS_VALIDATE, 'length'), //group name长度不合法
		array('group_name', 'checkDenyMember', -2, self::EXISTS_VALIDATE, 'callback'), //group name禁止注册
		array('group_name', '', -3, self::EXISTS_VALIDATE, 'unique'), //group name被占用
	);


	/**
	 * 检测用户Group是不是被禁止注册
	 * @param  string $group_name group名
	 * @return boolean          true - 未禁用，false - 禁止注册
	 */
	protected function checkDenyMember($group_name){
		return true; //TODO: 暂不限制，下一个版本完善
	}

	/**
	 * Create a group
	 * @param  string $group_name group name
	 * @param  string $group_desc group desc
	 * @return integer          create成功
	 */
	public function create($group_name, $group_desc){
		$data = array(
			'group_name' => $group_name,
			'group_desc' => $group_desc,
		);

		/* 添加group */
		if($this->create($data)){
			$gid = $this->add();
			return $gid ? $gid : 0; //0-未知错误，大于0-注册成功
		} else {
			return $this->getError(); //错误详情见自动验证注释
		}
	}

	/**
	 * 获取group信息
	 * @param  string  $gid         group ID or group name
	 * @param  boolean $is_groupname 是否使用group name查询
	 * @return array                group信息
	 */
	public function get_info($gid, $is_groupname = false){
		$map = array();
		if($is_groupname){ //通过group名获取
			$map['group_name'] = $gid;
		} else {
			$map['group_id'] = $gid;
		}

		$group = $this->where($map)->find();
		if(is_array($group)){
			return array($group['group_id'], $group['group_name'], $group['group_desc']);
		} else {
			return -1; //不存在或被禁用
		}
	}

	/**
	 * 获取group list
	 * @return array group list
	 */
	public function get_group_list(){
		$group_list = $this->select();
		if(is_array($group)){
			return $group_list;
		} else {
			return array();
		}
	}

	/**
	 * 更新group信息
	 * @param  integer $gid 用户ID
	 */
	protected function update($gid, $group_name, $group_desc){
		if(empty($gid) || empty($group_name)){
			$this->error = '参数错误！';
			return false;
		}

		$data = array(
			'group_name' => group_name,
			'group_desc' => group_desc,
		);

		//更新用户信息
		$data = $this->create($data);
		return $this->where(array('group_id'=>$gid))->save($data);
	}

}
