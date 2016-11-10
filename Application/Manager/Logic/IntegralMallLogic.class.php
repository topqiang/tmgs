<?php

namespace Manager\Logic;

/**
 * [商家服务列表 - 积分商城列表]
 * @author zhouwei
 * Class IntegralMallLogic
 * @package Manager\Logic
 */
class IntegralMallLogic extends BaseLogic {

    /**
     * @param array $request
     * @return array
     * 获取行为列表
     */
    function getList($request = array()) {
        if(!empty($request['goods_name'])){
            $param['where']['goods_name'] = array('like','%'.trim($request['goods_name']).'%');
        }
        $param['where']['status']   = array('lt',9);        //状态
        $param['order']             = 'status,create_time DESC';   //排序
        $param['page_size']         = C('LIST_ROWS');        //页码
        $param['parameter']         = $request;             //拼接参数

        $result = D('IntegralMall')->getList($param);

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
        $row = D('IntegralMall')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        return $row;
    }

}