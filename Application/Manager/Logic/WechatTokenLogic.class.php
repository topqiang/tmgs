<?php

namespace Manager\Logic;

/**
 * Class ActionLogic
 * @package Manager\Logic
 * 接口管理
 */
class WechatTokenLogic extends BaseLogic {

    /**
     * @param array $request
     * @return array
     * 获取行为列表
     */
    function getList($request = array()) {
    }

    /**
     * @param array $request
     * @return mixed
     */
    function findRow($request = array()) {
        $param['where']['id'] = 1;
        $param['where']['status'] = array('lt',9);
        $row = D('WechatToken')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        return $row;
    }
}