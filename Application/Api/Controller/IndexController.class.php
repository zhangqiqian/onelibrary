<?php
// +----------------------------------------------------------------------
// | OneLibrary [ DO IT NOW! ]
// +----------------------------------------------------------------------
// | Copyright (c) 2014 niko All rights reserved.
// +----------------------------------------------------------------------
// | Author: zhangqiqian <43874051@qq.com>
// +----------------------------------------------------------------------

namespace Api\Controller;
use Common\Model\CurriculaModel;
use Common\Model\LocationModel;
use Common\Model\MemberModel;
use Common\Model\PublishModel;
use Common\Model\MessageModel;

use Common\Model\UserLocationLogModel;
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
        $priority = I('priority', 1, 'intval');
        $notification = I('notification', 1, 'intval');
        $start = I('start', 0, 'intval');
        $limit = I('limit', 10, 'intval');

        $mMember = new MemberModel();
        $member = $mMember->get_member(UID);

        $locations = $this->get_near_locations($longitude, $latitude);
        $mPublish = new PublishModel();
        $publishes = $mPublish->get_publishes_by_user_features($locations, $member, $priority, $notification, $start, $limit);
        $messages = array();
        $mMessage = new MessageModel();
        $publish_ids = array();
        $message_ids = array();
        foreach ($publishes as $publish) {
            $message = $mMessage->get_message_for_app($publish['message_id']);
            if(!isset($messages[$publish['publish_id']])){
                $messages[$publish['publish_id']] = array(
                    'publish_id' => $publish['publish_id'],
                    'message_id' => $publish['message_id'],
                    'title' => $message['title'],
                );
                $publish_ids[] = $publish['publish_id'];
                $message_ids[] = $publish['message_id'];
            }
            if($message && $publish['user_uid'] > 0 ){
                $params = array('status' => 1); //switch to read
                $mPublish->update_publish($publish['publish_id'], $params);
            }
        }

        if(!empty($locations) && !empty($publishes)){
            //save user location to log
            $mUserLocation = new UserLocationLogModel();
            $location_ids = array();
            foreach ($locations as $location) {
                $location_ids[] = $location['location_id'];
            }

            $log = array(
                'uid' => UID,
                'longitude' => $longitude,
                'latitude' => $latitude,
                'location_ids' => $location_ids,
                'publish_ids' => array_unique($publish_ids),
                'message_ids' => array_unique($message_ids),
            );
            $mUserLocation->insert_user_location_log($log);
        }

        $next_start = 0;
        if(count($messages) >= $limit){
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

    public function get_curricula_list(){
        $major = I('major', 0, 'intval');

        $mCurricula = new CurriculaModel();
        $curriculas_ret = $mCurricula->get_curriculas_by_info($major);
        $curriculas = array();
        foreach ($curriculas_ret as $curricula) {
            $curriculas[$curricula['curricula_id']] = $curricula['name'];
        }
        $this->ajaxReturn(array('errno' => 0, 'result' => $curriculas));
    }

    public function get_member_options(){
        $this->ajaxReturn(
            array(
                'errno' => 0,
                'result' => array(
                    'grades' => C('USER_GRADES'),
                    'majors' => C('MAJOR_MAPPING'),
                )
            )
        );
    }

    public function get_update_info(){
        $filename = DOWNLOAD_PATH.'version.txt';
        $content = file_get_contents($filename);
        $result = json_decode($content, true);
        $this->ajaxReturn(array('errno' => 0, 'result' => $result));
    }
}