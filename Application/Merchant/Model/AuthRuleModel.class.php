<?php
namespace Merchant\Model;

/**
 * Class AuthRuleModel
 * @package Merchant\Model
 * 权限规则 模型
 */
class AuthRuleModel extends BaseModel {
    /**
     * 检验类型
     */
    const RULE_URL  = 1;
    const RULE_MAIN = 2;

    /**
     * @param $param
     * @return mixed
     */
    function getList($param = array()) {
        $list = $this->field('id,name,title,parent_id')->where($param['where'])->select();
        return $list;
    }

    function findRow($param = array()) { }
}