<?php

namespace Common\Model;
use Think\Model;

/**
 * Class MemberOperateModel
 * @package Home\Model
 * 会员操作  找回密码  激活账号
 * 发送邮件 发送短信
 */
class MemberOperateModel extends Model{

    protected $tableName = 'member_operate';


    /**
     * 获取短信验证码
     * @param $mobile
     * @param $unique_code
     * @return array
     */
    public function sendVerify($mobile,$unique_code = '') {
        $vc = get_vc(6,2);//获取标识
        if($unique_code == 'retrieve'){
            $member = M('Member')->where(array('account'=>$mobile))->find();
        }elseif($unique_code == 'register'){
            $member = true;
        }
        if($member) {
            //是否进行过此操作
            $operate = $this->where(array('way'=>$mobile,'type'=>$unique_code))->find();
            $expire_time = time()+600;//过期时间
            if($operate) {
                /**每天只能进行三次操作**/
                if($operate['ctime'] > strtotime(date('Y-m-d')) && $operate['ctime'] < strtotime(date('Y-m-d 23:59:59')) && intval($operate['times'])%3 == 0){
                    return array('error'=>'操作次数超限！');
                } else {
                    /**后一天操作  次数置一 否则次数加一**/
                    if($operate['ctime'] < strtotime(date('Y-m-d'))) {
                        $times = 1;
                    } else {
                        $times = intval($operate['times']) + 1;
                    }
                    //修改记录
                    $res = $this->where(array('id'=>$operate['id']))->data(array('vc'=>$vc,'expire_time'=>$expire_time,'times'=>$times,'ctime'=>time()))->save();
                }
            } else {
                //添加记录
                $res = $this->data(array('way'=>$mobile,'vc'=>$vc,'times'=>1,'expire_time'=>$expire_time,'type'=>$unique_code,'ctime'=>time()))->add();
            }
            if($res){
                $send = api('System/sendMsg',array($mobile,$unique_code,array('vc'=>$vc)));
                if($send){
                    return array('success'=>'信息已送达！','verify'=>$vc);
                }else{
                    return array('error'=>$send['error']);
                }
            } else {
                return array('error'=>'操作失败！！');
            }
        } else {
            return array('error'=>'您输入的手机号码不存在！');
        }
    }

}