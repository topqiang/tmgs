<?php

namespace Manager\Logic;

/**
 * Class IntegralRuleLogic
 * @package Manager\Logic
 * 积分规则
 */
class IntegralRuleLogic extends BaseLogic {


    function getList($request = array()) {}

    /**
     * @param array $request
     * @return mixed
     */
    function findRow($request = array()) {
        if(!empty($request['id'])) {
            $param['where']['id'] = $request['id'];
        } else {
            $this->setLogicError('参数错误！'); return false;
        }
        $param['where']['status'] = array('lt',9);
        $row = D('IntegralRule')->findRow($param);
        $row['pic'] = api('System/getFiles', array($row['pic']));
        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        return $row;
    }
}