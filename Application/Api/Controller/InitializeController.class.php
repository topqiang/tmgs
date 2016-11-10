<?php
namespace Api\Controller;
use Think\Controller;

/**
 * Class InitializeController
 * @package Api\Controller
 * APP启动基本配置
 */
class InitializeController extends BaseController{

    public function _initialize(){
        parent::_initialize();
    }

    /**
     * APP启动页
     * 传递参数的方式：post
     * 需要传递的参数：null
     */
    public function appStart(){
        D('Initialize','Logic')->appStart();
    }
    /**
     * 下载APP
     */
    public function downloadNewVersion(){
        $file = "./Uploads/Version/TaoMi.apk";
        header("Content-type: application/vnd.android.package-archive;");
        header('Content-Disposition: attachment; filename="' . 'TaoMi.apk' . '"');
        header("Content-Length: ". filesize($file));
        readfile($file);
    }

    public function merDownload()
    {
        D(CONTROLLER_NAME,'Logic')->merDownload();
    }

    /**
     * 下载APP(商家)
     */
    public function downloadNewMerVersion(){
        $file = "./Uploads/Version/TaoMiMer.apk";
        header("Content-type: application/vnd.android.package-archive;");
        header('Content-Disposition: attachment; filename="' . 'TaoMiMer.apk' . '"');
        header("Content-Length: ". filesize($file));
        readfile($file);
    }
}