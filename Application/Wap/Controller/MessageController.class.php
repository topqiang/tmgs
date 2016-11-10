<?php
namespace Wap\Controller;

/**
 * Class MessageController
 * @package Wap\Controller
 * 消息相关
 */
class MessageController extends BaseController{

    /**
     * 消息列表
     */
    public function msg()
    {
        $this->display();
    }

    /**
     *  系统消息
     */
    public function sysmsg()
    {
        $this->display();
    }

    /**
     *  消息详情
     */
    public function msginfo()
    {
        $this->display();
    }
}