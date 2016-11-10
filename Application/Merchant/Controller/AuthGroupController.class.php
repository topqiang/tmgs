<?php

namespace Merchant\Controller;

/**
 * Class AuthGroupController
 * @package Manager\Controller
 * 管理员分组 权限控制器
 */
class AuthGroupController extends BaseController {

    /**
     * 访问授权列表
     */
    function access() {
        $this->checkRule(self::$rule);
        $list = D('AuthGroup','Logic')->getAccess(I('get.'));
        $this->assign('list',$list['all_rules']);
        $this->assign('rules',$list['have_rules']);
        $this->display('access');
    }
}
