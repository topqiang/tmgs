<?php

namespace Api\Controller;
/**
 * Class CheckInController
 * @package Api\Controller
 * 签到相关
 */
class CheckInController extends BaseController
{
    /*签到页面*/
    public function checkShow()
    {
        D(CONTROLLER_NAME,'Logic')->checkShow(I('post.'));
    }

    /*点击签到*/
    public function checkClick()
    {
        D(CONTROLLER_NAME,'Logic')->checkClick(I('post.'));
    }
}