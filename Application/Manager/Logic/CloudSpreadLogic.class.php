<?php

namespace Manager\Logic;

/**
 * [云推广 - 逻辑]
 * Class CloudSpreadLogic
 * @package Manager\Logic
 */
class CloudSpreadLogic extends BaseLogic {

    /**
     * @param array $request
     * @return array
     * 获取行为列表
     */
    function getList($request = array()) {
        if(!empty($request['goods_id'])){
            $model = M('goods')->where(array('cn_goods_name'=>array('like','%'.trim($request['goods_id']).'%')))->getField('id',true);
            $param['where']['goods_id']  = array('in',$model ? $model : '');
            unset($model);
        }
        if(!empty($request['order_sn'])){
            $param['where']['order_sn'] = array('like','%'.trim($request['order_sn']).'%');
        }
        if(!empty($request['merchant_id'])){
            $model = M('Merchant')->where(array('merchant_name'=>array('like','%'.trim($request['merchant_id']).'%')))->getField('id',true);
            $param['where']['merchant_id']  = array('in',$model ? $model : '');
            unset($model);
        }
        $param['where']['status']   = array('lt',9);        //状态
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