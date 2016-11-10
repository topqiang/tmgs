<?php
namespace Api\Controller;
use Think\Controller;

/**
 * Class ArticleController
 * @package Api\Controller
 * 文章系统
 */
class ArticleController extends BaseController{

    public function _initialize(){
        parent::_initialize();
    }


    /**
     * 用户注册协议
     * 传递参数的方式：post
     * 需要传递的参数：
     */
    public function userAgreement(){
        D('Article','Logic')->userAgreement(I('post.'));
    }

    /**
     * 帮助中心列表
     * 传递参数的方式：post
     * 需要传递的参数：
     * 语言：language ue英文，cn中文
     * 用户id：m_id
     * type：1系统消息，2订单消息
     */
    public function helpList(){
        D('Article','Logic')->helpList(I('post.'));
    }
    /**
     * 帮助中心文章详情
     * 传递参数的方式：post
     * 需要传递的参数：
     * 语言：language ue英文，cn中文
     * 用户id：m_id
     * help_id:文章id
     */
    public function helpInfo(){
        D('Article','Logic')->helpInfo(I('post.'));
    }

    /**
     * 设置界面
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户id：m_id
     * 语言：language ue英文，cn中文
     */
    public function setPage(){
        D('Article','Logic')->setPage(I('post.'));
    }

    /**
     * 关于我们
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户id：m_id
     */
    public function aboutUs(){
        D('Article','Logic')->aboutUs(I('post.'));
    }
    /**
     * 等级特权
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户id：m_id
     */
    public function rankPrivilege(){
        D('Article','Logic')->rankPrivilege(I('post.'));
    }
    /**
     * 升级规则
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户id：m_id
     */
    public function exlimitRules(){
        D('Article','Logic')->exlimitRules(I('post.'));
    }
    /**
     * 联系我们
     * 传递参数的方式：post
     * 需要传递的参数：
     * 语言版本：language：cn 中文  ue英文
     * 用户id：m_id
     * 未取消息条数： $un_read_num
     * 客服账号：$service['account']
     * 客服头像： $service['head_pic']
     * 客服电话：$config['SERVICE_LINE']
     */
    public function onlineService(){
        D('Article','Logic')->onlineService(I('post.'));
    }
    /**
     * 商家协议
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户id：m_id
     */
    public function merchantRules(){
        D('Article','Logic')->merchantRules(I('post.'));
    }
    /**
     * 保证金问题
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户id：m_id
     */
    public function securityRules(){
        D('Article','Logic')->securityRules(I('post.'));
    }
}