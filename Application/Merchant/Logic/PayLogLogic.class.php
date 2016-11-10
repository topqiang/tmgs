<?php

namespace Merchant\Logic;

/**
 * [账单明细]
 * @author zhouwei
 * Class PayLogLogic
 * @package Merchant\Logic
 */
class PayLogLogic extends BaseLogic {

    /**
     * @param array $request
     * @return array
     * 获取行为列表
     */
    function getList($request = array()) {
        $session = session('merInfo');
        if(!empty($request['title'])){
            $param['where']['title'] = array('like','%'.trim($request['title']).'%');
        }
        if(!empty($request['content'])){
            $param['where']['content'] = array('like','%'.trim($request['content']).'%');
        }
        $param['where']['status']   = array('lt',9);        //状态
        $param['where']['object_id'] = $session['mer_id'];
        $param['where']['type'] = 2;
        $param['order']             = 'create_time DESC';   //排序
        $param['page_size']         = C('LIST_ROWS');        //页码
        $param['parameter']         = $request;             //拼接参数

        $result = D('PayLog')->getList($param);
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
        $row = D('PayLog')->findRow($param);
        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        return $row;
    }
}