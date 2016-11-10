<?php
namespace Manager\Model;

/**
 * Class LoveMapModel
 * @package Manager\Model
 * 爱心捐助轮播
 */
class LoveMapModel extends BaseModel {

    /**
     * @var array
     * 自动验证规则
     */
    protected $_validate = array ();

    /**
     * @var array
     * 自动完成规则
     */
    protected $_auto = array(
        array('create_time', 'time', self::MODEL_INSERT, 'function'),
        array('update_time', 'time', self::MODEL_UPDATE, 'function'),
    );

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
        $row['pic1'] = api('System/getFiles',array($row['pic1']));
        $row['pic2'] = api('System/getFiles',array($row['pic2']));
        $row['pic3'] = api('System/getFiles',array($row['pic3']));
        $row['pic4'] = api('System/getFiles',array($row['pic4']));
        $row['pic5'] = api('System/getFiles',array($row['pic5']));
        return $row;
    }
}