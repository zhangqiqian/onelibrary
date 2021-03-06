<?php
// +----------------------------------------------------------------------
// | OneLibrary [ DO IT NOW! ]
// +----------------------------------------------------------------------
// | Copyright (c) 2014 niko All rights reserved.
// +----------------------------------------------------------------------
// | Author: zhangqiqian <43874051@qq.com>
// +----------------------------------------------------------------------

namespace Home\Controller;
use Think\Controller;
use Common\Model\MessageModel;

/**
 * 前台首页控制器
 */
class IndexController extends HomeController {
    public function index(){
        $this->display();
    }

    public function messages_by_category(){
        $category_id = I('category_id', 0, 'intval');

        $mMessage = new MessageModel();
        $messages = $mMessage->get_messages_by_category($category_id);
        /*foreach ($messages as $i => $message) {
            if(strlen($message['title']) > 69){
                $messages[$i]['title'] = substr($message['title'], 0, 66)."...";
            }
        }*/
        $this->ajaxReturn(array('errno' => 0, 'result' => $messages));
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
        $this->display('Index/message_detail');
    }
}