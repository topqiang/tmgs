<?php

namespace Manager\Logic;

/**
 * Class SendTemplateLogic
 * @package Manager\Logic
 * 发信模板 逻辑层
 */
class SendTemplateLogic extends BaseLogic{

    /**
     * @param array $request
     * @return array
     */
    function getList($request = array()) {
        $param['where']['status']   = array('lt',9);        //状态
        $param['order']             = 'create_time DESC';   //排序
        $param['page_size']         = C('LIST_ROWS');        //页码
        $param['parameter']         = $request;             //拼接参数

        $result = D('SendTemplate')->getList($param);

        return $result;
    }

    /**
     * @param array $data
     * @return array
     * 模板内容 根据类型判断是否过滤
     */
    protected function processData($data = array()) {
        $data['template'] = $data['type'] == 1 ? filter_html($_POST['template']) : $_POST['template'];
        return $data;
    }

    /**
     * @param array $request
     * @return bool
     */
    function findRow($request = array()) {
        if(!empty($request['id'])) {
            $param['where']['id'] = $request['id'];
        } else {
            $this->setLogicError('参数错误！'); return false;
        }
        $param['where']['status'] = array('lt',9);
        $row = D('SendTemplate')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        return $row;
    }
}