<?php

namespace Manager\Logic;

/**
 * Class LotteryBackgroundLogic
 * @package Manager\Logic
 * 行为信息 逻辑层
 */
class LotteryBackgroundLogic extends BaseLogic {

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
        if(!empty($request['id'])) {
            $param['where']['id'] = $request['id'];
        } else {
            $this->setLogicError('参数错误！'); return false;
        }
        $param['where']['status'] = array('lt',9);
        $row = D('LotteryBackground')->findRow($param);
        $row['carousel_pic'] = api('System/getFiles',array($row['carousel_pic']));
        $row['redbag_pic'] = api('System/getFiles',array($row['redbag_pic']));
        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        return $row;
    }
}