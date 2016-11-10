<?php
namespace Api\Controller;
use Think\Controller;

/**
 * Class RegisterLogController
 * @package Api\Controller
 * 登录注册模块
 */
class RegisterLogController extends BaseController{

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
        D('RegisterLog','Logic')->sendVerify(I('post.'));
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
        D('RegisterLog','Logic')->register(I('post.'));
    }

    /**
     * @param array $request
     * 用户账号登录
     * 邮箱账号：account
     * 密码：password
     */
    public function login(){
        D('RegisterLog','Logic')->login(I('post.'));
    }

    /**
     * 三方登陆
     * 传递参数的方式：post
     * 需要传递的参数：
     * 三方账号    open_id
     * 三方类型    type  1  QQ  2  微信  3  微博
     */
    public function threeLogin(){
        D('RegisterLog','Logic')->threeLogin(I('post.'));
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
        D('RegisterLog','Logic')->resetPassword(I('post.'));
    }

}