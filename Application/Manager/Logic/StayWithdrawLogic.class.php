<?php

namespace Manager\Logic;

/**
 * @author zhouwei
 * Class WithdrawLogic
 * @package Manager\Logic
 * 财务管理 - 提现管理 - 逻辑
 */
class StayWithdrawLogic extends BaseLogic {

    /**
     * @param array $request
     * @return array
     * 获取行为列表
     */
    function getList($request = array()) {
        if($_POST['type'] && !empty($request['account'])){
            if($request['type'] == 2){
                $mer = M('Merchant') -> where(array('account'=>array('like','%'.trim($request['account']).'%')))->field('id')->select();
                $param['where']['object_id']  = array('in',implode(',',array_column($mer,'id')));
                $param['where']['type'] = 2;
            }else{
                $mem = M('Member') -> where(array('account'=>array('like','%'.trim($request['account']).'%')))->field('id')->select();
                $param['where']['object_id']  = array('in',implode(',',array_column($mem,'id')));
                $param['where']['type'] = 1;
            }
        }
        if(!empty($request['create_time'])){
            $param['where']['create_time'] = array(array('egt',strtotime(trim($request['create_time']))),array( 'elt',strtotime('+1day',strtotime(trim($request['create_time']))) ),'AND');
        }

        if($_GET['type']){
            $param['where']['type'] = $_GET['type'];
        }

        if(!empty($request['id_card'])){
            $id_card = M('member_card')->where(array('id_card'=>array('like','%'.trim($request['id_card']).'%')))->field('id')->select();
            $param['where']['m_c_id'] = array('in',implode(',',array_column($id_card,'id')));
        }
        if(!empty($request['card_number'])){
            $id_card = M('member_card')->where(array('card_number'=>array('like','%'.trim($request['id_card']).'%')))->field('id')->select();
            $param['where']['m_c_id'] = array('in',implode(',',array_column($id_card,'id')));
        }
        if(!empty($request['name'])){
            $id_card = M('member_card')->where(array('name'=>array('like','%'.trim($request['name']).'%')))->field('id')->select();
            $param['where']['m_c_id'] = array('in',implode(',',array_column($id_card,'id')));
        }
//        $param['where']['status']   = array('lt',9);        //状态
        $param['where']['status']   = '0';        //状态
        $param['order']             = 'status,type DESC';   //排序
        $param['page_size']         = C('LIST_ROWS');        //页码
        $param['parameter']         = $request;             //拼接参数
        $result = D('Withdraw')->getList($param);

        foreach($result['list'] as $k=>$v){
            if($v['type'] == 1){
                // 用户
                $result['list'][$k]['object_id'] = M('Member') -> where(array('id'=>$v['object_id']))->getField('account');
            }else{
                // 商家
                $result['list'][$k]['object_id'] = M('Merchant') -> where(array('id'=>$v['object_id']))->getField('account');
            }
            $result['list'][$k]['m_c_id'] = M('member_card') -> where(array('id'=>$v['m_c_id']))->find();
            $result['list'][$k]['m_c_id']['bank_id'] = M('support_bank') -> where(array('id'=> $result['list'][$k]['m_c_id']['bank_id']))->getField('bank_name');
            $result['list'][$k]['type'] = ($v['type'] == 1 ? '用户' : '商家');
        }
        return $result;
    }

    /**
     * @param array $request
     * @return mixed
     */
    function findRow($request = array()) {}
}