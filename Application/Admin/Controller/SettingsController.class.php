<?php
// +----------------------------------------------------------------------
// | OneLibrary [ DO IT NOW! ]
// +----------------------------------------------------------------------
// | Copyright (c) 2014 niko All rights reserved.
// +----------------------------------------------------------------------
// | Author: zhangqiqian <43874051@qq.com>
// +----------------------------------------------------------------------

namespace Admin\Controller;
use Common\Model\MatchModel;
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

    public function message_distribute(){
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
        $this->assign('regions', $locations);

        $this->display();
    }

    public function message_distribute_submit(){
        $uid = I('user_uid', 0, 'intval');
        $grade = I('user_grade', 0, 'intval');
        $major = I('user_major', 0, 'intval');
        $gender = I('user_gender', 0, 'intval');
        $region_id = I('region_id', 0, 'intval');
        $datetime = I('datetime', '', 'trim');
        $message_id = I('message_id', 0, 'intval');
        $priority = I('priority', 0, 'intval');

        if(empty($datetime)){
            $date_time = time();
        }else{
            $date_time = strtotime($datetime);
        }

        $match = array(
            'user_uid' => $uid,
            'user_grade' => $grade,
            'user_major' => $major,
            'user_gender' => $gender,
            'region_id' => $region_id,
            'datetime' => $date_time,
            'message_id' => $message_id,
            'status' => 0,
            'priority' => $priority
        );

        $mMatch = new MatchModel();
        $ret = $mMatch->insert_match($match);
        if($ret){
            $this->ajaxReturn(array('errno' => 0, 'errmsg' => 'Success to distribute.', 'url' => '', 'location' => ''));
        }else{
            $this->ajaxReturn(array('errno' => 1, 'errmsg' => 'Failure to distribute.', 'url' => '', 'location' => ''));
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

        $mMatch = new MatchModel();
        $ret = $mMatch->remove_match_by_msg_id($message_id);
        if(!$ret['ok']){
            $remove_result = false;
        }

        if($remove_result){
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
        $countries = C('COUNTRY_CODE_MAP');
        $this->assign('countries', $countries);
        $this->display();
    }

    public function location_add_submit(){
        $name = I('name', '', 'trim');
        $longitude = I('longitude', 0.0, 'floatval');
        $latitude = I('latitude', 0.0, 'floatval');
        $status = I('status', 0, 'intval');
        $country_code = I('country_code', '', 'trim');
        $region = I('region', '', 'trim');
        $city = I('city', '', 'trim');
        $desc = I('desc', '', 'trim');

        $country_codes = C('COUNTRY_CODE_MAP');
        if(!in_array($country_code, array_keys($country_codes))){
            $this->ajaxReturn(array('errno' => 1, 'errmsg' => 'Country code is invalid.', 'location' => 'country'));
        }

        $location = array(
            'name' => $name,
            'longitude' => $longitude,
            'latitude' => $latitude,
            'status' => $status,
            'country_code' => $country_code,
            'region' => $region,
            'city' => $city,
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

        $countries = C('COUNTRY_CODE_MAP');
        $this->assign('countries', $countries);

        $this->display();
    }

    public function location_edit_submit(){
        $location_id = I('location_id', 0, 'intval');
        $name = I('name', '', 'trim');
        $longitude = I('longitude', 0.0, 'floatval');
        $latitude = I('latitude', 0.0, 'floatval');
        $status = I('status', 0, 'intval');
        $country_code = I('country_code', '', 'trim');
        $region = I('region', '', 'trim');
        $city = I('city', '', 'trim');
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
            'country_code' => $country_code,
            'region' => $region,
            'city' => $city,
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

}