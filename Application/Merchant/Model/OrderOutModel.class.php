<?php
namespace Merchant\Model;

/**
 * Class ActionModel
 * @package Manager\Model
 * 行为信息 模型
 */
class OrderOutModel extends BaseModel {

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
        foreach($list as $k => $v){
            $list[$k]['m_id'] = M('Member')->where(array('id'=>$v['m_id']))->field('account,nickname')->find(); // 查用户的 账号 / 昵称
            $list[$k]['merchant_id'] = M('Merchant')->where(array('id'=>$v['merchant_id']))->field('merchant_name')->find(); // 查用户的 商家名称
            $list[$k]['g_id'] = M('Goods')->where(array('id'=>$v['g_id']))->getField('cn_goods_name'); // 查询 商品的 中文名称
            $list[$k]['order_id'] = M('Order')->where(array('id'=>$v['order_id']))->getField('order_sn'); // 订单号
        }
        return array('list'=>$list,'page'=>!empty($page_show) ? $page_show : '');
    }

    /**
     * @param array $param
     * @return mixed
     */
    function findRow($param = array()) {
        $row = $this->where($param['where'])->find();
        $row['m_id'] = M('Member')->where(array('id'=>$row['m_id']))->field('account,nickname')->find(); // 查用户的 账号 / 昵称
        $row['merchant_id'] = M('Merchant')->where(array('id'=>$row['merchant_id']))->field('merchant_name')->find(); // 查用户的 商家名称
        $row['g_id'] = M('Goods')->where(array('id'=>$row['g_id']))->getField('cn_goods_name'); // 查询 商品的 中文名称
        $row['order_id'] = M('Order')->where(array('id'=>$row['order_id']))->getField('order_sn'); // 订单号
        $row['express'] = M('DeliveryCompany')->where(array('delivery_code'=>$row['express']))->getField('company_name');
        $row['voucher'] = api('System/getFiles',array($row['voucher'])); // 凭证
        return $row;
    }
}