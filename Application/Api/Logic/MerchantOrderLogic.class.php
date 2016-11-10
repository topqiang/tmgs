<?php
namespace Api\Logic;

/**
 * Class MerchantOrderLogic
 * @package Api\Logic
 */
class MerchantOrderLogic extends BaseLogic{

    //初始化
    public function _initialize(){
        parent::_initialize();
    }
    /**
     * 订单列表
     * 商家ID     merchant_id
     * 类别       type 2  待发货  3  待收货  5  订单完成  6  取消订单  8  全部
     * 分页参数   p
     */
    public function orderList($request = array()){
        //商家ID不能为空
        if(!$request['merchant_id']){
            apiResponse('error','商家ID不能为空');
        }
        //类别传递有误  type 2  待发货  3  待收货  5  订单完成  6  取消订单  8  全部
        if(!in_array($request['type'],array(2,3,5,6,8))){
            apiResponse('error','类别传递有误');
        }
//        if($request['type'] != 2&&$request['type'] != 3&&$request['type'] != 5&&$request['type'] != 6&&$request['type'] != 8){
//
//        }
        $where['merchant_id'] = $request['merchant_id'];
        $where['remove_status'] = array('neq',1);
        if($request['type'] != 8){
            $where['status'] = $request['type'];
        }
        //分页参数不能为空
        if(!$request['p']){
            apiResponse('error','分页参数不能为空');
        }
        $order_list = M('Order') ->where($where) ->field('id as order_id, m_id, order_sn, goods_info_serialization, status,delivery_sn,delivery_code') ->page($request['p'].',15')-> order('submit_order_time DESC') ->select();
        foreach($order_list as $k =>$v){
            $member = M('Member') ->where(array('id'=>$v['m_id'])) ->field('id as m_id, nickname') ->find();
            $goods_info = unserialize($v['goods_info_serialization']);
            $price = 0;
            $num = 0;
            $delivery_price = 0;
            foreach($goods_info['goods'] as $key => $val){
                $goods[$key]['goods_id'] = $val['goodsDetail']['id'];
                $goods[$key]['goods_name'] = $val['goodsDetail']['goods_name'];
                $goods[$key]['attr_con_name'] = $val['product']['attr_con_name'];
                $goods[$key]['goods_pic'] = C('API_URL').$val['goodsDetail']['goods_pic'];
                $goods[$key]['num'] = $val['num'];
                $goods[$key]['goods_price'] = $val['goodsDetail']['price'];
                $goods[$key]['delivery_cost'] = $val['delivery_cost'];
                $total_price = $goods[$key]['goods_price'] + $goods[$key]['delivery_cost'];
                $goods[$key]['totalprice'] = $total_price?$total_price.'':'0.00';
                $price = $price + $total_price;
                $delivery_price = $goods[$key]['delivery_cost'] + $delivery_price;
                $num = $num + $val['num'];
            }
            $order_list[$k]['delivery_sn'] = $v['delivery_sn'];
            $order_list[$k]['delivery_code'] = $v['delivery_code'];
            $order_list[$k]['goods_info_serialization'] = $goods;
            $order_list[$k]['num'] = $num?$num.'':'0';
            $order_list[$k]['price'] = $price?$price.'':'0.00';
            $order_list[$k]['delivery_price'] = $delivery_price?$delivery_price.'':'0.00';
            $order_list[$k]['member'] = $member['nickname'];
            if($v['status'] == 7){
                $order_list[$k]['sale_status'] = M('AfterSale') ->where(array('order_id'=>$v['order_id']))->getField('status');//状态 1 发起申请 2卖家同意 3 卖家确认退货地址 4买家已退货 5卖家确认收货 6售后完成 9 拒绝申请
            }
        }
        if(!$order_list){
            $order_list = array();
        }
        apiResponse('success','',$order_list);
    }

    /**
     * 订单详情
     * 商家ID       merchant_id
     * 订单ID       order_id
     */
    public function orderInformation($request = array()){
        //商家ID不能为空
        if(!$request['merchant_id']){
            apiResponse('error','商家ID不能为空');
        }
        //订单ID不能为空
        if(!$request['order_id']){
            apiResponse('error','订单ID不能为空');
        }
        //查询订单信息
        $where['merchant_id'] = $request['merchant_id'];
        $where['id']  = $request['order_id'];
        $where['remove_status'] = array('neq',9);
        $order = M('Order') ->where($where)
            ->field('id as order_id, m_id, order_sn, submit_order_time, status, delivery_code, delivery_sn, goods_info_serialization, leave_msg, address')
            ->find();
        if(!$order){
            apiResponse('error','该订单详情有误');
        }
        $member = M('Member') ->where(array('id'=>$order['m_id'])) ->getField('nickname');
        if($order['delivery_code']){
            $delivery_company = M('DeliveryCompany') ->where(array('delivery_code' => $order['delivery_code'])) ->getField('company_name');
        }else{
            $delivery_company = '';
        }
        // 售後處理
        $OrderOut = M('AfterSale') -> where(array('order_id'=>$order['order_id'])) -> find();
        $order['after_sale']['reason'] = $OrderOut['reason'] ? $OrderOut['reason'] : '';
        $order['after_sale']['explain'] = $OrderOut['explan'] ? $OrderOut['explan'] : '';
        $order['after_sale']['step'] = $OrderOut['status'] ? orderOutStatus($OrderOut['status']) : '';
        $order['after_sale']['judge_status'] = $OrderOut['status'] ? $OrderOut['status'] : 0 ;
        $order['after_sale']['as_id'] = $OrderOut['id'] ? $OrderOut['id'] : 0 ;
        if(empty($OrderOut['certificate'])){
            $order['after_sale']['pic'] = '0';
        }else{
            $order['after_sale']['pic'] = '1';
        }
        $order['delivery_company'] = $delivery_company;
        $order['nickname'] = $member;
        $goods_info = unserialize($order['goods_info_serialization']);
        $address = unserialize($order['address']);
        $address['address_id'] = $address['id'];
        $address['address_address'] = $address['address'];
        unset($address['id']);
        unset($address['address']);
        $order['address'] = $address;
        $order['submit_order_time'] = date('Y-m-d',$order['submit_order_time']);
        unset($order['goods_info_serialization']);
        $price = 0;
        $num = 0;
        $delivery_price = 0;
        foreach($goods_info['goods'] as $key => $val){
            $goods[$key]['goods_id'] = $val['goodsDetail']['id'];
            $goods[$key]['goods_name'] = $val['goodsDetail']['goods_name'];
            $goods[$key]['attr_con_name'] = $val['product']['attr_con_name'];
            $goods[$key]['goods_pic'] = C('API_URL').$val['goodsDetail']['goods_pic'];
            $goods[$key]['num'] = $val['num'];
            $goods[$key]['goods_price'] = $val['goodsDetail']['price'];
            $goods[$key]['delivery_cost'] = $val['delivery_cost'];
            $total_price = $goods[$key]['goods_price'] + $goods[$key]['delivery_cost'];
            $goods[$key]['totalprice'] = $total_price?$total_price.'':'0.00';
            $price = $price + $total_price;
            $delivery_price = $goods[$key]['delivery_cost'] + $delivery_price;
            $num = $num + $val['num'];
        }
        $order['goods_info_serialization'] = $goods;
        $order['num'] = $num?$num.'':'0';
        $order['price'] = $price?$price.'':'0.00';
            $order['delivery_price'] = $delivery_price?$delivery_price.'':'0.00';
        apiResponse('success','',$order);
    }

    /**
     * 配送订单
     * 订单ID     order_id
     * 物流ID     delivery_id
     * 物流单号   delivery_sn
     */
    public function deliveryOrder($request = array()){
        //订单ID不能为空
        if(!$request['order_id']){
            apiResponse('error','订单ID不能为空');
        }
        //物流id不能为空
        if(!$request['delivery_id']){
            apiResponse('error','物流ID不能为空');
        }
        //物流单号不能为空
        if(!$request['delivery_sn']){
            apiResponse('error','物流单号不能为空');
        }
        $delivery = M('DeliveryCompany') ->where(array('id'=>$request['delivery_id'])) ->find();
        $where['id'] = $request['order_id'];
        $data['delivery_code'] = $delivery['delivery_code'];
        $data['delivery_sn']   = $request['delivery_sn'];
        $data['update_time']   = time();
        $data['start_delivery_time'] = time();
        $data['status'] = 3;
        $result = M('Order') ->where($where) ->data($data) ->save();
        if(!$result){
            apiResponse('error','配送信息有误');
        }
        apiResponse('success','配送成功');
    }

    /**
     * 物流列表
     */
    public function deliveryList(){
        //直接查询订单列表
        $delivery_list = M('DeliveryCompany') ->field('id as delivery_id, delivery_code, company_name') ->select();
        if(!$delivery_list){
            $delivery_list = array();
        }
        apiResponse('success','',$delivery_list);
    }


    /**
     * 评价列表
     * 商家ID   merchant_id
     * 分页参数    p
     */
    public function evaluateList($request = array()){
        //商家ID不能为空
        if(!$request['merchant_id']){
            apiResponse('error','商家ID不能为空');
        }
        //分页参数不能为空
        if(!$request['p']){
            apiResponse('error','分页参数不能为空');
        }
        //连表查询
        $result = M('Evaluate') ->alias('eva') ->where(array('id'=>$request['merchant_id']))
            -> field('eva.id as evaluate_id, eva.evaluate_pic, eva.review, eva.rank, eva.goods_attr, eva.evaluate_time, member.nickname, member.head_pic, goods.cn_goods_name as goods_name')
            ->join(array(
                'LEFT JOIN'.C('DB_PREFIX').'member member ON member.id = eva.m_id',
                'LEFT JOIN'.C('DB_PREFIX').'goods goods ON goods.id = eva.g_id',
            ))
            ->order('create_time desc')
            ->page($request['p'].',15')
            ->select();
        if(!$result){
            $result = array();
            apiResponse('success','',$result);
        }
        foreach ($result as $k => $v){
            $path = M('File') ->where(array('id'=>$v['head_pic'])) ->getField('path');
            $result[$k]['head_pic'] = $path?C('API_URL').$path:C('API_URL').'/Uploads/Member/default.png';
            $eva_pic = explode(',',$v['evaluate_pic']);
            foreach($eva_pic as $key => $val){
                $path = M('File') ->where(array('id'=>$v)) ->getField('path');
                $eva_pic[$key]['eva_picture'] = $path?C('API_URL').$path:'';
            }
            if(!$eva_pic){
                $eva_pic = array();
            }
            $result[$k]['evaluate_pic'] = $eva_pic;
        }
        apiResponse('success','',$result);
    }
    /**
     * 售后处理页
     * 商家ID   merchant_id
     * 订单ID   order_id
     * 售后ID   order_out_id
     */
    public function salesProcessPage($request = array()){
        //商家ID不能为空
        if(!$request['merchant_id']){
            apiResponse('error','商家ID不能为空');
        }
        //订单ID不能为空
        if(!$request['order_id']){
            apiResponse('error','订单ID不能为空');
        }
        //售后ID不能为空
        if(!$request['order_out_id']){
            apiResponse('error','售后ID不能为空');
        }

        //商家ID不能为空
        $result = M('OrderOut') ->where(array('id'=>$request['order_out_id']))
            ->field('id as order_out_id, m_id, order_id, g_id, merchant_id, name, phone, address, explain, voucher, reason, express, serial_number, express_type,step')
            ->find();
        if(!$result){
            apiResponse('error','该订单详情有误');
        }

        $vougher = explode(',',$result['voucher']);
        $voucher_pic = array();
        foreach($vougher as $k =>$v){
            $path = M('File') ->where(array('id'=>$v)) ->getField('path');
            $voucher_pic[$k]['pic'] = $path?C('API_URL').$path:'';
        }
        $nickname = M('Member') ->where(array('id'=>$result['m_id'])) ->getField('nickname');
        $result['voucher'] = $voucher_pic?$voucher_pic:array();
        $result['nickname'] = $nickname;
        $order = M('Order') ->where(array('id'=>$result['order_id']))
            ->field('id as order_id, address, order_sn, delivery_code, delivery_sn, goods_info_serialization, submit_order_time, leave_msg')
            ->find();

        if(!$order){
            apiResponse('error','订单信息有误');
        }
        $address = unserialize($order['address']);
        $address['address_id'] = $address['id'];
        $address['address_stay'] = $address['address'];
        unset($address['id']);
        unset($address['address']);
        $order['address_address'] = $address;
        unset($order['address']);


        $delivery_company = M('DeliveryCompany') ->where(array('delivery_code'=>$order['delivery_code'])) ->find();
        if($delivery_company){
            $order['company_name'] = $delivery_company['company_name'];
        }else{
            $order['company_name'] = '';
        }
        $goods = unserialize($order['goods_info_serialization']);

        unset($order['goods_info_serialization']);
        $goods_info = array();
        foreach($goods['goods'] as $k => $v){
            if($result['g_id'] == $v['goodsDetail']['id']){
                $goods_info['goods_id']   = $v['goodsDetail']['id'];
                $goods_info['goods_name'] = $v['goodsDetail']['goods_name'];
                $goods_info['goods_pic']  = C('API_URL') . $v['goodsDetail']['goods_pic'];
                $goods_info['price']      = $v['goodsDetail']['price'].'';
                $goods_info['num']        = $v['num'].'';
                $goods_info['attr_con_name'] = $v['product']['attr_con_name'];
                $goods_info['trade_price']   = $v['goodsDetail']['trade_price'];
            }else{
                continue;
            }
        }

        if(!$goods_info){
            apiResponse('error','该商品信息有误');
        }
        $goods_data = M('Goods') ->where(array('id'=>$result['g_id'])) ->find();
        $goods_info['delivery_price'] = $goods_data['cn_delivery_cost'].'';
        $unit_price = ($goods_info['price'] - $goods_info['delivery_price'])/$goods_info['num'];
        $goods_info['unit_price'] = $unit_price?$unit_price.'':'0.00';
        $order['goods_info'] = $goods_info;
        $result['order'] = $order;
        apiResponse('success','',$result);
    }
    /**
     * 售后处理   拒绝或者同意
     * 商家ID   merchant_id
     * 退货ID   order_out_id
     * 操作方式  type  1  同意退货  2  拒绝退货
     */
    public function dealProcess($request = array()){
        //退货ID不能为空
        if(!$request['order_out_id']){
            apiResponse('error','退货ID不能为空');
        }
        //商家ID不能为空
        if(!$request['merchant_id']){
            apiResponse('error','商家ID不能为空');
        }
        //操作方式有误  type  1  同意退货  2  拒绝退货
        if($request['type'] != 1&&$request['type'] != 2){
            apiResponse('error','操作方式有误');
        }
        //对数据库进行相应操作
        $where['id'] = $request['order_out_id'];
        $where['merchant_id'] = $request['merchant_id'];
        if($request['type'] == 1){
            $data['step'] = 1;
        }else{
            $data['step'] = 9;
        }
        $data['update_time'] = time();
        $result = M('OrderOut') ->where($where) ->data($data) ->save();
        if(!$result){
            apiResponse('error','操作失败');
        }
        apiResponse('success','操作成功');
    }
    /**
     * 填写收货地址信息
     * 商家ID   merchant_id
     * 退货ID   order_out_id
     * 收货人姓名  name
     * 联系方式    phone
     * 收货地址    address
     * 退货说明    explain
     */
    public function merchantAddress($request = array()){
        //退货ID不能为空
        if(!$request['order_out_id']){
            apiResponse('error','退货ID不能为空');
        }
        //商家ID不能为空
        if(!$request['merchant_id']){
            apiResponse('error','商家ID不能为空');
        }
        //收货人姓名不能为空
        if(!$request['name']){
            apiResponse('error','收货人姓名不能为空');
        }
        //联系方式不能为空
        if(!$request['phone']){
            apiResponse('error','联系方式不能为空');
        }
        //收货地址不能为空
        if(!$request['address']){
            apiResponse('error','收货地址不能为空');
        }
        //退货说明不能为空
        if(!$request['explain']){
            apiResponse('error','退货说明不能为空');
        }
        //对数据库进行相应操作
        $where['id'] = $request['order_out_id'];
        $where['merchant_id'] = $request['merchant_id'];
        $data['name'] = $request['name'];
        $data['phone'] = $request['phone'];
        $data['address'] = $request['address'];
        $data['explain'] = $request['explain'];
        $data['update_time'] = time();
        $data['step'] = 2;
        $result = M('OrderOut') ->where($where) ->data($data) ->save();
        if(!$result){
            apiResponse('error','输入地址信息失败');
        }
        apiResponse('success','输入地址信息成功');
    }

    /**
     * 删除订单
     * @param array $request
     */
    public function merchantDelOrder($request=array()){
        if(empty($request['order_id']) || !isset($request['order_id'])) apiResponse('error','订单ID不能为空');
        if(empty($request['merchant_id']) || !isset($request['merchant_id'])) apiResponse('error','商家ID不能为空');
        $where['id'] = $request['order_id'];
        $where['merchant_id'] = $request['merchant_id'];
        $setStatus = M('Order') -> where($where) -> data(array('remove_status'=>1)) ->  save();
        if($setStatus){
            apiResponse('success','订单删除成功');
        }else{
            apiResponse('error','订单删除失败');
        }
    }
}