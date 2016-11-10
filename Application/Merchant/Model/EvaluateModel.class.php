<?php
namespace Merchant\Model;

/**
 * Class ActionModel
 * @package Manager\Model
 * 行为信息 模型
 */
class EvaluateModel extends BaseModel {

    /**
     * @var array
     * 自动验证规则
     */
    protected $_validate = array (

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
     *   ["id"] => string(2) "16"
    ["m_id"] => string(2) "15"
    ["g_id"] => string(2) "19"
    ["merchant_id"] => string(1) "6"
    ["evaluate_pic"] => string(3) "995"
    ["review"] => string(10) "very good "
    ["rank"] => string(1) "2"
    ["order_sn"] => string(15) "146552975611433"
    ["order_id"] => string(3) "123"
    ["goods_attr"] => string(0) ""
    ["evaluate_time"] => string(10) "1465550639"
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
        foreach ($list as $k=>$v) {
            $list[$k]['m_id'] = M('Member')->where(array('id'=>$v['m_id']))->getField('nickname');
            $list[$k]['g_id'] = M('Goods')->where(array('id'=>$v['g_id']))->getField('cn_goods_name');
            $list[$k]['merchant_id'] = M('Merchant')->where(array('id'=>$v['merchant_id']))->getField('merchant_name');
        }
        return array('list'=>$list,'page'=>!empty($page_show) ? $page_show : '');
    }

    /**
     * @param array $param
     * @return mixed
     */
    function findRow($param = array()) {
        $row = $this->where($param['where'])->find();
        $row['m_id'] = M('Member') -> where(array('id'=>$row['m_id']))->getField('nickname');
        $row['g_id'] = M('goods')  -> where(array('id'=>$row['g_id']))->getField('cn_goods_name');
        $row['merchant_id'] = M('Merchant')  -> where(array('id'=>$row['merchant_id']))->getField('merchant_name');
        $row['evaluate_pic'] = M('file')  -> where(array('id'=>array('in',$row['evaluate_pic'])))->field('path')->select();
        return $row;
    }
}