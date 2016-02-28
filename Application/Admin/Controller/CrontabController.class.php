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
use Common\Model\MemberModel;
use Common\Model\MessageModel;
use Common\Model\PublishModel;
use Common\Model\UserBookModel;
use Think\Controller;

class CrontabController extends Controller {

    public function test(){
        echo "OK\n";
    }

    public function publish_book_message(){
        $mUser = new MemberModel();
        $mUserBook = new UserBookModel();
        $mBook = new BookModel();
        $mLocation = new LocationModel();

        //获取所有的用户
        $users = $mUser->get_members();
        foreach ($users as $user) {
            echo "---- ".$user['nickname']."\n";

            //查找新的user book信息
            $user_books = $mUserBook->get_user_books($user['uid']);
            //插入新的book信息
            foreach ($user_books as $user_book) {
                $book = $mBook->get_book($user_book['book_id']);
                echo "-------- ".$book['title']."\n";

                $message = array(
                    'title' => $book['title'],
                    'content' => $book['summary'],
                    'author' => array($book['author']),
                    'category' => 0,
                    'link' => 'http://www.onelibrary.cn',
                    'pubdate' => time(),
                    'status' => 0,  //0, no handle; 1, handled.
                    'level' => 0,  //0, no level; 1...9
                    'tags' => $book['subject'],
                    'desc' => '',
                );
                //插入到message中
                $mMessage = new MessageModel();
                $message_id = $mMessage->insert_message($message);
                echo "-------- insert message: ".$message_id."\n";

                //发布新的信息
                if($message_id > 0){
                    $publish_message = array(
                        'user_uid' => $user['uid'],
                        'location_id' => 61,
                        'publish_time' => time(),
                        'expire_time' => time() + 30 * 24 * 3600,
                        'message_id' => $message_id,
                        'status' => 0,
                        'priority' => 1,
                        'similarity' => $user_book['similarity']
                    );
                    $mPublish = new PublishModel();
                    $publish_id = $mPublish->insert_publish($publish_message);
                    echo "-------- publish message: ".$publish_id."\n";
                }

                //更新user book的状态为1
                $data = array(
                    'status' => 1
                );
                $mUserBook->update_user_book($user['uid'], $book['book_id'], $data);
            }
        }
    }
}