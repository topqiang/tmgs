<?php
namespace Manager\Model;

/**
 * Class WechatTokenModel
 * @package Manager\Model
 * 接口管理
 */
class WechatTokenModel extends BaseModel {

    /**
     * @var array
     * 自动验证规则
     */
    protected $_validate = array ();

    /**
     * @var array
     * 自动完成规则
     */
    protected $_auto = array();

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