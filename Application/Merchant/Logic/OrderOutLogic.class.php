<?php

namespace Merchant\Logic;

/**
 * 退货管理
 * @author zhouwei
 * Class ActionLogic
 * @package Merchant\Logic
 */
class OrderOutLogic extends BaseLogic {


    function getList($request = array()) {
        if(!empty($request['id'])){
            $param['where']['id'] = array('like','%'.trim($request['id']).'%');
        }
        if(!empty($request['order_id'])){
            $model = M('order') -> where(array('order_sn'=>array('like','%'.trim($request['order_id']).'%')))->field('id')->select();
            $param['where']['order_id'] = array('in',implode(',',array_column($model,'id')));
            unset($model);
        }
        if(!empty($request['nickname'])){
            $model = M('Member') -> where(array('nickname'=>array('like','%'.trim($request['nickname']).'%')))->field('id')->select();
            $param['where']['m_id'] = array('in',implode(',',array_column($model,'id')));
            unset($model);
        }
        if(!empty($request['account'])){
            $model = M('Member') -> where(array('account'=>array('like','%'.trim($request['account']).'%')))->field('id')->select();
            $param['where']['m_id'] = array('in',implode(',',array_column($model,'id')));
            unset($model);
        }
        if(!empty($request['merchant_name'])){
            $model = M('Merchant') -> where(array('merchant_name'=>array('like','%'.trim($request['merchant_name']).'%')))->field('id')->select();
            $param['where']['merchant_id'] = array('in',implode(',',array_column($model,'id')));
            unset($model);
        }
        if(!empty($request['g_id'])){
            $model = M('Goods') -> where(array('cn_goods_name'=>array('like','%'.trim($request['g_id']).'%')))->field('id')->select();
            $param['where']['g_id'] = array('in',implode(',',array_column($model,'id')));
            unset($model);
        }
        if(!empty($request['type'])){
            $param['where']['step'] = $request['type'];
        }
        $session = session('merInfo');
        $param['where']['merchant_id'] =$session['mer_id'];
        $param['where']['status']   = array('lt',9);        //状态
        $param['order']             = 'create_time DESC';   //排序
        $param['page_size']         = C('LIST_ROWS');        //页码
        $param['parameter']         = $request;             //拼接参数

        $result = D('OrderOut')->getList($param);

        return $result;
    }


    function findRow($request = array()) {
        if(!empty($request['id'])) {
            $param['where']['id'] = $request['id'];
        } else {
            $this->setLogicError('参数错误！'); return false;
        }
        $param['where']['status'] = array('lt',9);
        $row = D('OrderOut')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        return $row;
    }

    /**
     * @param array $request  model 模型  ids操作的主键ID  status要改为的状态
     * @return bool
     * 修改状态
     */
    function changeStepStatus($request = array()) {
        //判断参数
        if(empty($request['model']) || empty($request['ids']) || !isset($request['step'])) {
            $this->setLogicError('参数错误！'); return false;
        }
        //执行前操作
        if(!$this->beforeSetStatus($request)) { return false; }
        //判断是数组ID还是字符ID
        if(is_array($request['ids'])) {
            //数组ID
            $where['id'] = array('in',$request['ids']);
            $ids = implode(',',$request['ids']);
        } elseif (is_numeric($request['ids'])) {
            //数字ID
            $where['id'] = $request['ids'];
            $ids = $request['ids'];
        }

        $data = array(
            'step'        => $request['step'],
            'update_time'   => time()
        );

        $result = D($request['model'])->where($where)->data($data)->save();

        if($result) {
            //行为日志
            api('Manager/ActionLog/actionLog', array('change_status',$request['model'],$ids,AID));
            //执行后操作
            if(!$this->afterSetStatus($result,$request)) { return false; }
            $this->setLogicSuccess('操作成功！'); return true;
        } else {
            $this->setLogicError('操作失败！'); return false;
        }
    }

}