<?php

namespace Merchant\Logic;

/**
 * [广告办理]
 * @author zhouwei
 * Class AdPositionLogic
 * @package Merchant\Logic
 */
class AdPositionLogic extends BaseLogic
{


    function getList($request = array())
    {
        if(!empty($request['order_sn'])){
            $param['where']['order_sn'] = array('like','%'.trim($request['order_sn']).'%');
        }
        $session = session('merInfo');
        $param['where']['merchant_id'] = $session['mer_id'];
        $param['where']['pay_status'] = array('eq',1);
        $param['where']['status'] = array('lt', 9);        //状态
        $param['page_size'] = C('LIST_ROWS');        //页码
        $param['parameter'] = $request;             //拼接参数
        $result = D('AdPositionTotal')->getList($param);

        return $result;
    }

    function findRow($request = array())
    {
        if (!empty($request['id'])) {
            $param['where']['id'] = $request['id'];
        } else {
            $this->setLogicError('参数错误！');
            return false;
        }
        $param['where']['status'] = array('lt', 9);
        $row = D('AdPositionTotal')->findRow($param);

        if (!$row) {
            $this->setLogicError('未查到此记录！');
            return false;
        }
        return $row;
    }
}