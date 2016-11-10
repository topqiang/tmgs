<?php

namespace Manager\Controller;

/**
 * [商家服务列表 - 积分商城列表]
 * @author zhouwei
 * Class IntegralMallController
 * @package Manager\Controller
 */
class IntegralMallController extends BaseController {

    /**
     * 禁用操作
     */
    function forbid() {
        $this->checkRule(self::$rule);
        $Object = D(CONTROLLER_NAME,'Logic');
        $data = I('request.');
        $data['audit_time'] = time();
        $result = $Object->setStatus($data);
        if($result) {
            $this->success($Object->getLogicSuccess());
        } else {
            $this->error($Object->getLogicError());
        }
    }

}
