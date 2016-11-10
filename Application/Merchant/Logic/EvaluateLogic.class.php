<?php

namespace Merchant\Logic;

/**
 * @author zhouwei
 * Class EvaluateLogLogic
 * @package Merchant\Logic
 * 订单管理 - 评价列表 - 逻辑
 */
class EvaluateLogic extends BaseLogic{

    /**
     * @param array $request
     * @return array
     * 获取日志列表
     */
    function getList($request = array()) {
        $session = session('merInfo'); // mer_id
        if(!empty($request['order_sn'])) {
            //按管理员账号查询
            $param['where']['order_sn'] = array('like','%'.trim($request['order_sn']).'%');
        }
        if(!empty($request['evaluate_time'])){
            $param['where']['evaluate_time'] = array(array('egt',strtotime(trim($request['evaluate_time']))),array( 'elt',strtotime('+1day',strtotime(trim($request['evaluate_time']))) ),'AND');
        }
        if(!empty($request['rank'])){
            $param['where']['rank'] = $request['rank'];
        }
        if(!empty($request['m_id'])){
            $model  = M('Member') -> where(array('nickname'=>array('like','%'.trim($request['m_id']).'%')))->field('id')->select();
            $param['where']['m_id'] = array('in',implode(',',array_column($model,'id')));
            unset($model);
        }

        if(!empty($request['g_id'])){
            $model  = M('goods') -> where(array('cn_goods_name'=>array('like','%'.trim($request['g_id']).'%')))->field('id')->select();
            $param['where']['g_id'] = array('in',implode(',',array_column($model,'id')));
        }
        $param['where']['merchant_id'] = $session['mer_id'];
        $param['where']['status'] = array('lt',9);//状态
        $param['order'] = 'evaluate_time DESC';//排序
        $param['page_size'] = C('LIST_ROWS'); //页码
        $param['parameter'] = $request; //拼接参数

        $result = D('Evaluate')->getList($param);
        return $result;
    }

    /**
     * @param $request
     * @return mixed
     */
    function findRow($request = array()) {
        if(!empty($request['id'])) {
            $param['where']['id'] = $request['id'];
        } else {
            $this->setLogicError('参数错误！'); return false;
        }

        $param['where']['status'] = array('lt',9);
        $row = D('Evaluate')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        return $row;
    }
}