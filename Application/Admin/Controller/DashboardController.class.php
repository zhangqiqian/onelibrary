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

    public function message(){
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
        $this->assign('regions', $locations);

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
            $this->ajaxReturn(array('errno' => 0, 'errmsg' => 'Success.', 'url' => U('Dashboard/message'), 'location' => ''));
        }else{
            $this->ajaxReturn(array('errno' => 1, 'errmsg' => 'Failure.', 'url' => U('Dashboard/message'), 'location' => ''));
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
            $this->ajaxReturn(array('errno' => 0, 'errmsg' => 'Success.', 'url' => U('Dashboard/message'), 'location' => ''));
        }else{
            $this->ajaxReturn(array('errno' => 1, 'errmsg' => 'Failure.', 'url' => U('Dashboard/message'), 'location' => ''));
        }
    }


    public function location(){
        $this->display();
    }


}