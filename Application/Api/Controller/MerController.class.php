<?php
namespace Api\Controller;
use Think\Controller;

/**
 * Class MerController
 * @package Api\Controller
 * 商家信息模块
 */
class MerController extends BaseController{

    public function _initialize(){
        parent::_initialize();
    }

    /**
     * @param array $request
     * 个人中心
     * 商家ID    m_id
     */
    public function merchantCenter(){
        D('Mer','Logic')->merchantCenter(I('post.'));
    }

    /**
     * @param array $request
     * 个人资料页
     * 商家ID      m_id
     */
    public function userBaseData(){
        D('Mer','Logic')->userBaseData(I('post.'));
    }

    /**
     * 修改商家资料
     * 传递参数的方式：post
     * 需要传递的参数：
     * 语言版本：language：cn 中文  ue英文
     */
    public function modifyBaseData(){
        D('Mer','Logic')->modifyBaseData(I('post.'));
    }

    /**
     * 解冻保障金
     */
    public function thawSecurity (){
        D('Mer','Logic') ->thawSecurity(I('post.'));
    }

    /**
     * 保障金列表
     */
    public function securityList (){
        D('Mer','Logic') ->securityList(I('post.'));
    }

    /**
     * 保障金下单
     */
    public function securityOrder(){
        D('Mer','Logic') ->securityOrder(I('post.'));
    }

    /**
     * 我的钱包
     */
    public function merchantWallet(){
        D('Mer','Logic') ->merchantWallet(I('post.'));
    }

    /*环信列表*/
    public function merchantEsmobList(){
        D('Mer','Logic') ->merchantEsmobList(I('post.'));
    }

    /**
     * 支付宝回调
     */
    public function alipayNotify(){
        $out_trade_no = $_POST['out_trade_no'];
        $where['order_security_sn'] = $out_trade_no;
        $where['status'] = array('eq',0);
        $security = M('Security')->where($where)->find();
        if($security){
            //修改保障金订单状态
            unset($where);
            $where['id'] = $security['id'];
            $data['type'] = 1;
            $data['status'] = 1;
            $res = M('Security')->where($where)->data($data)->save();
            if(!$res){
                apiResponse('error','保证金下单失败');
            }
            //添加用户余额
            unset($where);
            $where['id'] = $security['merchant_id'];
            $data['integrity_merchant_status'] = 1;
            $data['integrity_merchant_cost'] = $security['money'];
            $data['integrity_merchant_type'] = $security['grade'];
            $data['update_time']             = time();
            $result = M('Merchant') ->where($where) ->data($data) ->save();
            if(!$result){
                apiResponse('error','修改状态失败');
            }
            //添加账单明细
            unset($data);
            $data['type']   = '2';
            $data['object_id']   = $security['merchant_id'];
            $data['title']       = '保证金支付';
            $data['content']     = '支付宝支付';
            $data['money']       = $security['money'];
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
        $where['order_security_sn'] = $xml_res["out_trade_no"];
        $where['status'] = array('eq',0);
        $security = M('Security') ->where($where) ->find();
        if($security){
            //修改订单状态
            unset($where);
            $where['id'] = $security['id'];
            $data['type'] = 2;
            $data['status'] = 1;
            $res = M('Security')->where($where)->data($data)->save();
            if(!$res){
                apiResponse('error','微信充值失败');
            }
            //修改商家状态
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
            $data['type']   = '2';
            $data['object_id']   = $security['merchant_id'];
            $data['title']       = '保证金支付';
            $data['content']     = '微信支付';
            $data['money']       = $security['money'];
            $data['symbol']      = '0';
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
        $where['order_security_sn'] =$_POST['order_security_sn'];
        $recharge_info =M('Security')->where($where)->find();
        if($recharge_info['status'] == 0){
            $result_data['status'] = '0';
            apiResponse('success','支付成功',$result_data);

        }else{
            $result_data['status'] = '1';
            apiResponse('success','支付成功',$result_data);

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
        D('Mer','Logic') -> addBankCard(I('post.'));
    }
    /**
     * 我的银行卡列表
     * 用户ID    m_id
     */
    public function merchantCardList(){
        D('Mer','Logic') -> merchantCardList(I('post.'));
    }
    /**
     * 删除银行卡
     * 用户ID      m_id
     * 银行卡ID    card_id
     */
    public function deleteCardList(){
        D('Mer','Logic') -> deleteCardList(I('post.'));
    }

    /**
     * 提现
     * 用户ID    m_id
     * 提现金额  money
     * 银行卡ID  m_c_id
     */
    public function withdraw(){
        D('Mer','Logic') -> withdraw(I('post.'));
    }

    /**
     * 明细
     * 用户ID    m_id
     */
    public function detailList(){
        D('Mer','Logic') -> detailList(I('post.'));
    }

    /**
     * @param array $request
     * 修改密码
     * 用户id：m_id
     * 旧密码：old_password
     * 新密码：new_password
     * 第二次密码：sec_password
     */
    public function modifyPassword(){
        D('Mer','Logic')->modifyPassword(I('post.'));
    }

    /**
     * 修改绑定手机号第一步
     * 用户ID      m_id
     * 手机号码    account
     * 验证码      verify
     */
    public function modifyAccountOne(){
        D('Mer','Logic') ->modifyAccountOne(I('post.'));
    }

    /**
     * 三方绑定手机号//绑定手机号第二步
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户ID    m_id
     * 手机账号  account
     * 验证码    verify
     */
    public function threeAccount(){
        D('Mer','Logic')->threeAccount(I('post.'));
    }

    /* 保证金订单是否成功 */
    public function collaterOrderRefer()
    {
        D('Mer','Logic')->collaterOrderRefer(I('post.'));
    }

}