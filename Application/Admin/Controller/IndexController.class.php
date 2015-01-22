<?php
// +----------------------------------------------------------------------
// | OneLibrary [ DO IT NOW! ]
// +----------------------------------------------------------------------
// | Copyright (c) 2014 niko All rights reserved.
// +----------------------------------------------------------------------
// | Author: zhangqiqian <43874051@qq.com>
// +----------------------------------------------------------------------

namespace Admin\Controller;
use Think\Controller;

class IndexController extends AdminController {
    /**
     * 后台首页
     * @author zhangqiqian <43874051@qq.com>
     */
    public function index(){
        $this->display();
    }
}