<?php

namespace Wap\Controller;

/**
 * Class LoveController
 * @package Wap\Controller
 * 爱心相关
 */
class LoveController extends BaseController
{
    /**
     * 爱心帮扶首页
     */
    public function lovehelp()
    {
        $this->display();
    }

    /**
     * 爱心帮扶详情
     */
    public function loveinfo()
    {
        $this->display();
    }

    /**
     * 他的捐助
     */
    public function hislove()
    {
        $this->display();
    }
}