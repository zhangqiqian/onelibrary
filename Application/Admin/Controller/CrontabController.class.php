<?php
// +----------------------------------------------------------------------
// | OneLibrary [ DO IT NOW! ]
// +----------------------------------------------------------------------
// | Copyright (c) 2014 niko All rights reserved.
// +----------------------------------------------------------------------
// | Author: zhangqiqian <43874051@qq.com>
// +----------------------------------------------------------------------

namespace Admin\Controller;
use Common\Model\BookModel;
use Common\Model\CourseBookModel;
use Common\Model\CurriculaModel;
use Common\Model\LocationModel;
use Common\Model\MemberModel;
use Common\Model\MessageModel;
use Common\Model\PreferenceModel;
use Common\Model\PublishModel;
use Common\Model\UserBookModel;
use Common\Model\UserLocationLogModel;
use Common\Model\UserLocationModel;
use Think\Controller;

class CrontabController extends Controller {

    public function test(){
        echo "OK\n";
    }

    public function publish_book_message(){
        $mUser = new MemberModel();
        $mUserBook = new UserBookModel();
        $mBook = new BookModel();

        $now = date("H", time());
        $hour = intval($now);
        if($hour > 22 || $hour < 6){
            return ;
        }
        //获取所有的用户
        $users = $mUser->get_members();
        foreach ($users as $user) {
            //查找新的user book信息
            $user_books = $mUserBook->get_user_books($user['uid'], 0, 1);
            //插入新的book信息
            foreach ($user_books as $user_book) {
                $book = $mBook->get_book($user_book['book_id']);
                $message = array(
                    'title' => $book['title'],
                    'content' => $book['summary']."\n\n——《".$book['title']."》,".$book['author'].", ".$book['publisher'].", ".$book['pubdate'],
                    'author' => array($book['author']),
                    'category' => 1,//图书
                    'link' => 'http://www.onelibrary.cn',
                    'pubdate' => time(),
                    'status' => 0,  //0, no handle; 1, handled.
                    'level' => 0,  //0, no level; 1...9
                    'tags' => $book['subject'],
                    'tag_weight' => $book['tags'],
                    'desc' => '',
                );
                //插入到message中
                $mMessage = new MessageModel();
                $message_id = $mMessage->insert_message($message);

                $location_id = $this->get_user_location($user['uid'], $book['tags']);
                //发布新的信息
                if($message_id > 0){
                    if($user_book['similarity'] > 60){
                        $priority = 3;
                    }elseif($user_book['similarity'] > 30){
                        $priority = 2;
                    }else{
                        $priority = 1;
                    }
                    $publish_message = array(
                        'user_uid' => $user['uid'],
                        'location_id' => $location_id,
                        'publish_time' => time(),
                        'expire_time' => time() + 30 * 24 * 3600,
                        'message_id' => $message_id,
                        'status' => 0, //0:send
                        'priority' => $priority,
                        'similarity' => $user_book['similarity']
                    );
                    $mPublish = new PublishModel();
                    $publish_id = $mPublish->insert_publish($publish_message);
                }

                //更新user book的状态为1
                $data = array(
                    'status' => 1
                );
                $mUserBook->update_user_book($user['uid'], $book['book_id'], $data);
            }
        }
    }

    private function get_user_location($uid, $message_tags){
        $mUserLocation = new UserLocationModel();

        $location_id = 0;
        $user_location = $mUserLocation->get_user_location($uid);
        if($user_location){
            $locations = array();
            foreach ($user_location['locations'] as $key => $location) {
                $square_sum = 0;
                foreach ($location['tags'] as $location_tag) {
                    foreach ($message_tags as $message_tag) {
                        if($location_tag['tag'] == $message_tag['tag']){
                            $abs_val = abs($location_tag['weight'] - $message_tag['weight']);
                            $square_sum += pow($abs_val, 2);
                        }
                    }
                }
                $locations[$key] = sqrt($square_sum);
            }

            $max = 0;
            $id = 0;
            foreach ($locations as $key => $val) {
                if($val > $max){
                    $max = $val;
                    $id = $key;
                }
            }
            $location_id = $id;
        }
        
        return $location_id;
    }


    /**
     * 分析用户的log信息，包含位置、发布时间、信息内容等的分析，
     */
    public function analyze_user_location(){
        $mUserLocation = new UserLocationModel();
        $mUserLocationLog = new UserLocationLogModel();
        $mLocation = new LocationModel();
        $mMessage = new MessageModel();

        $end_time = time();
        $start_time = $end_time - 24 * 3600;
        
        $mMember = new MemberModel();
        $users = $mMember->get_members();

        foreach ($users as $user) {
            $logs = $mUserLocationLog->get_location_logs_by_user($user['uid'], $start_time, $end_time);
            $user_location = $mUserLocation->get_user_location($user['uid']);
            if(empty($user_location)){
                $user_location = array(
                    'uid' => $user['uid'],
                    'status' => 0,
                    'locations' => array(),
                    'mtime' => time(),
                );
                $mUserLocation->insert_user_location($user_location);
            }

            $locations = $user_location['locations'];
            foreach ($logs as $log) {
                foreach ($log['location_ids'] as $location_id) {
                    if(isset($locations[$location_id])){
                        $locations[$location_id]['count'] += 1;
                    }else{
                        $location = $mLocation->get_location($location_id);
                        $locations[$location_id] = array(
                            'type' => isset($location['location_type']) ? $location['location_type'] : 0,
                            'count' => 1,
                            'tags' => array(),
                        );
                    }
                }

                foreach ($log['message_ids'] as $message_id) {
                    $message = $mMessage->get_message($message_id);
                    $message_tags = isset($message['tag_weight']) ? $message['tag_weight'] : array();
                    foreach ($locations as $key => $val) {
                        if(empty($val['tags'])){
                            $locations[$key]['tags'] = $message_tags;
                        }else{
                            $locations[$key]['tags'] = array_merge($val['tags'], $message_tags);
                        }
                    }
                }
            }
            //tags 去重
            foreach ($locations as $location_key => $location) {
                $new_tags = array();
                foreach ($location['tags'] as $tag) {
                    if(isset($new_tags[$tag['tag']])){
                        if($new_tags[$tag['tag']] < $tag['weight']){
                            $new_tags[$tag['tag']] = $tag['weight'];
                        }
                    }else{
                        $new_tags[$tag['tag']] = $tag['weight'];
                    }
                }
                arsort($new_tags);
                $map_tags = array();
                foreach ($new_tags as $tag => $weight) {
                    $map_tags[] = array(
                        'tag' => $tag,
                        'weight' => $weight,
                    );
                }
                $locations[$location_key]['tags'] = array_slice($map_tags, 0, 10);
            }
            $user_location['locations'] = $locations;
            $mUserLocation->update_user_location($user['uid'], $user_location);
        }
    }

    public function publish_lcaotion_test_message(){
        $mUser = new MemberModel();
        $mLocation = new LocationModel();

        //获取所有的用户
        $users = $mUser->get_members();
        foreach ($users as $user) {
            $locations = $mLocation->get_all_locations();
            //插入新的book信息
            foreach ($locations as $location) {
                $message = array(
                    'title' => "位置测试：".$location['name'],
                    'content' => "您目前所在的位置是：".$location['name'].", 如果位置不符，请记录下来当前所在位置的详细信息: 经纬度和范围。",
                    'author' => array('Niko'),
                    'category' => 2,//图书
                    'link' => 'http://www.onelibrary.cn',
                    'pubdate' => time(),
                    'status' => 0,  //0, no handle; 1, handled.
                    'level' => 0,  //0, no level; 1...9
                    'tags' => array('address'),
                    'tag_weight' => array(),
                    'desc' => '',
                );
                //插入到message中
                $mMessage = new MessageModel();
                $message_id = $mMessage->insert_message($message);

                //发布新的信息
                if($message_id > 0){
                    $publish_message = array(
                        'user_uid' => $user['uid'],
                        'location_id' => $location['location_id'],
                        'publish_time' => time(),
                        'expire_time' => time() + 24 * 3600,
                        'message_id' => $message_id,
                        'status' => 0,
                        'priority' => 3,
                        'similarity' => 100
                    );
                    $mPublish = new PublishModel();
                    $publish_id = $mPublish->insert_publish($publish_message);
                }
            }
        }
    }

    public function analyze_preference(){
        $mPref = new PreferenceModel();
        $mMember = new MemberModel();
        $members = $mMember->get_members();
        $tags = array();
        foreach ($members as $member) {
            if(isset($member['tags'])){
                foreach ($member['tags'] as $tag) {
                    if(isset($tags[$tag['tag']])){
                        $tags[$tag['tag']]['weight'] += $tag['weight'];
                        $tags[$tag['tag']]['count'] += 1;
                    }else{
                        $tags[$tag['tag']] = array(
                            'weight' => $tag['weight'],
                            'count' => 1
                        );
                    }
                }
            }
        }

        foreach ($tags as $key => $tag) {
            $pref = $mPref->get_preference_by_keyword($key);
            $weight = round($tag['weight']/$tag['count'], 3);
            if($pref){
                $params = array(
                    'weight' => $weight,
                );
                $mPref->update_preference($pref['pref_id'], $params);
            }else{
                $start_time = time() - 5 * 365 * 24 * 3600;
                $end_time = 0;
                $mPref->add_keyword($key, $weight, 1, $start_time, $end_time);
            }
        }

        $mCurricula = new CurriculaModel();
        $curriculas = $mCurricula->get_all_curriculas();
        foreach ($curriculas as $curricula) {
            foreach ($curricula['courses'] as $course) {
                $pref = $mPref->get_preference_by_keyword($course['name']);
                if(empty($pref)){
                    $start_time = time() - 5 * 365 * 24 * 3600;
                    $end_time = 0;
                    $mPref->add_keyword($course['name'], 1, 2, $start_time, $end_time);
                }
            }
        }
    }

    public function push_book_message_by_course(){
        $mUser = new MemberModel();
        $mCurricula = new CurriculaModel();
        $mCourseBook = new CourseBookModel();
        $mBook = new BookModel();
        $mLocation = new LocationModel();

        $now = time();
        $today = intval($now / 86400) * 86400;
        $week = intval(date('w', $now));
        $week_names = array("日", "一", "二", "三", "四", "五", "六");
        if($week > 5){
            return;
        }

        $courses = array();
        $curriculas = $mCurricula->get_all_curriculas();
        foreach ($curriculas as $curricula) {
            foreach ($curricula['courses'] as $course) {
                if($week == $course['week']){
                    $course_key = substr(md5($curricula['curricula_id']."_".$course['course_id']."_".$course['start_time']."_".$course['classroom']), 0, 8);
                    $courses[$course_key] = array(
                        'name' => $course['name'],
                        'course_id' => $course['course_id'],
                        'start_time' => $today + $course['start_time'] - 8*3600,
                        'end_time' => $today + $course['end_time'] - 8*3600,
                        'location_id' => $course['classroom'],
                        'curricula_id' => $curricula['curricula_id']
                    );
                }
            }
        }

        foreach ($courses as $course) {
            //找到与此相关的用户
            $users = $mUser->get_members_by_curricula($course['curricula_id']);
            if(empty($users)) continue;
            $location = $mLocation->get_location($course['location_id']);
            $course_books = $mCourseBook->get_course_books($course['course_id'], 20, 3);
            $content = "提醒: 今天是".date('Y年m月d日', $today)."星期".$week_names[$week].", <".$course['name'].">课程将于".date('H:i', $course['start_time'])." 在 ".$location['name']." 开始。\n\n";
            if(!empty($course_books)){
                $content = $content."猜你喜欢下面的图书: \n";
            }
            $i = 0;
            $total_sim = 0;
            foreach ($course_books as $course_book) {
                $book = $mBook->get_book($course_book['book_id']);
                $content = $content.($i+1).".《".$book['title']."》: ".$book['summary']." \n   —— ".$book['author'].", ".$book['publisher'].", ".$book['pubdate']."\n";
                $total_sim += $course_book['similarity'];
                $i += 1;
            }
            $avg_sim = $i > 0 ? round($total_sim/$i, 1) : 20;

            $message = array(
                'title' => "课程提醒: ".$course['name']."(".date('Y年m月d日', $today).")",
                'content' => $content,
                'author' => array("Onelibrary"),
                'category' => 7,//课程
                'link' => 'http://www.onelibrary.cn',
                'pubdate' => time(),
                'status' => 0,  //0, no handle; 1, handled.
                'level' => 0,  //0, no level; 1...9
                'tags' => array('课程提醒', $course['name']),
                'tag_weight' => array(),
                'desc' => '',
            );
            //插入到message中
            $mMessage = new MessageModel();
            $message_id = $mMessage->insert_message($message);

            //找到与此相关的用户
            foreach ($users as $user) {
                //发布新的信息
                if($message_id > 0){
                    $publish_message = array(
                        'user_uid' => $user['uid'],
                        'location_id' => $course['location_id'],
                        'publish_time' => $course['start_time'] - 3600,
                        'expire_time' => $course['end_time'],
                        'message_id' => $message_id,
                        'status' => 0, //0:send
                        'priority' => 3,
                        'similarity' => $avg_sim
                    );
                    $mPublish = new PublishModel();
                    $publish_id = $mPublish->insert_publish($publish_message);
                }
                
                //更新user book的状态为1
                $data = array(
                    'status' => 1
                );
                foreach ($course_books as $course_book) {
                    $mCourseBook->update_course_book($course['course_id'], $course_book['book_id'], $data);
                }
            }
        }
    }

    public function repair_course(){
        $mCurricula = new CurriculaModel();
        $curriculas = $mCurricula->get_all_curriculas();
        foreach ($curriculas as $curricula) {
            if(!empty($curricula['courses'])){
                foreach ($curricula['courses'] as $key => $course) {
                    $curricula['courses'][$key]['course_id'] = substr(md5($course['name']), 0, 8);
                }
                $mCurricula->update_curricula($curricula['curricula_id'], $curricula);
            }
        }
    }
}