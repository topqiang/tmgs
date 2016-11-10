<?php

namespace Manager\Logic;

/**
 * 商家
 * Class MerchantLogic
 * @package Manager\Logic
 */
class MerchantLogic extends BaseLogic {

    /**
     * @param array $request
     * @return array
     * 获取行为列表
     */
    function getList($request = array()) {
        foreach ($request as $k => $v) {
            if(!empty($v)  && $k != 'p'){
                if ($k == 'create_time') {
                    $param['where'][$k] = array(array('egt',strtotime(trim($v))),array( 'elt',strtotime('+1day',strtotime(trim($v))) ),'AND');
                }elseif($k == 'status' || $k == 'good_shop' || $k == 'good_service'){
                    $param['where'][$k] = $v - 1;
                }else {
                    $param['where'][$k] = array('like','%'.trim($v).'%');
                }
            }
        }
//        $param['where']['status']   = array('lt',9);        //状态
        $param['order']             = 'status DESC,create_time DESC';   //排序
        $param['page_size']         = C('LIST_ROWS');        //页码
        $param['parameter']         = $request;             //拼接参数

        $result = D('Merchant')->getList($param);

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
        $row = D('Merchant')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        return $row;
    }


}