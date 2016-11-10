<?php
namespace Merchant\Controller;

/**
 * Class ConfigController
 * @package Manager\Controller
 * 系统配置 控制器
 */
class ConfigController extends BaseController {


    /**
     * 首页关联数据
     */
    function getIndexRelation() {
        $this->assign('groups',C('CONFIG_GROUP_LIST'));
    }

    /**
     * 修改时关联数据
     */
    function getUpdateRelation() {
        $this->assign('types',C('CONFIG_TYPE_LIST'));
        $this->assign('groups',C('CONFIG_GROUP_LIST'));
    }

    /**
     * 新添时关联数据
     */
    function getAddRelation() {
        $this->assign('types',C('CONFIG_TYPE_LIST'));
        $this->assign('groups',C('CONFIG_GROUP_LIST'));
    }

}
