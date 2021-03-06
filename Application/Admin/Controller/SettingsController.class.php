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
use Common\Model\CurriculaModel;

/**
 * 后台-Settings
 * @author zhangqiqian <43874051@qq.com>
 */
class SettingsController extends AdminController {

    public function message(){
        $search = I('search', '', 'trim');
        $start = I('start', 0, 'intval');
        $limit = I('limit', 20, 'intval');

        $mMessage = new MessageModel();
        $ret = $mMessage->get_message_list($search, $start, $limit);

        $count = $ret['count'];
        $pages = intval($count / $limit) + 1;
        $page = intval($start / $limit) + 1;

        $prev_start = $start - $limit >= 0 ? $start - $limit : 0;
        $next_start = $start + $limit >= $count ? $start : $start + $limit;
        $last_start = ($pages - 1) * $limit;

        $this->assign('messages', $ret['messages']);
        $this->assign('pages', $pages);
        $this->assign('page', $page);
        $this->assign('prev_start', $prev_start);
        $this->assign('next_start', $next_start);
        $this->assign('last_start', $last_start);
        $this->assign('limit', $limit);
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
            'tags' => $tags,
            'tag_weight' => array()
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

        $grades = C('USER_GRADES');
        unset($grades[0]);
        unset($grades[3]);
        unset($grades[4]);
        unset($grades[5]);
        unset($grades[6]);
        $this->assign('grades', $grades);

        $majors = C('MAJOR_MAPPING');
        unset($majors[0]);
        $this->assign('majors', $majors);

        $priorities = C('MESSAGE_PRIORITY');
        $this->assign('priorities', $priorities);

        $mLocation = new LocationModel();
        $locations = $mLocation->get_all_locations();
        $this->assign('locations', $locations);

        $this->display();
    }

    public function message_publish_submit(){
        $grade_id = I('grade_id', 0, 'intval');
        $major_id = I('major_id', 0, 'intval');
        $location_id = I('location_id', 0, 'intval');
        $publish_time = I('publish_time', '', 'trim');
        $expire_time = I('expire_time', '', 'trim');
        $message_id = I('message_id', 0, 'intval');
        $priority = I('priority', 0, 'intval');
        $similarity = I('similarity', 1, 'intval');

        if(empty($publish_time)){
            $pub_time = time();
        }else{
            $pub_time = strtotime($publish_time);
        }

        if(empty($expire_time)){
            $date_time = time() + 24 * 3600;
        }else{
            $date_time = strtotime($expire_time);
        }

        $uids = array();
        if($grade_id > 0 || $major_id > 0){
            $mMember = new MemberModel();
            $members = $mMember->get_members_by_grade_major($grade_id, $major_id);
            foreach ($members as $member) {
                $uids[] = $member['uid'];
            }
        }else{
            $uids[] = 0;
        }

        $mPublish = new PublishModel();
        foreach ($uids as $uid) {
            $publish = array(
                'user_uid' => $uid,
                'location_id' => $location_id,
                'publish_time' => $pub_time,
                'expire_time' => $date_time,
                'message_id' => $message_id,
                'status' => 0,
                'priority' => $priority,
                'similarity' => $similarity
            );
            $mPublish->insert_publish($publish);
        }

        $this->ajaxReturn(array('errno' => 0, 'errmsg' => 'Success to publish.', 'url' => U('Settings/publish'), 'location' => ''));
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
        $contents = explode("\n", $message['content']);
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
        $search = I('search', '', 'trim');
        $start = I('start', 0, 'intval');
        $limit = I('limit', 20, 'intval');

        $uids = array();
        if($search){
            $mMember = new MemberModel();
            $members = $mMember->get_member_by_name($search);
            if($members){
                foreach ($members as $member) {
                    $uids[] = $member['uid'];
                }
            }
        }
        
        $mPublish = new PublishModel();
        $ret = $mPublish->get_publish_list($uids, $start, $limit);
        $count = $ret['count'];
        $pages = intval($count / $limit) + 1;
        $page = intval($start / $limit) + 1;

        $prev_start = $start - $limit >= 0 ? $start - $limit : 0;
        $next_start = $start + $limit >= $count ? $start : $start + $limit;
        $last_start = ($pages - 1) * $limit;

        $this->assign('publishes', $ret['publishes']);
        $this->assign('pages', $pages);
        $this->assign('page', $page);
        $this->assign('prev_start', $prev_start);
        $this->assign('next_start', $next_start);
        $this->assign('last_start', $last_start);
        $this->assign('limit', $limit);
        $this->assign('search', $search);
        $this->display();
    }

    public function publish_edit(){
        $publish_id = I('publish_id', 0, 'intval');
        $mMember = new MemberModel();
        $members = $mMember->get_member_list();
        $this->assign('members', $members);

        $priorities = C('MESSAGE_PRIORITY');
        $this->assign('priorities', $priorities);

        $mLocation = new LocationModel();
        $locations = $mLocation->get_all_locations();
        $this->assign('locations', $locations);

        $mPublish = new PublishModel();
        $publish = $mPublish->get_publish($publish_id);
        $this->assign('publish', $publish);
        $this->display();
    }

    public function publish_edit_submit(){
        $publish_id = I('publish_id', 0, 'intval');
        $uid = I('user_uid', 0, 'intval');
        $location_id = I('location_id', 0, 'intval');
        $publish_time = I('publish_time', '', 'trim');
        $expire_time = I('expire_time', '', 'trim');
        $message_id = I('message_id', 0, 'intval');
        $priority = I('priority', 0, 'intval');
        $similarity = I('similarity', 1, 'intval');

        if(empty($publish_id)){
            $this->ajaxReturn(array('errno' => 1, 'errmsg' => 'Publish ID is invalid.', 'location' => ''));
        }

        if(empty($publish_time)){
            $pub_time = time();
        }else{
            $pub_time = strtotime($publish_time);
        }
        if(empty($expire_time)){
            $date_time = time() + 24 * 3600;
        }else{
            $date_time = strtotime($expire_time);
        }

        $publish = array(
            'user_uid' => $uid,
            'location_id' => $location_id,
            'publish_time' => $pub_time,
            'expire_time' => $date_time,
            'message_id' => $message_id,
            'status' => 0,
            'priority' => $priority,
            'similarity' => $similarity
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
        $search = I('search', '', 'trim');
        $start = I('start', 0, 'intval');
        $limit = I('limit', 20, 'intval');

        $mLocation = new LocationModel();
        $ret = $mLocation->get_location_list($search, $start, $limit);

        $count = $ret['count'];
        $pages = intval($count / $limit) + 1;
        $page = intval($start / $limit) + 1;

        $prev_start = $start - $limit >= 0 ? $start - $limit : 0;
        $next_start = $start + $limit >= $count ? $start : $start + $limit;
        $last_start = ($pages - 1) * $limit;

        $this->assign('locations', $ret['locations']);
        $this->assign('pages', $pages);
        $this->assign('page', $page);
        $this->assign('search', $search);
        $this->assign('prev_start', $prev_start);
        $this->assign('next_start', $next_start);
        $this->assign('last_start', $last_start);
        $this->assign('limit', $limit);
        $this->display();
    }

    public function location_add(){
        $this->assign('location_types', C('LOCATION_TYPE_MAPPING'));
        $this->display();
    }

    public function location_add_submit(){
        $name = I('name', '', 'trim');
        $longitude = I('longitude', 0.0, 'floatval');
        $latitude = I('latitude', 0.0, 'floatval');
        $location_type = I('location_type', 0, 'intval');
        $status = I('status', 0, 'intval');
        $radius = I('radius', 0, 'intval');
        $address = I('address', '', 'trim');
        $desc = I('desc', '', 'trim');

        $location = array(
            'name' => $name,
            'longitude' => $longitude,
            'latitude' => $latitude,
            'location_type' => $location_type,
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
        $this->assign('location_types', C('LOCATION_TYPE_MAPPING'));
        $this->display();
    }

    public function location_edit_submit(){
        $location_id = I('location_id', 0, 'intval');
        $name = I('name', '', 'trim');
        $longitude = I('longitude', 0.0, 'floatval');
        $latitude = I('latitude', 0.0, 'floatval');
        $location_type = I('location_type', 0, 'intval');
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
            'location_type' => $location_type,
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

    public function get_near_locations(){
        $longitude = I('longitude', 0, 'doubleval');
        $latitude = I('latitude', 0, 'doubleval');
        $mLocation = new LocationModel();
        $locations = $mLocation->get_locations_by_location($longitude, $latitude);
        $near_locations = array();
        foreach ($locations as $location) {
            if($location['status'] > 0){
                $distance = get_distance($latitude, $longitude, $location['latitude'], $location['longitude']);
                if($distance <= $location['radius']){
                    $near_locations[] = $location;
                }
            }
        }
        $this->ajaxReturn($near_locations);
    }

    public function curricula(){
        $search = I('search', '', 'trim');
        $start = I('start', 0, 'intval');
        $limit = I('limit', 20, 'intval');

        $mCurricula = new CurriculaModel();
        $ret = $mCurricula->get_curricula_list($search, $start, $limit);

        $count = $ret['count'];
        $pages = intval($count / $limit) + 1;
        $page = intval($start / $limit) + 1;

        $prev_start = $start - $limit >= 0 ? $start - $limit : 0;
        $next_start = $start + $limit >= $count ? $start : $start + $limit;
        $last_start = ($pages - 1) * $limit;

        $majors = C('MAJOR_MAPPING');
        foreach ($ret['curriculas'] as $key => $curricula) {
            $ret['curriculas'][$key]['major'] = isset($majors[$curricula['major']]) ? $majors[$curricula['major']] : '其他';
        }
        $this->assign('curriculas', $ret['curriculas']);
        $this->assign('pages', $pages);
        $this->assign('page', $page);
        $this->assign('search', $search);
        $this->assign('prev_start', $prev_start);
        $this->assign('next_start', $next_start);
        $this->assign('last_start', $last_start);
        $this->assign('limit', $limit);
        $this->display();
    }

    public function curricula_detail(){
        $curricula_id = I('curricula', 0, 'intval');
        $mCurricula = new CurriculaModel();
        $curricula = $mCurricula->get_curricula($curricula_id);

        $majors = C('MAJOR_MAPPING');
        $curricula['major'] = isset($majors[$curricula['major']]) ? $majors[$curricula['major']] : '其他';

        $courses = array();
        for($i = 1; $i < 8; $i += 2){
            $courses[$i] = array();
            for($j = 1; $j < 8; $j++){
                $courses[$i][$j] = array();
            }
        }
        $mMember = new MemberModel();
        $mLocation = new LocationModel();
        foreach ($curricula['courses'] as $course) {
            $member = $mMember->get_member($course['teacher']);
            $course['teacher'] = $member['nickname'];
            if (is_string($course['classroom'])) {
                $classroom = $course['classroom'];
            }else{
                $location = $mLocation->get_location(intval($course['classroom']));
                $classroom = $location['name'];
            }
            $course['classroom'] = $classroom;
            $courses[$course['section']][$course['week']] = $course;
        }
        $curricula['courses'] = $courses;

        $term_start = $curricula['term_start'];
        $month = intval(date("m", $term_start));
        if($month > 8){
            $term_year_start = intval(date("Y", $term_start));
            $term_year_end = $term_year_start + 1;
        }else{
            $term_year_end = intval(date("Y", $term_start));
            $term_year_start = $term_year_end - 1;
        }
        $curricula['term_year_start'] = $term_year_start;
        $curricula['term_year_end'] = $term_year_end;

        $this->assign('curricula', $curricula);
        $this->display();
    }

    public function curricula_add(){
        $majors = C('MAJOR_MAPPING');
        $this->assign('majors', $majors);
        $this->display();
    }

    public function curricula_add_submit(){
        $name = I('name', '', 'trim');
        $major = I('major', 0, 'intval');
        $class = I('class', '', 'trim');
        $grade = I('grade', 0, 'intval');
        $term = I('term', 1, 'intval');
        $term_start = I('term_start', '', 'trim');
        $term_end = I('term_end', '', 'trim');
        $status = I('status', 1, 'intval');
        $desc = I('desc', '', 'trim');

        if(empty($term_start)){
            if($term == 1){
                $term_start = date('Y', time()).'-09-01';
            }else{
                $next_year = intval(date('Y', time())) + 1;
                $term_start = $next_year.'-01-12';
            }
        }
        if(empty($term_end)){
            $next_year = intval(date('Y', time())) + 1;
            if($term == 1){
                $term_end = $next_year.'-02-22';
            }else{
                $term_end = $next_year.'-07-10';
            }
        }
        $term_start_time = strtotime($term_start);
        $term_end_time = strtotime($term_end);

        $curricula = array(
            'name' => $name,
            'major' => $major,
            'class' => $class,
            'grade' => $grade,
            'courses' => array(),
            'term' => $term,
            'term_start' => intval($term_start_time),
            'term_end' => intval($term_end_time),
            'status' => $status,
            'desc' => $desc,
        );
        $mCurricula = new CurriculaModel();
        $ret = $mCurricula->insert_curricula($curricula);
        if($ret){
            $this->ajaxReturn(array('errno' => 0, 'errmsg' => 'Success.', 'url' => U('Settings/curricula'), 'location' => ''));
        }else{
            $this->ajaxReturn(array('errno' => 1, 'errmsg' => 'Failure.', 'url' => U('Settings/curricula'), 'location' => ''));
        }
    }

    public function curricula_edit(){
        $curricula_id = I('curricula_id', 0, 'intval');
        $mCurricula = new CurriculaModel();
        $curricula = $mCurricula->get_curricula($curricula_id);
        $this->assign('curricula', $curricula);

        $majors = C('MAJOR_MAPPING');
        $this->assign('majors', $majors);

        $this->display();
    }

    public function curricula_edit_submit(){
        $curricula_id = I('curricula_id', 0, 'intval');
        $name = I('name', '', 'trim');
        $major = I('major', 0, 'intval');
        $class = I('class', '', 'trim');
        $grade = I('grade', 0, 'intval');
        $term = I('term', 1, 'intval');
        $term_start = I('term_start', '', 'trim');
        $term_end = I('term_end', '', 'trim');
        $status = I('status', 1, 'intval');
        $desc = I('desc', '', 'trim');

        if(empty($term_start)){
            if($term == 1){
                $term_start = date('Y', time()).'-09-01';
            }else{
                $next_year = intval(date('Y', time())) + 1;
                $term_start = $next_year.'-01-12';
            }
        }
        if(empty($term_end)){
            $next_year = intval(date('Y', time())) + 1;
            if($term == 1){
                $term_end = $next_year.'-02-22';
            }else{
                $term_end = $next_year.'-07-10';
            }
        }
        $term_start_time = strtotime($term_start);
        $term_end_time = strtotime($term_end);

        if($curricula_id == 0){
            $this->ajaxReturn(array('errno' => 1, 'errmsg' => 'Curricula ID is invalid.', 'location' => ''));
        }

        $curricula = array(
            'name' => $name,
            'major' => $major,
            'class' => $class,
            'grade' => $grade,
            'term' => $term,
            'term_start' => intval($term_start_time),
            'term_end' => intval($term_end_time),
            'status' => $status,
            'desc' => $desc,
        );

        $mCurricula = new CurriculaModel();
        $ret = $mCurricula->update_curricula($curricula_id, $curricula);
        if($ret['ok']){
            $this->ajaxReturn(array('errno' => 0, 'errmsg' => 'Success.', 'url' => U('Settings/curricula'), 'location' => ''));
        }else{
            $this->ajaxReturn(array('errno' => 1, 'errmsg' => 'Failure.', 'url' => U('Settings/curricula'), 'location' => ''));
        }
    }

    public function curricula_del(){
        $curricula_id = I('curricula_id', 0, 'intval');
        $mCurricula = new CurriculaModel();
        $curricula = $mCurricula->get_curricula($curricula_id);
        $this->assign('curricula', $curricula);
        $this->display();
    }

    public function curricula_del_submit(){
        $curricula_id = I('curricula_id', 0, 'intval');
        if(empty($curricula_id)){
            $this->ajaxReturn(array('errno' => 1, 'errmsg' => 'Curricula ID is invalid.', 'location' => ''));
        }

        $remove_result = true;
        $mCurricula = new CurriculaModel();
        $ret = $mCurricula->remove_curricula($curricula_id);
        if(!$ret['ok']){
            $remove_result = false;
        }

        if($remove_result){
            $this->ajaxReturn(array('errno' => 0, 'errmsg' => 'Success.', 'url' => U('Settings/curricula'), 'location' => ''));
        }else{
            $this->ajaxReturn(array('errno' => 1, 'errmsg' => 'Failure.', 'url' => U('Settings/curricula'), 'location' => ''));
        }
    }

    public function course_add(){
        $curricula_id = I('curricula_id', 0, 'intval');
        $course_week = I('week', 0, 'intval');

        $mCurricula = new CurriculaModel();
        $curricula = $mCurricula->get_curricula($curricula_id);
        $this->assign('curricula_id', $curricula['curricula_id']);
        $this->assign('course_week', $course_week);

        $mMember = new MemberModel();
        $members = $mMember->get_teacher_list();
        $teachers = array();
        foreach ($members as $member) {
            $teachers[$member['uid']] = $member['nickname'];
        }
        $this->assign('teachers', $teachers);

        $mLocation = new LocationModel();
        $locations = $mLocation->get_locations_by_type(array(1,2,3,4,7));
        $calssrooms = array();
        foreach ($locations as $location) {
            $calssrooms[$location['location_id']] = $location['name'];
        }
        $this->assign('locations', $calssrooms);
        $this->display();
    }

    public function course_add_submit(){
        $curricula_id = I('curricula_id', 0, 'intval');
        $course_name = I('course_name', '', 'trim');
        $teacher = I('teacher', 0, 'intval');
        $classroom = I('classroom', '', 'intval');
        $course_week = I('course_week', 0, 'intval');
        $course_section = I('course_section', 0, 'intval');
        $course_period = I('course_period', 1, 'intval');

        if($curricula_id == 0){
            $this->ajaxReturn(array('errno' => 1, 'errmsg' => 'Curricula ID is invalid.', 'location' => ''));
        }

        if($course_week == 0 || $course_section == 0){
            $this->ajaxReturn(array('errno' => 1, 'errmsg' => 'Week or section is invalid.', 'location' => ''));
        }

        $section_times = C('COURSE_SECTION_TIME_MAPPING');
        $course = array(
            'course_id' => substr(md5($course_name), 0, 8),
            'name' => $course_name,
            'teacher' => $teacher,
            'classroom' => $classroom,
            'week' => $course_week,
            'section' => $course_section,
            'start_time' => $section_times[$course_section]['start'],
            'end_time' => $section_times[$course_section]['end'],
            'period' => $course_period,
        );

        $mCurricula = new CurriculaModel();
        $curricula = $mCurricula->get_curricula($curricula_id);
        unset($curricula['_id']);
        $curricula['courses'][] = $course;

        $mCurricula = new CurriculaModel();
        $ret = $mCurricula->update_curricula($curricula_id, $curricula);
        if($ret['ok']){
            $this->ajaxReturn(array('errno' => 0, 'errmsg' => 'Success.', 'url' => U('Settings/curricula_detail').'/curricula/'.$curricula_id, 'location' => ''));
        }else{
            $this->ajaxReturn(array('errno' => 1, 'errmsg' => 'Failure.', 'url' => U('Settings/curricula_detail').'/curricula/'.$curricula_id, 'location' => ''));
        }
    }

    public function course_edit(){
        $curricula_id = I('curricula_id', 0, 'intval');
        $course_week = I('week', 0, 'intval');
        $course_section = I('section', 0, 'intval');

        $mCurricula = new CurriculaModel();
        $curricula = $mCurricula->get_curricula($curricula_id);
        $this->assign('curricula_id', $curricula['curricula_id']);
        foreach ($curricula['courses'] as $course) {
            if($course_week == $course['week'] && $course_section == $course['section']){
                $this->assign('course', $course);
                break;
            }
        }

        $mMember = new MemberModel();
        $members = $mMember->get_teacher_list();
        $teachers = array();
        foreach ($members as $member) {
            $teachers[$member['uid']] = $member['nickname'];
        }
        $this->assign('teachers', $teachers);

        $mLocation = new LocationModel();
        $locations = $mLocation->get_locations_by_type(array(1,2,3,4,7));
        $calssrooms = array();
        foreach ($locations as $location) {
            $calssrooms[$location['location_id']] = $location['name'];
        }
        $this->assign('locations', $calssrooms);

        $this->display();
    }

    public function course_edit_submit(){
        $curricula_id = I('curricula_id', 0, 'intval');
        $course_name = I('course_name', '', 'trim');
        $teacher = I('teacher', 0, 'intval');
        $classroom = I('classroom', '', 'intval');
        $old_week = I('old_week', 0, 'intval');
        $old_section = I('old_section', 0, 'intval');
        $course_week = I('course_week', 0, 'intval');
        $course_section = I('course_section', 0, 'intval');
        $course_period = I('course_period', 1, 'intval');

        if($curricula_id == 0){
            $this->ajaxReturn(array('errno' => 1, 'errmsg' => 'Curricula ID is invalid.', 'location' => ''));
        }

        if($course_week == 0 || $course_section == 0){
            $this->ajaxReturn(array('errno' => 1, 'errmsg' => 'Week or section is invalid.', 'location' => ''));
        }

        $section_times = C('COURSE_SECTION_TIME_MAPPING');
        $course = array(
            'course_id' => substr(md5($course_name), 0, 8),
            'name' => $course_name,
            'teacher' => $teacher,
            'classroom' => $classroom,
            'week' => $course_week,
            'section' => $course_section,
            'start_time' => $section_times[$course_section]['start'],
            'end_time' => $section_times[$course_section]['end'],
            'period' => $course_period,
        );

        $mCurricula = new CurriculaModel();
        $curricula = $mCurricula->get_curricula($curricula_id);
        foreach ($curricula['courses'] as $key => $old_course) {
            if($old_week == $old_course['week'] && $old_section == $old_course['section']){
                unset($curricula['courses'][$key]);
            }
        }
        unset($curricula['_id']);
        $curricula['courses'][] = $course;

        $mCurricula = new CurriculaModel();
        $ret = $mCurricula->update_curricula($curricula_id, $curricula);
        if($ret['ok']){
            $this->ajaxReturn(array('errno' => 0, 'errmsg' => 'Success.', 'url' => U('Settings/curricula_detail').'/curricula/'.$curricula_id, 'location' => ''));
        }else{
            $this->ajaxReturn(array('errno' => 1, 'errmsg' => 'Failure.', 'url' => U('Settings/curricula_detail').'/curricula/'.$curricula_id, 'location' => ''));
        }
    }

    public function course_del(){
        $curricula_id = I('curricula_id', 0, 'intval');
        $course_week = I('week', 0, 'intval');
        $course_section = I('section', 0, 'intval');

        $mCurricula = new CurriculaModel();
        $curricula = $mCurricula->get_curricula($curricula_id);
        $this->assign('curricula_id', $curricula['curricula_id']);
        if($course_week > 0 && $course_section > 0){
            foreach ($curricula['courses'] as $course) {
                if($course_week == $course['week'] && $course_section == $course['section']){
                    $this->assign('course', $course);
                    break;
                }
            }
        }
        $this->display();
    }

    public function course_del_submit(){
        $curricula_id = I('curricula_id', 0, 'intval');
        $course_week = I('course_week', 0, 'intval');
        $course_section = I('course_section', 0, 'intval');

        if($curricula_id == 0){
            $this->ajaxReturn(array('errno' => 1, 'errmsg' => 'Curricula ID is invalid.', 'location' => ''));
        }

        $mCurricula = new CurriculaModel();
        $curricula = $mCurricula->get_curricula($curricula_id);
        $new_key = count($curricula['courses']);
        foreach ($curricula['courses'] as $key => $old_course) {
            if($course_week == $old_course['week'] && $course_section == $old_course['section']){
                $new_key = $key;
                break;
            }
        }
        unset($curricula['_id']);
        unset($curricula['courses'][$new_key]);

        $mCurricula = new CurriculaModel();
        $ret = $mCurricula->update_curricula($curricula_id, $curricula);
        if($ret['ok']){
            $this->ajaxReturn(array('errno' => 0, 'errmsg' => 'Success.', 'url' => U('Settings/curricula_detail').'/curricula/'.$curricula_id, 'location' => ''));
        }else{
            $this->ajaxReturn(array('errno' => 1, 'errmsg' => 'Failure.', 'url' => U('Settings/curricula_detail').'/curricula/'.$curricula_id, 'location' => ''));
        }
    }
}