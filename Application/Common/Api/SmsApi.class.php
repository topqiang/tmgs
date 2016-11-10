<?php
namespace Common\Api;
require_once './ThinkPHP/Library/Vendor/BasalticSms/nusoap.php';

/**
 * Class SmsApi
 * @package Common\Api
 * 消息发送接口
 */
class SmsApi {


    /**
     * 玄武科技短信文档
     * @param $receiver
     * @param $content
     */
//    public static function sendSms($receiver,$content){
//        //读取站点配置信息
//        $config = S('Config_Cache');
//        if(!$config){
//            $config = D('Config')->parseList();
//            S('Config_Cache',$config);
//        }
//        //添加配置到C函数
//        C($config);
//        $wsdl = './ThinkPHP/Library/Vendor/BasalticSms/a.wsdl';
//        $client = new \nusoap_client($wsdl,'wsdl');
//        $client->soap_defencoding = 'utf-8';
//        $client->decode_utf8 = false;
//        $client->xml_encoding = 'utf-8';
//        $params = array(
//            'account'=>C('SMS')['ACCOUNT'],
//            'password'=>C('SMS')['PASSWORD'],
//            'mtpack'=>array(
//                'uuid'=>$uid,
//                'batchID'=>$uid,
//                'batchName'=>'xu_test',
//                'sendType'=>0,
//                'msgType'=>1,
//                'msgs'=>array(
//                    'MessageData'=>array(
//                        array(
//                            'Content'=>$content,
//                            'Phone'=>$receiver,
//                            'vipFlag'=>true
//                        )
//
//                    )
//                ),
//                'distinctFlag'=>true,
//                'bizType'=>0,
//                'scheduleTime'=>0
//            )
//        );
//        $result = $client->call('Post', $params);
//        if($result['PostResult']['result']==0){
//            return true;
//        }else{
//            switch($result['PostResult']['result']){
//                case -1: return "短信平台余额不足";
//                case -2: return "短信平台参数无效";
//                case -3: return "短信平台连接不上服务器";
//                case -5: return "短信平台无效的短信数据，号码格式不对";
//                case -6: return "短信平台用户名密码错误";
//                case -7: return "短信平台旧密码不正确";
//                case -9: return "短信平台资金账户不存在";
//                case -11: return "短信平台包号码数量超过最大限制";
//                case -12: return "短信平台余额不足";
//                case -99: return "短信平台系统内部错误";
//                case -100: return "短信平台其它错误";
//            }
//        }
//
//
//
//
//
//
//    }

    /*
     * 九天企信短信Api
     */
//    public static function sendSms($receiver,$content){
//
//        //读取站点配置  先读取缓存
//        $config = S('Config_Cache');
//        if(!$config){
//            $config = D('Config')->parseList();
//            S('Config_Cache',$config);
//        }
//        //添加配置到 C函数
//        C($config);
//        //短信接口相关参数
//        $sms_cpid       = C('SMS')['CPID'];
//        $sms_password   = md5(C('SMS')['PASSWORD'] . "_" . time() . "_topsky");
//        $sms_phone      = $receiver; //手机号
//        $sms_channel_id = C('SMS')['CHANNEL'];
//        $sms_template   = $content; //发信内容
//        $sms_template   = urlencode(iconv("UTF-8","gb2312//ignore",$sms_template)); //处理信息的编码
//        $sms_url        = "http://admin.sms9.net/houtai/sms.php?"; //发送的网址
//        $sms_url       .= "cpid=$sms_cpid&password=$sms_password&msg=$sms_template&tele=$sms_phone&channelid=$sms_channel_id&timestamp=".time();
//
//        $sms_content    = file_get_contents($sms_url); //发送短信  获取返回信息
//        $sms_response   = explode(":",$sms_content);  //处理返回信息
//        if($sms_response[0] == "error") {
//            return array('error' => $sms_content);
//        } else {
//            return true;
//        }
//    }
    /*
      * 助通短信Api
      */
//    public  static function sendSms($receiver,$content){
//
//        //读取站点配置  先读取缓存
//        $config = S('Config_Cache');
//        if(!$config){
//            $config = D('Config')->parseList();
//            S('Config_Cache',$config);
//        }
//
//        //添加配置到 C函数
//        C($config);
//        //echo 1;exit;
//        $username = C('SMS')['USERNAME'];		//用户账号
//        $password = C('SMS')['PASSWORD'];		//密码
//        $mobile	 = $receiver;	//号码
//        $content = $content;		//内容
//        $content=iconv("UTF-8", "UTF-8", $content);
//        $dstime = '';		//为空代表立即发送  如果加了时间代表定时发送  精确到秒
//        $productid = C('SMS')['PRODUCTID'];		//内容
//        $xh = '';		//留空
//
//        $url='http://www.ztsms.cn:8800/sendXSms.do?username='.$username.'&password='.$password.'&mobile='.$mobile.'&content='.$content.'&dstime=&productid='.$productid.'&xh=';
//        $sms_content = file_get_contents($url);
//
//        $sms_response   = explode(",",$sms_content);  //处理返回信息
//        if($sms_response[0] != 1) {
//            return array('error' => '发送失败');
//        } else {
//            return true;
//        }
//    }

    /**
     * @param $receiver
     * @param $content
     * @return bool
     * 大汉三通短信
     */
    public static  function sendSms($receiver,$content){
        $url      = 'http://wt.3tong.net/json/sms/Submit';
        $data['account']  = 'dh54831';
        $data['password'] = ''.md5('g4Dgzv~%');
        $data['msgid']    = '';
        $data['phones']   = $receiver;
        $data['content']  = $content;
        $data['sign']     = '【淘米公社】';
        $data['subcode']  = '54831';
        $data['sendtime'] = '';

        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_URL, $url);
        curl_setopt ($ch, CURLOPT_POST, 1);
        if(json_encode($data) != ''){
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $file_contents = curl_exec($ch);
        curl_close($ch);

        return true;

    }

}