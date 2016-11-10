<?php

namespace Merchant\Logic;

/**
 * Class ActionLogLogic
 * @package Manager\Logic
 * 行为日志 逻辑层
 */
class ActionLogLogic extends BaseLogic{

    /**
     * @param array $request
     * @return array
     * 获取日志列表
     */
    function getList($request = array()) {
        if(!empty($request['account'])) {
            //按管理员账号查询
            $param['where']['account'] = $request['account'];
        }
        $param['where']['status'] = array('lt',9);//状态
        $param['order'] = 'create_time DESC';//排序
        $param['page_size'] = C('LIST_ROWS'); //页码
        $param['parameter'] = $request; //拼接参数

        $result = D('ActionLogView')->getCustomList($param);

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
        $row = D('ActionLogView')->getCustomRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        return $row;
    }
}