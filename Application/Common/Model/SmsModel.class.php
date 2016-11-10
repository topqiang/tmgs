<?php

namespace Common\Model;
use Think\Model;

/**
 * Class MemberOperateModel
 * @package Home\Model
 * 会员操作  找回密码  激活账号
 * 发送邮件 发送短信
 */
class SmsModel extends Model{
    protected $tableName = 'sms';

    /**
     * 获取短信验证码
     * @param $mobile
     * @param $unique_code
     * @return array
     */
    public function sendVerify($mobile,$unique_code = ''){
        $sms_info = $this->where(array('way' => $mobile, 'type' => $unique_code))->find();
        $expire_time = time() + 600;//获取过期时间
        $vc = get_vc(4, 2);//获取验证码

        if ($sms_info) {
            //有发信记录
            if ($sms_info['create_time'] > strtotime(date('Y-m-d')) && $sms_info['create_time'] < strtotime(date('Y-m-d 23:59:59')) && intval($sms_info['times']) % 100 == 0) {
                return array('error' => '你今天获取验证码次数已达到上限');
            } else {
                //次数未达到上限，判断如果上一次发送验证码的时间是今天，次数+1，否则次数设置为1；
                if ($sms_info['create_time'] < strtotime(date('Y-m-d'))) {
                    $times = 1;
                } else {
                    $times = intval($sms_info['times']) + 1;
                }
                //修改记录
                $res = $this->where(array('id' => $sms_info['id']))->data(array('vc' => $vc, 'expire_time' => $expire_time, 'times' => $times, 'create_time' => time()))->save();
            }
        } else {
            //无发信记录
            $res = $this->data(array('way' => $mobile, 'vc' => $vc, 'times' => 1, 'expire_time' => $expire_time, 'type' => $unique_code, 'create_time' => time()))->add();
        }

        if($res){
            $send = api('System/sendMsg',array($mobile,$unique_code,array('vc'=>$vc)));
            if ($send['success']) {
                return array('success' => '信息已送达');
            } else {
                return array('error' => $send['error']);
            }
        } else {
            return array('error' => '操作失败');
        }
    }
}