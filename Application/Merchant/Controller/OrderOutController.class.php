<?php

namespace Merchant\Controller;
/**
 * 退货管理
 * @author zhouwei
 * Class OrderOutController
 * @package Merchant\Controller
 */
class OrderOutController extends BaseController
{
    // 修改步奏
    function changeStep() {
        $this->checkRule(self::$rule);
        $Object = D(CONTROLLER_NAME,'Logic');
        $result = $Object->changeStepStatus(I('request.'));
        if($result) {
            $this->success($Object->getLogicSuccess());
        } else {
            $this->error($Object->getLogicError());
        }
    }
}