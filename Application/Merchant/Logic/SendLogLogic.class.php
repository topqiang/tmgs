<?php

namespace Manager\Logic;

/**
 * Class SendLogLogic
 * @package Manager\Logic
 * 发信记录逻辑层
 */
class SendLogLogic extends BaseLogic {

    /**
     * @param array $request
     * @return array
     */
    function getList($request = array()) {
        $param['where']['status']   = array('lt',9);        //状态
        $param['order']             = 'create_time DESC';   //排序
        $param['page_size']         = C('LIST_ROWS');       //页码
        $param['parameter']         = $request;             //拼接参数

        $result = D('SendLogView')->getList($param);

        return $result;
    }

    /**
     * @param array $request
     * @return bool
     */
    function findRow($request = array()) {
        if(!empty($request['id'])) {
            $param['where']['id'] = $request['id'];
        } else {
            $this->setLogicError('参数错误！'); return false;
        }
        $param['where']['status'] = array('lt',9);
        $row = D('SendLogView')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        return $row;
    }
}