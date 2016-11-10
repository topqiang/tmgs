<?php
namespace Manager\Model;

/**
 * Class IntegralRuleModel
 * @package Manager\Model
 * 积分规则
 */
class IntegralRuleModel extends BaseModel {


    /**
     * @param array $param  综合条件参数
     * @return array
     */
    function getList($param = array()) {}

    /**
     * @param array $param
     * @return mixed
     */
    function findRow($param = array()) {
        $row = $this->where($param['where'])->find();
        return $row;
    }
}