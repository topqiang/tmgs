<?php

namespace Manager\Logic;

/**
 * Class ConfigLogLogic
 * @package Manager\Logic
 * 系统配置值 逻辑层
 */
class ConfigSetLogic extends BaseLogic {

    /**
     * @param array $request
     * @return array
     */
    function getList($request = array()) {
        $param['where']['config_group'] = $request['config_group'];
        $param['where']['status'] = array('lt',9);//状态
        $param['order'] = 'create_time DESC';//排序
        $param['parameter'] = $request; //拼接参数

        $result = D('Config')->getList($param);

        return $result;
    }

    function findRow($request = array()) {}

    /**
     * @param array $request
     * @return bool|mixed|void
     * 批量更新
     */
    function update($request = array()) {
        if($request['config'] && is_array($request['config'])){
            foreach ($request['config'] as $name => $value) {
                D('Config')->where(array('name'=>$name))->data(array('value'=>$value))->save();
            }
        }
        S('Config_Cache',null);
        $this->setLogicSuccess('保存成功！'); return true;
    }
}