<?php

namespace Merchant\Controller;

/**
 * [诚信商家]
 * @author zhouwei
 * Class ActionController
 * @package Merchant\Controller
 */
class SecurityController extends BaseController
{

    private $merchant = '';
    private $model_merchant = '';
    private $model_security = '';

    public function _initialize()
    {
        $this->merchant = session('merInfo');
        $this->model_merchant = M('Merchant');
        $this->model_security = M('Security');
        parent::_initialize(); // TODO: Change the autogenerated stub
    }

    public function index()
    {
        $merchant = $this->merchant['mer_id']; // 商家信息
        $this->assign('merchant',$this->model_merchant->where(array('id'=>$merchant))->find());
        $this->display('index');

    }

    /**
     *  读出诚信商家的信息
     */
    public function protocol()
    {
        $this->assign('protocol',M('Article')->where(array('id'=>14))->find());
        $this->display('protocol');
    }

    /**
     * 选择业务
     */
    public function securityOpt()
    {
        $model = M('MerchantAdvertising')->where(array('type'=>7))->order('location DESC')->select();
        foreach($model as $k=>$v){
            $model[$k]['pic'] = M('file')->where(array('id'=>$v['pic']))->getField('path');
        }
        $this->assign('merAdvert',$model);
        $this->display('securityOpt');

    }

    /**
     * 选择 支付方式
     */
    public function selectPayWay()
    {
        if($_GET['id']){
            $advertModel = M('MerchantAdvertising')->where(array('id'=>$_GET['id']))->field('money,id')->find();
        }
        $this->assign('money',$advertModel);
        $this->display('selectPayWay');
    }

    /**
     * 下单
     */
    public function placeOrder()
    {
        $post = $_POST;
        $Model = M('MerchantAdvertising')->where(array('id'=>$post['sid']))->field('money,location')->find();
        $data['order_security_sn'] = time().rand('11111','99999');
        $data['merchant_id'] = $this->merchant['mer_id'];
        $data['type'] = $post['type'];
        $data['create_time'] = time();
        if($data['type'] == 4){
            $balance = $this->model_merchant->where(array('id'=>$this->merchant['mer_id']))->getField('balance');
            if($balance*1 < $Model['money']*1){
                $this->ajaxReturn(array('data'=>'','info'=>'余额不足','status'=>0,'url'=>''));
            }
        }
        $data['status'] = 0;
        $data['money'] = $Model['money'];
        $data['grade'] = $Model['location'];
        $add = M('Security') -> data($data) -> add();

        if($add){
            if($post['type'] == 4){
                $this->model_merchant -> where(array('id'=>$this->merchant['mer_id'])) -> setDec('balance',$Model['money']); // 当时余额支付的时候 不跳转直接完成
                $this->model_merchant -> where(array('id'=>$this->merchant['mer_id'])) -> data(array('integrity_merchant_status'=>1,'integrity_merchant_cost'=>$Model['money'],'integrity_merchant_type'=>$Model['location'])) -> save();
                $this->model_security -> where(array('id'=>$add)) -> data(array('status'=>1)) -> save();
                $this->addPayLog(array('type'=>2,'object_id'=>$this->merchant['mer_id'],'title'=>'诚信商家办理','content'=>'余额支付','symbol'=>0,'money'=>$Model['money']));
                $this->ajaxReturn(array('data'=>'','info'=>'办理成功','status'=>1,'url'=>U('Security/index')));
            }else{
                $this->ajaxReturn(array('data'=>'','info'=>'办理成功','status'=>1,'url'=>''));
            }
        }else{
            $this->ajaxReturn(array('data'=>'','info'=>'办理失败','status'=>0,'url'=>''));

        }
    }

    /**
     * 解除
     */
    public function relieve()
    {
        $this->assign('account',M('Merchant')->where(array('id'=>$this->merchant['mer_id']))->getField('account'));
        $this->display('relieve');
    }

    /**
     * 得到验证码
     */
    public function getCode()
    {
        $post = $_POST;
        if(!preg_match(C('MOBILE'),trim($post['account']))) {
            $this->error('请输入正确的手机号');
        }
        /* 手机号 , 发送方式*/
        $data = D('Sms')->sendVerify(trim($post['account']),'security');
        if(!empty($data['success'])){
            $this -> ajaxReturn(array('status'=>'1','info'=>$data['success']));
        }else{
            $this -> ajaxReturn(array('status'=>'0','info'=>$data['error']));
        }
    }

    /**
     * 解冻提交
     */
    public function relieveRefer()
    {
        $post = $_POST;
        $model_code = M('sms')->where(array('type'=>'merbind','way'=>$post['account'],'vc'=>$post['code'],'expire_time'=>array('gt',time())))->count();
        if($model_code < 0)$this->error('验证失败');
        $merchant_id = $this->merchant['mer_id'];
        $model = $this->model_merchant->where(array('id'=>$merchant_id))->find();
        $data['balance'] = $model['balance'] + $model['integrity_merchant_cost'];
        $data['integrity_merchant_status'] = 2;
        $data['integrity_merchant_cost'] = 0;
        $data['integrity_merchant_type'] = 0;
        $add = $this->model_merchant->where(array('id'=>$merchant_id))->data($data)->save();
        if($add){
            $this->addPayLog(array('type'=>2,'object_id'=>$this->merchant['mer_id'],'title'=>'诚信商家解冻','content'=>'增加余额','symbol'=>1,'money'=>$model['integrity_merchant_cost']));
            $this -> ajaxReturn(array('status'=>'1','info'=>'退款成功','url'=>U('Security/index')));
        }else{
            $this -> ajaxReturn(array('status'=>'0','info'=>'退款失败,请重新尝试'));

        }

    }


}
