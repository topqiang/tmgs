<?php
namespace Manager\Model;

/**
 * 商家排序
 * @author zhouwei
 * Class MerchantAdvertisingModel
 * @package Manager\Model
 */
class MerchantAdvertisingModel extends BaseModel {

    protected $_validate = array (
        array('money', 'require', '请输入价格', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
//        array('type', 'require', '请选择类别', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('location', 'require', '请填写排序', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
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
//        if(!empty($param['page_size'])) {
//            $total      = $this->where($param['where'])->count();
//            $Page       = $this->getPage($total, $param['page_size'], $param['parameter']);
//            $page_show  = $Page->show();
//        }

        $model  = $this->where($param['where'])->order($param['order']);

        //是否分页
//        !empty($param['page_size'])  ? $model = $model->limit($Page->firstRow,$Page->listRows) : '';

        $list = $model->select();

        return array('list'=>$list,'page'=>!empty($page_show) ? $page_show : '');
    }

    /**
     * @param array $param
     * @return mixed
     */
    function findRow($param = array()) {
        $row = $this->where($param['where'])->find();
        $row['pic'] = api('System/getFiles',array($row['pic']));
        $row['show_pic'] = api('System/getFiles',array($row['show_pic']));
        return $row;
    }
}