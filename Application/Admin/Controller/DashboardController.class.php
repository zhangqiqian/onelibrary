<?php
// +----------------------------------------------------------------------
// | OneLibrary [ DO IT NOW! ]
// +----------------------------------------------------------------------
// | Copyright (c) 2014 niko All rights reserved.
// +----------------------------------------------------------------------
// | Author: zhangqiqian <43874051@qq.com>
// +----------------------------------------------------------------------

namespace Admin\Controller;
use Common\Model\LocationModel;
use Common\Model\MatchModel;
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

    public function message(){
        $mMatch = new MatchModel();
        $matches = $mMatch->get_match_list();
        $this->assign('matches', $matches);
        $this->display();
    }

    public function match_edit(){
        $match_id = I('match_id', 0, 'intval');
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

        $mMatch = new MatchModel();
        $match = $mMatch->get_match($match_id);
        $this->assign('match', $match);
        $this->display();
    }

    public function match_edit_submit(){
        $match_id = I('match_id', 0, 'intval');
        $uid = I('user_uid', 0, 'intval');
        $grade = I('user_grade', 0, 'intval');
        $major = I('user_major', 0, 'intval');
        $gender = I('user_gender', 0, 'intval');
        $region_id = I('region_id', 0, 'intval');
        $datetime = I('datetime', '', 'trim');
        $message_id = I('message_id', 0, 'intval');
        $priority = I('priority', 0, 'intval');

        if(empty($match_id)){
            $this->ajaxReturn(array('errno' => 1, 'errmsg' => 'Match ID is invalid.', 'location' => ''));
        }

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
        $ret = $mMatch->update_match($match_id, $match);
        if($ret['ok']){
            $this->ajaxReturn(array('errno' => 0, 'errmsg' => 'Success to distribute.', 'url' => '', 'location' => ''));
        }else{
            $this->ajaxReturn(array('errno' => 1, 'errmsg' => 'Failure to distribute.', 'url' => '', 'location' => ''));
        }
    }

    public function match_read(){
        $match_id = I('match_id', 0, 'intval');
        $mMatch = new MatchModel();
        $match = $mMatch->get_match($match_id);
        $this->assign('match', $match);
        $this->display();
    }

    public function match_read_submit(){
        $match_id = I('match_id', 0, 'intval');
        if(empty($match_id)){
            $this->ajaxReturn(array('errno' => 1, 'errmsg' => 'Match ID is invalid.', 'location' => ''));
        }

        $mMatch = new MatchModel();
        $match = $mMatch->get_match($match_id);
        $params = array();
        if($match['status'] == 0){
            $params['status'] = 1;
        }else{
            $params['status'] = 0;
        }

        $mMatch = new MatchModel();
        $ret = $mMatch->update_match($match_id, $params);
        if($ret['ok']){
            $this->ajaxReturn(array('errno' => 0, 'errmsg' => 'Success.', 'url' => U('Dashboard/message'), 'location' => ''));
        }else{
            $this->ajaxReturn(array('errno' => 1, 'errmsg' => 'Failure.', 'url' => U('Dashboard/message'), 'location' => ''));
        }
    }

    public function match_del(){
        $match_id = I('match_id', 0, 'intval');
        $mMatch = new MatchModel();
        $match = $mMatch->get_match($match_id);
        $this->assign('match', $match);
        $this->display();
    }

    public function match_del_submit(){
        $match_id = I('match_id', 0, 'intval');
        if(empty($match_id)){
            $this->ajaxReturn(array('errno' => 1, 'errmsg' => 'Match ID is invalid.', 'location' => ''));
        }

        $mMatch = new MatchModel();
        $ret = $mMatch->remove_match($match_id);
        if($ret['ok']){
            $this->ajaxReturn(array('errno' => 0, 'errmsg' => 'Success.', 'url' => U('Dashboard/message'), 'location' => ''));
        }else{
            $this->ajaxReturn(array('errno' => 1, 'errmsg' => 'Failure.', 'url' => U('Dashboard/message'), 'location' => ''));
        }
    }


    public function location(){
        $this->display();
    }


}