<?php
namespace Api\Controller;
use Think\Controller;

/**
 * Class UnionPayController
 * @package Api\Controller
 * 银联支付
 */
class UnionPayController extends BaseController{

    public function _initialize()
    {
        Vendor('Unionpay.sdk.log#class');
        Vendor('Unionpay.sdk.SDKConfig');
        Vendor('Unionpay.sdk.secureUtil');
        Vendor('Unionpay.sdk.common');
        Vendor('Unionpay.sdk.acp_service');
    }

    /**
     * 银联充值
     * 用户ID      m_id
     * 充值金额    money
     */
    public function unionRecharge(){
        D('UnionPay','Logic') ->unionRecharge(I('post.'));
    }

    /**
     * 银联充值回调
     */
    public function unionRechargeNotify(){

        //判断是否传了签名
        if (isset ( $_POST ['signature'] )) {
            //验签
            if(\AcpService::validate($_POST)){
                //如果respCode为00 或者A6时表示支付成功
                if($_POST['respCode']=='00' || $_POST['respCode']=='A6'){
                    //书写后台处理代码
                    $orderId = $_POST ['orderId'];//订单编号
                    $where['order_sn'] = $orderId;
                    $where['status']   = array('eq',0);

                    $recharge_info = M('Recharge') ->where($where) ->find();
                    if($recharge_info) {
                        //修改充值订单状态
                        unset($where);
                        $where['id'] = $recharge_info['id'];
                        $data['recharge'] = 3;
                        $data['update_time'] = time();
                        $data['status'] = 1;
                        M('Recharge')->where($where)->data($data) ->save();

                        //添加用户余额
                        unset($where);
                        $where['id'] = $recharge_info['m_id'];
                        M('Member')->where($where)->setInc('balance', $recharge_info['money']);

                        //添加账单明细
                        unset($data);
                        $data['type'] = '1';
                        $data['object_id'] = $recharge_info['m_id'];
                        $data['title'] = '银联支付';
                        $data['content'] = '充值';
                        $data['money'] = $recharge_info['money'];
                        $data['symbol'] = '1';
                        $data['create_time'] = time();
                        M('PayLog')->data($data)->add();
                    }else{
                        apiResponse('error','订单有误');
                    }
                    apiResponse('success','充值成功');
                }
            }

        }
    }

    /**
     * 购买商品获取tn接口
     * 用户ID      m_id
     * 订单ID      order_group_sn
     * 付款方式    type = 1
     */
    public function orderGetTn(){
        D('UnionPay','Logic') ->orderGetTn(I('post.'));
    }

    /**
     * 购买商品银联支付回调
     */
    public function unionOrderNotify(){
        //判断是否传了签名
        if (isset ( $_POST ['signature'] )) {

            //验签
            if(\AcpService::validate($_POST)){

                //如果respCode为00 或者A6时表示支付成功
                if($_POST['respCode']=='00' || $_POST['respCode']=='A6'){
                    //书写后台处理代码
                    $orderId = $_POST ['orderId'];//订单编号
                    //获取订单号   并进行处理
                    $number = substr($orderId,0,1);
                    $bool = is_numeric($number);
                    //$bool为1  小订单   0  大订单
                    if($bool == 0){
                        //求大订单信息  并修改订单状态
                        $where['order_total_sn'] = $orderId;
                        $where['pay_status']     = 0;
                        $where['pay_type']       = 0;
                        $order_info = M('OrderGroup') ->where($where) ->find();
                        if(!$order_info){
                            apiResponse('error','订单信息有误');
                        }
                        $data['pay_status'] = 1;
                        $data['pay_type']   = 1;
                        $res = M('OrderGroup') ->where($where) ->data($data) ->save();
                        unset($where);
                        unset($data);
                        //通过大订单ID查询所有小订单并修改状态
                        $where['order_g_id'] = $order_info['id'];
                        $where['status']     = 0;
                        $where['pay_type']   = 0;
                        $where['remove_status'] = 0;
                        $order_data = M('Order') ->where($where) ->field('id as order_id, m_id, merchant_id, totalprice') ->select();
                        foreach($order_data as $k =>$v){
                            unset($where);
                            $where['id']      = $v['order_id'];
                            $data['pay_type'] = 1;
                            $data['status']   = 1;
                            $result =  M('Order') ->where($where) ->data($data) ->save();
                        }
                        //写进payment
                        unset($where);
                        unset($data);
                        $where['order_id'] = $orderId;
                        $data['payment']   = 1;
                        $data['update_time'] = time();
                        $result = M('Payment') ->where($where) ->data($data) ->save();
                        if(!$result){
                            apiResponse('error','操作有误');
                        }
                        //写进paylog
                        unset($data);
                        $data['object_id'] = $order_info['m_id'];
                        $data['type']      = 1;
                        $data['title']     = '支出';
                        $data['content']     = '商品结算';
                        $data['money']       = $order_info['order_total_price'];
                        $data['symbol']      = '0';
                        $data['create_time'] = time();
                        M('PayLog')->data($data)->add();
                    }else{
                        //$bool为小订单的情况   查询小订单状态  并修改订单状态
                        $where['order_sn']    = $orderId;
                        $where['remove_status'] = 0;
                        $where['status']        = 0;
                        $where['pay_type']      = 0;
                        $order_info = M('Order') ->where($where) ->find();
                        if(!$order_info){
                            apiResponse('error','订单信息有误');
                        }
                        $data['status'] = 1;
                        $data['pay_type'] = 1;
                        $res = M('Order') ->where($where) ->data($data) ->save();
                        //写进payment
                        unset($where);
                        unset($data);
                        $where['order_id'] = $orderId;
                        $data['payment']   = 1;
                        $data['update_time'] = time();
                        $result = M('Payment') ->where($where) ->data($data) ->save();
                        if(!$result){
                            apiResponse('error','操作有误');
                        }
                        //写进paylog
                        unset($data);
                        $data['object_id'] = $order_info['m_id'];
                        $data['type']      = 1;
                        $data['title']     = '支出';
                        $data['content']     = '商品结算';
                        $data['symbol']      = '0';
                        $data['money']       = $order_info['totalprice'];
                        $data['create_time'] = time();
                        M('PayLog')->data($data)->add();
                    }
                    apiResponse('success','下单成功');

                }else{
                    echo "支付失败";
                }
            }else{
                echo "验证签名失败";
            }

        }else{
            echo '签名为空';
        }
    }

    /**
     * 保障金下单tn接口
     * 用户ID      m_id
     * 订单ID      order_group_sn
     * 付款方式    type = 1
     */
    public function orderSecurity(){
        D('UnionPay','Logic') ->orderSecurity(I('post.'));
    }

    /**
     * 银联充值回调
     */
    public function unionSecurityNotify(){

        //判断是否传了签名
        if (isset ( $_POST ['signature'] )) {
            //验签
            if(\AcpService::validate($_POST)){
                //如果respCode为00 或者A6时表示支付成功
                if($_POST['respCode']=='00' || $_POST['respCode']=='A6'){
                    //书写后台处理代码
                    $orderId = $_POST ['orderId'];//订单编号
                    $where['order_security_sn'] = $orderId;
                    $where['status']   = array('eq',0);

                    $security = M('Security') ->where($where) ->find();
                    if($security) {
                        //修改充值订单状态
                        unset($where);
                        $where['id'] = $security['id'];
                        $data['status'] = 1;
                        M('Security')->where($where)->data($data) ->save();

                        //添加用户余额
                        unset($where);
                        $where['id'] = $security['merchant_id'];
                        $data['integrity_merchant_status'] = 1;
                        $data['integrity_merchant_cost'] = $security['money'];
                        $data['integrity_merchant_type'] = $security['grade'];
                        $data['update_time']             = time();
                        $result = M('Merchant') ->where($where) ->data($data) ->save();
                        if(!$result){
                            apiResponse('error','写入记录失败');
                        }
                        //添加账单明细
                        unset($data);
                        $data['type'] = '2';
                        $data['object_id'] = $security['merchant_id'];
                        $data['title'] = '保障金支付';
                        $data['content'] = '银联支付';
                        $data['money'] = $security['money'];
                        $data['symbol'] = '0';
                        $data['create_time'] = time();
                        M('PayLog')->data($data)->add();
                    }else{
                        apiResponse('error','订单有误');
                    }
                    apiResponse('success','充值成功');
                }
            }
        }
    }

    /**
     * 1 2 广告报名下单tn接口
     */
    public function advertSecurity(){
        D('UnionPay','Logic') ->advertSecurity(I('post.'));
    }

    /**
     * 1 2 广告报名银联下单回调
     */
    public function advertSecurityNotify(){

        //判断是否传了签名
        if (isset ( $_POST ['signature'] )) {
            //验签
            if(\AcpService::validate($_POST)){
                //如果respCode为00 或者A6时表示支付成功
                if($_POST['respCode']=='00' || $_POST['respCode']=='A6'){
                    //书写后台处理代码
                    $orderId = $_POST ['orderId'];//订单编号
                    $where['order_sn'] = $orderId;

                    $ad_order = M('AdPositionTotal') ->where($where) ->find();
                    if($ad_order) {
                        $data['pay_status'] = 1;
                        $data['pay_type']   = 3;
                        M('AdPositionTotal')->where($where)->data($data) ->save();

                        unset($data);
                        unset($where);
                        $time_date = explode(',',$ad_order['time']);

                        foreach($time_date as $k => $v){
                            $start_time = strtotime($v);
                            $end_time = $start_time + 86400;
                            $data['url']    = $ad_order['url'];
                            $data['pic']    = $ad_order['pic'];
                            $data['region'] = $ad_order['region'];
                            $data['type']   = $ad_order['type'];
                            $data['merchant_id'] = $ad_order['merchant_id'];
                            $data['a_p_t_id']    = $ad_order['id'];
                            $data['location']    = $ad_order['location'];
                            $data['start_time']  = $start_time;
                            $data['end_time']    = $end_time;
                            $result = M('AdPosition') ->add($data);
                        }
                        //添加账单明细
                        unset($data);
                        unset($where);
                        $data['type']      = 2;
                        $data['object_id'] = $ad_order['merchant_id'];
                        $data['title']     = '支出';
                        if($ad_order['type'] == 1){
                            $data['content']   = '首页广告位下单';
                        }else{
                            $data['content']   = '签到页广告位下单';
                        }
                        $data['symbol']    = 0;
                        $data['money']     = $ad_order['money'];
                        $data['create_time'] = time();
                        $result = M('PayLog') ->add($data);
                    }else{
                        apiResponse('error','订单有误');
                    }
                    apiResponse('success','充值成功');
                }
            }
        }
    }

    /**
     * 3 4 5 好货们下单tn接口
     */
    public function goodsSecurity(){
        D('UnionPay','Logic') ->goodsSecurity(I('post.'));
    }

    /**
     * 3 4 5 好货们的银联下单回调
     */
    public function goodsSecurityNotify(){

        //判断是否传了签名
        if (isset ( $_POST ['signature'] )) {
            //验签
            if(\AcpService::validate($_POST)){
                //如果respCode为00 或者A6时表示支付成功
                if($_POST['respCode']=='00' || $_POST['respCode']=='A6'){
                    //书写后台处理代码
                    $orderId = $_POST ['orderId'];//订单编号
                    $where['order_sn'] = $orderId;

                    $find_total = M('FindTotal') ->where($where) ->find();
                    if($find_total) {
                        $data['pay_status'] = 1;
                        $data['pay_type']   = 3;
                        M('FindTotal')->where($where)->data($data) ->save();

                        unset($data);
                        unset($where);
                        $time_date = explode(',',$find_total['time']);

                        foreach($time_date as $k => $v){
                            $start_time = strtotime($v);
                            $end_time = $start_time + 86400;
                            $data['merchant_id'] = $find_total['merchant_id'];
                            $data['type']        = $find_total['type'];
                            $data['object_id']   = $find_total['object_id'];
                            $data['start_time']  = $start_time;
                            $data['end_time']    = $end_time;
                            $data['f_t_id']      = $find_total['id'];
                            $res = M('FindBranch') ->add($data);
                        }
                        //添加账单明细
                        unset($data);
                        unset($where);
                        $data['type']      = 2;
                        $data['object_id'] = $find_total['merchant_id'];
                        $data['title']     = '支出';
                        if($find_total['type'] == 3){
                            $data['content']   = '发现好商品下单';
                        }elseif($find_total['type'] == 4) {
                            $data['content']   = '发现好店下单';
                        }else{
                            $data['content']   = '发现好服务下单';
                        }
                        $data['symbol']    = 0;
                        $data['money']     = $find_total['money'];
                        $data['create_time'] = time();
                        $result = M('PayLog') ->add($data);
                    }else{
                        apiResponse('error','订单有误');
                    }
                    apiResponse('success','充值成功');
                }
            }
        }
    }
    /**
     * 6 推广产品下单tn接口
     */
    public function spreadSecurity(){
        D('UnionPay','Logic') ->spreadSecurity(I('post.'));
    }

    /**
     * 6 推广产品银联下单回调
     */
    public function spreadSecurityNotify(){

        //判断是否传了签名
        if (isset ( $_POST ['signature'] )) {
            //验签
            if(\AcpService::validate($_POST)){
                //如果respCode为00 或者A6时表示支付成功
                if($_POST['respCode']=='00' || $_POST['respCode']=='A6'){
                    //书写后台处理代码
                    $orderId = $_POST ['orderId'];//订单编号
                    $where['order_sn'] = $orderId;
                    $CloudSpread = M('CloudSpread') ->where($where) ->find();
                    if($CloudSpread){
                        $data['pay_status'] = 1;
                        $data['pay_type']   = 3;
                        $data['update_time']  = time();
                        $result = M('CloudSpread')->where($where)->data($data) ->save();
                        if(!$result){
                            apiResponse('error','支付失败');
                        }

                        //添加账单明细
                        unset($data);
                        $data['type']      = 2;
                        $data['object_id'] = $CloudSpread['merchant_id'];
                        $data['title']     = '支出';
                        $data['content']   = '商品云推广';
                        $data['symbol']    = 0;
                        $data['money']     = $CloudSpread['pay_money'];
                        $data['create_time'] = time();
                        $result = M('PayLog') ->add($data);
                    }else{
                        apiResponse('error','订单有误');
                    }
                    apiResponse('success','充值成功');
                }
            }
        }
    }

   /*银联支付爱心*/
    public function unionLoveOrder(){
        D('UnionPay','Logic') ->unionRecharge(I('post.'));
    }

    /*银联支付回调*/
    public function unionLoveOrderNotify(){

        //判断是否传了签名
        if (isset ( $_POST ['signature'] )) {
            //验签
            if(\AcpService::validate($_POST)){
                //如果respCode为00 或者A6时表示支付成功
                if($_POST['respCode']=='00' || $_POST['respCode']=='A6'){
                    //书写后台处理代码
                    $orderId = $_POST ['orderId'];//订单编号
                    $where['order_sn'] = $orderId;
                    $where['status']   = array('eq',0);
                    $DonateLoveOrder = M('DonateLoveOrder') ->where($where) ->find();
                    if($DonateLoveOrder) {
                        //修改充值订单状态
                        unset($where);
                        $where['id'] = $DonateLoveOrder['id'];
                        $data['status'] = 1;
                        M('DonateLoveOrder')->where($where)->data($data) ->save();

                        //添加账单明细
                        unset($data);
                        $data['type'] = '1';
                        $data['object_id'] = $DonateLoveOrder['m_id'];
                        $data['title'] = '爱心捐助';
                        $data['content'] = '银联支付';
                        $data['money'] = $DonateLoveOrder['money'];
                        $data['symbol'] = '0';
                        $data['create_time'] = time();
                        M('PayLog')->data($data)->add();
                    }else{
                        apiResponse('error','订单有误');
                    }
                    apiResponse('success','充值成功');
                }
            }
        }
    }
}