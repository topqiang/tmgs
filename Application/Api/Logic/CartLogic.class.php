<?php
namespace Api\Logic;

/**
 * Class CartLogic
 * @package Api\Logic
 */
class CartLogic extends BaseLogic{
    /**
     * 购物车首页
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户ID：m_id
     */
    public function cartList($request = array()){
        //用户id不能为空
        if (empty($request['m_id'])) {
            apiResponse('error', '用户ID不能为空');
        }

        //统计未读消息数量
        $un_read_num = $this->getUnReadMessageNum($request['m_id']);
        $result_data['un_read_num'] = '' . $un_read_num;
        //对购物车数据表查询信息
        $where['m_id']     = $request['m_id'];
        $where['status']   = array('neq',9);
        $cart_list = M('Cart') ->where($where) ->field('id as cart_id,g_id as goods_id, product, num')
            ->order('create_time desc') ->select();
        if (empty($cart_list)) {
            $result_data['goods'] = array();
            apiResponse('success', '您的购物车空空如也',$result_data);
        }

        $divide = M('Divide') ->where(array('id'=>1)) ->find();

        foreach($cart_list as $k =>$v){

            unset($where);
            $where['id'] = $v['goods_id'];
            $where['is_shelves'] = 1;
            $goods[$k] = M('Goods') ->where($where) ->field('id as goods_id, cn_goods_name as goods_name, goods_pic') ->find();
            if(!$goods[$k]){
                unset($goods[$k]);
                continue;
            }

            $goods[$k]['cart_id'] = $v['cart_id'];
            $goods_pic = explode(',',$goods[$k]['goods_pic']);
            $path = M('File') ->where(array('id'=>$goods_pic[0])) ->getField('path');
            $goods[$k]['goods_pic'] = $path?C("API_URL").$path:'';
            $goods[$k]['num'] = $v['num'];
            unset($where);
            $where['attr_key_group'] = $v['product'];
            $where['goods_id']       = $v['goods_id'];
            $where['status']         = array('neq',9);
            $price = M('GoodsProduct') ->where($where) ->field('id as product_id, wholesale_prices, cn_price as price')->find();
            $goods[$k]['price'] = $price['price']?$price['price'].'':'0.00';
            $return_price = ($price['price'] - $price['wholesale_prices']) * (1 - $divide['divide_p']) * $divide['divide_m'] ;
            $goods[$k]['return_price'] = $return_price?$return_price.'':'0.00';
            $product_str = '';
            $product = explode(',',$v['product']);
            foreach($product as $key =>$value){
                $attr_value = M('AttributeContent')->where(array('id'=>$value))->field('attr_id,cn_attr_con_name as attr_con_name')->find();
                $attr_key   = M('Attribute')->where(array('id'=>$attr_value['attr_id']))->field('cn_attr_name as attr_name')->find();

                if($attr_value && $attr_key){
                    $product_str = $product_str.$attr_key['attr_name'].":".$attr_value['attr_con_name'].' ';
                }
                unset($attr_value);
                unset($attr_key);
            }
            $goods[$k]['product'] = $product_str;

        }
        if(!$goods){
            $result_data['goods'] = array();
            apiResponse('success', '您的购物车空空如也',$result_data);
        }else{
            $goods = array_values($goods);
            $result_data['goods'] = $goods;
        }
        apiResponse('success','',$result_data);
    }
    /**
     * 购物车删除
     * 传递参数的方式：post
     * 需要传递的参数：
     * 语言：language ue 英文，cn:中文
     * 购物车ID  cart_id  购物车ID json串：[{"cart_id":"1"},{"cart_id":"2"}]
     * 用户ID：m_id
     */
    public function cartdelete($request = array()){
        //用户ID不能为空
        if (empty($request['m_id'])) {
            apiResponse('error', '用户ID不能为空');
        }
        //购物车json串
        if (empty($_POST['cart_json'])) {
            apiResponse('error', '请选择要删除的内容');
        }
        //对购物车json串进行操作
        $cart_id_list = json_decode($_REQUEST['cart_json'],true);
        if(empty($cart_id_list)){
            apiResponse('error', 'JSON错误');
        }
        $cart = array();
        foreach ($cart_id_list as $k => $v){
            $cart[] = $v['cart_id'];
        }
        if(empty($cart)){
            apiResponse('error', 'JSON错误');
        }
        //对选中商品进行删除
        $where['id'] = array('IN',$cart);
        $data['update_time'] = time();
        $data['status'] = 9;
        $result = M('Cart') ->where($where) ->data($data) ->save();
        if(empty($result)){
            apiResponse('error', '删除有误');
        }
        apiResponse('success', '删除成功');
    }

    /**
     * 购物车移入收藏
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户ID   m_id
     * 购物车ID json串：[{"cart_id":"1"},{"cart_id":"2"}]
     */
    public function cartCollect($request = array()){
        //用户ID不能为空
        if(empty($request['m_id'])){
            apiResponse('error', '用户ID不能为空');
        }
        //操作对象不能为空
        if(empty($_POST['cart_json'])){
            apiResponse('error', '请选择操作对象');
        }
        //对操作对象进行操作
        $cart_id_list = json_decode($_POST['cart_json'],true);
        if(!$cart_id_list){
            apiResponse('error', 'JSON错误');
        }
        $index = 0;
        foreach ($cart_id_list as $k => $v){
            $cart[$index] = $v['cart_id'];
            $index += 1;
        }
        $where['m_id'] = $request['m_id'];
        $where['id'] = array('IN',$cart);
        $where['status'] = array('neq',9);
        $res = M('Cart') ->where($where) ->field('id as cart_id,g_id as goods_id')->select();
        if(empty($res)){
            apiResponse('error','操作信息有误');
        }
        foreach($res as $k => $v){
            //判断该商品是否已经被收藏，如果被收藏了，直接从购物车中删除
            unset($where);
            $where['m_id']      = $request['m_id'];
            $where['handle_id'] = $v['goods_id'];
            $where['type']      = 2;
            $collect_info = M('Collect')->where($where)->find();
            if($collect_info){
                unset($data);
                $data['create_time'] = time();
                $data['status']      = 1;
                M('Collect')->where($where)->data($data)->save();
            }else{
                unset($data);
                $data['handle_id'] = $v['goods_id'];
                $data['m_id']      = $request['m_id'];
                $data['create_time']= time();
                $data['type']       = 2;
                $data['status']     = 1;
                M('Collect')->data($data)->add();
            }
            unset($where);
            unset($data);
            $where['id'] = $v['cart_id'];
            $data['update_time'] = time();
            $data['status']      = 9;
            M('Cart') ->where($where) ->data($data) ->save();
        }
        apiResponse('success', '收藏成功');
    }
    /**
     * 加入购物车
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户ID    m_id
     * 商品ID    goods_id
     * 商品数量  num
     * 商品属性  attr_con_json    json串：[{"attr_con_id":"4"},{"attr_con_id":"10"}]
     */
    public function shoppingCart($request = array()){
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
            apiResponse('error','购买数量不能为空');
        }
        //查询商品详情
        $where['id'] = $request['goods_id'];
        $where['is_shelves'] = 1 ;
        $where['audit_status'] = array('IN',array(0,1));
        $goods = M('Goods') ->where($where) ->field('id as goods_id ,merchant_id') ->find();

        if(empty($goods)){
            apiResponse('error','商品属性有误');
        }
        //拆分商品属性
        unset($where);
        if(!empty($_POST['attr_con_json'])){
            $attr = json_decode($_POST['attr_con_json'],true);
            if(empty($attr)){
                apiResponse('error','Json格式有误');
            }
            $index = 0;
            foreach ($attr as $k => $v){
                $cart[$index] = $attr[$k]['attr_con_id'];
                $index += 1;
            }
            $product = implode(',',$cart);
        }
        $where['product']     = $product;
        $where['m_id']        = $request['m_id'];
        $where['g_id']        = $request['goods_id'];
        $where['merchant_id'] = $goods['merchant_id'];
        $where['status']      = array('neq',9);
        $res = M('Cart') ->where($where) -> find();
        //存在的话把数量相加  不存在的话直接写入
        if($res){
            $data['num'] = $res['num'] + $request['num'];
            $data['update_time'] = time();
            $result = M('Cart') ->where($where) ->data($data) ->save();
            apiResponse('success','加入购物车成功');
        }
        $data['m_id'] = $request['m_id'];
        $data['g_id'] = $request['goods_id'];
        $data['num'] = $request['num'];
        $data['product'] = $product;
        $data['merchant_id'] = $goods['merchant_id'];
        $data['create_time'] = time();
        $data['status'] = 1;
        $result = M('Cart') ->add($data);
        if(empty($result)){
            apiResponse('error','加入购物车失败');
        }
        apiResponse('success','加入购物车成功');
    }
    /**
     * 购物车编辑
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户ID      m_id
     * 购物车属性  cart_json    json串：[{"cart_id":"4","num":"5"},{"cart_id":"10","num","num":"6"}]
     */
    public function  modifyCart($request = array()){
        //用户ID不能为空
        if(empty($request['m_id'])){
            apiResponse('error','用户ID不能为空');
        }
        //购物车属性值不能为空   并转化json串
        if(empty($_POST['cart_json'])){
            apiResponse('error','购物车属性不能为空');
        }
        $cart_list = json_decode($_POST['cart_json'],true);
        if(empty($cart_list)){
            apiResponse('error','JSON错误');
        }
        //对json 串进行转化并操作
        foreach ($cart_list as $k => $v){
            $cart['cart_id'] = $cart_list[$k]['cart_id'];
            $cart['num'] = $cart_list[$k]['num'];
            if(empty($cart['cart_id'])){
                continue;
            }
            if(empty($cart['num'])){
                continue;
            }
            $where['id'] = $cart_list[$k]['cart_id'];
            $where['m_id'] = $request['m_id'];
            $res = M('Cart') ->where($where) ->find();
            if(empty($res)){
               continue;
            }
            $data['num'] = $cart['num'];
            $data['update_time'] = time();
            $result = M('Cart') ->where($where) ->data($data) ->save();
            if(!$result){
                continue;
            }
        }
        apiResponse('success','操作成功');
    }
}