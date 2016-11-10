<?php
namespace Manager\Model;

/**
 * Class PluginsModel
 * @package Manager\Model
 * 插件模型
 */
class PluginsModel extends BaseModel {


    /**
     * @var array
     * 自动验证规则
     */
    protected $_validate = array (
        array('name', 'require', '插件标识必须', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('name', '/^[a-zA-Z]\w{0,39}$/', '插件标识不合法', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('name', '1,40', '插件标识长度不能超过40个字符', self::EXISTS_VALIDATE, 'length', self::MODEL_BOTH),
        array('name', 'checkUnique', '该标识已经存在', self::EXISTS_VALIDATE, 'callback', self::MODEL_BOTH, array('name')),
        array('title', 'require', '插件标题必须', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('title', '1,20', '插件标题长度不能超过20个字符', self::EXISTS_VALIDATE, 'length', self::MODEL_BOTH),
        array('status', 'require', '插件状态必须', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
    );

    /**
     * @var array
     * 自动完成规则
     */
    protected $_auto = array(
        array('create_time', 'time', self::MODEL_INSERT, 'function'),
        array('update_time', 'time', self::MODEL_UPDATE, 'function'),
    );

    /**
     * @param $param
     * @return mixed
     */
    function getList($param = array()) {
        $list = $this->where($param['where'])->select();
        return $list;
    }

    /**
     * @param $param
     * @return mixed
     */
    function findRow($param = array()) {
        $row = $this->where($param['where'])->find();
        return $row;
    }
}