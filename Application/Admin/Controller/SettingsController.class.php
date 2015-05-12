<?php
// +----------------------------------------------------------------------
// | OneLibrary [ DO IT NOW! ]
// +----------------------------------------------------------------------
// | Copyright (c) 2014 niko All rights reserved.
// +----------------------------------------------------------------------
// | Author: zhangqiqian <43874051@qq.com>
// +----------------------------------------------------------------------

namespace Admin\Controller;
use Common\Model\PublishModel;
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
        $majors = C('MAJOR_MAPPING');
        $this->assign('majors', $majors);
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

        $grades = C('USER_GRADES');
        $this->assign('grades', $grades);

        $majors = C('MAJOR_MAPPING');
        $this->assign('majors', $majors);

        $this->assign('member', $member);
        $this->display();
    }

    public function user_edit_submit(){
        $uid = I('uid', 0, 'intval');
        $gender = I('gender', 1, 'intval');
        $major = I('major', '');
        $grade = I('grade', 1, 'intval');
        $desc = I('desc', '');

        if(empty($uid)){
            $this->ajaxReturn(array('errno' => 1, 'errmsg' => 'User id is invalid.', 'location' => ''));
        }
        if(!in_array($gender, array(0,1))){
            $this->ajaxReturn(array('errno' => 1, 'errmsg' => 'Gender value is invalid.', 'location' => 'gender'));
        }
        $grades = C('USER_GRADES');
        if(!in_array($grade, array_keys($grades))){
            $this->ajaxReturn(array('errno' => 1, 'errmsg' => 'Grade value is invalid.', 'location' => 'gender'));
        }

        $userinfo = array(
            'gender' => $gender,
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
        $author_str = I('author', '', 'trim');
        $category = I('category', 0, 'intval');
        $content = I('content', '', 'trim');
        $link = I('link', '', 'trim');
        $tag_str = I('tags', '', 'trim');
        $desc = I('desc', '', 'trim');

        $categories = C('MESSAGE_CATEGORIES');
        if(!in_array($category, array_keys($categories))){
            $this->ajaxReturn(array('errno' => 1, 'errmsg' => 'Category value is invalid.', 'location' => 'category'));
        }

        $authors = array();
        if($author_str){
            $authors_arr = explode(',', $author_str);
            foreach ($authors_arr as $author) {
                $authors[] = $author;
            }
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
            'author' => $authors,
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
        $author_str = I('author', '', 'trim');
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

        $authors = array();
        if($author_str){
            $authors_arr = explode(',', $author_str);
            foreach ($authors_arr as $author) {
                $authors[] = $author;
            }
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
            'author' => $authors,
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

    public function message_publish(){
        $message_id = I('message_id', 0, 'intval');
        $this->assign('message_id', $message_id);

        $mMember = new MemberModel();
        $members = $mMember->get_member_list();
        $this->assign('members', $members);

        $grades = C('USER_GRADES');
        $this->assign('grades', $grades);

        $majors = C('MAJOR_MAPPING');
        $this->assign('majors', $majors);

        $priorities = C('MESSAGE_PRIORITY');
        $this->assign('priorities', $priorities);

        $mLocation = new LocationModel();
        $locations = $mLocation->get_location_list();
        $this->assign('locations', $locations);

        $this->display();
    }

    public function message_publish_submit(){
        $uid = I('user_uid', 0, 'intval');
        $grade = I('user_grade', 0, 'intval');
        $major = I('user_major', 0, 'intval');
        $gender = I('user_gender', 0, 'intval');
        $location_id = I('location_id', 0, 'intval');
        $datetime = I('expire_time', '', 'trim');
        $message_id = I('message_id', 0, 'intval');
        $priority = I('priority', 0, 'intval');

        if(empty($datetime)){
            $date_time = time() + 24 * 3600;
        }else{
            $date_time = strtotime($datetime);
        }

        $publish = array(
            'user_uid' => $uid,
            'user_grade' => $grade,
            'user_major' => $major,
            'user_gender' => $gender,
            'location_id' => $location_id,
            'expire_time' => $date_time,
            'message_id' => $message_id,
            'status' => 0,
            'priority' => $priority
        );

        $mPublish = new PublishModel();
        $ret = $mPublish->insert_publish($publish);
        if($ret){
            $this->ajaxReturn(array('errno' => 0, 'errmsg' => 'Success to publish.', 'url' => U('Settings/publish'), 'location' => ''));
        }else{
            $this->ajaxReturn(array('errno' => 1, 'errmsg' => 'Failure to publish.', 'url' => '', 'location' => ''));
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

        $remove_result = true;
        $mMessage = new MessageModel();
        $ret = $mMessage->remove_message($message_id);
        if(!$ret['ok']){
            $remove_result = false;
        }

        $mPublish = new PublishModel();
        $ret = $mPublish->remove_publish_by_msg_id($message_id);
        if(!$ret['ok']){
            $remove_result = false;
        }

        if($remove_result){
            $this->ajaxReturn(array('errno' => 0, 'errmsg' => 'Success.', 'url' => U('Settings/message'), 'location' => ''));
        }else{
            $this->ajaxReturn(array('errno' => 1, 'errmsg' => 'Failure.', 'url' => U('Settings/message'), 'location' => ''));
        }
    }

    public function message_detail(){
        $message_id = I('message', 0, 'intval');
        $mMessage = new MessageModel();
        $message = $mMessage->get_message($message_id);

        $new_contents = array();
        $contents = explode("\r\n", $message['content']);
        foreach ($contents as $new_content) {
            $new_contents[] = $new_content;
        }
        $message['content'] = $new_contents;
        $categories = C('MESSAGE_CATEGORIES');
        $message['category'] = $categories[$message['category']];
        $this->assign('message', $message);
        $this->display();
    }

    public function publish(){
        $mPublish = new PublishModel();
        $publishes = $mPublish->get_publish_list();
        $this->assign('publishes', $publishes);
        $this->display();
    }

    public function publish_edit(){
        $publish_id = I('publish_id', 0, 'intval');
        $mMember = new MemberModel();
        $members = $mMember->get_member_list();
        $this->assign('members', $members);

        $grades = C('USER_GRADES');
        $this->assign('grades', $grades);

        $majors = C('MAJOR_MAPPING');
        $this->assign('majors', $majors);

        $priorities = C('MESSAGE_PRIORITY');
        $this->assign('priorities', $priorities);

        $mLocation = new LocationModel();
        $locations = $mLocation->get_location_list();
        $this->assign('locations', $locations);

        $mPublish = new PublishModel();
        $publish = $mPublish->get_publish($publish_id);
        $this->assign('publish', $publish);
        $this->display();
    }

    public function publish_edit_submit(){
        $publish_id = I('publish_id', 0, 'intval');
        $uid = I('user_uid', 0, 'intval');
        $grade = I('user_grade', 0, 'intval');
        $major = I('user_major', 0, 'intval');
        $gender = I('user_gender', 0, 'intval');
        $location_id = I('location_id', 0, 'intval');
        $datetime = I('expire_time', '', 'trim');
        $message_id = I('message_id', 0, 'intval');
        $priority = I('priority', 0, 'intval');

        if(empty($publish_id)){
            $this->ajaxReturn(array('errno' => 1, 'errmsg' => 'Publish ID is invalid.', 'location' => ''));
        }

        if(empty($datetime)){
            $date_time = time();
        }else{
            $date_time = strtotime($datetime);
        }

        $publish = array(
            'user_uid' => $uid,
            'user_grade' => $grade,
            'user_major' => $major,
            'user_gender' => $gender,
            'location_id' => $location_id,
            'expire_time' => $date_time,
            'message_id' => $message_id,
            'status' => 0,
            'priority' => $priority
        );

        $mPublish = new PublishModel();
        $ret = $mPublish->update_publish($publish_id, $publish);
        if($ret['ok']){
            $this->ajaxReturn(array('errno' => 0, 'errmsg' => 'Success to publish.', 'url' => '', 'location' => ''));
        }else{
            $this->ajaxReturn(array('errno' => 1, 'errmsg' => 'Failure to publish.', 'url' => '', 'location' => ''));
        }
    }

    public function publish_read(){
        $publish_id = I('publish_id', 0, 'intval');
        $mPublish = new PublishModel();
        $publish = $mPublish->get_publish($publish_id);
        $this->assign('publish', $publish);
        $this->display();
    }

    public function publish_read_submit(){
        $publish_id = I('publish_id', 0, 'intval');
        if(empty($publish_id)){
            $this->ajaxReturn(array('errno' => 1, 'errmsg' => 'Publish ID is invalid.', 'location' => ''));
        }

        $mPublish = new PublishModel();
        $publish = $mPublish->get_publish($publish_id);
        $params = array();
        if($publish['status'] == 0){
            $params['status'] = 1;
        }else{
            $params['status'] = 0;
        }

        $ret = $mPublish->update_publish($publish_id, $params);
        if($ret['ok']){
            $this->ajaxReturn(array('errno' => 0, 'errmsg' => 'Success.', 'url' => U('Settings/publish'), 'location' => ''));
        }else{
            $this->ajaxReturn(array('errno' => 1, 'errmsg' => 'Failure.', 'url' => U('Settings/publish'), 'location' => ''));
        }
    }

    public function publish_del(){
        $publish_id = I('publish_id', 0, 'intval');
        $mPublish = new PublishModel();
        $publish = $mPublish->get_publish($publish_id);
        $this->assign('publish', $publish);
        $this->display();
    }

    public function publish_del_submit(){
        $publish_id = I('publish_id', 0, 'intval');
        if(empty($publish_id)){
            $this->ajaxReturn(array('errno' => 1, 'errmsg' => 'Publish ID is invalid.', 'location' => ''));
        }

        $mPublish = new PublishModel();
        $ret = $mPublish->remove_publish($publish_id);
        if($ret['ok']){
            $this->ajaxReturn(array('errno' => 0, 'errmsg' => 'Success.', 'url' => U('Settings/publish'), 'location' => ''));
        }else{
            $this->ajaxReturn(array('errno' => 1, 'errmsg' => 'Failure.', 'url' => U('Settings/publish'), 'location' => ''));
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
        $name = I('name', '', 'trim');
        $longitude = I('longitude', 0.0, 'floatval');
        $latitude = I('latitude', 0.0, 'floatval');
        $status = I('status', 0, 'intval');
        $radius = I('radius', 0, 'intval');
        $address = I('address', '', 'trim');
        $desc = I('desc', '', 'trim');

        $location = array(
            'name' => $name,
            'longitude' => $longitude,
            'latitude' => $latitude,
            'status' => $status,
            'radius' => $radius,
            'address' => $address,
            'desc' => $desc
        );
        $mLocation = new LocationModel();
        $ret = $mLocation->insert_location($location);
        if($ret){
            $this->ajaxReturn(array('errno' => 0, 'errmsg' => 'Success.', 'url' => U('Settings/location'), 'location' => ''));
        }else{
            $this->ajaxReturn(array('errno' => 1, 'errmsg' => 'Failure.', 'url' => U('Settings/location'), 'location' => ''));
        }

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
        $name = I('name', '', 'trim');
        $longitude = I('longitude', 0.0, 'floatval');
        $latitude = I('latitude', 0.0, 'floatval');
        $status = I('status', 0, 'intval');
        $radius = I('radius', 0, 'intval');
        $address = I('address', '', 'trim');
        $desc = I('desc', '', 'trim');

        if(empty($location_id)){
            $this->ajaxReturn(array('errno' => 1, 'errmsg' => 'Location id is invalid.', 'location' => ''));
        }
        if(!in_array($status, array(0,1))){
            $this->ajaxReturn(array('errno' => 1, 'errmsg' => 'Status is invalid.', 'location' => 'gender'));
        }

        $location_info = array(
            'name' => $name,
            'longitude' => $longitude,
            'latitude' => $latitude,
            'status' => $status,
            'radius' => $radius,
            'address' => $address,
            'desc' => $desc
        );

        $mLocation = new LocationModel();
        $ret = $mLocation->update_location($location_id, $location_info);
        if($ret['ok']){
            $this->ajaxReturn(array('errno' => 0, 'errmsg' => 'Success.', 'url' => U('Settings/location'), 'location' => ''));
        }else{
            $this->ajaxReturn(array('errno' => 1, 'errmsg' => 'Failure.', 'url' => U('Settings/location'), 'location' => ''));
        }
    }

    public function location_del(){
        $location_id = I('location_id', 0, 'intval');
        $mLocation = new LocationModel();
        $location = $mLocation->get_location($location_id);
        $this->assign('location', $location);
        $this->display();
    }

    public function location_del_submit(){
        $location_id = I('location_id', 0, 'intval');
        if(empty($location_id)){
            $this->ajaxReturn(array('errno' => 1, 'errmsg' => 'Location ID is invalid.', 'location' => ''));
        }

        $mLocation = new LocationModel();
        $ret = $mLocation->remove_location($location_id);
        if($ret['ok']){
            $this->ajaxReturn(array('errno' => 0, 'errmsg' => 'Success.', 'url' => U('Settings/location'), 'location' => ''));
        }else{
            $this->ajaxReturn(array('errno' => 1, 'errmsg' => 'Failure.', 'url' => U('Settings/location'), 'location' => ''));
        }
    }

    public function location_locate(){
        $location_id = I('location_id', 0, 'intval');
        $mLocation = new LocationModel();
        $location = $mLocation->get_location($location_id);
        $this->assign('location', $location);
        $this->display();
    }

}