<?php
namespace Api\Controller;
use Think\Controller;

/**
 * Class MessageController
 * @package Api\Controller
 * 消息
 */
class MessageController extends BaseController{

    public function _initialize(){
        parent::_initialize();
    }

    /**
     * 消息首页
     * 传递参数的方式：post
     * 需要传递的参数：
     * 语言：language ue 英文，cn:中文
     * 用户id：m_id
     *
     */
    public function messageIndex(){
        D('Message','Logic')->messageIndex(I('post.'));
    }

    /**
     * 消息列表
     * 传递参数的方式：post
     * 需要传递的参数：
     * 语言：language ue 英文，cn:中文
     * 用户id：m_id
     * 类型：type 1系统消息，2订单消息
     * 分页参数：p
     */
    public function messageList(){
        D('Message','Logic')->messageList(I('post.'));
    }

    /**
     * 消息详情
     * 传递参数的方式：post
     * 需要传递的参数：
     *  信息id：message_id
     * 语言：language ue 英文，cn:中文
     * 类型：type 1系统消息，2订单消息
     */
    public function messageInfo(){
        D('Message','Logic')->messageInfo(I('post.'));
    }

    /**
     * 商家消息首页
     * 传递参数的方式：post
     * 需要传递的参数：
     * 商家id：merchant_id
     *
     */
    public function merMessageIndex(){
        D('Message','Logic')->merMessageIndex(I('post.'));
    }

    /**
     * 商家消息列表
     * 传递参数的方式：post
     * 需要传递的参数：
     * 语言：language ue 英文，cn:中文
     * 用户id：m_id
     * 类型：type 1系统消息，2订单消息
     * 分页参数：p
     */
    public function merMessageList(){
        D('Message','Logic')->merMessageList(I('post.'));
    }

    /**
     * 意见反馈
     * 传递参数的方式：post
     * 需要传递的参数：
     * 商家id：merchant_id
     *
     */
    public function feedBack(){
        D('Message','Logic')->feedBack(I('post.'));
    }
}