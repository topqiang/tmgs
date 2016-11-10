<?php

namespace Merchant\Logic;

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
        if(!empty($request['order_sn'])){
            $param['where']['order_sn'] = array('like','%'.$request['order_sn'].'%');
        }
        if(!empty($request['status'])){
            $param['where']['status']   = array(array('eq',$request['status']),array('NEQ',0),'AND');        //状态
        }else{
            $param['where']['status']   = array('NEQ','0');        //状态
        }
        if(!empty($request['submit_order_time'])) {
            $param['where']['submit_order_time'] = array(array('egt',strtotime(trim($request['submit_order_time']))),array( 'elt',strtotime('+1day',strtotime(trim($request['submit_order_time']))) ),'AND');
        }
        $session = session('merInfo');
        $param['where']['merchant_id'] =$session['mer_id'];
        // $param['where']['pay_type']   = array('neq','0');        //状态
        $param['order']             = 'status,submit_order_time DESC';   //排序
        $param['page_size']         = C('LIST_ROWS');        //页码
        $param['parameter']         = $request;             //拼接参数

        $result = D('Order')->getList($param);

        return $result;
    }

    /**
     * @param array $request
     * @return array
     * 已完成订单
     */
    function getAlreadyList($request = array()) {
        if(!empty($request['order_sn'])){
            $param['where']['order_sn'] = $request['order_sn'];
        }
        $param['where']['status'] = array('NOTIN', '0,1,2,3,4');
        if(!empty($request['submit_order_time'])) {
            $param['where']['submit_order_time'] = array(array('egt',strtotime(trim($request['submit_order_time']))),array( 'elt',strtotime('+1day',strtotime(trim($request['submit_order_time']))) ),'AND');
        }
        $session = session('merInfo');
        $param['where']['merchant_id'] =$session['mer_id'];
        // $param['where']['pay_type']   = array('neq','0');        //状态
        $param['order']             = 'status,submit_order_time DESC';   //排序
        $param['page_size']         = C('LIST_ROWS');        //页码
        $param['parameter']         = $request;             //拼接参数

        $result = D('Order')->getList($param);

        return $result;
    }
    /**
     * @param array $request
     * @return array
     * 获取行为列表
     */
    function getNotList($request = array()) {
        if(!empty($request['order_sn'])){
            $param['where']['order_sn'] = $request['order_sn'];
        }
        $param['where']['status'] = array('NOTIN','0,6,5');
        if(!empty($request['submit_order_time'])) {
            $param['where']['submit_order_time'] = array(array('egt',strtotime(trim($request['submit_order_time']))),array( 'elt',strtotime('+1day',strtotime(trim($request['submit_order_time']))) ),'AND');
        }
        $session = session('merInfo');
        $param['where']['merchant_id'] =$session['mer_id'];
        // $param['where']['pay_type']   = array('neq','0');        //状态
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