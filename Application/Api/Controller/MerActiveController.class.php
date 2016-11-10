<?php
namespace Api\Controller;
use Think\Controller;

/**
 * Class MerActiveController
 * @package Api\Controller
 * 商家活动模块
 */
class MerActiveController extends BaseController{

    public function _initialize(){
        parent::_initialize();
    }
    /**
     * 商家活动列表
     */
    public function activePage(){
        D('MerActive','Logic') ->activePage(I('post.'));
    }
    /**
     * 0  位置选择图
     */
    public function chooseLocation(){
        D('MerActive','Logic')->chooseLocation(I('post.'));
    }
    /**
     * 0  搜索商品
     */
    public function spreadChooseGoods(){
        D('MerActive','Logic')->spreadChooseGoods(I('post.'));
    }
    /**
     * 0  返回数据
     */
    public function findList(){
        D('MerActive','Logic')->findList(I('post.'));
    }
    /**
     * 1 2  首页广告已报名
     */
    public function firstPageList(){
        D('MerActive','Logic')->firstPageList(I('post.'));
    }
    /**
     * 1 2  首页广告报名
     */
    public function signFirstPage(){
        D('MerActive','Logic')->signFirstPage(I('post.'));
    }

    /**
     * 1 2  首页广告报名地点
     */
    public function choosePlace(){
        D('MerActive','Logic')->choosePlace(I('post.'));
    }
    /**
     * 1 2  已报名列表
     */
    public function entryList (){
        D('MerActive','Logic')->entryList(I('post.'));
    }

    /**
     * 1 2报名列表详情
     */
    public function entryDetail()
    {
        D('MerActive','Logic')->entryDetail(I('post.'));
    }

    /**
     * 1 2  首页广告报名日历
     */
    public function chooseTime(){
        D('MerActive','Logic')->chooseTime(I('post.'));
    }

    /**
     * 345 发现列表
     * @param  post array
     *  post['merchant_id'] 商家ID
     *  post['type'] 类型 3 商品 4 好店 5 服务
     */
    public function deliveryList()
    {
        D('MerActive','Logic')->deliveryList(I('post.'));
    }

    /**
     * 345  发现详情
     */
    public function deliveryInfo(){
        D('MerActive','Logic')->deliveryInfo(I('post.'));
    }
    /**
     * 3 4 5  发现模块  新增
     */
    public function addDeliveryGoods(){
        D('MerActive','Logic')->addDeliveryGoods(I('post.'));
    }

    /**
     * 6  推广列表
     */
    public function spreadList(){
        D('MerActive','Logic')->spreadList(I('post.'));
    }
    /**
     * 6  推广详情
     */
    public function spreadInfo(){
        D('MerActive','Logic')->spreadInfo(I('post.'));
    }
    /**
     * 6  点击记录
     */
    public function clickRecord (){
        D('MerActive','Logic')->clickRecord(I('post.'));
    }
    /**
     * 6  推广商品
     */
    public function spreadGoods(){
        D('MerActive','Logic')->spreadGoods(I('post.'));
    }
    /**
     * 7  入驻积分商城  已入驻商品
     */
    public function enterGoodsList(){
        D('MerActive','Logic')->enterGoodsList(I('post.'));
    }
    /**
     * 7  入驻积分商城  商品详情
     */
    public function enterGoodsInfo(){
        D('MerActive','Logic')->enterGoodsInfo(I('post.'));
    }
    /**
     * 7  入驻积分商城  新增商品
     */
    public function addEnterGoods(){
        D('MerActive','Logic')->addEnterGoods(I('post.'));
    }
    /**
     * 7  入驻积分商城  编辑商品
     */
    public function modifyEnterGoods(){
        D('MerActive','Logic')->modifyEnterGoods(I('post.'));
    }
    /**
     * 1 2  首页广告余额支付
     */
    public function advertBalancePay(){
        D('MerActive','Logic')->advertBalancePay(I('post.'));
    }
    /**
     * 3 4 5  好货们的余额支付
     */
    public function goodsBalancePay(){
        D('MerActive','Logic')->goodsBalancePay(I('post.'));
    }
    /**
     * 6  推广产品的余额支付
     * 商家ID       merchant_id
     * 推广对象ID   c_s_id
     */
    public function  spreadBalancePay(){
        D('MerActive','Logic')->spreadBalancePay(I('post.'));
    }
    /**
     * 1 2 广告报名支付宝回调
     */
    public function advertAlipayNotify(){
        $out_trade_no = $_POST['out_trade_no'];

        $where['order_sn'] =$out_trade_no;
        $where['status'] = array('neq',9);
        $order =M('AdPositionTotal') ->where($where) ->find();
        if($order){
            $data['pay_status'] = 1;
            $data['pay_type']   = 1;
            $data['update_time'] = time();
            $result = M('AdPositionTotal') ->where($where) ->data($data) ->save();
            if(!$result){
                apiResponse('error','支付失败');
            }

            $time_date = explode(',',$order['time']);

            foreach($time_date as $k => $v){
                $start_time = strtotime($v);
                $end_time = $start_time + 86400;
                $data['url']    = $order['url'];
                $data['pic']    = $order['pic'];
                $data['region'] = $order['region'];
                $data['type']   = $order['type'];
                $data['merchant_id'] = $order['merchant_id'];
                $data['a_p_t_id']    = $order['id'];
                $data['location']    = $order['location'];
                $data['start_time']  = $start_time;
                $data['end_time']    = $end_time;
                $result = M('AdPosition') ->add($data);
            }

            //添加账单明细
            unset($data);
            $data['type']      = 2;
            $data['object_id'] = $order['merchant_id'];
            $data['title']     = '支出';
            if($order['type'] == 1){
                $data['content']   = '首页广告位下单';
            }else{
                $data['content']   = '签到页广告位下单';
            }
            $data['symbol']    = 0;
            $data['money']     = $order['money'];
            $data['create_time'] = time();
            $result = M('PayLog') ->add($data);
        }else{
            apiResponse('error','支付失败');
        }
        apiResponse('success','支付成功');
    }
    /**
     * 1 2 广告报名微信回调
     */
    public function advertWXinNotify(){
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
        $xml_res = $this->xmlToArray($xml);
        $where['order_sn'] = $xml_res["out_trade_no"];
        $where['status'] = array('neq',9);
        $order = M('AdPositionTotal') ->where($where) ->find();
        if($order){
            $data['pay_status'] = 1;
            $data['pay_type']   = 2;
            $result = M('AdPositionTotal')->where($where)->data($data) ->save();
            if(!$result){
                apiResponse('error','支付失败');
            }

            $time_date = explode(',',$order['time']);

            foreach($time_date as $k => $v){
                $start_time = strtotime($v);
                $end_time = $start_time + 86400;
                $data['url']    = $order['url'];
                $data['pic']    = $order['pic'];
                $data['region'] = $order['region'];
                $data['type']   = $order['type'];
                $data['merchant_id'] = $order['merchant_id'];
                $data['a_p_t_id']    = $order['id'];
                $data['location']    = $order['location'];
                $data['start_time']  = $start_time;
                $data['end_time']    = $end_time;
                $result = M('AdPosition') ->add($data);
            }

            //添加账单明细
            unset($data);
            $data['type']      = 2;
            $data['object_id'] = $order['merchant_id'];
            $data['title']     = '支出';
            if($order['type'] == 1){
                $data['content']   = '首页广告位下单';
            }else{
                $data['content']   = '签到页广告位下单';
            }
            $data['symbol']    = 0;
            $data['money']     = $order['money'];
            $data['create_time'] = time();
            $result = M('PayLog') ->add($data);
        }else{
            apiResponse('error','支付失败');
        }
        apiResponse('success','支付成功');
    }

    /**
     * 3 4 5  好货们的支付宝回调
     */
    public function goodsAlipayNotify(){
        $out_trade_no = $_POST['out_trade_no'];

        $where['order_sn'] =$out_trade_no;
        $where['status'] = array('neq',9);
        $FindTotal = M('FindTotal') ->where($where) ->find();
        if($FindTotal){
            $data['pay_status'] = 1;
            $data['pay_type']   = 1;
            $result = M('FindTotal') ->where($where) ->data($data) ->save();
            if(!$result){
                apiResponse('error','支付失败');
            }
            $goods_time = explode(',',$FindTotal['time']);
            foreach($goods_time as $k => $v){
                $start_time = strtotime($v);
                $end_time = $start_time + 86400;
                $data['merchant_id'] = $FindTotal['merchant_id'];
                $data['type']        = $FindTotal['type'];
                $data['object_id']   = $FindTotal['object_id'];
                $data['start_time']  = $start_time;
                $data['end_time']    = $end_time;
                $data['f_t_id']      = $FindTotal['id'];
                $res = M('FindBranch') ->add($data);
            }
            unset($data);
            unset($where);
            $data['type']      = 2;
            $data['object_id'] = $FindTotal['merchant_id'];
            $data['title']     = '支出';
            if($FindTotal['type'] == 3){
                $data['content']   = '发现好商品下单';
            }elseif($FindTotal['type'] == 4) {
                $data['content']   = '发现好店下单';
            }else{
                $data['content']   = '发现好服务下单';
            }
            $data['symbol']    = 0;
            $data['money']     = $FindTotal['money'];
            $data['create_time'] = time();
            $result = M('PayLog') ->add($data);
        }else{
            apiResponse('error','支付失败');
        }
        apiResponse('success','支付成功');
    }
    /**
     * 3 4 5  好货们的微信回调
     */
    public function goodWXinNotify(){
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
        $xml_res = $this->xmlToArray($xml);
        $where['order_sn'] = $xml_res["out_trade_no"];
        $where['status'] = array('neq',9);
        $FindTotal = M('FindTotal') ->where($where) ->find();
        if($FindTotal){
            $data['pay_status'] = 1;
            $data['pay_type']   = 2;
            $result = M('FindTotal')->where($where)->data($data) ->save();
            if(!$result){
                apiResponse('error','支付失败');
            }
            $goods_time = explode(',',$FindTotal['time']);
            foreach($goods_time as $k => $v){
                $start_time = strtotime($v);
                $end_time = $start_time + 86400;
                $data['merchant_id'] = $FindTotal['merchant_id'];
                $data['type']        = $FindTotal['type'];
                $data['object_id']   = $FindTotal['object_id'];
                $data['start_time']  = $start_time;
                $data['end_time']    = $end_time;
                $data['f_t_id']      = $FindTotal['id'];
                $res = M('FindBranch') ->add($data);
            }

            //添加账单明细
            unset($data);
            unset($where);
            $data['type']      = 2;
            $data['object_id'] = $FindTotal['merchant_id'];
            $data['title']     = '支出';
            if($FindTotal['type'] == 3){
                $data['content']   = '发现好商品下单';
            }elseif($FindTotal['type'] == 4) {
                $data['content']   = '发现好店下单';
            }else{
                $data['content']   = '发现好服务下单';
            }
            $data['symbol']    = 0;
            $data['money']     = $FindTotal['money'];
            $data['create_time'] = time();
            M('PayLog') ->data($data) ->add();
        }else{
            apiResponse('error','支付失败');
        }
        apiResponse('success','支付成功');
    }

    /**
     * 6  推广产品的支付宝回调
     */
    public function spreadAlipayNotify(){
        $out_trade_no = $_POST['out_trade_no'];
        $where['order_sn'] =$out_trade_no;
        $where['status'] = array('neq',9);
        $CloudSpread =M('CloudSpread') ->where($where) ->find();
        if($CloudSpread){
            $data['pay_status'] = 1;
            $data['pay_type']   = 1;
            $data['update_time'] = time();
            $result = M('CloudSpread') ->where($where) ->data($data) ->save();
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
            apiResponse('error','支付失败');
        }
        apiResponse('success','支付成功');
    }

    /**
     * 6  推广产品的微信回调
     */
    public function spreadWXinNotify(){
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
        $xml_res = $this->xmlToArray($xml);
        $where['order_sn'] = $xml_res["out_trade_no"];
        $where['status'] = array('neq',9);
        $CloudSpread = M('CloudSpread') ->where($where) ->find();
        if($CloudSpread){
            $data['pay_status'] = 1;
            $data['pay_type']   = 2;
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
        }
        apiResponse('success','支付成功');
    }
    /**
     * 查询订单状态
     * 需要传递的参数：
     * 订单编号：order_sn
     * 查询类型  type  1  首页广告位  2  签到页广告位  3  发现好商品  4  发现好店  5  发现好服务  6  云推广
     */
    public function findStatus(){
        $order_sn = $_POST['order_sn'];
        if($_POST['type'] != 1&&$_POST['type'] != 2&&$_POST['type'] != 3&&$_POST['type'] != 4&&$_POST['type'] != 5&&$_POST['type'] != 6){
            apiResponse('error','订单类型有误');
        }
        if($_POST['type'] == 1||$_POST['type'] == 2){
            $where['order_sn'] = $order_sn;
            $where['type']     = $_POST['type'];
            $order = M('AdPositionTotal') ->find();
        }elseif($_POST['type'] == 3||$_POST['type'] == 4||$_POST['type'] == 5){
            $where['order_sn'] = $order_sn;
            $where['type']     = $_POST['type'];
            $order = M('FindTotal') ->find();
        }else{
            $where['order_sn'] = $order_sn;
            $order = M('CloudSpread') ->find();
        }
        if(!$order){
            apiResponse('error','订单信息有误');
        }
        if($order['pay_status']==0){
            $result_data['status'] = '0';
            apiResponse('success','订单支付失败',$result_data);
        }else{
            $result_data['status'] = '1';
            apiResponse('success','订单支付成功',$result_data);
        }
    }
    /**
     * 	作用：将xml转为array
     */
    function xmlToArray($xml){
        //将XML转为array
        $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $array_data;
    }

    public function delIntegralGoods()
    {
        D('MerActive','Logic')->delIntegralGoods(I('post.'));
    }

    /*================= 用户 =================*/

    /*积分商城列表*/
    public function integralList()
    {
        D('MerActive','Logic')->integralList(I('post.'));
    }
    /*积分商城办理*/
    public function addIntegral(){
        D('MerActive','Logic')->addIntegral(I('post.'));
    }

    /*积分商城详情*/
    public function integralLog()
    {
        D('MerActive','Logic')->integralLog(I('post.'));
    }


}