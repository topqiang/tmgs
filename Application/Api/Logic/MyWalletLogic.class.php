<?php
namespace Api\Logic;

/**
 * Class MyWalletLogic
 * @package Api\Logic
 * 我的钱包
 */
class MyWalletLogic extends BaseLogic{

    /**
     * 银行卡列表
     * 无参数
     */
    public function bankCardList(){
        $bankcard = M('SupportBank') ->field('id as bank_id, bank_name, bank_icon') ->select();
        foreach($bankcard as $k =>$v){
            $bankcard[$k]['bank_icon'] = C('API_URL').'/Uploads/BankIcon/'.$v['bank_icon'];
        }
        if(!$bankcard){
            $bankcard = array();
        }
        apiResponse('success','',$bankcard);
    }

    /**
     * 银行卡列表
     * 无参数
     */
    public function bankCard(){
        $bankcard = M('SupportBank') ->field('id as bank_id, bank_name, bank_icon') ->select();
        foreach($bankcard as $k =>$v){
            $bankcard[$k]['bank_icon'] = C('API_URL').'/Uploads/BankIcon/'.$v['bank_icon'];
        }
        if(!$bankcard){
            $bankcard = array();
        }
        apiResponse('success','',$bankcard);
    }

    /**
     * 添加银行卡
     * 用户ID      m_id
     * 持卡人姓名  name
     * 银行卡ID    bank_id
     * 银行卡卡号  card_number
     * 身份证号    id_card
     * 联系电话    phone
     */
    public function addBankCard($request = array()){
        //用户ID不能为空
        if(!$request['m_id']){
            apiResponse('error','用户ID不能为空');
        }
        //持卡人姓名不能为空
        if(!$request['name']){
            apiResponse('error','持卡人姓名不能为空');
        }
        //银行卡ID不能为空
        if(!$request['bank_id']){
            apiResponse('error','银行卡ID不能为空');
        }
        //银行卡卡号不能为空
        if(!$request['card_number']){
            apiResponse('error','银行卡号不能为空');
        }
        //身份证号不能为空
        if(!$request['id_card']){
            apiResponse('error','身份证号不能为空');
        }
        //联系电话不能为空
        if(!$request['phone']){
            apiResponse('error','联系电话不能为空');
        }
        if(!preg_match(C('MOBILE'),$request['phone'])) {
            apiResponse('error', '手机号码格式错误');
        }
        //查询银行卡号信息
        $where['card_number'] = $request['card_number'];
        $res = M('MemberCard') ->where($where) ->find();
        if($res){
            apiResponse('error','该银行卡已被注册');
        }
        $data['m_id']        = $request['m_id'];
        $data['type']        = 1;
        $data['order_sn']    = time().rand(10000,99999);
        $data['name']        = $request['name'];
        $data['bank_id']     = $request['bank_id'];
        $data['card_number'] = $request['card_number'];
        $data['id_card']     = $request['id_card'];
        $data['phone']       = $request['phone'];
        $data['create_time'] = time();
        $result = M('MemberCard') ->add($data);
        if(!$result){
            apiResponse('error','绑定银行卡失败');
        }
        apiResponse('success','绑定银行卡成功');
    }
    /**
     * 我的银行卡列表
     * 用户ID    m_id
     */
    public function myCardList($request = array()){
        //用户ID不能为空
        if(!$request['m_id']){
            apiResponse('error','用户ID不能为空');
        }
        //查询绑定的银行卡账号
        $where['m_id'] = $request['m_id'];
        $where['type'] = 1;
        $where['status'] = array('neq',9);
        $result = M('MemberCard') ->where($where) ->field('id as card_id, bank_id, card_number') ->select();
        if(!$result){
            $result = array();
            apiResponse('success','您还未绑定银行卡',$result);
        }
        foreach($result as $k =>$v){
            $number = substr($v['card_number'],-4,4);
            unset($where);
            $where['id'] = $v['bank_id'];
            $card = M('SupportBank') ->where($where) ->field('id as bank_id, bank_name, bank_icon') ->find();
            $card['bank_icon'] = C('API_URL').'/Uploads/BankIcon/'.$card['bank_icon'];
            $result[$k]['bank_name'] = $card['bank_name'];
            $result[$k]['bank_icon'] = $card['bank_icon'];
            $result[$k]['card_number'] = $number;
        }
        apiResponse('success','',$result);
    }
    /**
     * 删除银行卡
     * 用户ID      m_id
     * 银行卡ID    card_id
     */
    public function deleteCardList($request = array()){
        //用户ID不能为空
        if(!$request['m_id']){
            apiResponse('error','用户ID不能为空');
        }
        //我的银行卡ID不能为空
        if(!$request['card_id']){
            apiResponse('error','银行卡ID不能为空');
        }
        $where['m_id']    = $request['m_id'];
        $where['type']    = 1;
        $where['id'] = $request['card_id'];
        $data['status']   = 9;
        $data['update_time'] = time();
        $result = M('MemberCard') ->where($where) ->data($data) ->save();
        if(!$result){
            apiResponse('error','删除失败');
        }
        apiResponse('success','删除成功');
    }
    /**
     * 充值
     * 用户ID    m_id
     * 充值金额  money
     * 充值状态  type  1  微信支付  2  支付宝支付  3  银联支付
     */
    public function recharge($request = array()){
        //用户ID不能为空
        if(!$request['m_id']){
            apiResponse('error','用户ID不能为空');
        }
        //充值金额不能为空
        if($request['money']<=0){
            apiResponse('error','请输入充值金额');
        }
        $data['order_sn'] = time().rand(10000,99999);
        $data['m_id'] = $request['m_id'];
        $data['money'] = $request['money'];
        $data['create_time'] = time();
        $res = M('Recharge') ->add($data);
        if(!$res){
            apiResponse('error','充值失败');
        }
        apiResponse('success','充值成功',$data['order_sn']);
    }
    /**
     * 提现
     * 用户ID    m_id
     * 提现金额  money
     * 银行卡ID  m_c_id
     */
    public function withdraw($request = array()){
        //用户ID不能为空
        if(!$request['m_id']){
            apiResponse('error','用户ID不能为空');
        }
        //提现金额不能为空
        if(!$request['money']){
            apiResponse('error','请输入提现金额');
        }
        //提现银行卡不能为空
        if(!$request['m_c_id']){
            apiResponse('error','请选择提现银行卡');
        }
        //查询用户余额
        $member = M('Member') ->where(array('id'=>$request['m_id'])) ->field('id as m_id, balance') ->find();
        if($member['balance']<$request['money']){
            apiResponse('error','您的余额不足');
        }
        //查询银行卡信息
        $where['id'] = $request['m_c_id'];
        $where['status'] = array('neq',9);
        $member_card = M('MemberCard') ->where($where) ->field('id as m_c_id, bank_id ,card_number') ->find();
        if(!$member_card){
            apiResponse('error','该银行卡不存在');
        }
        $bank_name = M('SupportBank') ->where(array('id'=>$member_card['bank_id'])) ->getField('bank_name');

        $data['type']      = 1;
        $data['object_id'] = $request['m_id'];
        $data['m_c_id']    = $request['m_c_id'];
        $data['money']     = $request['money'];
        $data['create_time'] = time();
        $result = M('Withdraw') ->add($data);
        if(!$result){
            apiResponse('error','操作失败');
        }
        unset($data);
        $res = M('Member')->where(array('id'=>$request['m_id']))->setDec('balance',$request['money']);
        if(!$res){
            apiResponse('error','提现失败');
        }
        unset($data);
        unset($where);
        $data['type']    = 1;
        $data['object_id'] = $request['m_id'];
        $data['title']   = '提现';
        $data['content'] = $bank_name;
        $data['symbol']  = '0';
        $data['money']   = $request['money'];
        $data['create_time'] = time();
        $result = M('PayLog') ->add($data);
        if(!$result){
            apiResponse('error','提现失败');
        }
        apiResponse('success','提现成功');
    }
    /**
     * 明细
     * 用户ID    m_id
     * 分页参数  p
     */
    public function detailList($request = array()){
        //用户ID不能为空
        if(empty($request['m_id'])){
            apiResponse('error','用户id不能为空');
        }
        //分页参数不能为空
        if(empty($request['p'])){
            apiResponse('error','分页参数不能为空');
        }
        //查询账单信息
        $where['object_id'] = $request['m_id'];
        $where['type'] = 1;
        $pay_log_list = M('PayLog')->where($where)->field('id as p_l_id, title, content, symbol, money, create_time')->order('create_time desc') ->page($request['p'].',15') ->select();
        if(!$pay_log_list){
            apiResponse('error','无更多数据');
        }
        foreach($pay_log_list as $k =>$v){
            $pay_log_list[$k]['create_time'] = date('Y-m-d',$v['create_time']);
        }
        apiResponse('success','请求成功',$pay_log_list);
    }
    /**
     * 我的钱包
     * 用户ID    m_id
     */
    public function myWallet($request = array()){
        if(!$request['m_id']){
            apiResponse('error','用户ID不能为空');
        }
        $where['id'] = $request['m_id'];
        $where['status'] = array('neq',9);
        $member = M('Member') ->where($where) ->field('id as m_id, balance') ->find();
        if(!$member){
            apiResponse('error','用户信息有误');
        }
        //获取结算中金额
        unset($where);
        $where['m_id'] = $member['m_id'];
        $where['status'] = array('IN',array(1,2,3));
        $where['remove_status'] = array('neq',1);
        $amount = M('Order') ->where($where) ->getField('SUM(totalprice) as totalprice');
        $member['amount'] = $amount?$amount:'0.00';
        apiResponse('success','',$member);
    }

}