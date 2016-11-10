<?php
namespace Api\Logic;

/**
 * Class UnionPayLogic
 * @package Api\Logic
 * 银联支付
 */
class UnionPayLogic extends BaseLogic{
    /**
     * 银联充值
     * 用户ID      m_id
     * 充值金额    money
     */
    public function unionRecharge($request = array()){
        //用户ID不能为空
        if(!$request['m_id']){
            apiResponse('error','用户ID不能为空');
        }
        //充值金额不能为空
        if(!$request['money']){
            apiResponse('error','充值金额不能为空');
        }

        $data['order_sn'] = time().rand(10000,99999);
        $data['m_id'] = $request['m_id'];
        $data['money'] = $request['money'];
        $data['create_time'] = time();
        $res = M('Recharge') ->add($data);
        if(!$res){
            apiResponse('error','充值失败');
        }
        $backUrl = 'http://www.taomim.com/index.php/Api/UnionPay/unionRechargeNotify';
        $orderId = $data['order_sn'].'';
        $txnAnt  = ($request['money'] * 100).'';
        $this -> getTn($backUrl,$orderId,$txnAnt);
    }

    /**
     * 购买商品获取tn接口
     * 用户ID      m_id
     * 订单ID      order_group_sn
     */
    public function orderGetTn($request = array()){
        //用户ID不能为空
        if(!$request['m_id']){
            apiResponse('error','用户ID不能为空');
        }
        //订单ID不能为空
        if(!$request['order_group_sn']){
            apiResponse('error','订单ID不能为空');
        }
        if(!$request['money']){
            apiResponse('error','订单金额不能为空');
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
            $data['m_id'] = $request['m_id'];
            $data['order_id'] = $request['order_group_sn'];
            $data['money'] = $order_price['order_total_price'];
            $data['create_time'] = time();
            $result = M('Payment') ->add($data);
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
            $data['m_id']        = $request['m_id'];
            $data['order_id']    = $request['order_group_sn'];
            $data['money']       = $order_list['totalprice'];
            $data['create_time'] = time();
            $result = M('Payment') ->add($data);
        }
        if(!$result){
            apiResponse('error','下单失败');
        }
        $backUrl = 'http://www.taomim.com/index.php/Api/UnionPay/unionOrderNotify';
        $orderId = $request['order_group_sn'].'';
        $txnAnt  = ($request['money'] * 100).'';
        $res  = $this -> getTn($backUrl,$orderId,$txnAnt);
        if(!$res){
            apiResponse('error','银联下单失败');
        }
        apiResponse('success','付款成功');
    }
    /**
     * 保障金下单tn接口
     * 用户ID      merchant_id
     * 诚信商家ID  m_a_id
     * 金额        money
     * 诚信级别    location
     */
    public function orderSecurity($request = array()){
        if(!$request['merchant_id']){
            apiResponse('error','商家ID不能为空');
        }
        //诚信商家ID不能为空
        if(!$request['m_a_id']){
            apiResponse('error','诚信商家ID不能为空');
        }
        //金额不能为空
        if(!$request['money']){
            apiResponse('error','金额不能为空');
        }
        //诚信级别不能为空
        if(!$request['location']){
            apiResponse('error','诚信级别不能为空');
        }
        $data['order_security_sn'] = time().rand(10000,99999);
        $data['merchant_id']       = $request['merchant_id'];
        $data['type']              = 3;
        $data['money']             = $request['money'];
        $data['grade']             = $request['location'];
        $result = M('Security') ->add($data);
        if(!$result){
            apiResponse('error','保证金下单失败');
        }
        $backUrl = 'http://2.taomim.com/index.php/Api/UnionPay/unionSecurityNotify';
        $orderId = $data['order_security_sn'].'';
        $txnAnt  = ($request['money'] * 100).'';
        $res  = $this -> getTn($backUrl,$orderId,$txnAnt);
    }
    /**
     * 报名下单tn接口
     * 商家ID    merchant_id
     * 订单号    order_sn
     * 订单金额  money
     * 类型 type (12)广告报名 1  (345)好服务  2  (6)推广 3
     * @since 2016-10-18 合方法 修改为 类型返回tn
     */
    public function advertSecurity($request = array()){
        if(!$request['merchant_id']){
            apiResponse('error','商家ID不能为空');
        }
        //诚信商家ID不能为空
        if(!$request['order_sn']){
            apiResponse('error','订单号不能为空');
        }
        //金额不能为空
        if(!$request['money']){
            apiResponse('error','金额不能为空');
        }
        $where['order_sn'] = $request['order_sn'];
        $where['merchant_id'] = $request['merchant_id'];

        switch($request['type']){
            case '1':
                $order = M('AdPositionTotal') ->where($where) ->find();
                break;
            case '2':
                $order = M('FindTotal') ->where($where) ->find();
                break;
            case '3':
                $order = M('CloudSpread') ->where($where) ->find();
                break;
        }
        if(!$order){
            apiResponse('error','申请信息有误');
        }

        $backUrl = 'http://2.taomim.com/index.php/Api/UnionPay/unionSecurityNotify';
        $orderId = $request['order_sn'].'';
        $txnAnt  = ($request['money'] * 100).'';
        $res  = $this -> getTn($backUrl,$orderId,$txnAnt);
    }
    /**
     * 3 4 5 好货们下单tn接口
     * 商家ID    merchant_id
     * 订单号    order_sn
     * 订单金额  money
     */
    public function goodsSecurity($request = array()){
        if(!$request['merchant_id']){
            apiResponse('error','商家ID不能为空');
        }
        if(!$request['order_sn']){
            apiResponse('error','订单编号不能为空');
        }
        if(!$request['money']){
            apiResponse('error','订单金额不能为空');
        }
        $where['merchant_id'] = $request['merchant_id'];
        $where['order_sn']    = $request['order_sn'];
        $find_total = M('FindTotal') ->where($where) ->find();
        if(!$find_total){
            apiResponse('error','申请信息有误');
        }
        $backUrl = 'http://2.taomim.com/index.php/Api/UnionPay/unionSecurityNotify';
        $orderId = $request['order_sn'].'';
        $txnAnt  = ($request['money'] * 100).'';
        $res  = $this -> getTn($backUrl,$orderId,$txnAnt);
    }
    /**
     * 6 推广产品下单tn接口
     * 商家ID    merchant_id
     * 订单号    order_sn
     * 订单金额  money
     */
    public function spreadSecurity($request = array()){
        if(!$request['merchant_id']){
            apiResponse('error','商家ID不能为空');
        }
        if(!$request['order_sn']){
            apiResponse('error','推广对象订单号不能为空');
        }
        if(!$request['money']){
            apiResponse('error','推广对象订单号不能为空');
        }
        $where['merchant_id'] = $request['merchant_id'];
        $where['order_sn']    = $request['order_sn'];
        $result = M('CloudSpread') ->where($where) ->find();
        if(!$result){
            apiResponse('error','申请信息有误');
        }
        $backUrl = 'http://2.taomim.com/index.php/Api/UnionPay/unionSecurityNotify';
        $orderId = $request['order_sn'].'';
        $txnAnt  = ($request['money'] * 100).'';
        $res  = $this -> getTn($backUrl,$orderId,$txnAnt);
    }

    /**
     * @param array $request
     * 爱心捐助银联支付
     */
    public function DonateLoveUPayTn($request = array()){

        if(!$request['order_sn']){
            apiResponse('error','订单号不能为空');
        }
        if(!$request['money']){
            apiResponse('error','价格不能为空');
        }
        $where['order_sn']    = $request['order_sn'];
        $result = M('DonateLoveOrder') ->where($where) ->find();
        if(!$result){
            apiResponse('error','申请信息有误');
        }
        $backUrl = 'http://2.taomim.com/index.php/Api/UnionPay/unionSecurityNotify';
        $orderId = $request['order_sn'].'';
        $txnAnt  = ($request['money'] * 100).'';
        $res  = $this -> getTn($backUrl,$orderId,$txnAnt);
    }


    /**
     * 银联支付爱心
     * 用户ID      m_id
     * 订单ID      order_group_sn
     */
    public function unionLoveOrder($request = array()){
        if(!$request['m_id']){
            apiResponse('error','商家ID不能为空');
        }
        if(!$request['order_sn']){
            apiResponse('error','订单编号不能为空');
        }
        if(!$request['money']){
            apiResponse('error','订单金额不能为空');
        }
        $where['m_id'] = $request['m_id'];
        $where['order_sn']    = $request['order_sn'];
        $find_total = M('FindTotal') ->where($where) ->find();
        if(!$find_total){
            apiResponse('error','申请信息有误');
        }
        $backUrl = 'http://2.taomim.com/index.php/Api/UnionPay/unionLoveOrderNotify';
        $orderId = $request['order_sn'].'';
        $txnAnt  = ($request['money'] * 100).'';
        $res  = $this -> getTn($backUrl,$orderId,$txnAnt);
    }



    /**
     * @param $backUrl
     * @param $orderId
     * @param $txnAnt
     * 银联支付获取tn
     */
    public function getTn($backUrl,$orderId,$txnAnt)
    {
        // 'http://tmgs.txunda.com/index.php/Api/Test/unionPayNotify'
        $params = array(
            //以下信息非特殊情况不需要改动
            'version' => '5.0.0',                 //版本号
            'encoding' => 'utf-8',                  //编码方式
            'txnType' => '01',                      //交易类型
            'txnSubType' => '01',                  //交易子类
            'bizType' => '000201',                  //业务类型
            'frontUrl' => SDK_FRONT_NOTIFY_URL,  //前台通知地址
            'backUrl' => $backUrl,      //后台通知地址
            'signMethod' => '01',                  //签名方法
            'channelType' => '08',                  //渠道类型，07-PC，08-手机
            'accessType' => '0',                  //接入类型
            'currencyCode' => '156',              //交易币种，境内商户固定156

            //TODO 以下信息需要填写
            'merId' => '802130053110547',        //商户代码，请改自己的测试商户号，此处默认取demo演示页面传递的参数
            'orderId' => $orderId,    //商户订单号，8-32位数字字母，不能含“-”或“_”，此处默认取demo演示页面传递的参数，可以自行定制规则
            'txnTime' => '' . date('YmdHis'),    //订单发送时间，格式为YYYYMMDDhhmmss，取北京时间，此处默认取demo演示页面传递的参数
            'txnAmt' => ''.$txnAnt,    //交易金额，单位分，此处默认取demo演示页面传递的参数

            //TODO 其他特殊用法请查看 pages/api_05_app/special_use_purchase.php
        );
        \AcpService::sign($params); // 签名
        $url = SDK_App_Request_Url;
        $result_arr = \AcpService::post($params, $url);
        if (count($result_arr) <= 0) { //没收到200应答的情况
            apiResponse('error', '银联无应答-200');//没有收到200应答情况
        }

        if (!\AcpService::validate($result_arr)) {
            apiResponse('error', '应答报文验签失败');//应答报文验签失败
        }
        if ($result_arr["respCode"] == "00") {
            //成功
            $result_data['tn'] = $result_arr["tn"];
            apiResponse('success', '获取tn成功', $result_data);
        } else {
            apiResponse('error', "失败：" . $result_arr["respMsg"]);
        }
    }
}