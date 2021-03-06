<?php
namespace Manager\Model;

/**
 * Class AttributeContentModel
 * @package Manager\Model
 */
class AttributeContentModel extends BaseModel {

    /**
     * @var array
     * 自动验证规则
     */
    protected $_validate = array (
        array('cn_attr_con_name', 'require', '属性值必须填写', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('cn_attr_con_name', '1,50', '属性值长度不能超过50个字符', self::MUST_VALIDATE, 'length', self::MODEL_BOTH),
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
        }

        $model  = $this->where($param['where'])->order($param['order']);

        //是否分页
        !empty($param['page_size'])  ? $model = $model->limit($Page->firstRow,$Page->listRows) : '';

        $list = $model->select();
        foreach($list as $k=>$v){
            $list[$k]['attr_id'] = M('attribute')->where(array('id'=>$v['attr_id']))->getField('cn_attr_name');
        }
        return array('list'=>$list,'page'=>'');
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