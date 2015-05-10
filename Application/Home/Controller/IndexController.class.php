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
        $this->ajaxReturn(array('errno' => 0, 'result' => $messages));
    }

    public function message_detail(){
        $message_id = I('message_id', 0, 'intval');

        $mMessage = new MessageModel();
        $message = $mMessage->get_message($message_id);
        $categories = C('MESSAGE_CATEGORIES');
        $message['category'] = $categories[$message['category']];

        $this->assign('message', $message);
        $this->display('Index/message_detail');
    }
}