<?php
namespace Manager\Model;

/**
 * Class ActionLogViewModel
 * @package Manager\Model
 * 行为日志视图模型
 */
class ActionLogViewModel extends BaseModel {


    /**
     * @param array $param  综合条件参数
     * @return array
     * 获取列表
     */
    function getList($param = array()) {}

    /**
     * @param $param
     * @return mixed
     * 获取一行
     */
    function findRow($param = array()) {}
}