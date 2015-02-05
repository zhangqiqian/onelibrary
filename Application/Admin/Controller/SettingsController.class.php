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

    public function user_add(){
        $this->display();
    }

    public function user_add_submit(){
        $this->success('Success.', U('Settings/user'));
    }

    public function user_edit(){
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
        $this->assign('member', $member);
        $this->display();
    }

    public function user_edit_submit(){
        $uid = I('uid', 0, 'intval');
        if(empty($uid)){
            $this->error('Uid is required.');
        }
        $mMember = new MemberModel;
        $member = $mMember;
        $this->success('Success.', U('Settings/user'));
    }

    public function message(){
        $this->display();
    }

    public function location(){
        $this->display();
    }
}