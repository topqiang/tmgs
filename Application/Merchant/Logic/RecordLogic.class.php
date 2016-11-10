<?php

namespace Merchant\Logic;

/**
 * Class ActionLogic
 * @package Manager\Logic
 * 行为信息 逻辑层
 */
class RecordLogic extends BaseLogic {

    /**
     * @param array $request
     * @return array
     * 获取行为列表
     */
    function getList($request = array()) {
        if(empty($request['id'])){
            $this->setLogicError('参数错误！'); return false;
        }
        $param['where']['integral_mall_id'] = $request['id'];
        if(!empty($request['m_id'])){
            $model = M('Member')->where(array( 'nickname'=>array( 'like','%'.trim($request['m_id']).'%' ) ))->getField('id',true);
            if($model){
                $param['where']['m_id'] = array('in',$model);
            }else{
                $param['where']['m_id'] = array('in','');
            }
        }
        $param['where']['status']   = array('lt',9);        //状态
        $param['order']             = 'create_time DESC';   //排序
        $param['page_size']         = C('LIST_ROWS');        //页码
        $param['parameter']         = $request;             //拼接参数

        $result = D('IntegralMallLog')->getList($param);

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
        $row = D('IntegralMallLog')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        return $row;
    }
}