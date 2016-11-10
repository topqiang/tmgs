<?php
namespace Api\Controller;
use Think\Controller;

/**
 * Class MerRegisterController
 * @package Api\Controller
 * 商家登录注册模块
 */
class MerRegisterController extends BaseController{

    public function _initialize(){
        parent::_initialize();
    }

    /**
     * @param array $request
     * 发送验证码
     * 用户手机号   account
     * 验证码类型  activity  注册  bind  绑定  reset  找回
     */
    public function sendVerify(){
        D('MerRegister','Logic')->sendVerify(I('post.'));
    }

    /**
     * @param array $request
     * 注册
     * 用户手机号    account
     * 账号密码      password
     * 确认密码      sec_password
     * 验证码        verify
     */
    public function register(){
        D('MerRegister','Logic')->register(I('post.'));
    }

    /**
     * @param array $request
     * 用户账号登录
     * 邮箱账号：account
     * 密码：password
     */
    public function login(){
        D('MerRegister','Logic')->login(I('post.'));
    }

    /**
     * @param array $request
     *  忘记密码接口
     * 语言版本：language：cn 中文  ue英文
     * 邮箱账号：account
     * 验证码：verify
     * 新密码：new_password
     * 第二个密码：sec_password
     */
    public function resetPassword(){
        D('MerRegister','Logic')->resetPassword(I('post.'));
    }
    /**
     * @param array $request
     *  填写商家信息
     */
    public function merchantInformation(){
        D('MerRegister','Logic')->merchantInformation(I('post.'));
    }
    /**
     * @param array $request
     *  修改商家信息
     */
    public function modifyMerchantInformation(){
        D('MerRegister','Logic')->modifyMerchantInformation(I('post.'));
    }
}