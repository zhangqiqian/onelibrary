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
use Common\Model\LocationModel;
use Common\Model\MessageModel;
use Common\Model\PaperModel;
use Common\Model\PublishModel;
use Common\Model\MemberModel;
use Think\Controller;

class DashboardController extends AdminController {
    /**
     * 后台-Dashboard
     * @author zhangqiqian <43874051@qq.com>
     */
    public function index(){
        $this->display();
    }

    public function user(){
        $mMember = new MemberModel();
        $mPublish = new PublishModel();
        $member_list = $mMember->get_members();

        $user_total = 0;
        $active_user_count = 0;

        $start_time = 0;//strtotime("2016-05-15 UTC");
        $end_time = 0;//strtotime("2016-06-12 UTC");

        $users = array();
        $grade_users = array();
        $major_users = array();

        $grades = C('USER_GRADES');
        $majors = C('MAJOR_MAPPING');
        foreach ($member_list as $member) {
            //user
            $push_no_received_count = $mPublish->get_publish_count_by_status($member['uid'], 0, $start_time);
            $received_no_read_count = $mPublish->get_publish_count_by_status($member['uid'], 1, $start_time);
            $read_count = $mPublish->get_publish_count_by_status($member['uid'], 2, $start_time);

            $active = 0;
            if(($received_no_read_count + $read_count) > 30){
                $active = 1;
            }

            $users[$member['uid']] = array(
                'login_count' => $member['login_count'],
                'pushed_count' => $push_no_received_count,
                'received_count' => $received_no_read_count,
                'read_count' => $read_count,
                'name' => $member['nickname'],
                'uid' => $member['uid'],
                'active' => $active,
            );

            if($member['login_count'] > 10 || $read_count > 20 || $received_no_read_count > 30){
                $active_user_count += 1;
            }
            $user_total += 1;

            //grade
            if(isset($grade_users[$member['grade']])){
                $grade_users[$member['grade']]['y'] += 1;
            }else{
                $grade_users[$member['grade']] = array(
                    'name' => $grades[$member['grade']],
                    'y' => 1
                );
            }

            //major
            if(isset($major_users[$member['major']])){
                $major_users[$member['major']]['y'] += 1;
            }else{
                $major_users[$member['major']] = array(
                    'name' => $majors[$member['major']],
                    'y' => 1
                );
            }
        }

        $active_users = array(
            array(
                'name' => '活跃用户数',
                'y' => $active_user_count,
            ),
            array(
                'name' => '非活跃用户数',
                'y' => $user_total - $active_user_count,
            ),
        );
        ksort($grade_users);
        ksort($major_users);

        $sort_users = array();
        foreach ($users as $user) {
            $sort_users[$user['uid']] = $user['received_count'] + $user['read_count'];
        }

        arsort($sort_users);

        $read_count = array(
            'name' => "接收并已读数",
            'data' => array(),
        );
        $received_count = array(
            'name' => "接收但未读数",
            'data' => array(),
        );
        $pushed_count = array(
            'name' => "推送未接收数",
            'data' => array(),
        );
        $login_count = array(
            'name' => "登录数",
            'data' => array(),
        );
        $chart_user_names = array();
        foreach ($sort_users as $uid => $value) {
            $chart_user_names[] = $users[$uid]['name'];
            $read_count['data'][] = $users[$uid]['read_count'];
            $received_count['data'][] = $users[$uid]['received_count'];
            $pushed_count['data'][] = $users[$uid]['pushed_count'];
            $login_count['data'][] = $users[$uid]['login_count'];
        }
        $chart_users = array(
            $read_count,
            $received_count,
            $pushed_count,
            $login_count,
        );

        $this->assign('chart_active_users', json_encode($active_users));
        $active_users[] = array(
            'name' => '总用户数',
            'y' => $user_total,
        );
        $this->assign('table_total_users', $active_users);
        $this->assign('table_grade_users', $grade_users);
        $this->assign('chart_grade_users', json_encode(array_values($grade_users)));
        $this->assign('table_major_users', $major_users);
        $this->assign('chart_major_users', json_encode(array_values($major_users)));

        $this->assign('table_users', $users);
        $this->assign('chart_user_names', json_encode($chart_user_names));
        $this->assign('chart_users', json_encode($chart_users));
        $this->display();
    }

    public function message(){
        $mPublish = new PublishModel();
        $mMember = new MemberModel();
        $mMessage = new MessageModel();
        $mBook = new BookModel();
        $mPaper = new PaperModel();

        $start_time = 0;//strtotime("2016-05-15 UTC");
        $end_time = 0;//strtotime("2016-06-12 UTC");

        //info total stat
        $book_total = $mBook->get_book_count();
        $paper_total = $mPaper->get_paper_count();
        $infos = array(
            array(
                'name' => '图书数',
                'y' => $book_total,
            ),
            array(
                'name' => '期刊论文数',
                'y' => $paper_total,
            ),
        );
        $this->assign('chart_infos', json_encode($infos));
        $infos[] = array(
            'name' => '信息资源总数',
            'y' => $book_total + $paper_total,
        );
        $this->assign('table_infos', $infos);

        //messages by category
        $book_message_count = $mMessage->get_message_count_by_category(1, $start_time);
        $paper_message_count = $mMessage->get_message_count_by_category(2, $start_time);
        $info_message_count = $mMessage->get_message_count_by_category(4, $start_time);
        $course_message_count = $mMessage->get_message_count_by_category(7, $start_time);
        $messages = array(
            array(
                'name' => '图书',
                'y' => $book_message_count,
            ),
            array(
                'name' => '期刊论文',
                'y' => $paper_message_count,
            ),
            array(
                'name' => '资讯',
                'y' => $info_message_count,
            ),
            array(
                'name' => '课程',
                'y' => $course_message_count,
            ),
        );
        $this->assign('chart_messages', json_encode($messages));
        $this->assign('table_messages', $messages);

        //messages by grade
        $user_grades = array();
        $member_list = $mMember->get_members();
        foreach ($member_list as $member) {
            $user_grades[$member['uid']] = $member['grade'];
        }

        $publishes = $mPublish->get_all_publishes($start_time);
        $undergraduates_status = array();
        $graduates_status = array();
        $teachers_status = array();
        $pushed_status_trend = array();
        $received_status_trend = array();
        $read_status_trend = array();

        $status_names = C('STATUS_NAMES');
        foreach ($publishes as $publish) {
            if(isset($user_grades[$publish['user_uid']])){
                $grade = $user_grades[$publish['user_uid']];
                if($grade == 1){
                    if(isset($undergraduates_status[$publish['status']])){
                        $undergraduates_status[$publish['status']]['y'] += 1;
                    }else{
                        $undergraduates_status[$publish['status']] = array(
                            'name' => $status_names[$publish['status']],
                            'y' => 1
                        );
                    }
                }elseif ($grade == 2){
                    if(isset($graduates_status[$publish['status']])){
                        $graduates_status[$publish['status']]['y'] += 1;
                    }else{
                        $graduates_status[$publish['status']] = array(
                            'name' => $status_names[$publish['status']],
                            'y' => 1
                        );
                    }
                }else{
                    if(isset($teachers_status[$publish['status']])){
                        $teachers_status[$publish['status']]['y'] += 1;
                    }else{
                        $teachers_status[$publish['status']] = array(
                            'name' => $status_names[$publish['status']],
                            'y' => 1
                        );
                    }
                }

                $point_time = intval($publish['publish_time']/86400) * 86400;
                if($publish['status'] == 0){ //pushed
                    if(isset($pushed_status_trend[$point_time])){
                        $pushed_status_trend[$point_time] += 1;
                    }else{
                        $pushed_status_trend[$point_time] = 1;
                    }
                }elseif ($publish['status'] == 1){ //received
                    if(isset($received_status_trend[$point_time])){
                        $received_status_trend[$point_time] += 1;
                    }else{
                        $received_status_trend[$point_time] = 1;
                    }
                }else{ //read
                    if(isset($read_status_trend[$point_time])){
                        $read_status_trend[$point_time] += 1;
                    }else{
                        $read_status_trend[$point_time] = 1;
                    }
                }
            }
        }

        ksort($undergraduates_status);
        ksort($graduates_status);
        ksort($teachers_status);

        $total_status = array();
        foreach ($status_names as $id => $status_name) {
            $undergraduates_count = isset($undergraduates_status[$id]['y']) ? $undergraduates_status[$id]['y'] : 0;
            $graduates_count = isset($graduates_status[$id]['y']) ? $graduates_status[$id]['y'] : 0;
            $teachers_count = isset($teachers_status[$id]['y']) ? $teachers_status[$id]['y'] : 0;
            $total_status[] = array(
                'name' => $status_name,
                'y' => $undergraduates_count + $graduates_count + $teachers_count
            );
        }

        $this->assign('chart_total_status', json_encode($total_status));
        $this->assign('table_total_status', $total_status);

        $this->assign('chart_undergraduate_status', json_encode($undergraduates_status));
        $this->assign('table_undergraduate_status', $undergraduates_status);

        $this->assign('chart_graduate_status', json_encode($graduates_status));
        $this->assign('table_graduate_status', $graduates_status);

        $this->assign('chart_teacher_status', json_encode($teachers_status));
        $this->assign('table_teacher_status', $teachers_status);

        ksort($pushed_status_trend);
        ksort($received_status_trend);
        ksort($read_status_trend);

        $new_pushed_status_trend = array();
        foreach ($pushed_status_trend as $point_time => $val) {
            $new_pushed_status_trend[] = array($point_time * 1000, $val);
        }

        $new_received_status_trend = array();
        foreach ($received_status_trend as $point_time => $val) {
            $new_received_status_trend[] = array($point_time * 1000, $val);
        }

        $new_read_status_trend = array();
        foreach ($read_status_trend as $point_time => $val) {
            $new_read_status_trend[] = array($point_time * 1000, $val);
        }

        $series = array(
            array(
                'name' => '接收并已读',
                'data' => $new_read_status_trend,
            ),
            array(
                'name' => '接收但未读',
                'data' => $new_received_status_trend,
            ),
            array(
                'name' => '推送未接收',
                'data' => $new_pushed_status_trend,
            ),
        );
        $this->assign('chart_status_trend', json_encode($series));
        $this->display();
    }

    public function location(){
        $this->display();
    }


}