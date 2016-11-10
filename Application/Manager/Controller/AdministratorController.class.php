<?php

namespace Manager\Controller;

/**
 * Class AdministratorController
 * @package Manager\Controller
 * 管理员控制器
 */
class AdministratorController extends BaseController {


    /**
     * 新增时 关联数据
     */
    protected function getAddRelation() {
        //获取管理员组列表
        $groups = D('Administrator','Logic')->getGroupList(I('request.'));
        $this->assign('groups',$groups['list']);
    }

    /**
     * 修改时 关联数据
     */
    protected function getUpdateRelation() {
        //获取管理员组列表
        $groups = D('Administrator','Logic')->getGroupList(I('request.'));
        $this->assign('groups',$groups['list']);
    }

    /**
     * 修改密码
     */
    function rePass() {
        if(!IS_POST) {
            $this->display('rePass');
        } else {
            $Object = D('Administrator', 'Logic');
            $result = $Object->rePass(I('request.'));
            if ($result) {
                $this->success($Object->getLogicSuccess(), Cookie('__forward__'));
            } else {
                $this->error($Object->getLogicError());
            }
        }
    }

}
