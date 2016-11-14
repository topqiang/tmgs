<?php
namespace Wap\Controller;
/**
 * Class MemberController
 * @package Wap\Controller
 * 用户相关
 */
class MemberController extends BaseController
{

    /**
     * 注册协议
     */
    public function agreement()
    {
        $artcle = $this->artcle('2');
        $this->assign('deal',$artcle);
        $this->display();
    }

    /**
     * 登录
     */

    public function login()
    {
        $this->display();
    }

    /**
     * 注册
     */
    public function register()
    {
        $this->display();
    }

    /**
     * 修改密码
     */
    public function resetpwd()
    {
        $this->display();
    }

    /**
     * 收货地址列表
     */
    public function addresslist()
    {
        $this->display();
    }

    /**
     * 收藏列表
     */
    public function collection()
    {
        $this->display();
    }

    /**
     * 购物车
     */
    public function shopcart()
    {
        $this->display();
    }

    public function saveMid()
    {
        $m_id = $_POST['m_id'];
        if (isset($m_id)) {
            session("m_id",$m_id);
            $this -> ajaxReturn("success");
        } 
    }
    /**
     * 我的
     * array(1) {
    ["member_info"] => array(13) {
    ["m_id"] => string(1) "2"
    ["account"] => string(11) "18202229802"
    ["password"] => string(32) "e10adc3949ba59abbe56e057f20f883e"
    ["nickname"] => string(11) "18202229802"
    ["balance"] => string(8) "20000.00"
    ["head_pic"] => string(46) "http://2.taomim.com/Uploads/Member/default.png"
    ["easemob_account"] => string(15) "146924346437912"
    ["easemob_password"] => string(10) "1469243464"
    ["grade"] => string(1) "E"
    ["amount"] => string(4) "0.00"
    ["account_style"] => string(1) "1"
    ["password_style"] => string(1) "1"
    ["un_read_num"] => string(2) "22"
    }
    }
     */
    public function self()
    {
        $this->assign('info',session('member_info'));
        $this->display();
    }

    /**
     * 设置
     */
    public function setting()
    {
        $this->display();
    }

    /**
     * 绑定手机号
     */
    public function bindphone()
    {
        $this->display();
    }

    /**
     * 设置登录密码
     */
    public function setpwd()
    {
        $this->display();
    }

    /**
     * 修改密码
     */
    public function updatepwd()
    {
        $this->display();
    }

    /**
     * 个人资料
     */
    public function selfinfo()
    {
        $this->display();
    }

    /**
     * 我的钱包
     */
    public function mywallet()
    {
        $this->display();
    }

    /**
     * 我的银行卡
     */
    public function banklist()
    {
        $this->display();
    }

    /**
     * 添加银行卡
     */
    public function addbank()
    {
        $this->display();
    }

    /**
     * 添加银行返回
     */
    public function bankresult()
    {
        $this->display();
    }

    /**
     * 充值
     */
    public function recharge()
    {
        $this->display();
    }

    /**
     * 账单明细
     */
    public function transaction()
    {
        $this->display();
    }

    /**
     * 修改地址
     */
    public function editadd()
    {
        $this->display();
    }

    /**
     * 足迹
     */
    public function history()
    {
        $this->display();
    }

    /**
     * 我的推广/等级
     */
    public function mylevel()
    {
        $this->display();
    }

    /**
     * 推广获得收益
     */
    public function levelinfo()
    {
        $this->display();
    }


}