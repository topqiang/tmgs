<?php
namespace Manager\Model;

/**
 * 分组权限 模型
 */

class AuthGroupModel extends BaseModel {

    /**
     * @var array
     * 自动验证规则
     */
    protected $_validate = array (
        array('title', 'require', '您未填写组名', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('title', 'checkUnique', '该组名已经存在', self::EXISTS_VALIDATE, 'callback', self::MODEL_BOTH, array('title')),
        array('title', '1,15', '组名长度不能超过15个字符', self::EXISTS_VALIDATE, 'length', self::MODEL_BOTH),
        array('description', 'require', '组名描述不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('description', '1,80', '描述长度不能超过80个字符', self::EXISTS_VALIDATE, 'length', self::MODEL_BOTH),
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
     * @param $param
     * @return mixed
     */
    function findRow($param = array()) {
        $row = $this->where($param['where'])->find();
        return $row;
    }
}