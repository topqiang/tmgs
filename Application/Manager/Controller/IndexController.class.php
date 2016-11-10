<?php
namespace Manager\Controller;

/**
 * Class IndexController
 * @package Manager\Controller
 * 首页控制器
 */
class IndexController extends BaseController {


    public function index() {
        //$r = api('System/sendMsg',array('1207275734@qq.com', '', array('vc'=>123456),AID,'你好',2,'你好'));
        //$r = api('System/sendMsg',array('1207275734@qq.com', 'register', array('vc'=>123456)));
        //var_dump($r);
        $this->display('index');
    }
}
