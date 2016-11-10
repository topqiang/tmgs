<?php

namespace Api\Controller;

/**
 * Class DonateLoveController
 * @package Api\Controller
 * 爱心捐助
 */
class DonateLoveController extends BaseController
{

    /*爱心捐助*/
    public function donateLoveList()
    {
        D(CONTROLLER_NAME,'Logic')->DonateLoveList(I('post.'));
    }
    /*捐助详情*/
    public function donateLoveDetail()
    {
        D(CONTROLLER_NAME,'Logic')->donateLoveDetail(I('post.'));
    }
    /*捐助评论*/
    public function donateLoveComment()
    {
        D(CONTROLLER_NAME,'Logic')->donateLoveComment(I('post.'));
    }
    /*Ta的捐助*/
    public function donateLoveHim()
    {
        D(CONTROLLER_NAME,'Logic')->donateLoveHim(I('post.'));
    }
    /*用户协议*/
    public function donateLoveAgreement()
    {
        D(CONTROLLER_NAME,'Logic')->donateLoveAgreement(I('post.'));
    }
    /*评价*/
    public function donateLoveEvaluate()
    {
        D(CONTROLLER_NAME,'Logic')->donateLoveEvaluate(I('post.'));
    }
    /*增加爱心捐助记录*/
    public function donateLoveOrder()
    {
        D(CONTROLLER_NAME,'Logic')->donateLoveOrder(I('post.'));
    }

    /*订单状态*/
    public function getOrderStatus()
    {
        D(CONTROLLER_NAME,'Logic')->getOrderStatus(I('post.'));
    }
    /*余额支付*/
    public function loveOrderBalancePay()
    {
        D(CONTROLLER_NAME,'Logic')->loveOrderBalancePay(I('post.'));
    }

    /**
     * 支付宝回调
     */
    public function alipayNotify(){
        $out_trade_no = $_POST['out_trade_no']; // 订单号
        $where['order_sn'] = $out_trade_no; // 订单号
        $where['status'] = array('eq',0); // 状态
        $loveOrder = M('DonateLoveOrder')->where($where)->find();
        if($loveOrder){
            //修改保障金订单状态
            unset($where);
            $where['id'] = $loveOrder['id'];
            $data['pay_type'] = 3;
            $data['pay_status'] = 1;
            $res = M('DonateLoveOrder')->where($where)->data($data)->save();
            if(!$res){
                apiResponse('error','保证金下单失败');
            }

            M('DonateLove')->where(array('id'=>$loveOrder['dl_id']))->setInc('project_current_money',$loveOrder['money']); // 增加募集资金
            $id =  M('DonateLoveOrder') -> where(array('order_sn'=>$out_trade_no)) -> getField('dl_id');
            $count = M('DonateLove') -> where(array('id'=>$id)) -> field('project_current_money,project_aims_money')->find();
            if($count['project_current_money'] >= $count['project_aims_money'] ){
                M('DonateLove') -> where(array('id'=>$id)) -> data(array('status'=>2)) ->  save();
            }
            //添加账单明细
            unset($data);
            $data['type']   = '1';
            $data['object_id']   = $loveOrder['m_id'];
            $data['title']       = '爱心捐助';
            $data['content']     = '支付宝付款';
            $data['money']       = $loveOrder['money'];
            $data['symbol']      = '0';
            $data['create_time'] = time();
            M('PayLog')->data($data)->add();
        }
        apiResponse('success','支付保证金成功');
    }

    /**
     * 微信回调
     */
    public function wXinNotify(){
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
//        file_put_contents('wxnotify.txt',$xml);
        $xml_res = $this->xmlToArray($xml);
        $where['order_sn'] = $xml_res["out_trade_no"];
        $where['status'] = array('eq',0);
        $loveOrder = M('DonateLoveOrder') ->where($where) ->find();
        if($loveOrder){
            //修改订单状态
            unset($where);
            $where['id'] = $loveOrder['id'];
            $data['pay_type'] = 2;
            $data['pay_status'] = 1;
            $res = M('DonateLoveOrder')->where($where)->data($data)->save();
            if(!$res){
                apiResponse('error','微信充值失败');
            }
            M('DonateLove')->where(array('id'=>$loveOrder['dl_id']))->setInc('project_current_money',$loveOrder['money']); // 增加募集资金
            $id =  M('DonateLoveOrder') -> where(array('order_sn'=>$xml_res["out_trade_no"])) -> getField('dl_id');
            $count = M('DonateLove') -> where(array('id'=>$id)) -> field('project_current_money,project_aims_money')->find();
            if($count['project_current_money'] >= $count['project_aims_money'] ){
                M('DonateLove') -> where(array('id'=>$id)) -> data(array('status'=>2)) ->  save();
            }
            //添加账单明细
            unset($data);
            $data['type']   = '1';
            $data['object_id']   = $loveOrder['m_id'];
            $data['title']       = '爱心捐助';
            $data['content']     = '微信支付';
            $data['money']       = $loveOrder['money'];
            $data['symbol']      = '0';
            $data['create_time'] = time();
            M('PayLog')->data($data)->add();
        }else{
            apiResponse('error','该订单号不存在');
        }
        apiResponse('success','充值成功');
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