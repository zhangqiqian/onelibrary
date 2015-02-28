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
use Common\Model\MessageModel;
use Common\Model\LocationModel;

class SettingsController extends AdminController {
    /**
     * 后台-Settings
     * @author zhangqiqian <43874051@qq.com>
     */
    public function user(){
        $member = new MemberModel();
        $members = $member->get_member_list();
        $this->assign('members', $members);
        $this->display();
    }

    public function user_add(){
        $grades = C('USER_GRADES');
        $this->assign('grades', $grades);
        $this->display();
    }

    public function user_add_submit(){
        //TODO
        $this->ajaxReturn(array('errno' => 0, 'errmsg' => 'This feature is not implemented.', 'url' => U('Settings/user'), 'location' => ''));
    }

    public function user_edit(){
        $uid = I('uid', 0, 'intval');
        $mMember = new MemberModel();
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
        $mMember = new MemberModel();
        $ret = $mMember->update_member($uid, $userinfo);
        $this->ajaxReturn(array('errno' => 0, 'errmsg' => 'Success.', 'url' => U('Settings/user'), 'location' => ''));
    }

    public function message(){
        $mMessage = new MessageModel();
        $messages = $mMessage->get_message_list();
        $this->assign('messages', $messages);
        $this->display();
    }

    public function message_add(){
        $categories = C('MESSAGE_CATEGORIES');
        $this->assign('categories', $categories);
        $this->display();
    }

    public function message_add_submit(){
        $title = I('title', '', 'trim');
        $author = I('author', '', 'trim');
        $category = I('category', 0, 'intval');
        $content = I('content', '', 'trim');
        $link = I('link', '', 'trim');
        $tag_str = I('tags', '', 'trim');
        $desc = I('desc', '', 'trim');

        $categories = C('MESSAGE_CATEGORIES');
        if(!in_array($category, array_keys($categories))){
            $this->ajaxReturn(array('errno' => 1, 'errmsg' => 'Category value is invalid.', 'location' => 'category'));
        }

        $tags = array();
        if($tag_str){
            $tags_arr = explode(',', $tag_str);
            foreach ($tags_arr as $tag) {
                $tags[] = $tag;
            }
        }
        $message = array(
            'title' => $title,
            'author' => $author,
            'category' => $category,
            'content' => $content,
            'link' => $link,
            'desc' => $desc,
            'pubdate' => time(),
            'status' => 0,
            'level' => 0,
            'tags' => $tags
        );
        $mMessage = new MessageModel();
        $ret = $mMessage->insert_message($message);
        if($ret){
            $this->ajaxReturn(array('errno' => 0, 'errmsg' => 'Success.', 'url' => U('Settings/message'), 'location' => ''));
        }else{
            $this->ajaxReturn(array('errno' => 1, 'errmsg' => 'Failure.', 'url' => U('Settings/message'), 'location' => ''));
        }
    }

    public function message_edit(){
        $message_id = I('message_id', 0, 'intval');
        $mMessage = new MessageModel();
        $message = $mMessage->get_message($message_id);
        $this->assign('message', $message);

        $categories = C('MESSAGE_CATEGORIES');
        $this->assign('categories', $categories);

        $this->display();
    }

    public function message_edit_submit(){
        $message_id = I('message_id', 0, 'intval');
        $title = I('title', '', 'trim');
        $author = I('author', '', 'trim');
        $category = I('category', 0, 'intval');
        $content = I('content', '', 'trim');
        $link = I('link', '', 'trim');
        $tag_str = I('tags', '', 'trim');
        $desc = I('desc', '', 'trim');

        if(empty($message_id)){
            $this->ajaxReturn(array('errno' => 1, 'errmsg' => 'Message ID is invalid.', 'location' => ''));
        }

        $categories = C('MESSAGE_CATEGORIES');
        if(!in_array($category, array_keys($categories))){
            $this->ajaxReturn(array('errno' => 1, 'errmsg' => 'Category value is invalid.', 'location' => 'category'));
        }

        $tags = array();
        if($tag_str){
            $tags_arr = explode(',', $tag_str);
            foreach ($tags_arr as $tag) {
                $tags[] = trim($tag);
            }
        }

        $message = array(
            'title' => $title,
            'author' => $author,
            'category' => $category,
            'content' => $content,
            'pubdate' => time(),
            'link' => $link,
            'tags' => $tags,
            'desc' => $desc,
        );
        $mMessage = new MessageModel();
        $ret = $mMessage->update_message($message_id, $message);
        if($ret['ok']){
            $this->ajaxReturn(array('errno' => 0, 'errmsg' => 'Success.', 'url' => U('Settings/message'), 'location' => ''));
        }else{
            $this->ajaxReturn(array('errno' => 1, 'errmsg' => 'Failure.', 'url' => U('Settings/message'), 'location' => ''));
        }
    }

    public function message_del(){
        $message_id = I('message_id', 0, 'intval');
        $mMessage = new MessageModel();
        $message = $mMessage->get_message($message_id);
        $this->assign('message', $message);
        $this->display();
    }

    public function message_del_submit(){
        $message_id = I('message_id', 0, 'intval');
        if(empty($message_id)){
            $this->ajaxReturn(array('errno' => 1, 'errmsg' => 'Message ID is invalid.', 'location' => ''));
        }

        $mMessage = new MessageModel();
        $ret = $mMessage->remove_message($message_id);
        if($ret['ok']){
            $this->ajaxReturn(array('errno' => 0, 'errmsg' => 'Success.', 'url' => U('Settings/message'), 'location' => ''));
        }else{
            $this->ajaxReturn(array('errno' => 1, 'errmsg' => 'Failure.', 'url' => U('Settings/message'), 'location' => ''));
        }
    }

    public function location(){
        $mLocation = new LocationModel();
        $locations = $mLocation->get_location_list();
        $this->assign('locations', $locations);
        $this->display();
    }

    public function location_add(){
        $this->display();
    }

    public function location_add_submit(){
        //TODO
        $this->ajaxReturn(array('errno' => 0, 'errmsg' => 'This feature is not implemented.', 'url' => U('Settings/location'), 'location' => ''));
    }

    public function location_edit(){
        $location_id = I('location_id', 0, 'intval');
        $mLocation = new LocationModel();
        $location = $mLocation->get_location($location_id);
        $this->assign('location', $location);
        $this->display();
    }

    public function location_edit_submit(){
        $location_id = I('location_id', 0, 'intval');
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

        $location_info = array(
            'sex' => $sex,
            'major' => $major,
            'grade' => $grade,
            'desc' => $desc,
        );
        $mLocation = new LocationModel();
        $ret = $mLocation->update_location($location_id, $location_info);
        $this->ajaxReturn(array('errno' => 0, 'errmsg' => 'Success.', 'url' => U('Settings/location'), 'location' => ''));
    }

}