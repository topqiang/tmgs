<?php
namespace Api\Controller;
use Think\Controller;
/**
 * Class MyWalletController
 * @package Api\Controller
 * 我的钱包模块
 */
class MyWalletController extends BaseController{

    public function _initialize(){
        parent::_initialize();
    }
    /**
     * 银行卡列表
     * 无参数
     */
    public function bankCardList(){
        D('MyWallet','Logic') -> bankCardList();
    }
    /**
     * 银行卡列表
     * 无参数
     */
    public function bankCard(){
        D('MyWallet','Logic') -> bankCard();
    }
    /**
     * 添加银行卡
     * 用户ID      m_id
     * 持卡人姓名  name
     * 银行卡ID    card_id
     * 银行卡卡号  card_number
     * 身份证号    id_card
     * 联系电话    phone
     */
    public function addBankCard(){
        D('MyWallet','Logic') -> addBankCard(I('post.'));
    }
    /**
     * 银行卡列表
     * 用户ID    m_id
     */
    public function myCardList(){
        D('MyWallet','Logic') -> myCardList(I('post.'));
    }
    /**
     * 删除银行卡
     * 用户ID      m_id
     * 银行卡ID    card_id
     */
    public function deleteCardList(){
        D('MyWallet','Logic') -> deleteCardList(I('post.'));
    }
    /**
     * 充值
     * 用户ID    m_id
     * 充值金额  money
     * 充值状态  type  1  微信支付  2  支付宝支付  3  银联支付
     */
    public function recharge(){
        D('MyWallet','Logic') -> recharge(I('post.'));
    }
    /**
     * 提现
     * 用户ID    m_id
     * 提现金额  money
     * 银行卡ID  m_c_id
     */
    public function withdraw(){
        D('MyWallet','Logic') -> withdraw(I('post.'));
    }
    /**
     * 支付宝回调
     */
    public function alipayNotify(){
        $out_trade_no = $_POST['out_trade_no'];
        $where['order_sn'] =$out_trade_no;
        $where['status'] = array('eq',0);
        $recharge_info =M('Recharge')->where($where)->find();
        if($recharge_info){
            //修改充值订单状态
            unset($where);
            $where['id'] = $recharge_info['id'];
            $data['recharge'] = 2;
            $data['update_time'] = time();
            $data['status']      = 1;
            $res = M('Recharge')->where($where)->data($data)->save();
            if(!$res){
                apiResponse('error','充值失败');
            }
            //添加用户余额
            unset($where);
            $where['id'] = $recharge_info['m_id'];
            $result = M('Member')->where($where)->setInc('balance',$recharge_info['money']);
            if(!$result){
                apiResponse('error','写入金额失败');
            }
            //添加账单明细
            unset($data);
            $data['type']   = '1';
            $data['object_id']   = $recharge_info['m_id'];
            $data['title']       = '支付宝支付';
            $data['content']     = '充值';
            $data['money']       = $recharge_info['money'];
            $data['symbol']      = '1';
            $data['create_time'] = time();
            M('PayLog')->data($data)->add();
        }
        apiResponse('success','充值成功');
    }
    /**
     * 微信回调
     */
    public function wXinNotify(){
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
        file_put_contents('wxnotify.txt',$xml);
        $xml_res = $this->xmlToArray($xml);
        $where['order_sn'] =$xml_res["out_trade_no"];
        $where['status'] = array('eq',0);
        $recharge_info = M('Recharge') ->where($where) ->find();
        if($recharge_info){
            //修改充值订单状态
            unset($where);
            $where['id'] = $recharge_info['id'];
            $data['pay_type'] = 1;
            $data['update_time'] = time();
            $data['status']      = 1;
            $res = M('Recharge')->where($where)->data($data)->save();
            if(!$res){
                apiResponse('error','微信充值失败');
            }

            //添加用户余额
            unset($where);
            $where['id'] = $recharge_info['m_id'];
            $result = M('Member')->where($where)->setInc('balance',$recharge_info['money']);
            if(!$result){
                apiResponse('error','写入记录失败');
            }

            //添加账单明细
            unset($data);
            $data['type']   = '1';
            $data['object_id']   = $recharge_info['m_id'];
            $data['title']       = '微信支付';
            $data['content']     = '充值';
            $data['money']       = $recharge_info['money'];
            $data['symbol']      = '1';
            $data['create_time'] = time();
            M('PayLog')->data($data)->add();
        }else{
            apiResponse('error','该订单号不存在');
        }
        apiResponse('success','充值成功');
    }

    /**
     * 查询订单状态
     * 需要传递的参数：
     * 订单编号：order_sn
     */
    public function findStatus(){
        $where['order_sn'] =$_POST['order_sn'];
        $recharge_info =M('Recharge')->where($where)->find();
        if($recharge_info['status']==0){
            $result_data['status'] = '0';
        }else{
            $result_data['status'] = '1';
        }
        apiResponse('success','请求成功',$result_data);
    }
    /**
     * 明细
     * 用户ID    m_id
     */
    public function detailList(){
        D('MyWallet','Logic') -> detailList(I('post.'));
    }
    /**
     * 我的钱包
     * 用户ID    m_id
     */
    public function myWallet(){
        D('MyWallet','Logic') -> myWallet(I('post.'));
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