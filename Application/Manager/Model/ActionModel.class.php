<?php
namespace Manager\Model;

/**
 * Class ActionModel
 * @package Manager\Model
 * 行为信息 模型
 */
class ActionModel extends BaseModel {

    /**
     * @var array
     * 自动验证规则
     */
    protected $_validate = array (
        array('unique_code', 'require', '行为标识必须', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('unique_code', '/^[a-zA-Z]\w{0,39}$/', '行为标识不合法', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('unique_code', '1,30', '行为标识长度不能超过30个字符', self::MUST_VALIDATE, 'length', self::MODEL_BOTH),
        array('unique_code', 'checkUnique', '行为标识已经存在', self::MUST_VALIDATE, 'callback', self::MODEL_BOTH, array('unique_code')),
        array('name', 'require', '行为名称不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('name', '1,30', '标题长度不能超过30个字符', self::MUST_VALIDATE, 'length', self::MODEL_BOTH),
        array('remark', 'require', '行为描述不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('remark', '1,120', '行为描述不能超过120个字符', self::MUST_VALIDATE, 'length', self::MODEL_BOTH),
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
     * @param array $param  综合条件参数
     * @return array
     */
    function getList($param = array()) {
        if(!empty($param['page_size'])) {
            $total      = $this->where($param['where'])->count();
            $Page       = $this->getPage($total, $param['page_size'], $param['parameter']);
            $page_show  = $Page->show();
        }

        $model  = $this->where($param['where'])->order($param['order']);

        //是否分页
        !empty($param['page_size'])  ? $model = $model->limit($Page->firstRow,$Page->listRows) : '';

        $list = $model->select();

        return array('list'=>$list,'page'=>!empty($page_show) ? $page_show : '');
    }

    /**
     * @param array $param
     * @return mixed
     */
    function findRow($param = array()) {
        $row = $this->where($param['where'])->find();
        return $row;
    }
}