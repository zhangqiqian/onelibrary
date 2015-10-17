<?php
// +----------------------------------------------------------------------
// | OneLibrary [ DO IT NOW! ]
// +----------------------------------------------------------------------
// | Copyright (c) 2014 niko All rights reserved.
// +----------------------------------------------------------------------
// | Author: zhangqiqian <43874051@qq.com>
// +----------------------------------------------------------------------

namespace Api\Controller;
use Common\Model\LocationModel;
use Common\Model\MemberModel;
use Common\Model\PublishModel;
use Common\Model\MessageModel;

use Think\Controller;

class IndexController extends ApiController {
    /**
     * 后台首页
     * @author zhangqiqian <43874051@qq.com>
     */
    public function index(){
        $this->ajaxReturn(array('errno' => 0, 'errmsg' => 'Welcome to Api'));
    }

    public function messages(){
        $longitude = I('longitude', 0.0, 'floatval');
        $latitude = I('latitude', 0.0, 'floatval');
        $last_time = I('last_time', 0, 'intval');
        $start = I('start', 0, 'intval');
        $limit = I('limit', 10, 'intval');

        $last_time = $last_time == 0 ? time() : $last_time;

        $mMember = new MemberModel();
        $member = $mMember->get_member(UID);

        $locations = $this->get_near_locations($longitude, $latitude);
        $mPublish = new PublishModel();
        $publishes = $mPublish->get_publishes_by_user_features($locations, $member, $last_time, $start, $limit);
        $messages = array();
        $mMessage = new MessageModel();
        foreach ($publishes as $publish) {
            $message = $mMessage->get_message_for_app($publish['message_id']);
            if(!isset($messages[$publish['publish_id']])){
                $messages[$publish['publish_id']] = array(
                    'publish_id' => $publish['publish_id'],
                    'message_id' => $publish['message_id'],
                    'title' => $message['title'],
                );
            }
            if($message && $publish['user_uid'] > 0 ){
                $params = array('status' => 1); //switch to read
                $mPublish->update_publish($publish['publish_id'], $params);
            }
        }

        $next_start = 0;
        if(count($message) >= 10){
            $next_start = $start + $limit;
        }
        $this->ajaxReturn(array('errno' => 0, 'result' => array_values($messages), 'start' => $next_start));
    }

    public function message(){
        $message_id = I('message_id', 0, 'intval');

        $mMessage = new MessageModel();
        $message = $mMessage->get_message_for_app($message_id);
        $this->ajaxReturn(array('errno' => 0, 'result' => $message));
    }

    private function get_near_locations($longitude, $latitude){
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
        return $near_locations;
    }

    public function get_distance(){
        $latitude1 = I('latitude1', 0.0, 'floatval');
        $longitude1 = I('longitude1', 0.0, 'floatval');
        $latitude2 = I('latitude2', 0.0, 'floatval');
        $longitude2 = I('longitude2', 0.0, 'floatval');

        $distance = get_distance($latitude1, $longitude1, $latitude2, $longitude2);
        $this->ajaxReturn(array('errno' => 0, 'distance' => $distance));
    }
}