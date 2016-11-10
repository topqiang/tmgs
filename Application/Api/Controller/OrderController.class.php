<?php
namespace Api\Controller;
use Think\Controller;

/**
 * Class OrderController
 * @package Api\Controller
 * 足迹模块
 */
class OrderController extends BaseController{

    public function _initialize(){
        parent::_initialize();
    }
    /**
     * 单个商品下单
     * 传递参数的方式：post
     * 需要传递的参数：
     * 语言版本：language cn中文 ue英文
     * 用户id：m_id
     * 商品id：goods_id
     * 港口id：haven_id
     * 商品属性：attr_id_group 逗号隔开， 如果商品没有属性，则为空
     * 购买数量：num
     * 地址id：address_id
     * 买家留言:leave_msg 如果没有留言 则为空
     */
    public function addOneOrder(){
        D('Order','Logic')->addOneOrder(I('post.'));
    }
    /**
     * 购物车提交订单
     * 传递参数的方式：post
     * 需要传递的参数：
     * 语言版本：language：cn 中文  ue英文
     * 用户ID       m_id
     * 用户地址ID   address_id
     * 港口ID       haven_id
     * 购物车信息   json串：[{"cart_id":"4"},{"cart_id":"10"}]
     * 买家留言     message    可以为空
     */
    public function cartOrder(){
        D('Order','Logic')->cartOrder(I('post.'));
    }
    /**
     * 取消交易
     * 传递参数的方式：post
     * 需要传递的参数：
     * 语言版本：language：cn 中文  ue英文
     * 用户ID  m_id
     * 订单ID  order_id
     */
    public function cancellationOrder(){
        D('Order','Logic')->cancellationOrder(I('post.'));
    }
    /**
     * 确认收货
     * 传递参数的方式：post
     * 需要传递的参数：
     * 语言版本：language：cn 中文  ue英文
     * 用户ID  m_id
     * 订单ID  order_id
     */
    public function confirmOrder(){
        D('Order','Logic')->confirmOrder(I('post.'));
    }
    /**
     * 删除订单
     * 传递参数的方式：post
     * 需要传递的参数：
     * 语言版本：language：cn 中文  ue英文
     * 用户ID  m_id
     * 订单ID  order_id
     */
    public function deleteOrder(){
        D('Order','Logic')->deleteOrder(I('post.'));
    }
    /**
     * 用户订单列表
     * 传递参数的方式：post
     * 需要传递的参数：
     * 语言版本：language：cn 中文  ue英文
     * 用户ID  m_id
     * 类型  type  0待配送，1待收货，2待评价，3已完成，4全部
     * 分页信息：p
     */
    public function orderList(){
        D('Order','Logic')->orderList(I('post.'));
    }
    /**
     * 用户订单详情
     * 传递参数的方式：post
     * 需要传递的参数：
     * 语言版本：language：cn 中文  ue英文
     * 用户ID  m_id
     * 商家ID  merchant_id
     */
    public function userOrderDetails (){
        D('Order','Logic')->userOrderDetails(I('post.'));
    }
    /**
     * 配送信息表
     * 传递参数的方式：post
     * 需要传递的参数：
     * 语言版本：language：cn 中文  ue英文
     * 商家ID  merchant_id
     * 订单ID  order_id
     * 联系人姓名  delivery_people
     * 联系人电话  delivery_phone
     * 联系人备注  delivery_remark
     */
    public function Distribution(){
        D('Order','Logic')->Distribution(I('post.'));
    }
    /**
     * 商家订单列表
     * 传递参数的方式：post
     * 需要传递的参数：
     * 语言版本：language：cn 中文  ue英文
     * 用户ID  m_id
     * 商家ID  merchant_id
     * 订单ID  order_id
     * 类型  type  0待配送，1待收货，2待评价，3已完成，4全部
     */
    public function merchantOrderList(){
        D('Order','Logic')->merchantOrderList(I('post.'));
    }
    /**
     * 商家订单详情
     * 传递参数的方式：post
     * 需要传递的参数：
     * 语言版本：language：cn 中文  ue英文
     * 用户ID  m_id
     * 商家ID  merchant_id
     */
    public function merchantOrderDetails (){
        D('Order','Logic')->merchantOrderDetails(I('post.'));
    }

    /**
     * 下单准备接口
     * 传递参数的方式：post
     * 需要传递的参数：
     * 语言版本：language :cn中文 ue英文
     * 地址id：address_id
     * 购物车信息   json串：[{"cart_id":"4"},{"cart_id":"10"}]
     */
    public function readAddCartOrder(){
        D('Order','Logic')->readAddCartOrder(I('post.'));
    }
    /**
     * 单品下单准备接口
     * 传递参数的方式：post
     * 需要传递的参数：
     * 地址id：address_id
     * 购物车信息   json串：[{"cart_id":"4"},{"cart_id":"10"}]
     */
    public function readyAddOrder(){
        D('Order','Logic')->readyAddOrder(I('post.'));
    }
    /**
     * 查看配送信息
     * 传递参数的方式：post
     * 需要传递的参数：
     * 订单ID  order_id
     */
    public function viewDistribution(){
        D('Order','Logic')->viewDistribution(I('post.'));
    }
    /**
     * 付款方式
     * 传递参数的方式：post
     * 需要传递的参数：
     * 订单ID  order_id
     */
    public function payment(){
        D('Order','Logic')->payment(I('post.'));
    }
    /**
     * 支付宝回调
     */
    public function alipayNotify(){
        $out_trade_no = $_POST['out_trade_no'];
        //取订单的第一位  不是数字走总订单   是数字走分订单
        $number = substr($out_trade_no , 0, 1);
        $bool = is_numeric($number);

        if($bool == 0){
            //查询总订单数据   并修改状态
            $where['order_total_sn'] = $out_trade_no;
            $where['pay_status']     = 0;
            $where['pay_type']       = 0;
            $order_info = M('OrderGroup') ->where($where) ->find();
            if(!$order_info){
                apiResponse('error','订单信息有误');
            }
            $data['pay_status'] = 2;
            $data['pay_type']   = 3;
            $res = M('OrderGroup') ->where($where) ->data($data) ->save();
            if(!$res){
                apiResponse('error','改变订单状态失败');
            }
            unset($where);
            unset($data);
            //通过总订单ID查询分订单  循环修改分订单状态
            $where['order_g_id'] = $order_info['id'];
            $where['status']     = 0;
            $where['pay_type']   = 0;
            $where['remove_status'] = 0;
            $order_data = M('Order') ->where($where) ->field('id as order_id, m_id, merchant_id, totalprice') ->select();
            foreach($order_data as $k =>$v){
                unset($where);
                $where['id']      = $v['order_id'];
                $data['pay_type'] = 3;
                $data['status']   = 2;
                $data['update_time'] = time();
                $result =  M('Order') ->where($where) ->data($data) ->save();
                if(!$result){
                    apiResponse('error','分订单信息有误');
                }
            }
            //写进payment  修改订单状态
            unset($where);
            unset($data);
            $where['order_id'] = $out_trade_no;
            $data['payment']   = 3;
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
            $res = M('PayLog')->data($data)->add();
            if(!$res){
                apiResponse('error','输入详情有误');
            }
        }else{
            //分订单状态
            $where['order_sn']    = $out_trade_no;
            $where['remove_status'] = 0;
            $where['status']        = 0;
            $where['pay_type']      = 0;
            $order_info = M('Order') ->where($where) ->find();
            if(!$order_info){
                apiResponse('error','订单信息有误');
            }
            $data['status'] = 2;
            $data['pay_type'] = 3;
            $res = M('Order') ->where($where) ->data($data) ->save();
            //写进paylog
            unset($where);
            unset($data);
            $where['order_id'] = $out_trade_no;
            $data['payment']   = 3;
            $data['update_time'] = time();
            $result = M('Payment') ->where($where) ->data($data) ->save();
            if(!$result){
                apiResponse('error','操作有误');
            }
            //写进payment
            unset($data);
            $data['object_id'] = $order_info['m_id'];
            $data['type']      = 1;
            $data['title']     = '支出';
            $data['content']     = '商品结算';
            $data['symbol']      = '0';
            $data['money']       = $order_info['totalprice'];
            $data['create_time'] = time();
            $res = M('PayLog')->data($data)->add();
            if(!$res){
                apiResponse('error','输入详情有误');
            }
        }
        apiResponse('success','支付成功');
    }
    /**
     * 微信回调
     */
    public function wXinNotify(){
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
        $xml_res = $this->xmlToArray($xml);
        $order_sn = $xml_res["out_trade_no"];
        //获取订单号   并进行处理
        $number = substr($order_sn,0,1);
        $bool = is_numeric($number);
        //$bool为1  小订单   0  大订单
        if($bool == 0){
            //求大订单信息  并修改订单状态
            $where['order_total_sn'] = $order_sn;
            $where['pay_status']     = 0;
            $where['pay_type']       = 0;
            $order_info = M('OrderGroup') ->where($where) ->find();
            if(!$order_info){
                apiResponse('error','订单信息有误');
            }
            $data['pay_status'] = 2;
            $data['pay_type']   = 2;
            $res = M('OrderGroup') ->where($where) ->data($data) ->save();
            if(!$res){
                apiResponse('error','订单状态有误');
            }
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
                $data['pay_type'] = 2;
                $data['status']   = 2;
                $data['update_time'] = time();
                $result =  M('Order') ->where($where) ->data($data) ->save();
            }
            //写进payment
            unset($where);
            unset($data);
            $where['order_id'] = $order_sn;
            $data['payment']   = 2;
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
            $where['order_sn']      = $order_sn;
            $where['remove_status'] = 0;
            $where['status']        = 0;
            $where['pay_type']      = 0;
            $order_info = M('Order') ->where($where) ->find();
            if(!$order_info){
                apiResponse('error','订单信息有误');
            }
            $data['status'] = 2;
            $data['pay_type'] = 2;
            $res = M('Order') ->where($where) ->data($data) ->save();
            //写进paylog
            unset($where);
            unset($data);
            $where['order_id'] = $order_sn;
            $data['payment']   = 2;
            $data['update_time'] = time();
            $result = M('Payment') ->where($where) ->data($data) ->save();
            if(!$result){
                apiResponse('error','操作有误');
            }
            //写进payment
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
        apiResponse('success','操作成功');
    }
    /**
     * 查询订单状态
     * 需要传递的参数：
     * 订单编号：order_list_sn
     */
    public function findStatus(){
        $order_list_sn = substr($_POST['order_list_sn'],0,1);
        $bool = is_numeric($order_list_sn);
        if($bool == 0){
            $where['order_total_sn'] = $_POST['order_list_sn'];
            $recharge_info = M('OrderGroup') ->where($where) ->find();
            if($recharge_info['pay_status']==0){
                $result_data['status'] = '2';
            }else{
                $result_data['status'] = '1';
            }
        }else{
            $where['order_sn'] = $_POST['order_list_sn'];
            $recharge_info = M('Order') ->where($where) ->find();
            if($recharge_info['status']==0){
                $result_data['status'] = '2';
            }else{
                $result_data['status'] = '1';
            }
        }
        apiResponse('success','',$result_data);
    }
    /**
     * 	作用：将xml转为array
     */
    function xmlToArray($xml){
        //将XML转为array
        $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $array_data;
    }



}