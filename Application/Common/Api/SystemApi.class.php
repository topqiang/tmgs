<?php
namespace Common\Api;

/**
 * Class SystemApi
 * @package Common\Api
 *
 */
class SystemApi {

    /**
     * @param $receiver  接收账号
     * @param $unique_code 模板标识
     * @param $params  替换数组  键值为要替换的字符标记  值为替换为的值
     * @param int $type  没有模板标识时使用 发送类型
     * @param string $subject  没有模板标识时使用
     * @param string $content  没有模板标识时使用
     * @param int $sender 发送者 0为系统
     * @return bool|mixed
     * 发送邮件、短信并记录
     */
    public static function sendMsg($receiver, $unique_code, $params, $sender = 0, $content = '', $type = 0, $subject = '') {

        //判断是否存在模板标识  存在模板标识情况 根据标识获取发信模板信息 调用模板的标题、内容 并根据$params替换  不存在情况直接发送 $subject $content
        if(!empty($unique_code)) {
            //根据标识获取发信模板信息 //TODO 附件
            $tpl = M('SendTemplate')->field('id,type,subject,template,status')->where(array('unique_code'=>$unique_code,'status'=>array('lt',9)))->find();
            if($tpl) {
                if($tpl['status'] == 0) {
                    return array('error'=>'模板已禁用！');
                }
                //存在模板情况调用模板的标题、内容 并根据$params替换
                $subject = $tpl['subject'];
                $type    = $tpl['type'];
                //替换赋值
                foreach ($params as $key => $param) {
                    $content = preg_replace("/{" . $key . "}/i", $param, $tpl['template']);
                }
            } else {
                return array('error'=>'模板不存在！');
            }
        } elseif(empty($unique_code) && empty($content)) {
            return array('error'=>'发送内容为空！');
        }
        //获取接收会员ID
        $m_id = M('Member')->where(array('mobile'=>$receiver))->getField('id');
        //创建发信记录参数 //TODO 记录附件ID列表
        $data = array(
            'm_id'          => empty($m_id) ? 0 : $m_id, //会员ID
            'receiver'      => $receiver,                //接受者账号
            'sender'        => $sender,                  //发送者 0系统 其他:管理员ID
            'content'       => $content,                 //发送内容
            'type'          => $type,                    //发送类型
            'template_id'   => empty($tpl['id']) ? 0 : $tpl['id'], //模板ID
            'create_time'   => time()                    //发送时间
        );

        //判断发送类型
        if($type == 1) {
            //验证手机号格式
            if(!preg_match(C('MOBILE'),$receiver)) {
                return array('error'=>'手机号格式不正确！');
            }
            //发送短信
            $res = api('Sms/sendSms',array($receiver, $content));
        } elseif($type == 2) {
            //验证邮箱格式
            if(!preg_match(C('EMAIL'),$receiver)) {
                return array('error'=>'邮箱格式不正确！');
            }
            //发送邮件 //TODO 发送带有附件的邮件
            $res = api('Email/sendEmail',array($receiver, $receiver, $subject, $content));
        } else {
            return array('error'=>'发送类型不正确！');
        }

        //判断发信是否成功
        if($res === true) {
            //发送成功
            $data['status'] = 1;
            M('SendLog')->data($data)->add();
            return array('success'=>'发信成功');
        } else {
            //发送失败
            M('SendLog')->data($data)->add();
            return array('error'=>$res);
        }
    }

    /**
     * @param string $ids
     * @param array $fields
     * @return array
     * 获取文件列表 并排序
     */
    public static function getFiles($ids = '', $fields = array()) {
        //没有ID返回空数组
        if(empty($ids)) {
            return array();
        }
        //判断ids是否存在‘,’ 如果存在则是多图  不存在全部按单图处理
        $param['where']['file.id'] = (false !== strpos($ids,',')) ? array('IN',$ids) : $ids;

        //如果存在指定file表字段 清空关联条件 设置字段名称
        if(!empty($fields)) {
            //清空关联条件
            $param['join'] = array();

            //设置字段名称
            foreach($fields as $value) {
                $param['field'][] = 'file.'.$value;
            }
        }
        $files = D('File','Service')->select($param);
        //多图情况  按照ids排序
        return (false !== strpos($ids,',')) ? sort_by_array(explode(',',$ids),$files['list'],'id') : $files['list'];
    }
}