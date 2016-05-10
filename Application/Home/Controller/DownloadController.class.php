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

        $version_file = DOWNLOAD_PATH.'version.json';
        $content = file_get_contents($version_file);
        $version_info = json_decode($content, true);
        $version = floatval($version_info['version']);
        $showname = 'onelibrary-'.$version.'-release.apk';
        Http::download($filename, $showname);
    }

    public function ios(){
        $this->display();
    }
}