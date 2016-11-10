<?php

namespace Manager\Logic;

/**
 * @author zhouwei
 * Class OrderLogic
 * @package Merchant\Logic
 * 订单管理 - 订单列表 - 逻辑
 */
class OrderLogic extends BaseLogic {

    /**
     * @param array $request
     * @return array
     * 获取行为列表
     */
    function getList($request = array()) {
        foreach ($request as $k => $v) {
            if(!empty($v)  && $k != 'p' && $k !='status'){
                if ($k == 'submit_order_time') {
                    $param['where'][$k] = array(array('egt',strtotime(trim($v))),array( 'elt',strtotime('+1day',strtotime(trim($v))) ),'AND');
                } else {
                    $param['where'][$k] = array('like','%'.trim($v).'%');
                }
            }
        }
        $session = session('merInfo');

        if(!empty($request['status'])){
            $param['where']['status']   = array(array('eq',$request['status'] - 1),array('lt',9),'AND');        //状态
        }else{
            $param['where']['status']   = array('lt',9);      //状态
        }
        $param['order']             = 'status,submit_order_time DESC';   //排序
        $param['page_size']         = C('LIST_ROWS');        //页码
        $param['parameter']         = $request;             //拼接参数
        $result = D('Order')->getList($param);

        return $result;
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
        $row = D('Order')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        return $row;
    }
}