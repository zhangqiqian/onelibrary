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
use Org\Net\Http;

/**
 * 前台首页控制器
 */
class DownloadController extends HomeController {
    public function index(){
        $this->display();
    }

    public function android(){
        $filename = DOWNLOAD_PATH.'onelibrary-android.apk';
        $showname = 'onelibrary-1.0-release.apk';
        Http::download($filename, $showname);
    }

    public function ios(){
        $this->display();
    }
}