<?php
// +----------------------------------------------------------------------
// | OneLibrary [ DO IT NOW! ]
// +----------------------------------------------------------------------
// | Copyright (c) 2014 niko All rights reserved.
// +----------------------------------------------------------------------
// | Author: zhangqiqian <43874051@qq.com>
// +----------------------------------------------------------------------

namespace Admin\Controller;
use Think\Controller;
use Common\Model\MemberModel;

class SettingsController extends AdminController {
    /**
     * 后台-Settings
     * @author zhangqiqian <43874051@qq.com>
     */
    public function user(){
        $member = new MemberModel;
        $members = $member->get_member_list();
        $this->assign('members', $members);
        $this->display();
    }

    public function update_user(){
        $uid = I('uid', 0, 'intval');
        if(empty($uid)){
            $this->ajaxReturn(array('errno' => 400401, 'errmsg' => 'Uid is required.'));
        }
        $mMember = new MemberModel;
        $member = $mMember->get_member($uid);
        if($member){
            unset($member['_id']);
            unset($member['birthday']);
            unset($member['ctime']);
            unset($member['last_login_ip']);
            unset($member['last_login_time']);
            unset($member['login_count']);
            unset($member['mtime']);
            unset($member['status']);
            unset($member['login_count']);
        }
        $this->ajaxReturn($member);
    }

    public function update_user_do(){
        $member = new MemberModel;
        $members = $member->get_member_list();
        $this->assign('members', $members);
        $this->display();
    }

    public function message(){
        $this->display();
    }

    public function location(){
        $this->display();
    }
}