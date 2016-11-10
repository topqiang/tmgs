<?php

namespace Merchant\Logic;

/**
 * @author zhouwei
 * Class WithdrawLogic
 * @package Merchant\Logic
 * 财务管理-提现管理-逻辑
 */
class WithdrawLogic extends BaseLogic
{

    /**
     * @param array $request
     * @return array
     * 获取行为列表
     */
    function getList($request = array())
    {
        if(!empty($request['create_time'])) {
            $param['where']['create_time'] = array(array('egt',strtotime(trim($request['create_time']))),array( 'elt',strtotime('+1day',strtotime(trim($request['create_time']))) ),'AND');
        }
        if(!empty($request['money'])){
            $param['where']['money'] = array('like','%'.$request['money'].'%');
        }
        if(!empty($request['name']) || !empty($request['card_number'])){
            if(!empty($request['name'])) $where['name'] = array('like','%'.trim($request['name']).'%') ;
            if(!empty($request['card_number']))$where['card_number'] = array('like','%'.trim($request['card_number']).'%');
            $model = M('member_card') ->where($where) -> field('id') -> select();
            $param['where']['m_c_id'] = array('in',implode(',',array_column($model,'id')));
            unset($model);
            unset($where);
        }
        $session = session('merInfo');
        $param['where']['type'] = 2;
        $param['where']['object_id'] = $session['mer_id'];
        $param['where']['status'] = array('lt', 9);        //状态
        $param['order'] = 'status,create_time DESC';   //排序
        $param['page_size'] = C('LIST_ROWS');        //页码
        $param['parameter'] = $request;             //拼接参数

        $result = D('Withdraw')->getList($param);
        return $result;
    }

    /**
     * @param array $request
     * @return mixed
     */
    function findRow($request = array())
    {
        if (!empty($request['id'])) {
            $param['where']['id'] = $request['id'];
        } else {
            $this->setLogicError('参数错误！');
            return false;
        }
        $param['where']['status'] = array('lt', 9);
        $row = D('Withdraw')->findRow($param);

        if (!$row) {
            $this->setLogicError('未查到此记录！');
            return false;
        }
        return $row;
    }

    /**
     * @param array $request
     * @return bool|mixed
     * 新增 或 修改
     */
    function update($request = array())
    {
        //执行前操作
        if (!$this->beforeUpdate($request)) {
            return false;
        }
        $model = $request['model'];
        unset($request['model']);
        //获取数据对象
        $data = D($model)->create($request);
        if (!$data) {
            $this->setLogicError(D($model)->getError());
            return false;
        }
        //处理数据
        $data = $this->processData($data);
        $session = session('merInfo'); //mer_id
        //判断增加还是修改
        if (empty($data['id'])) {
            //新增数据

            if (0 == $data['money']) {
                $this->setLogicError('请不要输入0元提现');
                return false;
            }
            if (!empty($data['money'])) {
                $judgeBalance = M('Merchant')->where(array('id' => $session['mer_id']))->getField('balance');
                if ($data['money'] > $judgeBalance) {
                    $this->setLogicError('当前体现的金额大于您的余额');
                    return false;
                }
            }
            $data['object_id'] = $session['mer_id'];
            $data['type'] = 2;
            $result = D($model)->data($data)->add();
            if ($result) {
                $bank_id = M('member_card')->where(array('id' => $data['m_c_id']))->getField('bank_id');
                $bank_id = M('support_bank')->where(array('id' => $bank_id))->getField('bank_name');
                M('merchant')->where(array('id' => $session['mer_id']))->setDec('balance', $data['money']);
                M('pay_log')->data( array( 'type' => 2,'object_id' => $session['mer_id'],'title' => '提现','content' => $bank_id,'symbol' => 0,'money' =>$data['money'],
                    'create_time'   =>time() ) )->add();
            }
            if (!$result) {
                $this->setLogicError('新增时出错！');
                return false;
            }
            //行为日志
            api('Merchant/ActionLog/actionLog', array('add', $model, $result, AID));
        } else {
            //创建修改参数
            $where['id'] = $request['id'];
            $result = D($model)->where($where)->data($data)->save();
            if (!$result) {
                $this->setLogicError('您未修改任何值！');
                return false;
            }
            //行为日志
            api('Merchant/ActionLog/actionLog', array('edit', $model, $data['id'], AID));
        }
        //执行后操作
        if (!$this->afterUpdate($result, $request)) {
            return false;
        }

        $this->setLogicSuccess($data['id'] ? '更新成功！' : '新增成功！');
        return true;
    }

}