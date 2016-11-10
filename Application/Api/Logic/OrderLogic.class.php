<?php
namespace Api\Logic;

/**
 * Class OrderLogic
 * @package Api\Logic
 */
class OrderLogic extends BaseLogic{

    //初始化
    public function _initialize(){
        parent::_initialize();
    }
    /**
     *  @param array $request
     * 单个商品下单
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户id：m_id
     * 商品id：goods_id
     * 商品属性：attr_id_group 逗号隔开， 如果商品没有属性，则为空
     * 购买数量：num
     * 地址id：address_id
     * 买家留言:leave_msg 如果没有留言 则为空
     */
    public function addOneOrder($request = array()){
        //用户ID不能为空
        if(empty($request['m_id'])){
            apiResponse('error','用户ID不能为空');
        }
        //商品ID不能为空
        if(empty($request['goods_id'])){
            apiResponse('error','商品ID不能为空');
        }
        //购买数量不能为空
        if(empty($request['num'])){
            apiResponse('error','请填写购买数量');
        }
        //地址信息不能为空
        if(empty($request['address_id'])){
            apiResponse('error','请填写地址信息');
        }
        //商品属性不能为空
        if(empty($request['attr_id_group'])){
            apiResponse('error','商品属性不能为空');
        }

        //检查参数的数据库中对应的信息是否正确
        $where['id']     = $request['address_id'];
        $where['status'] = array('neq',9);
        $address_info    = M('Address')->where($where)->find();
        if(empty($address_info)){
            apiResponse('error','地址信息有误');
        }
        $address_info['province_id'] = M('Region') ->where(array('id'=>$address_info['province_id'])) ->getField('region_name');
        $address_info['city_id']     = M('Region') ->where(array('id'=>$address_info['city_id']))     ->getField('region_name');
        $address_info['area_id']     = M('Region') ->where(array('id'=>$address_info['area_id']))     ->getField('region_name');

        //查询商品是否存在
        $goods_info = M('Goods')->where(array('id'=>$request['goods_id']))->find();
        if(empty($goods_info)){
            apiResponse('error','商品不存在');
        }

        //如果是商品自定义属性的商品，则需要判断产品信息是否存在
        unset($where);
        $where['attr_key_group'] = $request['attr_id_group'];
        $where['goods_id']       = $request['goods_id'];
        $where['status']         = array('neq',9);
        $goods_product_info = M('GoodsProduct')->where($where)->find();
        if(empty($goods_product_info)){
            apiResponse('error','商品属性错误');
        }

        //通过商品信息获取商家id ，查询商家是否存在
        unset($where);
        $where['id']     = $goods_info['merchant_id'];
        $where['status'] = array('eq',1);
        $merchant_info = M('Merchant')->where($where)->find();
        if(!$merchant_info){
            apiResponse('error','商家不存在或已被封店');
        }

        //订单总表下单
        $order_price = $goods_product_info['cn_price']?$goods_product_info['cn_price']:'0.00';
        $trade_price = $goods_product_info['wholesale_prices']?$goods_product_info['wholesale_prices']:'0.00';
        $order_total_sn = 'a'.time().rand(1000,9999);
        $data['m_id']              = $request['m_id'];
        $data['address_id']        = $request['address_id'];
        $data['order_total_sn']    = $order_total_sn;
        $data['order_total_price'] = $order_price * $request['num'] + $goods_info['cn_delivery_cost'];
        $data['create_time']       = time();
        $order_group_res = M('OrderGroup')->data($data)->add();
        if(empty($order_group_res)){
            apiResponse('error','下单失败');
        }

        //订单分表下单
        unset($data);
        $data['m_id']        = $request['m_id'];
        $data['merchant_id'] = $goods_info['merchant_id'];
        $data['order_g_id']  = $order_group_res;
        $data['address']     = serialize($address_info);
        $data['order_sn']    = time().rand(00001,99999);
        $data['trade_price'] = $goods_product_info['wholesale_prices'] * $request['num'];
        $data['submit_order_time'] = time();

        //序列化商品信息
        $attr_id_arr = explode(',',$request['attr_id_group']);

        $con = array();
        foreach($attr_id_arr as $k =>$v){
            $con[] = $v;
        }
        sort($con);
        $attr_id_arr = $con;
        $attr_con_name = '';
        foreach($attr_id_arr as $k =>$v){
            $attr_content = M('AttributeContent') ->where(array('id'=>$v)) ->find();
            $attr = M('Attribute') ->where(array('id'=>$attr_content['attr_id'])) ->find();
            $attr_con_name = $attr_con_name.' '.$attr['cn_attr_name'].':'.$attr_content['cn_attr_con_name'].'';
        }

        $attr_con_name = trim($attr_con_name);
        $goods_info_serialization['goods'][0]['product']['attr_con_name'] =$attr_con_name;
        $goods_info_serialization['goods'][0]['num'] = $request['num'];
        $goods_info_serialization['goods'][0]['goodsDetail']['id'] = $goods_info['id'];
        $goods_info_serialization['goods'][0]['goodsDetail']['goods_name'] = $goods_info['cn_goods_name'];
        $goods_info_serialization['goods'][0]['goodsDetail']['price'] = $order_price;
        $goods_info_serialization['goods'][0]['goodsDetail']['trade_price'] = $trade_price;
        $goods_pic = explode(',',$goods_info['goods_pic']);
        $goods_info_serialization['goods'][0]['goodsDetail']['goods_pic'] = M('File')->where(array('id'=>$goods_pic[0]))->getField('path');
        $goods_info_serialization['goods'][0]['price'] = $order_price * $request['num'] + $goods_info['cn_delivery_cost'];
        $goods_info_serialization['goods'][0]['delivery_cost'] = $goods_info['cn_delivery_cost'];
        $data['goods_info_serialization'] = serialize($goods_info_serialization);
        if($request['leave_msg']){
            $data['leave_msg'] = $request['leave_msg'];
        }
        $data['totalprice'] = $order_price * $request['num'] + $goods_info['cn_delivery_cost'];
        $add_order_res = M('Order')->data($data)->add();
        if($add_order_res){
            $result['order_list_sn'] = $order_total_sn;
            apiResponse('success','下单成功',$result);
        }else{
            apiResponse('error','下单失败');
        }
    }
    /**
     * @param array $request
     * 购物车提交订单
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户ID       m_id
     * 用户地址ID   address_id
     * 购物车信息   json串：[{"cart_id":"4"},{"cart_id":"10"}]
     * 买家留言     message_json    [{"merchant_id":"2","message":"这是第一个"},{"merchant_id":"1","message":"这是第二个"}]
     */
    public function cartOrder($request = array()){
        //用户ID不能为空
        if(empty($request['m_id'])) {
            apiResponse('error', '用户ID不能为空');
        }
        //用户地址ID不能为空
        if (empty($request['address_id'])) {
            apiResponse('error', '地址ID不能为空');
        }
        //购物车ID不能为空   为一个json串
        if (empty($_POST['cart_json'])) {
            apiResponse('error','请选择下单的商品');
        }
        $cart_list = json_decode($_POST['cart_json'],true);

        //将获得的json串转换一下
        if(empty($cart_list)) {
            apiResponse('error', '商品参数选择错误');
        }

        if(empty($_POST['message_json'])) {
            apiResponse('error', '留言参数不能为空');
        }
        $message_list = json_decode($_POST['message_json'],true);

        if(!$message_list){
            apiResponse('error', 'JSON错误');
        }
        //查询送货地点是否正确
        $where['id'] = $request['address_id'];
        $where['status'] = array('neq', 9);
        $address = M('Address')->where($where)->find();
        if(empty($address)){
            apiResponse('error', '地址信息有误');
        }
        $address['province_id'] = M('Region') ->where(array('id'=>$address['province_id'])) ->getField('region_name');
        $address['city_id']     = M('Region') ->where(array('id'=>$address['city_id']))     ->getField('region_name');
        $address['area_id']     = M('Region') ->where(array('id'=>$address['area_id']))     ->getField('region_name');

        $id = array();
        $total_price = 0;
        $list = array();
        //判断选择结果的正确性
        foreach($cart_list as $k =>$v){
            $id[] = $v['cart_id'];
            unset($where);
            $where['id'] = $v['cart_id'];
            $where['status'] = array('neq',9);
            $cart = M('Cart') ->where($where) ->find();
            if(!$cart){
                continue;
            }
            $list[$k] = $cart;
            $merchant_list[] = $cart['merchant_id'];
            unset($where);
            $where['attr_key_group'] = $cart['product'];
            $where['goods_id']       = $cart['g_id'];
            $where['status']         = array('neq',9);
            $price = M('GoodsProduct') ->where($where) ->find();
            $priceer = $price['cn_price']?$price['cn_price']:'0.00';
            $list[$k]['price'] = $priceer;
            $total_price = $total_price + $priceer*$cart['num'];
        }
        $order_total_sn = 'a'.time().rand(1000,9999);
        $data['m_id']              = $request['m_id'];
        $data['address_id']        = $request['address_id'];
        $data['order_total_sn']    = $order_total_sn;
        $data['order_total_price'] = $total_price;
        $data['create_time']       = time();
        $order_group_id = M('OrderGroup')->data($data)->add();
        if(empty($order_group_id)){
            apiResponse('error','下单失败');
        }
        $merchant_id = array_unique($merchant_list);
        if(!$merchant_id){
            apiResponse('error','购物车信息有误');
        }
        foreach($merchant_id as $k =>$v){
            $order_sn = time().rand(10000,99999);
            $goods_info_serialization = array();
            $total = 0;
            $tradeprice = 0;
            foreach($cart_list as $key => $val){
                unset($where);
                $where['id'] = $val['cart_id'];
                $where['merchant_id'] = $v;
                $where['status'] = array('neq',9);
                $cart = M('Cart') ->where($where) ->field('id as cart_id, m_id, g_id, product, num') ->find();
                if(!$cart){
                    continue;
                }
                $price = M('GoodsProduct') ->where(array('goods_id'=>$cart['g_id'],'attr_key_group'=>$cart['product'],'status'=>array('neq',9)))
                    ->field('id as product_id, wholesale_prices, cn_price as price') ->find();

                $goods_info = M('Goods') ->where(array('id'=>$cart['g_id']))
                    ->field('id as goods_id, cn_goods_name as goods_name, goods_pic,cn_delivery_cost') ->find();
                if(!$goods_info){
                    continue;
                }
                $goods_info_serialization['goods'][$key]['goodsDetail']['id'] = $goods_info['goods_id'];
                $goods_info_serialization['goods'][$key]['goodsDetail']['goods_name'] = $goods_info['goods_name'];
                $goods_pic = explode(',',$goods_info['goods_pic']);
                $path = M('File') ->where(array('id'=>$goods_pic[0])) ->getField('path');
                $goods_info_serialization['goods'][$key]['goodsDetail']['goods_pic'] = $path;
                $goods_info_serialization['goods'][$key]['goodsDetail']['price'] = $price['price']?$price['price']:'0.00';
                $goods_info_serialization['goods'][$key]['goodsDetail']['trade_price'] = $price['wholesale_prices']?$price['wholesale_prices']:'0.00';
                $goods_info_serialization['goods'][$key]['num'] = $cart['num'];
                $goods_info_serialization['goods'][$key]['price'] = $price['price']?$price['price']*$cart['num'] + $goods_info['cn_delivery_cost']:'0.00';
                $goods_info_serialization['goods'][$key]['delivery_cost'] = $goods_info['cn_delivery_cost']?$goods_info['cn_delivery_cost']:'0.00';
                $product = explode(',',$cart['product']);
                $attr_con_name = '';
                foreach($product as $kk =>$vv){
                    $attr_content = M('AttributeContent')->where(array('id'=>$vv))->find();
                    $attr = M('Attribute')->where(array('id'=>$attr_content['attr_id']))->find();
                    $attr_con_name = $attr_con_name.' '.$attr['cn_attr_name'].':'.$attr_content['cn_attr_con_name'].'';
                }
                $attr_con_name = trim($attr_con_name);
                $goods_info_serialization['goods'][$key]['product']['attr_con_name'] = $attr_con_name;
                $total = $total + $goods_info_serialization['goods'][$key]['price'];
                $tradeprice = $tradeprice + $price['wholesale_prices'] * $cart['num'];
            }
            unset($data);
            $data['m_id']        = $request['m_id'];
            $data['merchant_id'] = $v;
            $data['order_g_id']  = $order_group_id;
            $data['address']     = serialize($address);
            $data['order_sn']    = $order_sn;
            $data['submit_order_time'] = time();
            $data['totalprice']  = $total;
            $data['trade_price'] = $tradeprice;
            foreach($message_list as $k1 =>$v1){
                if($v1['merchant_id']==$v){
                    $data['leave_msg'] = $v1['message'];
                }
            }
            $data['goods_info_serialization'] = serialize($goods_info_serialization);
            $add_order_res = M('Order')->data($data)->add();
        }
        unset($where);
        unset($data);
        $where['id'] = array('in',$id);
        $data['status'] = 9;
        $data['update_time'] = time();
        M('Cart')->where($where) ->data($data) ->save();

        $result['order_list_sn'] = $order_total_sn;
        apiResponse('success','下单成功',$result);
    }
    /**
     * 取消交易
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户ID  m_id
     * 订单ID  order_id
     */
    public function cancellationOrder($request = array()){
        //用户ID不能为空
        if(empty($request['m_id'])){
            apiResponse('error','用户id不能为空');
        }
        //订单ID不能为空
        if(empty($request['order_id'])){
            apiResponse('error','订单id不能为空');
        }
        //查询订单信息
        $where['id'] = $request['order_id'];
        $where['m_id'] = $request['m_id'];
        $result = M('Order') ->where($where) ->find();
        if(empty($result)){
            apiResponse('error','未查询到订单信息');
        }
        //取消订单
        $data['status'] = 6;
        $data['update_time'] = time();
        $result_data = M('Order') ->where($where) ->data($data) ->save();
        if(!$result_data){
            apiResponse('error','取消订单失败');
        }
        unset($where);
        unset($data);
        if($result['status'] == 1){
            $where['id'] = $request['m_id'];
            $data['balance'] = $result['totalprice'];
            $member = M('Member') ->where($where) ->setInc('balance',$data['balance']);
            if(!$member){
                apiResponse('error','操作失败');
            }
        }

        apiResponse('success','取消订单成功');
    }
    /**
     * 确认收货
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户ID  m_id
     * 订单ID  order_id
     */
    public function confirmOrder($request = array()){
        //用户ID不能为空
        if(empty($request['m_id'])){
            apiResponse('error','用户id不能为空');
        }
        //订单ID不能为空
        if(empty($request['order_id'])){
            apiResponse('error','订单id不能为空');
        }
        //查询订单信息
        $where['id'] = $request['order_id'];
        $where['m_id'] = $request['m_id'];
        $result = M('Order') ->where($where) -> field('id as order_id, merchant_id, goods_info_serialization, totalprice, trade_price') ->find();
        if(empty($result)){
            apiResponse('error','未查询到订单信息');
        }
        //改变订单状态
        $data['status'] = 4;
        $data['update_time'] = time();
        $data['confirm_receipts_time'] = time();
        $result_data = M('Order') ->where($where) ->data($data) ->save();
        if(empty($result_data)){
            apiResponse('error','确认收货失败');
        }
        //对该商品销量加1
        $goods_info = unserialize($result['goods_info_serialization']);
        foreach ($goods_info['goods'] as $k => $v) {
            unset($where);
            unset($data);
            $where['id'] = $v['goodsDetail']['id'];
            M('Goods') ->where($where) ->setInc('sales',$v['num']);
        }
        if(empty($goods_info)){
            apiResponse('error','确认收货失败');
        }
        unset($where);
        unset($data);
        $where['id'] = $result['merchant_id'];
        $where['status'] = array('neq',9);
        $merchant = M('Merchant') ->where($where) ->find();
        $data['balance'] = $merchant['balance'] + $result['totalprice'];
        $res = M('Merchant') ->where($where) ->data($data) ->save();
        if(!$res){
            apiResponse('error','确认收货失败');
        }

        //查询后台返利系统   获取返利条件
        unset($where);
        unset($data);
        $divide = M('Divide') ->find();
        //查询我的上级
        $relation = M('Relation') ->where(array('m_id'=>$request['m_id'])) ->field('parent_id,m_id') ->find();
        if($relation['parent_id'] != 0){
            //我的D级存在  写入付款Contribution
            $data['parent_id']   = $relation['parent_id'];
            $data['m_id']        = $relation['m_id'];
            $data['create_time'] = time();
            $data['money']       = $result['totalprice'];
            $data['result_money'] = ($result['totalprice'] - $result['trade_price']) * (1 - $divide['divide_p']) * $divide['divide_d'];
            $Contribution = M('Contribution') ->add($data);
            //查询我的c级
            unset($where);
            unset($data);
            $parent = M('Relation') ->where(array('m_id'=>$relation['parent_id'])) ->field('parent_id,m_id') ->find();
            if($parent['parent_id'] != 0){
                //我的c级存在  写入   消费总数不写
                $data['parent_id'] = $parent['parent_id'];
                $data['m_id']      = $relation['m_id'];
                $data['create_time']  = time();
                $data['result_money'] = ($result['totalprice'] - $result['trade_price']) * (1 - $divide['divide_p']) * $divide['divide_c'];
                $Contribution = M('Contribution') ->add($data);
                unset($where);
                unset($data);
                //查询我的b级    存在就写入
                $parent2 = M('Relation') ->where(array('m_id'=>$parent['parent_id'])) ->field('parent_id,m_id') ->find();
                if($parent2['parent_id'] != 0){
                    $data['parent_id'] = $parent2['parent_id'];
                    $data['m_id']      = $relation['m_id'];
                    $data['create_time'] = time();
                    $data['result_money'] = ($result['totalprice'] - $result['trade_price']) * (1 - $divide['divide_p']) * $divide['divide_b'];
                    $Contribution = M('Contribution') ->add($data);
                    unset($where);
                    unset($data);
                    //查询我的A级  存在就写入  并返回所有数据
                    $parent3 = M('Relation') ->where(array('m_id'=>$parent2['parent_id'])) ->field('parent_id,m_id') ->find();
                    if($parent3['parent_id']){
                        $data['account_platform_balance_b'] = ($result['totalprice'] - $result['trade_price']) * $divide['divide_p'] + $divide['account_platform_balance_b'];
                        $res = M('Divide') ->where(array('id'=>1)) ->data($data) ->save();
                        unset($where);
                        unset($data);
                        $data['parent_id'] = $parent3['parent_id'];
                        $data['m_id']      = $relation['m_id'];
                        $data['create_time'] = time();
                        $data['result_money'] = ($result['totalprice'] - $result['trade_price']) * (1 - $divide['divide_p']) * $divide['divide_a'];
                        $Contribution = M('Contribution') ->add($data);

                        unset($where);
                        unset($data);
                        $where['id'] = $parent3['parent_id'];
                        $data['balance'] = ($result['totalprice'] - $result['trade_price']) * (1 - $divide['divide_p']) * $divide['divide_a'];
                        if($data['balance'] != 0){
                            $member = M('Member') ->where($where) ->setInc('balance',$data['balance']);
                        }
                        $platform_b = $data['balance'];
                        unset($data);
                        $date['type']     = 2;
                        $date['order_id'] = $request['order_id'];
                        $date['m_id']     = $request['m_id'];
                        $date['money']    = $platform_b ;
                        $date['create_time'] = time();
                        $result_b = M('Datasheet') ->add($date);
                    }else{
                        //我的a级不存在  返回所有数据
                        $data['account_platform_balance_a'] = ($result['totalprice'] - $result['trade_price']) * (1 - $divide['divide_p']) * $divide['divide_a'] + $divide['account_platform_balance_a'];
                        $data['account_platform_balance_b'] = ($result['totalprice'] - $result['trade_price']) * $divide['divide_p'] + $divide['account_platform_balance_b'];
                        $res = M('Divide') ->where(array('id'=>1)) ->data($data) ->save();
                        $platform_a = ($result['totalprice'] - $result['trade_price']) * (1 - $divide['divide_p']) * $divide['divide_a'];
                        $platform_b = ($result['totalprice'] - $result['trade_price']) * $divide['divide_p'];
                        unset($data);
                        $data['type']     = 1;
                        $data['order_id'] = $request['order_id'];
                        $data['m_id']     = $request['m_id'];
                        $data['money']    = $platform_a;
                        $data['create_time'] = time();
                        $result_a = M('Datasheet') ->add($data);
                        $date['type']     = 2;
                        $date['order_id'] = $request['order_id'];
                        $date['m_id']     = $request['m_id'];
                        $date['money']    = $platform_b ;
                        $date['create_time'] = time();
                        $result_b = M('Datasheet') ->add($date);
                    }
                    //把我的b级的钱打入账户余额
                    unset($where);
                    unset($data);
                    $where['id'] = $parent2['parent_id'];
                    $data['balance'] = ($result['totalprice'] - $result['trade_price']) * (1 - $divide['divide_p']) * ($divide['divide_b']);
                    if($data['balance'] != 0){
                        $member = M('Member') ->where($where) ->setInc('balance',$data['balance']);
                    }
                }else{
                    //我的b级不存在  返回所有数据
                    $data['account_platform_balance_a'] = ($result['totalprice'] - $result['trade_price']) * (1 - $divide['divide_p']) * ($divide['divide_a'] + $divide['divide_b']) + $divide['account_platform_balance_a'];
                    $data['account_platform_balance_b'] = ($result['totalprice'] - $result['trade_price']) * $divide['divide_p'] + $divide['account_platform_balance_b'];
                    $res = M('Divide') ->where(array('id'=>1)) ->data($data) ->save();
                    $platform_a = ($result['totalprice'] - $result['trade_price']) * (1 - $divide['divide_p']) * ($divide['divide_a'] + $divide['divide_b']);
                    $platform_b = ($result['totalprice'] - $result['trade_price']) * $divide['divide_p'];
                    unset($data);
                    $data['type']     = 1;
                    $data['order_id'] = $request['order_id'];
                    $data['m_id']     = $request['m_id'];
                    $data['money']    = $platform_a;
                    $data['create_time'] = time();
                    $result_a = M('Datasheet') ->add($data);
                    $date['type']     = 2;
                    $date['order_id'] = $request['order_id'];
                    $date['m_id']     = $request['m_id'];
                    $date['money']    = $platform_b ;
                    $date['create_time'] = time();
                    $result_b = M('Datasheet') ->add($date);
                }
                //把优惠打入到c级余额
                unset($where);
                unset($data);
                $where['id'] = $parent['parent_id'];
                $data['balance'] = ($result['totalprice'] - $result['trade_price']) * (1 - $divide['divide_p']) * ($divide['divide_c']);
                if($data['balance'] != 0){
                    $member = M('Member') ->where($where) ->setInc('balance',$data['balance']);
                }
            }else{
                //我的D级不存在  返回所有数据
                $data['account_platform_balance_a'] = ($result['totalprice'] - $result['trade_price']) * (1 - $divide['divide_p']) * ($divide['divide_a'] + $divide['divide_c'] + $divide['divide_b']) + $divide['account_platform_balance_a'];
                $data['account_platform_balance_b'] = ($result['totalprice'] - $result['trade_price']) * $divide['divide_p'] + $divide['account_platform_balance_b'];
                $data['update_time'] = time();
                $res = M('Divide') ->where(array('id'=>1)) -> data($data) ->save();
                $platform_a = ($result['totalprice'] - $result['trade_price']) * (1 - $divide['divide_p']) * ($divide['divide_a'] + $divide['divide_c'] + $divide['divide_b']);
                $platform_b = ($result['totalprice'] - $result['trade_price']) * $divide['divide_p'];
                unset($data);
                $data['type']     = 1;
                $data['order_id'] = $request['order_id'];
                $data['m_id']     = $request['m_id'];
                $data['money']    = $platform_a;
                $data['create_time'] = time();
                $result_a = M('Datasheet') ->add($data);
                $date['type']     = 2;
                $date['order_id'] = $request['order_id'];
                $date['m_id']     = $request['m_id'];
                $date['money']    = $platform_b ;
                $date['create_time'] = time();
                $result_b = M('Datasheet') ->add($date);
            }
            //我的D级存在  把获利写入余额
            unset($where);
            unset($data);
            $where['id'] = $relation['parent_id'];
            $data['balance'] = ($result['totalprice'] - $result['trade_price']) * (1 - $divide['divide_p']) * ($divide['divide_d']);
            if($data['balance'] != 0){
                $member = M('Member') ->where($where) ->setInc('balance',$data['balance']);
            }
        }else{
            //我的D级不存在  返回全部
            $data['account_platform_balance_a'] = ($result['totalprice'] - $result['trade_price']) * (1 - $divide['divide_p']) * (1 - $divide['divide_m']) + $divide['account_platform_balance_a'];
            $data['account_platform_balance_b'] = ($result['totalprice'] - $result['trade_price']) * $divide['divide_p'] + $divide['account_platform_balance_b'];
            $data['update_time'] = time();
            $res = M('Divide') ->where(array('id'=>1)) ->data($data) ->save();
            $platform_a = ($result['totalprice'] - $result['trade_price']) * (1 - $divide['divide_p']) * (1 - $divide['divide_m']);
            $platform_b = ($result['totalprice'] - $result['trade_price']) * $divide['divide_p'];
            unset($data);
            $data['type']     = 1;
            $data['order_id'] = $request['order_id'];
            $data['m_id']     = $request['m_id'];
            $data['money']    = $platform_a;
            $data['create_time'] = time();
            $result_a = M('Datasheet') ->add($data);
            $date['type']     = 2;
            $date['order_id'] = $request['order_id'];
            $date['m_id']     = $request['m_id'];
            $date['money']    = $platform_b;
            $date['create_time'] = time();
            $result_b = M('Datasheet') ->add($date);
        }
        //本人获利写入余额
        unset($where);
        unset($data);
        $where['id'] = $request['m_id'];
        $data['balance'] = ($result['totalprice'] - $result['trade_price']) * (1 - $divide['divide_p']) * ($divide['divide_m']);
        if($data['balance'] != 0){
            $member = M('Member') ->where($where) ->setInc('balance',$data['balance']);
        }
        apiResponse('success','确认收货成功');
    }
    /**
     * 删除订单
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户ID  m_id
     * 订单ID  order_id
     */
    public function deleteOrder($request = array()){
        //用户ID不能为空
        if(empty($request['m_id'])){
            apiResponse('error','用户id不能为空');
        }
        //订单ID不能为空
        if(empty($request['order_id'])){
            apiResponse('error','订单id不能为空');
        }
        //查询订单信息
        $where['id'] = $request['order_id'];
        $where['m_id'] = $request['m_id'];
        $result = M('Order') ->where($where) ->find();

        if(empty($result)){
            apiResponse('error','未查询到订单信息');
        }
        //删除订单
        $data['remove_status'] = 1;
        $data['update_time'] = time();
        $result_data = M('Order') ->where($where) ->data($data) ->save();
        if($result_data){
            apiResponse('success','删除成功');
        }
        apiResponse('error','删除失败');
    }
    /**
     * 用户订单列表
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户ID  m_id
     * 类型  type  0待支付，2待发货，3待收货，4待评价，8全部
     * 分页参数：p
     */
    public function orderList($request = array()){
        //用户ID不能为空
        if(empty($request['m_id'])){
            apiResponse('error','用户id不能为空');
        }
        //类型值只能在0-4之间
        if($request['type']!=0 && $request['type']!=2 && $request['type']!=3 && $request['type']!=4 && $request['type']!=8 ){
            apiResponse('error','类型错误');
        }
        //分页参数不能为空
        if(empty($request['p'])){
            apiResponse('error','分页参数错误');
        }

        //统计未读消息数量
        $un_read_num = $this->getUnReadMessageNum($request['m_id']);
        $result['un_read_num'] = '' . $un_read_num;

        //根据用户ID  查询订单
        $where['m_id'] = $request['m_id'];
        if($request['type']!=8){
            $where['status'] = $request['type'];
        }
        $where['remove_status'] = array('eq',0);
        $order = M('Order') ->where($where)
            -> field('id as order_id, order_sn, merchant_id, goods_info_serialization, totalprice, delivery_code, delivery_sn, status, trade_price')
            -> order('submit_order_time desc, id desc')->page($request['p'].',10') ->select();
        if(empty($order)){
            $result['order'] = array();
            apiResponse('success','您还没有任何订单',$result);
        }
        //查询后台标识
        $divide = M('Divide') ->where(array('id'=>1)) ->field('id as divide_id, divide_m, divide_p') ->find();
        if(!$divide){
            $divide['divide_m'] = 0;
        }


        //查询商家信息以及订单信息
        foreach($order as $k =>$v) {
            unset($where);
            $where['id'] = $v['merchant_id'];
            $merchant_info = M('Merchant')->where($where)->field('id as merchant_id, merchant_name')->find();

            $order[$k]['merchant_name'] = $merchant_info['merchant_name'] ? $merchant_info['merchant_name'] : '';
            $order[$k]['sale_status'] = M('AfterSale') -> where(array('order_id'=>$v['order_id'])) -> getField('status');
            $order[$k]['sale_status'] = $order[$k]['sale_status'] ? $order[$k]['sale_status'] : '';
            $goods_list = unserialize($v['goods_info_serialization']);
            $goods = array();
            $goods_num = 0;
            foreach ($goods_list['goods'] as $key => $value){
                $goods[$key]['id']            = $value['goodsDetail']['id'];
                $goods[$key]['goods_pic']     = $value['goodsDetail']['goods_pic'] ? C('API_URL') . $value['goodsDetail']['goods_pic'] : '';
                $goods[$key]['goods_name']    = $value['goodsDetail']['goods_name'] ? $value['goodsDetail']['goods_name'] : '';
                $goods[$key]['price']         = $value['goodsDetail']['price'] ? $value['goodsDetail']['price'] : '0.00';
                $goods[$key]['num']           = $value['num'] ? $value['num'] : '0';
                $goods[$key]['attr_con_name'] = $value['product']['attr_con_name'] ? $value['product']['attr_con_name'] : '';
                $goods[$key]['attr_con_name'] = $goods[$key]['attr_con_name'] ? $goods[$key]['attr_con_name'] : '';
                $goods[$key]['trade_price']   = $value['goodsDetail']['trade_price']?$value['goodsDetail']['trade_price']:'0.00';
                $goods[$key]['return_price']  = ($goods[$key]['price'] - $goods[$key]['trade_price']) * (1 - $divide['divide_p']) * $divide['divide_m'];
                $goods[$key]['return_price']  = $goods[$key]['return_price']?''.$goods[$key]['return_price']:'0.00';
                $goods_num                    = $goods_num + $goods[$key]['num'];
            }
            $goods = array_values($goods);
            $order[$k]['goods_list'] = $goods;
            $order[$k]['goods_num'] = ''.$goods_num;
            unset($order[$k]['goods_info_serialization']);

            unset($where);
            if($v['delivery_code'] != ''){
                $where['delivery_code'] = $v['delivery_code'];
                $order[$k]['delivery'] = M('DeliveryCompany') ->where($where) ->field('id as delivery_id, delivery_code, company_name') ->find();
                $order[$k]['delivery']['key'] = '6c8d76a022fca426';
            }else{
                $order[$k]['delivery'] = array();
            }

        }
        $result['order'] = $order;
        apiResponse('success','',$result);
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
    public function Distribution($request = array()){
        //判断语言版本
        $this -> checkLanguage($request['language']);
        if(empty($request['merchant_id'])){
            $message = $request['language'] == 'cn'?'商家id不能为空':'The merchant_id ID can not be empty .';
            apiResponse('error',$message);
        }
        //订单ID不能为空
        if(empty($request['order_id'])){
            $message = $request['language'] == 'cn'?'订单id不能为空':'The order_id ID can not be empty .';
            apiResponse('error',$message);
        }
        //订单类型不能为空
        if(empty($request['type'])){
            $message = $request['language'] == 'cn'?'类型不能为空':'The type can not be empty .';
            apiResponse('error',$message);
        }
        //订单类型不能为空
        if($request['type'] != 1&&$request['type'] != 2){
            $message = $request['language'] == 'cn'?'类型错误':'The type is error .';
            apiResponse('error',$message);
        }
        //联系人姓名不能为空
        if(empty($request['delivery_people'])){
            $message = $request['language'] == 'cn'?'联系人姓名不能为空':'The delivery people name can not be empty .';
            apiResponse('error',$message);
        }
        //联系人电话不能为空
        if(empty($request['delivery_phone'])){
            $message = $request['language'] == 'cn'?'联系人电话为空':'The delivery phone can not be empty .';
            apiResponse('error',$message);
        }
        //联系人备注不能为空
        if(!empty($request['delivery_remark'])){
            $data['delivery_remark'] = $request['delivery_remark'];
        }else{
            $data['delivery_remark'] = '';
        }
        $where['id'] = $request['order_id'];
        $where['merchant_id'] = $request['merchant_id'];
        if($request['type'] == 1){
            $where['status'] = 0 ;
            $data['status']  = 1;
            $data['start_delivery_time'] = time();
        }else{
            $where['status'] = 1;
        }
        $where['remove_status'] = 0;
        if($request['language'] == 'cn'){
            $order_info = M('Order') ->where($where) ->find();
        }else{
            $order_info = M('Order') ->where($where) ->find();
        }
        if(empty($order_info)){
            $message = $request['language'] == 'cn'?'该订单无法添加配送':'The order can not be added distribution .';
            apiResponse('error',$message);
        }
        $data['delivery_people'] = $request['delivery_people'];
        $data['delivery_phone']  = $request['delivery_phone'];
        $data['update_time'] = time();
        $result_data = M('Order') ->where($where) ->data($data) ->save();
        if($result_data){
            $message = $request['language'] == 'cn'?'操作成功':'Add distribution success.';
            apiResponse('success',$message);
        }
        $message = $request['language'] == 'cn'?'操作失败':'Add distribution failed.';
        apiResponse('error',$message);
    }
    /**
     * 用户订单详情
     * 传递参数的方式：post
     * 需要传递的参数：
     * 语言版本：language：cn 中文  ue英文
     * 用户ID  m_id
     * 订单ID  order_id
     */
    public function userOrderDetails ($request = array()){
        //用户ID  不能为空
        if(empty($request['m_id'])){
            apiResponse('error','用户id不能为空');
        }
        if(empty($request['order_id'])){
            apiResponse('error','订单id不能为空');
        }
        //统计未读消息数量
        $un_read_num = $this->getUnReadMessageNum($request['m_id']);
        $result['un_read_num'] = '' . $un_read_num;
        //根据已有条件查询订单详情
        $where['id'] = $request['order_id'];
        $where['m_id'] = $request['m_id'];
        $where['remove_status'] = 0;
        $order_info = M('Order') ->where($where)
            -> field('id as order_id, m_id, merchant_id, address, order_sn, goods_info_serialization, submit_order_time, leave_msg, delivery_code, delivery_sn, totalprice, status')
            -> find();

        if(empty($order_info)){
            apiResponse('error','订单详情不存在');
        }
        //查询商家信息  ID  头像  商家名称  环信账号
        unset($where);
        $where['id'] = $order_info['merchant_id'];
        $where['status'] = array('neq',9);
        $merchant_info = M('Merchant') ->where($where) ->field('id as merchant_id , head_pic, merchant_name, easemob_account') ->find();

        $merchant_info['easemob_account'] = '8001';
        $merchant_info['head_pic'] = C('API_URL').'/Uploads/Member/service.png';

        if(empty($merchant_info)){
            apiResponse('success','商家信息有误');
        }

        $address    = unserialize($order_info['address']);
        $goods_info = unserialize($order_info['goods_info_serialization']);
        if(!$goods_info){
            apiResponse('success','订单详情有误');
        }
        $i = 0;
        $delivery_cost = 0;
        foreach ($goods_info['goods'] as $k => $v) {
            $goods[$i]['id'] = $v['goodsDetail']['id'];
            $goods[$i]['goods_pic'] = $v['goodsDetail']['goods_pic'] ? C('API_URL') . $v['goodsDetail']['goods_pic'] : '';
            $goods[$i]['goods_name'] = $v['goodsDetail']['goods_name'] ? $v['goodsDetail']['goods_name'] : '';
            $goods[$i]['price'] = $v['goodsDetail']['price'] ? $v['goodsDetail']['price'] : '0.00';
            $goods[$i]['num'] = $v['num'] ? $v['num'] : '0';
            $goods[$i]['attr_con_name'] = $v['product']['attr_con_name'] ? $v['product']['attr_con_name'] : '';
            $goods[$i]['delivery_cost'] = $v['delivery_cost']?$v['delivery_cost'].'':'0.00';
            $delivery_cost = $delivery_cost + $goods[$i]['delivery_cost'];
            $i = $i + 1;
        }

        //获取快递信息
        if($order_info['delivery_code'] != ''){
            $delivery_company = M('DeliveryCompany') ->where(array('delivery_code'=>$order_info['delivery_code']))->field('id as delivery_id, delivery_code, company_name') ->find();
            $result['delivery_company'] = $delivery_company;
            $result['key'] = '6c8d76a022fca426';
        }else{
            $result['delivery_company'] = '';
            $result['key'] = '';
        }

        $result['delivery_sn'] = $order_info['delivery_sn']?$order_info['delivery_sn']:'';
        $result['service_account'] = '8001';
        $result['service_pic'] = C('API_URL').'/Uploads/Member/service.png';
        //开始传入数据
        $order['sale_status']     =  M('AfterSale') -> where(array('order_id'=>$order_info['order_id'])) -> getField('status');
        $order['sale_status'] = $order['sale_status'] ? $order['sale_status'] : '';
        $order['goods_list']      = $goods;
        $order['address']         = $address;
        $order['status']          = $order_info['status'];
        $order['totalprice']      = $order_info['totalprice'];
        $order['delivery_cost']   = $delivery_cost?$delivery_cost.'':'0.00';
        $order['order_sn']        = $order_info['order_sn'];
        $order['order_id']        = $order_info['order_id'];
        $order['leave_msg']       = $order_info['leave_msg'];
        $order['submit_order_time'] = date('Y-m-d H:i',$order_info['submit_order_time']);
        $order['merchant']        = $merchant_info;
        $result['order']          = $order;
        unset($order);
        if(empty($result['order'])){
            $result['order'] = array();
            apiResponse('success','订单详情有误',$result);
        }
        apiResponse('success','',$result);
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
    public function merchantOrderList($request = array()){
        //判断语言版本
        $this -> checkLanguage($request['language']);
        //用户ID不能为空
        if(empty($request['m_id'])){
            $message = $request['language'] == 'cn'?'用户id不能为空':'The user ID can not be empty .';
            apiResponse('error',$message);
        }
        //商家ID不能为空
        if(empty($request['merchant_id'])){
            $message = $request['language'] == 'cn'?'商家id不能为空':'The merchant ID can not be empty .';
            apiResponse('error',$message);
        }
        //分页参数不能为空
        if(empty($request['p'])){
            $message = $request['language'] == 'cn'?'分页参数不能为空.':'The page parameter can not be empty .';
            apiResponse('error',$message);
        }
        //类型参数不能为空
        if(!isset($request['type'])){
            $message = $request['language'] == 'cn'?'类型参数不能为空.':'The type parameter can not be empty .';
            apiResponse('error',$message);
        }
        //类型值只能在0-4之间
        if($request['type']!=0 && $request['type']!=1 && $request['type']!=2 && $request['type']!=3 && $request['type']!=4 ){
            $message = $request['language'] == 'cn'?'类型参数有误.':'The type parameter is error .';
            apiResponse('error',$message);
        }
        //统计未读消息数量
        $result['m_id'] = $request['m_id'];
        $un_read_num = $this->getUnReadMessageNum($result['m_id']);
        $result['un_read_num'] = '' . $un_read_num;

        //获取商家信息
        $where['id'] = $request['merchant_id'];
        $where['status'] = array('neq',9);
        if($request['language'] == 'cn'){
            $merchant_info =M('Merchant') ->where($where) ->field('id as merchant_id,is_integrity,cn_merchant_name as merchant_name') ->find();
        }else{
            $merchant_info =M('Merchant') ->where($where) ->field('id as merchant_id,is_integrity,ue_merchant_name as merchant_name') ->find();
        }
        if(empty($merchant_info)){
            $result['order'] = array();
            $message = $request['language'] == 'cn'?'商家信息有误.':'The merchant infomation is error .';
            apiResponse('success',$message,$result);
        }

        //根据所得信息查询商家订单列表
        unset($where);
        $where['merchant_id'] = $request['merchant_id'];
        if($request['type']!=4){
            $where['status'] = $request['type'];
        }
        $where['remove_status'] = 0;
        if($request['language'] == 'cn'){
            $where['version'] = 1;
            $order_list = M('Order') ->where($where) ->field('id as order_id,merchant_id, m_id, order_sn, goods_info_serialization,delivery_people,delivery_phone,delivery_remark,totalprice,status')->order('submit_order_time desc, id desc')->page($request['p'].',10') ->select();
        }else{
            $where['version'] = 2;
            $order_list = M('Order') ->where($where) ->field('id as order_id,merchant_id, m_id, order_sn, goods_info_serialization,delivery_people,delivery_phone,delivery_remark,totalprice,status')->order('submit_order_time desc, id desc')->page($request['p'].',10') ->select();
        }
        if(empty($order_list)){
            $result['order'] = array();
            $message = $request['language'] == 'cn'?'您的订单记录为空.':'Your order record is empty .';
            apiResponse('success',$message,$result);
        }
        //根据订单列表获取商品信息
        foreach($order_list as $k =>$v){
            $member[$k] = M('Member') ->where(array('id'=>$v['m_id'])) -> field('id as m_id, nickname') ->find();
            $goods_list = unserialize($v['goods_info_serialization']);
            $goods = array();
            $i = 0;
            $goods_num = 0;
            foreach ($goods_list['goods'] as $key => $value) {
                $goods[$i]['goods_id']  = $value['goodsDetail']['id'];
                $goods[$i]['goods_pic'] = $value['goodsDetail']['goods_pic'] ? C('API_URL') . $value['goodsDetail']['goods_pic'] : '';
                $goods[$i]['goods_name'] = $value['goodsDetail']['goods_name'] ? $value['goodsDetail']['goods_name'] : '';
                $goods[$i]['price'] = $value['goodsDetail']['price'] ? $value['goodsDetail']['price'] : '0.00';
                $goods[$i]['num'] = $value['num'] ? $value['num'] : '0';
                $goods[$i]['is_integrity'] = $merchant_info['is_integrity'] ? $merchant_info['is_integrity'] : '0';
                $goods[$i]['attr_con_name'] = $request['language'] == 'cn' ? $value['product']['cn_attr_con_name'] : $value['product']['eu_attr_con_name'];
                $goods_num = $goods_num+$goods[$i]['num'];
                $i = $i + 1;
            }
            $order_list[$k]['m_id']          = $member[$k]['m_id'];
            $order_list[$k]['member_nickname'] = $member[$k]['nickname'];
            $order_list[$k]['is_integrity']  = $merchant_info['is_integrity'];
            $order_list[$k]['goods_list']    = $goods;
            $order_list[$k]['goods_num']     = ''.$goods_num;
            unset($order_list[$k]['goods_info_serialization']);
        }
        $result['order'] = $order_list;
        $message = $request['language'] == 'cn'?'请求成功':'Request success .';
        apiResponse('success',$message,$result);
    }
    /**
     * 商家订单详情
     * 传递参数的方式：post
     * 需要传递的参数：
     * 语言版本：language：cn 中文  ue英文
     * 用户ID  m_id
     * 商家ID  merchant_id
     * 订单ID  order_id
     * 用户环信账号  member
     */
    public function merchantOrderDetails ($request = array()){
        //用户ID不能为空
        if(empty($request['m_id'])){
            apiResponse('error','用户id不能为空');
        }
        //订单ID不能为空
        if(empty($request['order_id'])){
            apiResponse('error','订单id不能为空');
        }
        //商家ID不能为空
        if(empty($request['merchant_id'])){
            apiResponse('error','商家id不能为空');
        }
        //统计未读消息数量
        $result['m_id'] = $request['m_id'];
        $un_read_num = $this->getUnReadMessageNum($result['m_id']);
        $result['un_read_num'] = '' . $un_read_num;
        //根据商家ID  获取商家信息
        $where['id'] = $request['merchant_id'];
        $where['status'] = array('neq',9);
        if($request['language'] == 'cn'){
            $merchant_info =M('Merchant') ->where($where) ->field('id as merchant_id,head_pic,is_integrity,cn_merchant_name as merchant_name') ->find();
        }else{
            $merchant_info =M('Merchant') ->where($where) ->field('id as merchant_id,head_pic,is_integrity,ue_merchant_name as merchant_name') ->find();
        }
        if($merchant_info['head_pic']){
            $path = M('File')->where(array('id'=>$merchant_info['head_pic']))->getField('path');
            $merchant_info['head_pic'] = $path?C('API_URL').$path:C('API_URL').'/Uploads/Merchant/default.png';
        }
        if(empty($merchant_info)){
            $message = $request['language'] == 'cn'?'商家信息有误.':'The merchant infomation is error .';
            apiResponse('success',$message,$result);
        }
        //根据已有条件查询订单详情
        unset($where);
        $where['id'] = $request['order_id'];
        $where['merchant_id'] = $request['merchant_id'];
        $where['remove_status'] = 0;
        if($request['language'] == 'cn'){
            $where['version'] = 1;
            $order_info = M('Order') ->where($where) -> field('id as order_id, m_id ,merchant_id ,address , order_sn , goods_info_serialization , submit_order_time ,delivery_people, delivery_phone, delivery_remark, leave_msg, totalprice, status') -> find();
        }else{
            $where['version'] = 2;
            $order_info = M('Order') ->where($where) -> field('id as order_id, m_id ,merchant_id ,address , order_sn , goods_info_serialization , submit_order_time ,delivery_people, delivery_phone, delivery_remark, leave_msg, totalprice, status') -> find();
        }
        if(empty($order_info)){
            $message = $request['language'] == 'cn'?'订单详情不存在':'The order information is empty .';
            apiResponse('error',$message);
        }
        //搜索买家详情
        unset($where);
        $where['id'] = $order_info['m_id'];
        $where['status'] = array('neq',9);
        $member_info = M('Member') ->where($where) ->field('id as m_id, nickname, head_pic, easemob_account') ->find();

        $path = M('File')->where(array('id'=>$member_info['head_pic']))->getField('path');
        $member_info['head_pic'] = $path?C('API_URL').$path:C('API_URL').'/Uploads/Member/default.png';

        if(empty($member_info)){
            $message = $request['language'] == 'cn'?'用户信息有误':'The user information is error .';
            apiResponse('success',$message,$result);
        }
        //查询订单详情以及地址详情
        $address = unserialize($order_info['address']);
        $address['haven_name'] = $address['haven_id']?$address['haven_id']:'';
        $goods_info = unserialize($order_info['goods_info_serialization']);
        if(!$goods_info){
            $message = $request['language'] == 'cn'?'订单详情有误':'The goods details is error .';
            apiResponse('success',$message,$result);
        }
        $i = 0;
        foreach ($goods_info['goods'] as $k => $v) {
            $goods[$i]['goods_id']   = $v['goodsDetail']['id'];
            $goods[$i]['goods_pic']  = $v['goodsDetail']['goods_pic'] ? C('API_URL') . $v['goodsDetail']['goods_pic'] : '';
            $goods[$i]['goods_name'] = $v['goodsDetail']['goods_name'] ? $v['goodsDetail']['goods_name'] : '';
            $goods[$i]['price']      = $v['goodsDetail']['price'] ? $v['goodsDetail']['price'] : '0.00';
            $goods[$i]['num']        = $v['num'] ? $v['num'] : '0';
            $goods[$i]['is_integrity'] = $merchant_info['is_integrity'] ? $merchant_info['is_integrity'] : '0';
            $goods[$i]['attr_con_name'] = $request['language'] == 'cn' ? $v['product']['cn_attr_con_name'] : $v['product']['eu_attr_con_name'];
            $i = $i + 1;
        }
        $order['goods_list'] = $goods;
        $order['address'] = $address;
        $order['status']  = $order_info['status'];
        $order['delivery_people'] = $order_info['delivery_people'];
        $order['delivery_phone']  = $order_info['delivery_phone'];
        $order['delivery_remark'] = $order_info['delivery_remark'];
        $order['totalprice']      = $order_info['totalprice'];
        $order['order_sn']        = $order_info['order_sn'];
        $order['order_id']        = $order_info['order_id'];
        $order['leave_msg']       = $order_info['leave_msg'];
        $order['submit_order_time'] = date('Y-m-d H:i',$order_info['submit_order_time']);
        $order['member']          = $member_info;
        $order['merchant']        = $merchant_info;
        $result['order']          = $order;
        unset($order);
        if(empty($result['order'])){
            $message = $request['language'] == 'cn'?'请求失败':'Request error .';
            apiResponse('success',$message,$result);
        }
        $message = $request['language'] == 'cn'?'请求成功':'Request success .';
        apiResponse('success',$message,$result);
    }
    /**
     * 购物车下单准备接口
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户ID       m_id
     * 购物车信息   json串：[{"cart_id":"4"},{"cart_id":"10"}]
     */
    public function readAddCartOrder($request = array()){
        //用户ID不能为空
        if (empty($request['m_id'])) {
            apiResponse('error', '用户ID不能为空');
        }
        //购物车信息
        if (empty($_POST['cart_json'])) {
            apiResponse('error', '请选择要买的商品');
        }
        $cart_list = json_decode($_POST['cart_json'], true);

        //将获得的json串转换
        if(empty($cart_list)){
            apiResponse('error', 'Json错误');
        }
        //查询用户是否有默认收货地址
        $where['m_id'] = $request['m_id'];
        $where['is_default'] = 1;
        $where['status'] = array('neq',9);
        $address = M('Address') ->where($where) ->field('id as address_id, name, tel, province_id, city_id, area_id, address') ->find();
        if(empty($address)){
            $address = array();
            $address['is_default'] = '0';
        }else{
            $address['province_id'] = M('Region') ->where(array('id'=>$address['province_id'])) ->getField('region_name');
            $address['city_id']     = M('Region') ->where(array('id'=>$address['city_id']))     ->getField('region_name');
            $address['area_id']     = M('Region') ->where(array('id'=>$address['area_id']))     ->getField('region_name');
            $address['is_default']  = '1';
        }
        $result['address'] = $address;
        //先查询商家ID并把重复的删掉
        $merchant = array();
        foreach($cart_list as $k => $v){
            unset($where);
            $where['id'] = $v['cart_id'];
            $where['status'] = array('neq',9);
            $merchant_list = M('Cart') ->where($where) ->field('merchant_id') ->find();
            if(!$merchant_list){
                continue;
            }
            $merchant[] = $merchant_list['merchant_id'];
        }
        if(!$merchant){
            $result['merchant'] = array();
            apiResponse('success','购物车信息有误',$result);
        }

        $merchant_id = array_unique($merchant);
        //根据商家ID进行商品分类（有点乱）
        $num_price = 0;
        $num_result_total = 0;
        $delivery_price = 0;
        foreach($merchant_id as $k =>$v){
            unset($where);
            $where['id'] = $v;
            $where['status'] = array('neq',9);
            $merchant_info[$k] = M('Merchant') ->where($where) ->field('id as merchant_id, merchant_name') ->find();
            $total_num = 0;
            $total_price = 0;
            $total_result_price = 0;
            $delivery_cost = 0;
            foreach($cart_list as $key => $val){
                unset($where);
                $where['id'] = $val['cart_id'];
                $where['status'] = array('neq',9);
                $where['merchant_id'] = $v;
                $cart_info = M('Cart') ->where($where) ->field('id as card_id, g_id, product, num') ->find();
                if(!$cart_info){
                    continue;
                }
                $goods_info[$key] = M('Goods') ->where(array('id'=>$cart_info['g_id'],'is_shelves'=>1))
                    ->field('id as goods_id, cn_goods_name as goods_name, goods_pic, cn_delivery_cost as delivery_cost') ->find();
                $goods_pic = explode(',',$goods_info[$key]['goods_pic']);
                $path = M('File') ->where(array('id'=>$goods_pic[0]))->getField('path');
                $goods_info[$key]['goods_pic'] = $path?C("API_URL").$path:'';
                $goods_info[$key]['num'] = $cart_info['num'];
                $product = explode(',',$cart_info['product']);
                $attr = '';
                foreach($product as $keys =>$value){
                    $attr_value = M('AttributeContent') ->where(array('id'=>$value,'status'=>array('neq',9)))
                        ->field('id as attr_con_id, attr_id, cn_attr_con_name as attr_con_name') ->find();
                    $attr_key = M('Attribute') ->where(array('id'=>$attr_value['attr_id'],'status'=>array('neq',9)))
                        ->field('id as attr_id, cn_attr_name as attr_name') ->find();
                    if(empty($attr_value)||empty($attr_key)){
                        continue;
                    }
                    $attr = $attr.' '.$attr_key['attr_name'].':'.$attr_value['attr_con_name'];
                }
                $goods_info[$key]['attr'] = $attr;
                $price = M('GoodsProduct') ->where(array('attr_key_group'=>$cart_info['product'],'goods_id'=>$cart_info['g_id'],'status'=>array('neq',9))) ->field('cn_price as price, wholesale_prices') ->find();
                $divide = M('Divide') ->find();
                $goods_info[$key]['price'] = $price['price']?$price['price']:'0.00';
                $goods_info[$key]['return_price'] = ($price['price'] - $price['wholesale_prices']) * (1 - $divide['divide_p']) * $divide['divide_m'];
                $goods_info[$key]['total_price'] = $cart_info['num'] * $goods_info[$key]['price'] + $goods_info[$key]['delivery_cost'];
                $total_num = $total_num + $cart_info['num'];
                $total_price = $total_price + $goods_info[$key]['total_price'];
                $total_result_price = $total_result_price + ($goods_info[$key]['return_price'] * $goods_info[$key]['num']);
                $delivery_price = $delivery_price + $goods_info[$key]['delivery_cost'];
                unset($attr);
            }
            $goods_info = array_values($goods_info);
            $merchant_info[$k]['goods_info']   = $goods_info;
            $merchant_info[$k]['total_num']    = $total_num ;
            $merchant_info[$k]['total_price']  = $total_price ;
            $merchant_info[$k]['total_return_price']  = $total_result_price ;
            $merchant_info[$k]['delivery_cost'] = $delivery_price?$delivery_price.'':'0.00';
            $num_price = $num_price + $total_price;
            $delivery_cost = $delivery_cost + $delivery_price;
            $num_result_total = $num_result_total + $total_result_price;
            unset($goods_info);
            unset($total_num);
            unset($total_price);
            unset($total_result_price);
        }
        if(!$merchant_info){
            $result['merchant_info'] = array();
        }
        $merchant_info = array_values($merchant_info);
        $result['merchant_info'] = $merchant_info;
        $result['price']   = $num_price;
        $result['return_price'] = $num_result_total;
        $result['delivery_cost'] = $delivery_cost?$delivery_cost.'':'0.00';
        apiResponse('success','',$result);
    }
    /**
     * 单品下单准备接口
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户ID       m_id
     * 商品ID       goods_id
     * 属性值名称   json串：[{"attr_con_id":"4"}]
     * 购买数量     num
     */
    public function readyAddOrder($request = array()){
        //用户ID不能为空
        if(!$request['m_id']){
            apiResponse('error','用户ID不能为空');
        }
        //商品ID不能为空
        if(!$request['goods_id']){
            apiResponse('error','商品ID不能为空');
        }
        //商品属性不能为空
        if(!$_POST['attr_con_id']){
            apiResponse('error','请选择商品属性');
        }
        //购买数量不能为空
        if(!$request['num']){
            apiResponse('error','请输入购买数量');
        }

        //查询是否存在默认地址   如果有直接返回
        $where['m_id'] = $request['m_id'];
        $where['is_default'] = 1;
        $where['status'] = array('neq',9);
        $address = M('Address') ->where($where) ->field('id as address_id, name, tel, province_id, city_id, area_id, address') ->find();

        if(empty($address)){
            $address = array();
            $address['is_default'] = '0';
        }else{
            $address['province_id'] = M('Region') ->where(array('id'=>$address['province_id'])) ->getField('region_name');
            $address['city_id'] = M('Region') ->where(array('id'=>$address['city_id'])) ->getField('region_name');
            $address['area_id'] = M('Region') ->where(array('id'=>$address['area_id'])) ->getField('region_name');
            $address['is_default'] = '1';
        }
        $result['address'] = $address;

        //根据商品ID查询商品信息
        unset($where);
        $where['id'] = $request['goods_id'];
        $where['is_shelves'] = 1;
        $goods_info = M('Goods') ->where($where) ->field('id as goods_id, merchant_id, cn_goods_name as goods_name, goods_pic, cn_delivery_cost as delivery_cost') ->find();
        $goods_pic = explode(',',$goods_info['goods_pic']);
        $path = M('File') ->where(array('id'=>$goods_pic[0]))->getField('path');
        $goods_info['goods_pic'] = $path?C("API_URL").$path:'';

        unset($where);
        $where['id'] = $goods_info['merchant_id'];
        $where['status'] = array('neq',9);
        $goods_info['merchant_name'] = M('Merchant') ->where($where) ->getField('merchant_name');

        $attr_con = json_decode($_POST['attr_con_id'],true);
        if(!$attr_con){
            apiResponse('error','JSON错误');
        }

        $con = array();
        foreach($attr_con as $k => $v){
            $con[] = $v['attr_con_id'];
        }
        sort($con);
        $attr_con = $con;

        $product = '';
        foreach($attr_con as $k => $v){
            $attr = $v;
            $attr_value = M('AttributeContent') ->where(array('id'=>$attr)) ->field('id as attr_con_id, attr_id, cn_attr_con_name as attr_con_name') ->find();
            $attr_key = M('Attribute') ->where(array('id'=>$attr_value['attr_id'])) ->field('id as attr_id, cn_attr_name as attr_name') ->find();
            $product = $product.' '.$attr_key['attr_name'].':'.$attr_value['attr_con_name'];
            $attr_data[] = $attr_value['attr_con_id'];
        }
        if(!$attr){
            apiResponse('error','商品属性值有误');
        }
        $goods_info['product'] = $product;
        $attr_group = implode(',',$attr_data);
        unset($where);
        $where['attr_key_group'] = $attr_group;
        $where['status'] = array('neq',9);
        $where['goods_id'] = $request['goods_id'];
        $price = M('GoodsProduct') ->where($where) ->field('cn_price as price, wholesale_prices') ->find();
        $divide = M('Divide') ->find();
        $goods_info['price'] = $price['price']?$price['price']:'0.00';
        $goods_info['return_price'] = ($price['price'] - $price['wholesale_prices']) * (1 - $divide['divide_p']) * $divide['divide_m'];
        $goods_info['return_price'] = $goods_info['return_price']?$goods_info['return_price'].'':'0.00';
        $goods_info['return_total_price'] = $goods_info['return_price'] * $request['num'].'';
        $goods_info['num'] = $request['num'].'';
        $goods_info['total_price'] = ($goods_info['price'] * $request['num'] + $goods_info['delivery_cost']).'';
        $result['goods_info'] = $goods_info;
        apiResponse('success','',$result);
    }
    /**
     * 付款方式
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户ID  m_id
     * 订单号码  order_group_sn
     * 付款方式  type  1  微信支付  2  支付宝支付  3  银联支付  4  余额支付
     */
    public function payment($request = array()){
        //用户ID不能为空
        if(!$request['m_id']){
            apiResponse('error','用户ID不能为空');
        }
        //订单ID不能为空
        if(!$request['order_group_sn']){
            apiResponse('error','订单单号不能为空');
        }
        //付款方式不能为空
        if($request['type'] != 1&&$request['type'] != 2&&$request['type'] != 3&&$request['type'] != 4){
            apiResponse('error','付款方式有误');
        }
        $first_letter = substr($request['order_group_sn'],0,1);
        $bool = is_numeric($first_letter);
        if($bool == 0){
            //查询订单总价
            $order_price = M('OrderGroup') ->where(array('order_total_sn'=>$request['order_group_sn'])) ->field('id as order_g_id, order_total_sn, order_total_price') ->find();
            if(!$order_price){
                apiResponse('error','订单信息有误');
            }

            //查询子订单信息
            $where['order_g_id']    = $order_price['order_g_id'];
            $where['status']        = 0;
            $where['remove_status'] = 0;
            $order = M('Order') ->where($where) ->field('id as order_id, m_id, merchant_id, totalprice') ->select();
            if(!$order){
                apiResponse('error','该订单信息有误');
            }
            if($request['type'] != 4){
                $data['m_id'] = $request['m_id'];
                $data['order_id'] = $request['order_group_sn'];
                $data['money'] = $order_price['order_total_price'];
                $data['create_time'] = time();
                $result = M('Payment') ->add($data);
            }else{
                unset($where);
                $data['pay_status'] = 2;
                $data['pay_type']   = 4;
                $result =  M('OrderGroup') ->where(array('order_total_sn'=>$request['order_group_sn'])) ->data($data) ->save();
                unset($data);
                $where['id'] = $request['m_id'];
                $where['status'] = array('neq',9);
                $member = M('Member') ->where($where) ->field('id as m_id, balance') ->find();
                if($member['balance'] < $order_price['order_total_price']){
                    apiResponse('error','您的余额不足');
                }
                $data['balance'] = $member['balance'] - $order_price['order_total_price'];
                $res = M('Member') ->where($where) ->data($data) ->save();
                if(!$res){
                    apiResponse('error','付款失败');
                }
                $data['m_id']     = $request['m_id'];
                $data['order_id'] = $request['order_group_sn'];
                $data['payment']  = 4;
                $data['money']    = $order_price['order_total_price'];
                $data['create_time'] = time();
                $result = M('Payment') ->add($data);

                foreach($order as $k => $v){
                    unset($where);
                    unset($data);
                    $where['id'] = $v['order_id'];
                    $data['status'] = 2;
                    $data['pay_type'] = 4;
                    $result_data = M('Order') ->where($where) ->data($data) ->save();
                    if($result_data){
                        continue;
                    }
                }
                unset($data);
                unset($where);
                $data['type']      = 1;
                $data['object_id'] = $request['m_id'];
                $data['title']     = '支出';
                $data['content']   = '商品结算';
                $data['symbol']    = 0;
                $data['money']     = $order_price['order_total_price'];
                $data['create_time'] = time();
                $result = M('PayLog') ->add($data);
                if(!$result){
                    apiResponse('error','操作有误');
                }
            }
        }else{
            unset($where);
            unset($data);
            $where['order_sn'] = $request['order_group_sn'];
            $where['status']        = 0;
            $where['remove_status'] = 0;
            $order_list = M('Order') ->where($where) ->field('id as order_id, m_id, merchant_id, totalprice') ->find();
            if(!$order_list){
                apiResponse('error','该订单详情有误');
            }
            if($request['type']!=4){
                $data['m_id']        = $request['m_id'];
                $data['order_id']    = $request['order_group_sn'];
                $data['money']       = $order_list['totalprice'];
                $data['create_time'] = time();
                $result = M('Payment') ->add($data);
            }else{
                unset($where);
                unset($data);
                $where['id'] = $request['m_id'];
                $where['status'] = array('neq',9);
                $member = M('Member') ->where($where) ->field('id as m_id, balance') ->find();
                if($member['balance'] < $order_list['totalprice']){
                    apiResponse('error','您的余额不足');
                }
                $data['balance'] = $member['balance'] - $order_list['totalprice'];
                $res = M('Member') ->where($where) ->data($data) ->save();
                if(!$res){
                    apiResponse('error','付款失败');
                }
                unset($data);
                $data['m_id'] = $request['m_id'];
                $data['order_id']    = $request['order_group_sn'];
                $data['payment']     = 4;
                $data['money']       = $order_list['totalprice'];
                $data['create_time'] = time();
                $result = M('Payment') ->add($data);
                unset($where);
                unset($data);
                $where['order_sn'] = $request['order_group_sn'];
                $data['status'] = 2;
                $data['pay_type'] = 4;
                $data['update_time'] = time();
                $result_data = M('Order') ->where($where) ->data($data) ->save();
                if(!$result_data){
                    apiResponse('error','付款失败');
                }
                unset($data);
                unset($where);
                $data['type']      = 1;
                $data['object_id'] = $request['m_id'];
                $data['title']     = '支出';
                $data['content']   = '商品结算';
                $data['symbol']    = 0;
                $data['money']     = $order_list['totalprice'];
                $data['create_time'] = time();
                $result = M('PayLog') ->add($data);
                if(!$result){
                    apiResponse('error','操作有误');
                }
            }
        }
        unset($where);
        unset($data);
        $data['m_id'] = $request['m_id'];
        $data['type'] = 2;
        $data['cn_title'] = '订单消息';
        $data['cn_content'] = '您已购买淘米公社平台的商品，订单为'.$request['order_group_sn'].'，请等待商家接单';
        $data['create_time'] = time();
        $message_info = M('Message') ->add($data);
        apiResponse('success','付款成功');
    }

}