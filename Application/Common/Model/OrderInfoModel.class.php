<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/10/13
 * Time: 13:59
 */

namespace Common\Model;


class OrderInfoModel extends BaseModel{


    /**
     * @var array
     * 自动验证规则
     */
    protected $_validate = array(
        array('m_id','require','请登陆',self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('consignee','require','收货人不能为空',self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('province','require','省份不能为空',self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('city','require','城市不能为空',self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('district','require','地区不能为空',self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('address','require','详细地址不能为空',self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('mobile','require','联系手机不能为空',self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('goods_amount','require','商品总额不能为空',self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('pay_fee','require','支付费用不能为空',self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('shipping_id','require','配送方式id不能为空',self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('shipping_name','require','配送方式名称不能为空',self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
    );


    /**
     * @var array
     * 自动完成规则
     */
    protected $_auto = array (
        array('create_time', 'time', self::MODEL_INSERT, 'function'),
        array('update_time', 'time', self::MODEL_UPDATE, 'function'),
    );



    /**
     * @param array $param  综合条件参数
     * @return array
     */
    function getList($param = array()) {

    	if(!empty($param['page_size'])) {
    		$total      = $this->alias('oi')->where($param['where'])->count();
    		$Page       = $this->getPage($total, $param['page_size'], $param['parameter']);
    		$page_show  = $Page->show();
    	}
    	
        if (!empty($param['where']['oi.m_id'])){
        	$field = 'oi.id,oi.order_sn,oi.status,oi.goods_amount,oi.pay_status,oi.pay_id,oi.is_comment,oi.end_time';
        	$model = $this->alias('oi')
        	->where($param['where'])
        	->field($field)
        	->order($param['order']);
        } elseif (!empty($param['where']['oi.id'])){
        	$field = 'oi.*,co.coupon_money,pre.minus_price';
        	$model = $this->alias('oi')
        	->where($param['where'])
        	->field($field)
        	->join(C('DB_PREFIX').'coupon co ON co.id = oi.coupon_id','LEFT')
        	->join(C('DB_PREFIX').'preferential pre ON pre.id = oi.preferential_id','LEFT')
        	->order($param['order']);
        }
        
        //是否分页
        !empty($param['page_size'])  ? $model = $model->limit($Page->firstRow,$Page->listRows) : '';
        $list = $model->select();
        
        foreach ($list as &$value){
        	$where = "order_id=".$value['id'];
        	$result = D('order_goods')->where($where)->select();
        	$value['list'] = $result;
        }
        //是否分页
        //!empty($param['page_size'])  ? $model = $model->limit($Page->firstRow,$Page->listRows) : '';
        
        
//         ,
//         og.goods_id,og.goods_img,og.goods_name,og.market_price,og.goods_price,og.buy_number,og.status

        return $list;
    }

    /**
     * @param $param
     * @return mixed
     */
    function findRow($param = array()) {

        $field = 'oi.*,og.goods_id,og.goods_name,og.goods_img,og.market_price,og.goods_price,og.buy_number,og.status,
                co.coupon_money,ad.consignee,ad.mobile,ad.address,ad.longitude,ad.latitude';
        $result = D('OrderInfo')->alias('oi')
            ->where($param['where'])
            ->field($field)
            ->join(C('DB_PREFIX').'address ad ON oi.address_id = ad.id','LEFT')
            ->join(C('DB_PREFIX').'order_goods og ON og.order_id = oi.id','LEFT')
            ->join(C('DB_PREFIX').'coupon co ON co.id = oi.coupon_id','LEFT')
            ->order($param['order'])
            ->find();

        return $result;
    }
}