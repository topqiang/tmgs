<?php

namespace Manager\Logic;

/**
 * [数据统计 - 平台分成统计]
 * @author zhouwei
 * Class DataSheetLogic
 * @package Manager\Logic
 */
class DatasheetLogic extends BaseLogic
{
    /**
     * @param array $request
     * @return array
     * 获取行为列表
     */
    function getList($request = array()) {
        if(!empty($request['type'])){
            $param['where']['type'] =  $request['type'];
        }
        if(!empty($request['order_id'])){
            $param['where']['order_id'] =  array('like','%'.$request['order_id'].'%');
        }
        if(!empty($request['create_time'])){
            $param['where']['create_time'] = array(array('egt',strtotime(trim($request['create_time']))),array( 'elt',strtotime('+1day',strtotime(trim($request['create_time']))) ),'AND');
        }
        $param['where']['status']   = array('lt',9);        //状态
        $param['order']             = 'create_time DESC';   //排序
        $param['page_size']         = C('LIST_ROWS');        //页码
        $param['parameter']         = $request;             //拼接参数

        $result = D('Datasheet')->getList($param);

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
        $row = D('Datasheet')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        return $row;
    }
}