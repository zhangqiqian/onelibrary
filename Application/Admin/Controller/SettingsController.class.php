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

class SettingsController extends AdminController {
    /**
     * 后台首页-Settings
     * @author zhangqiqian <43874051@qq.com>
     */
    public function user(){
        $this->display();
    }

    public function message(){
        $this->display();
    }

    public function location(){
        $this->display();
    }
}