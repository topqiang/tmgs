<?php
namespace Wap\Controller;
use Think\Controller;

class BaseController extends Controller{

    /**
     * @param $url 提交地址
     * @param array $param 参数
     * @return mixed 字符串
     * post提交(返回字符串)
     */
    function PostUrl($url,$param=array())
    {
        $ch = curl_init() ;  // 创建CURL操作
        curl_setopt($ch,CURLOPT_POST,1); // 开启POST 传输
        curl_setopt($ch,CURLOPT_POSTFIELDS,$param) ; // 传输数据
        curl_setopt($ch,CURLOPT_URL,$url); // 设置url
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1); // 不返回网页,返回字符串
        $result = curl_exec($ch); // 所有操作后执行curl 操作
        curl_close($ch);  // 关闭CURL操作
        return $result;
    }

    /**
     * 协议相关
     */
    function artcle($id)
    {
        $model = M('Article')->where(array('id'=>$id))->field('title,cn_content as content')->find();
        return $model;
    }
}