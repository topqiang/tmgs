<?php
namespace Manager\Controller;

/**
 * Class SendLogController
 * @package Manager\Controller
 * 发信记录控制器
 */
class SendLogController extends BaseController {

    /**
     * 详情
     */
    public function detail() {
        $this->checkRule(self::$rule);
        $Object = D('SendLog','Logic');
        $row = $Object->findRow(I('get.'));
        if($row) {
            $this->assign('row', $row);
            $this->display('detail');
        } else {
            $this->error($Object->getLogicError());
        }
    }
}
