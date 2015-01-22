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

class DashboardController extends AdminController {
    /**
     * 后台-Dashboard
     * @author zhangqiqian <43874051@qq.com>
     */
    public function index(){
        $this->display();
    }

    public function message(){
        $this->display();
    }

    public function location(){
        $this->display();
    }


}