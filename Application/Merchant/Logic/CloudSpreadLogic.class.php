<?php

namespace Merchant\Logic;

/**
 * [云推广]
 * @author zhouwei
 * Class CloudSpreadLogic
 * @package Merchant\Logic
 */
class CloudSpreadLogic extends BaseLogic {

    /**
     * @param array $request
     * @return array
     * 获取行为列表
     */
    function getList($request = array()) {
        $session = session('merInfo');
        if(!empty($request['goods_id'])){
            $param['where']['goods_id']  = array('in',M('goods')->where(array('cn_goods_name'=>array('like','%'.trim($request['goods_id']).'%')))->getField('id',true));
        }
        $param['where']['merchant_id'] =$session['mer_id'];
        $param['where']['status']  = array('eq',1); // 状态
        $param['where']['change_number']  = array('gt',0); // 状态
        $param['where']['pay_status']  = array('eq',1); // 支付状态
        $param['order']             = 'create_time DESC';   //排序
        $param['page_size']         = C('LIST_ROWS');        //页码
        $param['parameter']         = $request;             //拼接参数
        $result = D('CloudSpread')->getList($param);
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
        $row = D('CloudSpread')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        return $row;
    }

    function getDetail($request = array())
    {
        $param['where']['cloud_spread_id'] = $request['cloud_spread_id'];
        $param['order']           = 'create_time DESC';   //排序
        $param['page_size']       = C('LIST_ROWS');        //页码
        $param['parameter']       = $request;             //拼接参数
        $result = D('CloudSpreadLog')->getList($param);
        return $result;
    }
}