<?php
namespace Merchant\Controller;

/**
 * Class PluginsController
 * @package Merchant\Controller
 * 插件控制器
 */
class PluginsController extends BaseController {

    /**
     * 插件安装
     */
    function install() {
        $this->checkRule(self::$rule);
        $Object = D('Plugins','Logic');
        $result = $Object->install(I('request.'));
        if($result) {
            $this->success($Object->getLogicSuccess());
        } else {
            $this->error($Object->getLogicError());
        }
    }

    /**
     * 卸载插件
     */
    function uninstall() {
        $this->checkRule(self::$rule);
        $Object = D('Plugins','Logic');
        $result = $Object->uninstall(I('request.'));
        if($result) {
            $this->success($Object->getLogicSuccess());
        } else {
            $this->error($Object->getLogicError());
        }
    }

    /**
     * 进入配置页面
     */
    function config() {
        $this->checkRule(self::$rule);
        $Object = D('Plugins','Logic');
        $result = $Object->config(I('request.'));
        if($result) {
            $this->assign('data',$result);
            $this->display('config');
        } else {
            $this->error($Object->getLogicError());
        }
    }

    /**
     * @param null $plugins
     * @param null $controller
     * @param null $action
     * @return bool
     * 用于调度各个扩展的URL访问需求
     */
    function execute($plugins = null, $controller = null, $action = null) {
        $Object = D('Plugins','Logic');
        $result = $Object->execute($plugins, $controller, $action);
        if(!$result) {
            $this->error($Object->getLogicError());
        }
    }
}
