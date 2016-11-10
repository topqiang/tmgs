<?php

namespace Manager\Logic;

/**
 * [诚信商家]
 * @author zhouwei
 * Class SecurityLogic
 * @package Manager\Logic
 */
class SecurityLogic extends BaseLogic {

    /**
     * @param array $request
     * @return array
     * 获取行为列表
     */
    function getList($request = array()) {
        if(!empty($request['order_security_sn'])){
            $param['where']['order_security_sn'] = array('like','%'.trim($request['order_security_sn']).'%');
        }
        if(!empty($request['merchant_id'])){
            $param['where']['merchant_id'] = array('in',M('merchant')->where(array('merchant_name'=>array('like','%'.trim($request['merchant_id']).'%')))->getField('id',true));
        }
        $param['where']['status']   = array('eq',1);        //状态
        $param['order']             = 'create_time DESC';   //排序
        $param['page_size']         = C('LIST_ROWS');        //页码
        $param['parameter']         = $request;             //拼接参数

        $result = D('Security')->getList($param);
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
        $row = D('Security')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        return $row;
    }
}