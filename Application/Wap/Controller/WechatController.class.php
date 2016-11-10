<?php
namespace Wap\Controller;

class WechatController extends BaseController{
	
    public function index(){
        echo U('Wechat/checkSignature');
    }
	public function checkSignature()
	{
		$signature = $_GET["signature"];
		$timestamp = $_GET["timestamp"];
		$nonce     = $_GET["nonce"];
		$token_obj = D('WechatToken')->getFind(array('field'=>'token'));
		$token     = $token_obj['token'];
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
	} 

	public function responseMsg(){
		 $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        if (!empty($postStr)){
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $fromUsername = $postObj -> FromUserName;   //消息的来源
            $toUsername = $postObj -> ToUserName;       //消息往哪个公共平台发送
            $eventType = $postObj -> Event;             //事件的类型 CLICK-subscribe-unsubscribe
            $key = $postObj -> EventKey;                //点击事件的键值

            //获取用户的openid和经纬度入库
            $arr = (array)$postObj;
            $data['lat'] = $arr['Latitude'];
            $data['lng'] = $arr['Longitude'];
            $data['openid'] = $arr['FromUserName'];
            $data['ctime'] = time();
            $Point = M("Point");
            $point_info = $Point->where(array('openid'=>$data['openid']))->find();
            if($point_info){
                if(!empty($data['lat']) && !empty($data['lng'])){
                    //更新用户坐标
                    $Point->where(array('openid'=>$data['openid']))->save($data);
                }
            }else{
                //保存用户坐标
                $Point->add($data);
            }

            $msgType = $postObj->MsgType;				//image-location-image-link-event-music-news
            $keyWord = trim($postObj -> Content);       //用户发送的信息
            $time = time();
            if(!empty($keyWord)){
                $this->sendMsg($keyWord,$fromUsername,$toUsername,$time);
            }
            //关注时
            if($eventType == 'subscribe'){
                $where['wxa_type'] = 1;
                $result = $this->weixinart->findWeiXinArticle($where);
                if($result){
                    $tpl = $this->textTpl($result);
                    $resultStr = sprintf($tpl, $fromUsername, $toUsername, $time);
                    echo $resultStr;
                }
            }
            // 帮其退出
            if($eventType == 'unsubscribe'){
                session('M_ID',null);
                session('openid',null);
                session('m_account',null);
                session_unset("m_account");//清空指定的session
                unset($_SESSION["m_account"]);//清空指定的session
                unset($_SESSION["M_ID"]);//清空指定的session
                unset($_SESSION["openid"]);//清空指定的session
            }

            //点击事件
            if($eventType == 'CLICK'){
                $this->sendMsg($key,$fromUsername,$toUsername,$time);
            }
        }else{
            $echoStr = $_GET["echostr"];
            if($this->checkSignature()){
                echo $echoStr;
                exit;
            }
        }
	}
}