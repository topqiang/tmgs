<?php
namespace Plugins\UploadFile;
use Common\Controller\Plugin;


/**
 * Class UploadFilePlugin
 * @package Plugins\SystemInfo
 * 文件上传插件
 */
class UploadFilePlugin extends Plugin {

    /**
     * @var array
     * 插件基本信息
     */
    public $info = array (
        'name'          =>  'UploadFile',
        'title'         =>  '文件上传',
        'description'   =>  '文件上传',
        'status'        =>  1,
        'author'        =>  '黑暗中的武者',
        'version'       =>  '0.1'
    );

    public function install() {
        return true;
    }

    public function uninstall() {
        return true;
    }

    //实现的upload钩子方法
    public function upload($param) {
        //参数
        $this->assign('plugins_param', $param);
        //配置
        $this->assign('plugins_config', $this->getConfig());
        $this->display('widget');
    }
}