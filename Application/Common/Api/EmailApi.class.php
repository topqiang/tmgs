<?php
namespace Common\Api;

/**
 * Class EmailApi
 * @package Common\Api
 * 邮件发送接口
 */
class EmailApi {

    /**
     * 发送邮件
     * @param $to  接收者邮箱
     * @param $name 接收者名称
     * @param string $subject  邮件标题
     * @param string $body  邮件内容 支持html
     * @param null $attachment 附件 先上传到本地
     * @return bool|string
     */
    public static function sendEmail($to, $name, $subject = '', $body = '', $attachment = null,$lang) {
        //读取站点配置  先读取缓存
        $config = S('Config_Cache');
        if(!$config){
            $config = D('Config')->parseList();
            S('Config_Cache',$config);
        }

        //验证账号合法性
        if(!preg_match(C('EMAIL'),$to)) {
            if($lang != 'zh-cn'){
               return 'Email format is not correct.';
            }else{
                return '邮箱格式不正确';
            }
        }
        //从Vendor/PHPMailer目录导入class.phpmailer.php类文件
        vendor('PHPMailer.class#phpmailer');
        //初始化PHPMailer对象
        $mail             = new \Vendor\PHPMailer\PHPMailer(true);
        //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
        $mail->CharSet    = 'UTF-8';
        // 设定使用SMTP服务
        $mail->IsSMTP();
        // 启用 SMTP 验证功能
        $mail->SMTPAuth   = true;
        // 关闭SMTP调试功能
        $mail->SMTPDebug  = 0;
        // 使用安全协议
        //$mail->SMTPSecure = 'ssl';
        // SMTP 服务器地址
        $mail->Host       = $config['EMAIL']['SMTP_HOST'];
        // SMTP服务器的端口号
        $mail->Port       = $config['EMAIL']['SMTP_PORT'];
        // SMTP服务器用户名
        $mail->Username   = $config['EMAIL']['SMTP_USER'];
        // SMTP服务器密码
        $mail->Password   = $config['EMAIL']['SMTP_PASS'];
        //回复地址   为空则回复地址为发送地址
        $replyEmail       = $config['EMAIL']['REPLY_EMAIL']    ? $config['EMAIL']['REPLY_EMAIL'] :$config['EMAIL']['FROM_EMAIL'];

        //回复名称   为空则回复名称为发送名称
        $replyName        = $config['EMAIL']['REPLY_NAME']    ? $config['EMAIL']['REPLY_NAME'] :$config['EMAIL']['FROM_NAME'];
        //添加回复地址
        $mail->AddReplyTo($replyEmail,$replyName);
        $mail->From       = $config['EMAIL']['FROM_EMAIL'];
        $mail->FromName   = $config['EMAIL']['FROM_NAME'];
        //添加发送地址
        $mail->AddAddress($to,$name);
        //邮件标题
        $mail->Subject    = $subject;
        //纯文本内容
        $mail->AltBody    = "";
        //设置多少个字符串就换行
        $mail->WordWrap   = 80;
        //先上传到本地 添加附件  本地文件  相对路径
        if(is_array($attachment)){
            foreach ($attachment as $file){
                is_file($file) && $mail->AddAttachment($file);
            }
        }
        //邮件主体
        $mail->MsgHTML($body);
        //是否支持html
        $mail->IsHTML(true);
        
        return $mail->Send() ? true : $mail->ErrorInfo;
    }
}