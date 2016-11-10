<?php
namespace Wap\Controller;
/**
 * Class IndexController
 * @package Wap\Controller
 * 首页相关
 */
class IndexController extends BaseController{
    /**
     * 首页
     */
    public function index()
    {
        $new = new \Think\Jssdk('wx3d2cbf361bbf58f3','50a51debbed1f9dafdcfacfb30ddb94e');
        $return = $new->getSignPackage();
        $url = C('API_URL').'/index.php/Api/Index/index';
        $this->assign('parameter',$return);
        $this->assign('url',$url);
        $this->display();
    }

    /**
     * 发现
     */
    public function discover()
    {
        $this->display();
    }

    /**
     * 发现好货
     */
    public function goodsdiscover()
    {
        $this->display();
    }

    /**
     * 发现好店
     */
    public function shopdiscover()
    {
        $this->display();
    }

    /**
     * 转换坐标 并返回城市 lng 117.200983 lat 39.084158
     */
    public function geoIp()
    {
        $getCoord = D('Geo','Service');
//        $coord = $getCoord ->  conCoord();
        $geography = $getCoord -> reGeo($_GET['lat'],$_GET['lng']);
        $this->ajaxReturn($geography);
    }
}