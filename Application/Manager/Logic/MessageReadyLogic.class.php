<?php

namespace Manager\Logic;

/**
 * Class MessageReadyLogic
 * @package Manager\Logic
 * 消息准备
 */
class MessageReadyLogic extends BaseLogic {

    /**
     * @param array $request
     * @return array
     * 获取行为列表
     */
    function getList($request = array()) {
        if(!empty($request['account'])){
            $param['where']['account'] = array('like','%'.$request['account'].'%');
        }
        $param['where']['status']   = array('neq',9);        //状态
        $param['order']             = 'create_time DESC';   //排序
        $param['page_size']         = C('LIST_ROWS');        //页码
        $param['parameter']         = $request;             //拼接参数

        $result = D('MessageReady')->getList($param);

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
        $row = D('MessageReady')->findRow($param);
        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        return $row;
    }

    /**
     * @param array $data
     * @return array
     */
    public function processData($data){
        $data['ue_content'] = $_POST['ue_content'];
        $data['cn_content'] = $_POST['cn_content'];

        return $data;
    }
}