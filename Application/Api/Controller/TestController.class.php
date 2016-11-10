<?php
namespace Api\Controller;
use Think\Controller;

/**
 * Class TestController
 * @package Api\Controller
 * 测试
 */
class TestController extends BaseController{
    public function _initialize()
    {
        Vendor('Unionpay.sdk.log#class');
        Vendor('Unionpay.sdk.SDKConfig');
        Vendor('Unionpay.sdk.secureUtil');
        Vendor('Unionpay.sdk.common');
        Vendor('Unionpay.sdk.acp_service');



    }

    /**
     * 银联支付测试
     */
    public function unionPay(){
        echo "<meta charset='UTF-8'>";
        $params = array(

            //以下信息非特殊情况不需要改动
            'version' => '5.0.0',                 //版本号
            'encoding' => 'utf-8',				  //编码方式
            'txnType' => '01',				      //交易类型
            'txnSubType' => '01',				  //交易子类
            'bizType' => '000201',				  //业务类型
            'frontUrl' =>  SDK_FRONT_NOTIFY_URL,  //前台通知地址
            'backUrl' => SDK_BACK_NOTIFY_URL,	  //后台通知地址
            'signMethod' => '01',	              //签名方法
            'channelType' => '08',	              //渠道类型，07-PC，08-手机
            'accessType' => '0',		          //接入类型
            'currencyCode' => '156',	          //交易币种，境内商户固定156

            //TODO 以下信息需要填写
            'merId' => '802130053110547',		//商户代码，请改自己的测试商户号，此处默认取demo演示页面传递的参数
            'orderId' => ''.time(),	//商户订单号，8-32位数字字母，不能含“-”或“_”，此处默认取demo演示页面传递的参数，可以自行定制规则
            'txnTime' => ''.date('YmdHis'),	//订单发送时间，格式为YYYYMMDDhhmmss，取北京时间，此处默认取demo演示页面传递的参数
            'txnAmt' => '100',	//交易金额，单位分，此处默认取demo演示页面传递的参数

            //TODO 其他特殊用法请查看 pages/api_05_app/special_use_purchase.php
        );
        \AcpService::sign ( $params); // 签名
        $url = SDK_App_Request_Url;
        $result_arr = \AcpService::post ($params,$url);
        if(count($result_arr)<=0) { //没收到200应答的情况
            apiResponse('error','银联无应答-200');//没有收到200应答情况
        }

        if (!\AcpService::validate ($result_arr) ){
            apiResponse('error','应答报文验签失败');//应答报文验签失败
        }
        if ($result_arr["respCode"] == "00"){
            //成功
            $result_data['tn'] = $result_arr["tn"];
            apiResponse('success','获取tn成功',$result_data);
        } else {
            echo "失败：" . $result_arr["respMsg"] . "。<br>\n";
            apiResponse('error',"失败：" . $result_arr["respMsg"]);
        }
    }

    /**
     * 银联支付回调
     */
    public function unionPayNotify(){

    }
	
	
	public function kuaidiTest(){
		dump(post1('http://api.kuaidi100.com/api?id=6c8d76a022fca426&com=shunfeng&nu=308291063651&valicode=&show=0&muti=1&order=desc'));
	}

    /**
     * 我的邀请测试
     */
    public function invite(){
        $m_id = array('1');
        for($i = 0;$i<4;$i++){
            $m_id = $this->getNum($m_id);
        }
    }

    public function getNum($arr = array()){
        if($arr){
            $where['parent_id'] = array('in',$arr);
            $count = M('Relation')->where($where)->count();
            $m_id_res = M('Relation')->where($where)->field('m_id')->select();
            $m_id = array();
            foreach($m_id_res as $k =>$v){
                $m_id[] = $v['m_id'];
            }
            echo $count;
            echo "<br>";
            return $m_id;
        }else{
            echo 0;
            echo "<br>";
            return array();
        }
    }
    /**
     * 发现首页
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户id    m_id
     */
    public function discoverList(){
        D('Test','Logic')->discoverList(I('post.'));
    }
    /**
     * @param array $request
     * 商品详情
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户id：m_id
     * 商品id：goods_id
     */
    public function goodsInfo(){
        D('Goods','Logic')->goodsInfo(I('post.'));
    }
    /**
     * 商品列表
     * 传递参数的方式：post
     * 需要传递的参数：
     * 语言：language cn中文，ue 英文
     * 港口id：haven_id
     * 商品分类id：g_t_id
     * 类型：type:1商品，2服务
     * 用户id：m_id
     * 关键词：keywords
     * 综合排序：complex_order:1是，2否
     * 销量排序：sales_order:1升序，2降序
     * 价格排序：price_order:1升序，2降序
     * 价格下限:price_lower
     * 价格上限：price_Upper
     */
    public function goodsList(){
        D('Test','Logic')->goodsList(I('post.'));
    }
    public function test(){
        D('Test','Logic')->test(I('post.'));
    }

    /*添加订单*/
    public function addOrder()
    {
        D('Test','Logic')->addOrder(I('post.'));
    }

}