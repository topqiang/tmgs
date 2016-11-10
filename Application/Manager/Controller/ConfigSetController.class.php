<?php
namespace Manager\Controller;

/**
 * Class ConfigSetController
 * @package Manager\Controller
 * 系统配置值 控制器
 */
class ConfigSetController extends BaseController {

    /**
     * 首页关联数据
     */
    function getIndexRelation() {
        $this->assign('groups',C('CONFIG_GROUP_LIST'));
        $this->assign('group_name',C('CONFIG_GROUP_LIST').$_REQUEST['config_group']);
    }
}
