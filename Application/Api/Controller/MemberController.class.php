<?php
namespace Api\Controller;
use Think\Controller;

/**
 * Class MemberController
 * @package Api\Controller
 * 用户模块
 */
class MemberController extends BaseController{

    public function _initialize(){
        parent::_initialize();
    }

    /**
     * 发送验证码
     * 传递参数的方式：post
     * 需要传递的参数：
     * 语言版本：language：cn 中文  ue英文
     * 邮箱账号：account
     * 类型：type  bind reset
     */
    public function sendVerify(){
        D('Member','Logic')->sendVerify(I('post.'));
    }

    /**
     * @param array $request
     * 个人中心
     * 用户ID    m_id
     */
    public function memberCenter(){
        D('Member','Logic')->memberCenter(I('post.'));
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
        D('Member','Logic')->modifyPassword(I('post.'));
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
        D('Member','Logic')->threeAccount(I('post.'));
    }
    /**
     * 三方设置登录密码
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户ID    m_id
     * 用户密码  password
     * 第二次密码  sec_password
     */
    public function threePassword(){
        D('Member','Logic')->threePassword(I('post.'));
    }

    /**
     * 修改绑定手机号第一步
     * 用户ID      m_id
     * 手机号码    account
     * 验证码      verify
     */
    public function modifyAccountOne(){
        D('Member','Logic') ->modifyAccountOne(I('post.'));
    }

    /**
     * @param array $request
     * 个人资料页
     * 用户ID      m_id
     */
    public function userBaseData(){
        D('Member','Logic')->userBaseData(I('post.'));
    }

    /**
     * 修改个人资料
     * 传递参数的方式：post
     * 需要传递的参数：
     * 语言版本：language：cn 中文  ue英文
     * 用户id：m_id
     * 头像：head_pic（可以为空）
     * 姓名: nickname（可以为空）
     * 年龄：age(可以为空)
     */
    public function modifyBaseData(){
        D('Member','Logic')->modifyBaseData(I('post.'));
    }
    /**
     * 好友列表
     */
    public function conversationList(){
        D('Member','Logic') ->conversationList(I('post.'));
    }
}