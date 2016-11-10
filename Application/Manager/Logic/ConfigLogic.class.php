<?php

namespace Manager\Logic;

/**
 * Class ConfigLogLogic
 * @package Manager\Logic
 * 系统配置 逻辑层
 */
class ConfigLogic extends BaseLogic {

    /**
     * @param array $request
     * @return array
     */
    function getList($request = array()) {
        if(!empty($request['name'])) {
            //按配置名称
            $param['where']['name'] = $request['name'];
        } if(!empty($request['config_group'])) {
            //按配置名称
            $param['where']['config_group'] = $request['config_group'];
        }
        $param['where']['status'] = array('lt',9);//状态
        $param['order'] = 'create_time DESC';//排序
        $param['page_size'] = C('LIST_ROWS'); //页码
        $param['parameter'] = $request; //拼接参数

        $result = D('Config')->getList($param);

        return $result;
    }

    /**
     * @param int $result
     * @param array $request
     * @return boolean
     * 修改状态后执行
     */
    protected function afterSetStatus($result = 0, $request = array()) {
        S('Config_Cache',null);
        return true;
    }

    /**
     * @param $result
     * @param array $request
     * @return boolean
     * 更新后执行
     */
    protected function afterUpdate($result, $request = array()) {
        S('Config_Cache',null);
        return true;
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
        $row = D('Config')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        return $row;
    }
}