<?php
// +----------------------------------------------------------------------
// | OneLibrary [ DO IT NOW! ]
// +----------------------------------------------------------------------
// | Copyright (c) 2014 niko All rights reserved.
// +----------------------------------------------------------------------
// | Author: zhangqiqian <43874051@qq.com>
// +----------------------------------------------------------------------

namespace Api\Controller;
use Think\Controller;

class IndexController extends ApiController {
    /**
     * 后台首页
     * @author zhangqiqian <43874051@qq.com>
     */
    public function index(){
        if(UID){
            $this->ajaxReturn(array('errno' => 0, 'errmsg' => 'Welcome to index.'));
        } else {
            $this->ajaxReturn(array('errno' => 1, 'errmsg' => 'Please sign in.'));
        }
    }
}