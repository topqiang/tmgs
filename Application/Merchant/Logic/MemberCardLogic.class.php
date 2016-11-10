<?php

namespace Merchant\Logic;

/**
 * @author zhouwei
 * Class MemberCardLogic
 * @package Merchant\Logic
 * 财务管理-银行管理-逻辑
 */
class MemberCardLogic extends BaseLogic {

    /**
     * @param array $request
     * @return mixed
     * 列表
     */
    function getList($request = array()) {
        $session = session('merInfo');
        $param['where']['type'] = 2;
        $param['where']['m_id'] = $session['mer_id'];
        $param['where']['status']   = array('lt',9);        //状态
        $param['order']             = 'create_time DESC';   //排序
        $param['page_size']         = C('LIST_ROWS');        //页码
        $param['parameter']         = $request;             //拼接参数

        $result = D('MemberCard')->getList($param);
        foreach($result['list'] as $k=>$v){
            $result['list'][$k]['bank_id'] = M('support_bank') -> where(array('id'=>$v['bank_id'])) -> getField('bank_name');
        }
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
        $row = D('MemberCard')->findRow($param);
        $row['support'] = M('support_bank') -> select();
        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        return $row;
    }


    /**
     * @param array $request
     * @return bool|mixed
     * 新增 或 修改
     */
    function update($request = array()) {
        //执行前操作
        if(!$this->beforeUpdate($request)) { return false; }
        $model = $request['model'];
        unset($request['model']);
        //获取数据对象
        $data = D($model)->create($request);
        if(!$data) {
            $this->setLogicError(D($model)->getError()); return false;
        }
        //处理数据
        $data = $this->processData($data);
        //判断增加还是修改
        if(empty($data['id'])) {
            $session = session('merInfo');
            //新增数据
            $data['m_id'] =$session['mer_id'];
            $data['type'] =2;
            $result = D($model)->data($data)->add();
            if(!$result) {
                $this->setLogicError('新增时出错！'); return false;
            }
            //行为日志
            api('Merchant/MemberCard/actionLog', array('add',$model,$result,AID));
        } else {
            //创建修改参数
            $where['id'] = $request['id'];
            $result = D($model)->where($where)->data($data)->save();
            if(!$result) {
                $this->setLogicError('您未修改任何值！'); return false;
            }
            //行为日志
            api('Merchant/ActionLog/actionLog', array('edit',$model,$data['id'],AID));
        }
        //执行后操作
        if(!$this->afterUpdate($result,$request)) { return false; }

        $this->setLogicSuccess($data['id'] ? '更新成功！' : '新增成功！'); return true;
    }

}