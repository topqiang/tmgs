<?php
namespace Manager\Model;

/**
 * 退货管理
 * @author zhouwei
 * Class ActionModel
 * @package Manager\Model
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
        /*
            [0] => array(17) {
                ["id"] => string(1) "1"
                ["order_id"] => string(14) "14693191068182"
                ["g_id"] => string(36) "自然开口本色无漂白开心果"
                ["m_id"] => array(2) {
                ["account"] => string(11) "18931728999"
                ["nickname"] => string(11) "18931728999"
                }
                ["merchant_id"] => array(1) {
                ["merchant_name"] => string(15) "金雨旗舰店"
                }
                ["name"] => string(6) "周伟"
                ["phone"] => string(11) "18202229802"
                ["address"] => string(18) "天津市虹桥西"
                ["explain"] => string(49) "太差了,我连看都没看就知道他太差了"
                ["voucher"] => string(9) "1,2,3,4,5"
                ["reason"] => string(15) "就是想退货"
                ["money"] => string(5) "10.00"
                ["express"] => string(8) "shunfeng"
                ["serial_number"] => string(9) "300011002"
                ["express_type"] => string(1) "1"
                ["step"] => string(1) "0"
                ["create_time"] => string(1) "0"
            }
        */
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