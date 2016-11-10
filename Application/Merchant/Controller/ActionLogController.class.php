<?php

namespace Merchant\Controller;

/**
 * Class ActionLogController
 * @package Merchant\Controller
 * 行为日志控制器
 */
class ActionLogController extends BaseController {

    /**
     * 详情
     */
    public function detail() {
        $this->checkRule(self::$rule);
        $Object = D('ActionLog','Logic');
        $row = $Object->findRow(I('get.'));
        if($row) {
            $this->assign('row', $row);
            $this->display('detail');
        } else {
            $this->error($Object->getLogicError());
        }
    }
}
