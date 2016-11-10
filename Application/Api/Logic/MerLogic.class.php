<?php
namespace Api\Logic;
use Manager\Controller\PluginsController;

/**
 * Class MerLogic
 * @package Api\Logic
 */
class MerLogic extends BaseLogic{

    /**
     * @param array $request
     * 商家信息
     * 用户ID    m_id
     */
    public function merchantCenter($request = array()){
        //用户ID不能为空
        if(empty($request['merchant_id'])){
            apiResponse('error','商家id不能为空');
        }
        //查询用户信息
        $where['id']      = $request['merchant_id'];
        $where['status']  = array('neq',9);
        // 商家权重 : integrity_merchant_type 1铂金 2钻石 3诚信
        $member_info = M('Merchant') -> where($where)
            ->field('id as merchant_id, account, password, easemob_account, easemob_password, merchant_name, head_pic, status, integrity_merchant_status, integrity_merchant_cost, integrity_merchant_type')
            ->find();
        if(empty($member_info)){
            apiResponse('error','该商家不存在');
        }
        if($member_info['head_pic'] == 0){
            $member_info['head_pic'] = C('API_URL').'/Uploads/Merchant/default.png';
        }else{
            $path = M('File')->where(array('id'=>$member_info['head_pic']))->getField('path');
            $member_info['head_pic'] = $path?C('API_URL').$path:C('API_URL').'/Uploads/Merchant/default.png';
        }
        //获取未读消息的条数
        $un_read_num = $this->getUnReadMessageNum($member_info['merchant_id']);
        $member_info['un_read_num'] = ''.$un_read_num;
        $goods = M('Goods') ->where(array('merchant_id'=>$member_info['merchant_id'],'status'=>array('neq',9))) ->select();
        $goods_num = M('Goods') ->where(array('merchant_id'=>$member_info['merchant_id'],'status'=>array('neq',9))) ->count();
        $collect_num = M('Collect') ->where(array('type'=>1,'handle_id'=>$member_info['merchant_id'],'status'=>array('neq',9))) ->count();
        $sales = 0;
        foreach($goods as $k => $v){
            $sales = $sales + $v['sales'];
        }
        if($member_info['integrity_merchant_type'] == 0){
            $member_info['integrity_merchant_picture'] = C('API_URL').'/Uploads/Merchant/default.png';
        }else{
            $m_t_type = M("MerchantAdvertising") ->where(array('location'=>$member_info['integrity_merchant_type'],'type'=>7)) ->find();
            $path = M('File') ->where(array('id'=>$m_t_type['show_pic'])) ->getField('path');
            $member_info['integrity_merchant_picture'] = $path?C("API_URL").$path:'';
        }
        $member_info['sales']     = $sales?$sales:'0';
        $member_info['goods_num'] = $goods_num?$goods_num.'':'0';
        $member_info['collect_num'] = $collect_num?$collect_num.'':'0';
        $member_info['service_phone'] = '8002';
        $member_info['service_head_pic'] = C('API_URL').'/Uploads/Member/service.png';
        apiResponse('success','操作成功',$member_info);

    }

    /**
     * @param array $request
     * 商家资料页
     * 商家ID      merchant_id
     */
    public function userBaseData($request = array()){
        //商家id不能为空
        if(empty($request['merchant_id'])){
            apiResponse('error','商家id不能为空');
        }
        //查询商家信息
        $where['id'] = $request['merchant_id'];
        $member_info = M('Merchant') ->where($where) ->field('id as merchant_id,head_pic,merchant_name,address,contact_mobile,integrity_merchant_cost') ->find();
        if(empty($member_info)){
            apiResponse('error','用户信息不存在');
        }

        $path = M('File')->where(array('id'=>$member_info['head_pic']))->getField('path');
        $member_info['head_pic'] = $path?C('API_URL').$path:C('API_URL').'/Uploads/Merchant/default.png';

        $result_data = array();
        $result_data['merchant_id']    = $member_info['merchant_id'];
        $result_data['head_pic']       = $member_info['head_pic'];
        $result_data['merchant_name']  = $member_info['merchant_name']?$member_info['merchant_name']:'';
        $result_data['address']        = $member_info['address']?$member_info['address']:'';
        $result_data['contact_mobile'] = $member_info['contact_mobile']?$member_info['contact_mobile']:'';
        $result_data['integrity_merchant_cost'] = $member_info['integrity_merchant_cost']?$member_info['integrity_merchant_cost']:'0';

        apiResponse('success','操作成功',$result_data);
    }

    /**
     * 修改商家资料
     * 传递参数的方式：post
     * 需要传递的参数：
     * 商家ID：merchant_id
     * 头像：    head_pic（可以为空）
     * 商家名称: merchant_name（可以为空）
     * 商家地址：address(可以为空)
     */
    public function modifyBaseData($request = array()){
        if(!$request['merchant_id']){
            apiResponse('error','商家ID不能为空');
        }
        if($request['merchant_name']){
            $data['merchant_name'] = $request['merchant_name'];
        }
        if($request['address']){
            $data['address'] = $request['address'];
        }
        //上传头像
        if (!empty($_FILES['head_pic']['name'])) {
            $res = api('UploadPic/upload', array(array('save_path' => 'Merchant')));
            foreach ($res as $value) {
                $head_pic = $value['id'];
                $data['head_pic'] = $head_pic;
            }
        }
        $where['id'] = $request['merchant_id'];
        $data['update_time'] = time();
        $result = M('Merchant') ->where($where) ->data($data) ->save();
        if(!$result){
            apiResponse('error','修改信息失败');
        }
        apiResponse('success','修改信息成功');
    }

    /**
     * 解冻保障金
     * 传递参数的方式：post
     * 需要传递的参数：
     * 商家ID： merchant_id
     * 验证码： verify
     */
    public function thawSecurity($request = array()){
        //商家ID不能为空
        if(!$request['merchant_id']){
            apiResponse('error','商家ID不能为空');
        }
        //验证码不能为空
        if(!$request['verify']){
            apiResponse('error','验证码不能为空');
        }
        $merchant = M('Merchant') ->where(array('id'=>$request['merchant_id'])) ->find();
        if(!$merchant){
            apiResponse('error','商家信息有误');
        }
        $result['mobile'] = $merchant['account'];
        //对验证码进行验证
        $this ->_checkVerify($result['mobile'],$request['verify'],'security');
        //将解冻的保证金返回给商家余额
        $where['id'] = $request['merchant_id'];
        $data['integrity_merchant_cost'] = 0;
        $data['balance'] = $merchant['balance'] + $merchant['integrity_merchant_cost'];
        $data['update_time'] = time();
        $data['integrity_merchant_status'] = 2;
        $data['integrity_merchant_type'] = 0;
        $result_data = M('Merchant') ->where($where) ->data($data) ->save();
        if(!$result_data){
            apiResponse('error','解冻保证金失败');
        }
        apiResponse('success','解冻保证金成功');
    }

    /**
     * 保障金列表
     */
    public function securityList ($request = array()){
        $result = M('MerchantAdvertising') ->where(array('type'=>7,'status'=>array('neq',9)))
            ->field('id as m_a_id, money, title, content, location, pic') ->select();
        if(!$result){
            $result = array();
            apiResponse('success','未找到相关信息',$result);
        }
        foreach($result as $k => $v){
            $path = M('File') ->where(array('id'=>$v['pic'])) ->getField('path');
            $result[$k]['pic'] = $path?C('API_URL').$path:'';
        }
        apiResponse('success','',$result);
    }
    /**
     * 保障金余额下单
     * 商家ID        merchant_id
     * 诚信商家ID    m_a_id
     * 金额          money
     * 诚信级别      location
     * 付款方式      type    1  支付宝支付  2  微信支付  4  余额支付
     */
    public function securityOrder($request = array()){
        //商家ID不能为空
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
        if($request['type'] != 1&&$request['type'] != 2&&$request['type'] != 4){
            apiResponse('error','付款方式有误');
        }
        $merchant_advertising = M('MerchantAdvertising') ->where(array('id'=>$request['m_a_id'],'status'=>array('neq',9))) ->find();
        if(!$merchant_advertising){
            apiResponse('error','该诚信ID传值有误');
        }
        //查询商家信息
        $merchant = M('Merchant') ->where(array('id'=>$request['merchant_id'])) ->find();
        if($request['type'] == 4){
            if($merchant['balance'] < $request['money']){
                apiResponse('error','您的余额不足');
            }
            //修改状态
            $where['id']                       = $request['merchant_id'];
            $data['balance']                   = $merchant['balance'] - $request['money'];
            $data['integrity_merchant_status'] = 1;
            $data['integrity_merchant_cost']   = $request['money'];
            $data['integrity_merchant_type']   = $request['location'];
            $data['update_time']               = time();
            $result = M('Merchant') ->where($where) ->data($data) ->save();
            if(!$result){
                apiResponse('error','缴纳保证金失败');
            }
            //写入状态值
            unset($where);
            unset($data);
            $data['type']      = 2;
            $data['object_id'] = $request['merchant_id'];
            $data['title']     = '支出';
            $data['content']   = '保证金缴纳';
            $data['symbol']    = '0';
            $data['money']     = $request['money'];
            $data['create_time'] = time();
            $res = M('PayLog') ->add($data);
            $result = array();
            apiResponse('success','缴纳成功',$result);
        }else{
            $data['order_security_sn'] = time().rand(10000,99999);
            $data['merchant_id']       = $request['merchant_id'];
            $data['type']              = $request['type'];
            $data['money']             = $request['money'];
            $data['grade']             = $merchant_advertising['location'];
            $res = M('Security') ->add($data);
            $result['order_security_sn'] = $data['order_security_sn'];
            $result['money']             = $data['money'];
            apiResponse('success','缴纳成功',$result);
        }
    }
    /**
     * @param $verify
     * @param $type
     * @param $language
     * 检查验证码是否正确
     */
    public function _checkVerify($account,$verify,$type){
        $where['way']  = $account;
        $where['vc']   = $verify;
        $where['type'] = $type;
        //检查验证码是否错误
        $sms_info = M('Sms')->where($where)->find();

        if(empty($sms_info)){
            apiResponse('error','验证码错误');
        }

        //检查验证码是否过期
        if($sms_info['expire_time']<time()){
            apiResponse('error','验证码已过期');
        }
    }

    /**
     * 商家的钱包
     * 商家ID    merchant_id
     */
    public function merchantWallet($request = array()){
        if(!$request['merchant_id']){
            apiResponse('error','商家ID不能为空');
        }
        $merchant = M('Merchant') ->where(array('id'=>$request['merchant_id'])) ->find();
        if(!$merchant){
            apiResponse('error','商家信息有误');
        }
        $where['merchant_id'] = $request['merchant_id'];
        $where['status'] = array('in','1,2,3');
        $tradeprice = M('Order') ->where($where) ->getField('SUM(trade_price) as tradeprice');
        $result['merchant_id'] = $request['merchant_id'];
        $result['balance'] = $merchant['balance']?$merchant['balance'].'':'0.00';
        $result['tradeprice'] = $tradeprice?$tradeprice.'':'0.00';
        apiResponse('success','',$result);
    }
    /**
     * 添加银行卡
     * 用户ID      merchant_id
     * 持卡人姓名  name
     * 银行卡ID    bank_id
     * 银行卡卡号  card_number
     * 身份证号    id_card
     * 联系电话    phone
     */
    public function addBankCard($request = array()){
        //用户ID不能为空
        if(!$request['merchant_id']){
            apiResponse('error','商家ID不能为空');
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
        $where['type']        = 2;
        $where['status']      = array('neq',9);
        $res = M('MemberCard') ->where($where) ->find();
        if($res){
            apiResponse('error','该银行卡已被注册');
        }
        $data['m_id']        = $request['merchant_id'];
        $data['type']        = 2;
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
     * 商家ID    merchant_id
     */
    public function merchantCardList($request = array()){
        //用户ID不能为空
        if(!$request['merchant_id']){
            apiResponse('error','商家ID不能为空');
        }
        //查询绑定的银行卡账号
        $where['m_id'] = $request['merchant_id'];
        $where['type'] = 2;
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
     * 商家ID      merchant_id
     * 银行卡ID    card_id
     */
    public function deleteCardList($request = array()){
        //用户ID不能为空
        if(!$request['merchant_id']){
            apiResponse('error','商家ID不能为空');
        }
        //我的银行卡ID不能为空
        if(!$request['card_id']){
            apiResponse('error','银行卡ID不能为空');
        }
        $where['m_id']    = $request['merchant_id'];
        $where['type']    = 2;
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
     * 提现
     * 商家ID    merchant_id
     * 提现金额  money
     * 银行卡ID  m_c_id
     */
    public function withdraw($request = array()){
        //用户ID不能为空
        if(!$request['merchant_id']){
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
        $merchant = M('Merchant') ->where(array('id'=>$request['merchant_id'])) ->field('id as merchant_id, balance') ->find();
        if($merchant['balance']<$request['money']){
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

        $data['type']      = 2;
        $data['object_id'] = $request['merchant_id'];
        $data['m_c_id']    = $request['m_c_id'];
        $data['money']     = $request['money'];
        $data['create_time'] = time();
        $result = M('Withdraw') ->add($data);
        if(!$result){
            apiResponse('error','操作失败');
        }
        unset($data);
        $res = M('Merchant')->where(array('id'=>$request['merchant_id']))->setDec('balance',$request['money']);
        if(!$res){
            apiResponse('error','提现失败');
        }
        unset($data);
        unset($where);
        $data['type']    = 2;
        $data['object_id'] = $request['merchant_id'];
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
        if(empty($request['merchant_id'])){
            apiResponse('error','商家id不能为空');
        }
        //分页参数不能为空
        if(empty($request['p'])){
            apiResponse('error','分页参数不能为空');
        }
        //查询账单信息
        $where['object_id'] = $request['merchant_id'];
        $where['type'] = 2;
        $pay_log_list = M('PayLog')->where($where)->field('id as p_l_id, title, content, symbol, money, create_time')
            ->order('create_time desc') ->page($request['p'].',15') ->select();
        if(!$pay_log_list){
            apiResponse('error','无更多数据');
        }
        foreach($pay_log_list as $k =>$v){
            $pay_log_list[$k]['create_time'] = date('Y-m-d',$v['create_time']);
        }
        apiResponse('success','请求成功',$pay_log_list);
    }

    /**
     * @param array $request
     * 修改密码
     * 商家id：merchant_id
     * 旧密码：old_password
     * 新密码：new_password
     * 第二次密码：sec_password
     */
    public function modifyPassword($request = array()){
        //用户id不能为空
        if(empty($request['merchant_id'])){
            apiResponse('error','用户id不能为空');
        }
        //原密码不能为空
        if(empty($request['old_password'])){
            apiResponse('error','请输入原密码');
        }
        //新密码不能为空
        if(empty($request['new_password'])){
            apiResponse('error','请输入新密码');
        }
        //第二次密码不能为空
        if(empty($request['sec_password'])){
            apiResponse('error','请再次输入新密码');
        }
        //对密码进行验证
        $new_password = strlen($request['new_password']);
        if($new_password<6){
            apiResponse('error','密码长度不能小于6位');
        }

        if($request['new_password'] != $request['sec_password']){
            apiResponse('error','两次密码输入不一致');
        }

        //查询原密码是否正确并修改新密码
        $where['id'] = $request['merchant_id'];
        $where['password'] = md5($request['old_password']);
        $res = M('Merchant')->where($where)->count();
        if($res<=0){
            apiResponse('error','原密码错误');
        }
        $res = M('Merchant')->where(array('id'=>$request['merchant_id']))->data(array('password'=>md5($request['new_password']),'update_time'=>time()))->save();
        if($res){
            apiResponse('success','修改密码成功');
        }else{
            apiResponse('error','修改密码失败');
        }
    }

    /**
     * 修改绑定手机号第一步
     * 商家ID      merchant_id
     * 手机号码    account
     * 验证码      verify
     */
    public function modifyAccountOne($request = array()){
        //用户ID不能为空
        if(!$request['merchant_id']){
            apiResponse('error','商家ID不能为空');
        }
        //手机号码不能为空
        if(!$request['account']){
            apiResponse('error','请填写手机号码');
        }
        if(!preg_match(C('MOBILE'),$request['account'])) {
            apiResponse('error', '手机号码格式错误');
        }
        //验证码不能为空
        if(!$request['verify']){
            apiResponse('error','请填写验证码');
        }
        //检查验证码是否正确
        $this ->_checkVerify($request['account'],$request['verify'],'merbind');
        $where['id']      = $request['merchant_id'];
        $where['account'] = $request['account'];
        $result = M('Merchant') ->where($where) ->find();
        if(!$result){
            apiResponse('error','手机账号有误');
        }
        apiResponse('success','操作成功');
    }
    /**
     * 绑定手机号第二步
     * 传递参数的方式：post
     * 需要传递的参数：
     * 商家ID    merchant_id
     * 手机账号  account
     * 验证码    verify
     */
    public function threeAccount($request = array()){
        //用户ID不能为空
        if(!$request['merchant_id']){
            apiResponse('error','商家ID不能为空');
        }
        //手机账号不能为空
        if(!$request['account']){
            apiResponse('error','请输入手机号');
        }
        if(!preg_match(C('MOBILE'),$request['account'])) {
            apiResponse('error', '手机号码格式错误');
        }
        //验证码不能为空
        if(!$request['verify']){
            apiResponse('error','请输入验证码');
        }
        //对验证码进行验证
        $this ->_checkVerify($request['account'],$request['verify'],'merbind');

        $where['account'] = $request['account'];
        $where['status']  = array('neq',9);
        $account = M('Merchant') ->where($where) ->find();
        if($account){
            apiResponse('error','该手机号已被注册');
        }
        //绑定新的手机号
        unset($where);
        $where['id'] = $request['merchant_id'];
        $data['account'] = $request['account'];
        $data['update_time'] = time();
        $member = M('Merchant') ->where($where) ->data($data) ->save();
        if(!$member){
            apiResponse('error','绑定手机号失败');
        }
        apiResponse('success','绑定手机号成功');
    }

    /**
     * @param array $request
     * 保证金订单是否成功
     */
    public function collaterOrderRefer($request = array())
    {
        if(empty($request['merchant_id']) || !isset($request['merchant_id'])) apiResponse('error','商家ID不能为空');
        if(empty($request['order_security_sn']) || !isset($request['order_security_sn']))apiResponse('error','订单ID不能为空');
        $where['merchant_id'] = $request['merchant_id'];
        $where['order_security_sn'] = $request['order_security_sn'];
        $result['status'] = M('Security') -> where($where) -> getField('status');
        $result['status'] = $result['status'] ? $result['status'] : "0";
        apiResponse('success','',$result);
    }

    /**
     * @param array $request
     * 环信列表
     */
    public function merchantEsmobList($request = array())
    {
        if(empty($request['es_list']) || !isset($request['es_list'])) apiResponse('error','商家ID不能为空');
        $es_list = json_decode($request['es_list'],true);
        $result = array();
        foreach($es_list as $k=>$v)
        {
            $result[$k]['user'] = M('Member') -> where(array('id'=>$v['esmob_account']))->field('head_pic,nickname')->find();
            $result[$k]['user']['head_pic'] = M('file') -> where(array('id'=>$result[$k]['user']['head_pic']))->getField('path');
            $result[$k]['user']['head_pic'] = $result[$k]['user']['head_pic'] ? C('API_URL').$result[$k]['user']['head_pic']:'';
        }
        apiResponse('success','',$result);
    }


}