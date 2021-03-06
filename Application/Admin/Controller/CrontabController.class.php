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
use Common\Model\PaperModel;
use Common\Model\PreferenceModel;
use Common\Model\PublishModel;
use Common\Model\UserBookModel;
use Common\Model\UserLocationLogModel;
use Common\Model\UserLocationModel;
use Common\Model\UserPaperModel;
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
                if($location['type'] > 5){
                    continue;
                }
                $square_sum = 0;
                foreach ($location['tags'] as $location_tag) {
                    foreach ($message_tags as $message_tag) {
                        if($location_tag['tag'] == $message_tag['tag']){
                            $abs_val = abs($location_tag['weight'] - $message_tag['weight']);
                            $square_sum += pow($abs_val, 2);
                        }
                    }
                }
                $locations[$key] = array(
                    'value' => sqrt($square_sum),
                    'count' => $location['count'],
                );
            }

            $value_max = 0;
            $value_id = 0;
            foreach ($locations as $key => $val) {
                if($val['value'] > $value_max){
                    $value_max = $val['value'];
                    $value_id = $key;
                }
            }
            $location_id = $value_id;
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
        $start_time = $end_time - 7 * 24 * 3600;
        
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
                    $location = $mLocation->get_location($location_id);
                    if(isset($location['location_type']) && $location['location_type'] > 4 ) continue;
                    if(isset($locations[$location_id])){
                        $locations[$location_id]['count'] += 1;
                    }else{
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
                $locations[$location_key]['tags'] = array_slice($map_tags, 0, 5);
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

        $old_all_keywords = array();
        $preferences = $mPref->get_all_preferences();
        foreach ($preferences as $preference) {
            $old_all_keywords[] = $preference['keyword'];
        }

        $new_all_keywords = array();
        //common keywords
        $cmn_keywords = C('COMMON_KEYWORDS');
        foreach ($cmn_keywords as $cmn_keyword) {
            $new_all_keywords[] = $cmn_keyword;
            if(!in_array($cmn_keyword, $old_all_keywords)){
                $start_time = time() - 5 * 365 * 24 * 3600;
                $end_time = 0;
                $mPref->add_keyword($cmn_keyword, 1, 0, $start_time, $end_time);
            }
        }

        //member research keywords
        $mMember = new MemberModel();
        $members = $mMember->get_members();
        foreach ($members as $member) {
            if($member['grade'] == 1) continue;
            if(isset($member['research'])){
                foreach ($member['research'] as $research) {
                    $new_all_keywords[] = $research;
                    if(!in_array($research, $old_all_keywords)){
                        echo "---- add new keyword: ".$research."\n";
                        $start_time = time() - 5 * 365 * 24 * 3600;
                        $end_time = 0;
                        $mPref->add_keyword($research, 1, 1, $start_time, $end_time);
                    }
                }
            }
            if(isset($member['projects'])){
                foreach ($member['projects'] as $project) {
                    $new_all_keywords[] = $project;
                    if(!in_array($project, $old_all_keywords)){
                        echo "---- add new keyword: ".$project."\n";
                        $start_time = time() - 5 * 365 * 24 * 3600;
                        $end_time = 0;
                        $mPref->add_keyword($project, 1, 2, $start_time, $end_time);
                    }
                }
            }
        }

        //curricula keywords
        /*$mCurricula = new CurriculaModel();
        $curriculas = $mCurricula->get_all_curriculas();
        foreach ($curriculas as $curricula) {
            foreach ($curricula['courses'] as $course) {
                $all_keywords[] = $course;
                if(!in_array($course, $old_all_keywords)){
                    $start_time = time() - 5 * 365 * 24 * 3600;
                    $end_time = 0;
                    $mPref->add_keyword($course['name'], 1, 3, $start_time, $end_time);
                }
            }
        }*/

        $new_all_keywords = array_unique($new_all_keywords);
        $del_keywords = array_diff($old_all_keywords, $new_all_keywords);
        foreach ($del_keywords as $del_keyword) {
            $mPref->delete_preference_by_keyword($del_keyword);
        }
    }

    public function push_book_message_by_course(){
        $mUser = new MemberModel();
        $mCurricula = new CurriculaModel();
        $mCourseBook = new CourseBookModel();
        $mBook = new BookModel();
        $mLocation = new LocationModel();

        $now = time();
        $now_time = date('Y-m-d', $now);
        $today_timestamp = strtotime($now_time." UTC");
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
                        'start_time' => $today_timestamp + $course['start_time'] - 8*3600,
                        'end_time' => $today_timestamp + $course['end_time'] - 8*3600,
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
            $content = "提醒: 今天是".date('Y年m月d日', $now)."星期".$week_names[$week].", <".$course['name'].">课程将于".date('H:i', $course['start_time'])." 在 ".$location['name']." 开始。\n\n";
            if(!empty($course_books)){
                $content = $content."猜你喜欢下面的图书: \n";
            }
            $i = 0;
            $total_sim = 0;
            foreach ($course_books as $course_book) {
                $book = $mBook->get_book($course_book['book_id']);
                
                $book_arr = array();
                if(!empty(trim($book['author']))){
                    $book_arr[] = $book['author'];
                }
                if(!empty(trim($book['publisher']))){
                    $book_arr[] = $book['publisher'];
                }
                if(!empty(trim($book['pubdate']))){
                    $book_arr[] = $book['pubdate'];
                }
                $book_info = implode(', ', $book_arr);

                $content = $content.($i+1).".《".$book['title']."》: ".$book['summary']." \n   —— ".$book_info."\n";
                $total_sim += $course_book['similarity'];
                $i += 1;
            }
            $avg_sim = $i > 0 ? round($total_sim/$i, 1) : 20;

            $message = array(
                'title' => "课程提醒: ".$course['name']."(".date('Y年m月d日', $now).")",
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


    public function repulish_messages(){
        $now = date("H", time());
        $hour = intval($now);
        if($hour > 22 || $hour < 6){
            return ;
        }
        $mPublish = new PublishModel();
        $mMessage = new MessageModel();
        $push_messages = $mPublish->get_publishes_by_status(0, 20);
        foreach ($push_messages as $push_message) {
            unset($push_message['_id']);
            $message = $mMessage->get_message($push_message['message_id']);
            if($message){
                if($message['category'] == 7){
                    continue;
                }
                if(($push_message['mtime'] - $push_message['ctime']) > 30 * 24 * 3600 ){
                    $push_message['status'] = 3; //invalid
                    $mPublish->update_publish($push_message['publish_id'], $push_message);
                    continue;
                }

                $mMember = new MemberModel();
                $member = $mMember->get_member($push_message['user_uid']);
                if($member && intval($member['status']) == 2){
                    continue;
                }

                if($push_message['similarity'] < 20){
                    continue;
                }

                if(!isset($message['tag_weight'])){
                    $message['tag_weight'] = array();
                }
                $location_id = $this->get_user_location($push_message['user_uid'], $message['tag_weight']);
                $new_push_message = array(
                    'location_id' => $location_id,
                    'publish_time' => time(),
                    'expire_time' => time() + 2 * 24 * 3600
                );
                echo json_encode($push_message)."\n";
                $mPublish->update_publish($push_message['publish_id'], $new_push_message);
            }
        }
    }

    public function publish_paper_message(){
        $mUser = new MemberModel();
        $mUserPaper = new UserPaperModel();
        $mPaper = new PaperModel();

        $now = date("H", time());
        $hour = intval($now);
        if($hour > 22 || $hour < 6){
            return ;
        }
        //获取所有的用户
        $users = $mUser->get_members();
        foreach ($users as $user) {
            if($user['grade'] == 1){
                continue;
            }
            //查找新的user paper
            $user_papers = $mUserPaper->get_user_papers($user['uid'], 10, 1);
            //插入新的paper信息
            foreach ($user_papers as $user_paper) {
                $paper = $mPaper->get_paper($user_paper['paper_id']);
                if(empty($paper)){
                    continue;
                }
                $message = array(
                    'title' => $paper['title'],
                    'content' => $paper['summary']."\n\n——《".$paper['title']."》, ".$paper['author'].", ".$paper['journal'].", ".$paper['period'],
                    'author' => array($paper['author']),
                    'category' => 2,//期刊论文
                    'link' => $paper['link'],
                    'pubdate' => $paper['pubdate'],
                    'status' => 0,  //0, no handle; 1, handled.
                    'level' => 0,  //0, no level; 1...9
                    'tags' => $paper['keywords'],
                    'tag_weight' => $paper['tags'],
                    'desc' => '',
                );
                //插入到message中
                $mMessage = new MessageModel();
                $message_id = $mMessage->insert_message($message);

                $location_id = $this->get_user_location($user['uid'], $paper['tags']);
                //发布新的信息
                if($message_id > 0){
                    if($user_paper['similarity'] > 60){
                        $priority = 3;
                    }elseif($user_paper['similarity'] > 30){
                        $priority = 2;
                    }else{
                        $priority = 1;
                    }
                    $publish_message = array(
                        'user_uid' => $user['uid'],
                        'location_id' => $location_id,
                        'publish_time' => time(),
                        'expire_time' => time() + 7 * 24 * 3600,
                        'message_id' => $message_id,
                        'status' => 0, //0:send
                        'priority' => $priority,
                        'similarity' => $user_paper['similarity']
                    );
                    $mPublish = new PublishModel();
                    $publish_id = $mPublish->insert_publish($publish_message);
                }

                //更新user paper的状态为1
                $data = array(
                    'status' => 1
                );
                $mUserPaper->update_user_paper($user['uid'], $paper['paper_id'], $data);
            }
        }
    }
}