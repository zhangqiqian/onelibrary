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
        //TODO
        $this->ajaxReturn(array('errno' => 0, 'errmsg' => 'This feature is not implemented.', 'url' => U('Settings/user'), 'location' => ''));
    }

    public function user_edit(){
        $uid = I('uid', 0, 'intval');
        $mMember = new MemberModel;
        $member = $mMember->get_member($uid);
        $this->assign('member', $member);
        $this->display();
    }

    public function user_edit_submit(){
        $uid = I('uid', 0, 'intval');
        $sex = I('sex', 1, 'intval');
        $major = I('major', '');
        $grade = I('grade', 1, 'intval');
        $desc = I('desc', '');

        if(empty($uid)){
            $this->ajaxReturn(array('errno' => 1, 'errmsg' => 'User id is invalid.', 'location' => ''));
        }
        if(!in_array($sex, array(0,1))){
            $this->ajaxReturn(array('errno' => 1, 'errmsg' => 'Sex value is invalid.', 'location' => 'sex'));
        }
        $grades = C('USER_GRADES');
        if(!in_array($grade, array_keys($grades))){
            $this->ajaxReturn(array('errno' => 1, 'errmsg' => 'Grade value is invalid.', 'location' => 'sex'));
        }

        $userinfo = array(
            'sex' => $sex,
            'major' => $major,
            'grade' => $grade,
            'desc' => $desc,
        );
        $mMember = new MemberModel;
        $member = $mMember->update_member($uid, $userinfo);
        $this->ajaxReturn(array('errno' => 0, 'errmsg' => 'Success.', 'url' => U('Settings/user'), 'location' => ''));
    }

    public function message(){
        $this->display();
    }

    public function location(){
        $this->display();
    }
}