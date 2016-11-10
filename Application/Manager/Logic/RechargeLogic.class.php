<?php

namespace Manager\Logic;

/**
 * @author zhouwei
 * Class RechargeLogic
 * @package Manager\Logic
 * 财务管理-充值记录-逻辑
 */
class RechargeLogic extends BaseLogic {

    /**
     * @param array $request
     * @return mixed
     * 列表
     */
    function getList($request = array()) {
        if($request['order_sn']){
            $param['where']['order_sn']   = array('like','%'.trim($request['order_sn']).'%');      //订单号
        }
        if($request['m_id']){
            $mem = M('member')->where(array('account'=>array('like','%'.trim($request['m_id']).'%')))->field('id')->select();
            $param['where']['m_id']   = array('in',implode(',',array_column($mem,'id')));      //订单号
        }
        if($request['status']){
            $param['where']['status'] = $request['status'] - 1;
        }
        if($request['create_time']){
            $param['where']['create_time'] = array(array('egt',strtotime(trim($request['create_time']))),array( 'elt',strtotime('+1day',strtotime(trim($request['create_time']))) ),'AND');
        }
//        $param['where']['status']   = array('lt',9);        //状态
        $param['order']             = 'create_time DESC';   //排序
        $param['page_size']         = C('LIST_ROWS');        //页码
        $param['parameter']         = $request;             //拼接参数

        $result = D('Recharge')->getList($param);
        foreach($result['list'] as $k=>$v){
            $result['list'][$k]['m_id'] = M('Member')->where(array('id'=>$v['m_id']))->getField('account');
        }
        return $result;
    }


    function findRow($request = array()) {}
}