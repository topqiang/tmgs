<?php

namespace Merchant\Controller;
use Think\Controller;
/**
 * Class LoginController
 * @package Merchant\Controller
 * 登录控制器
 */
class LoginController extends Controller {

    /**
     * 判断是否登录 进入登录页面
     */
    function login() {
        if(is_login()) {
            $this->redirect('Index/index');
        } else {
            //读取站点配置 先读取缓存
            $config = S('Config_Cache');
            if(!$config) {
                $config = D('Config')->parseList();
                S('Config_Cache',$config);
            }
            //添加配置到 C函数
            C($config);
            $this->display('login');
        }
    }

    /**
     * 执行登录
     */
    function doLogin() {
        $Object = D('Administrator','Logic');
        $result = $Object->login(I('post.'));
        if($result) {
            $this->success($Object->getLogicSuccess(),U('Index/index'));
        } else {
            $this->error($Object->getLogicError());
        }
    }

    /**
     * 退出登录
     */
    public function logout() {
        if(is_login()){
            session('merInfo', null);
            session('mer_sign', null);
            $this->success('退出成功！', U('Login/login'));
        } else {
            $this->redirect(U('Login/login'));
        }
    }

    /**
     * 验证码验证
     */
    public function verify() {
        $verify = new \Think\Verify();
        $verify->entry(1);
    }
}
