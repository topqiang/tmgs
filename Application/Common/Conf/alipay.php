<?php
/**
 * 支付宝配置参数
 */
return array(
    'ALIPAY_CONFIG' => array(
        //合作身份者id，以2088开头的16位纯数字，卖家成功申请支付宝接口后获取到的PID；
        'PARTNER'              => '2088121452146451',

        //安全检验码，以数字和字母组成的32位字符，卖家成功申请支付宝接口后获取到的Key
        'KEY'                  => 'fmxftefkk89uc679dgpdfq5nfwz9ll92',

        //签名方式 不需修改 MD5加密
        'SIGN_TYPE'            => strtoupper('MD5'),

        //字符编码格式 目前支持 gbk或utf-8
        'INPUT_CHARSET'        => strtolower('utf-8'),

        //ca证书路径地址，用于curl中ssl校验 请保证cacert.pem文件在当前文件夹目录中
        'CACERT'               => getcwd().'\\cacert.pem',

        //访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
        'TRANSPORT'            => 'http',

        //卖家支付宝账号
        'SELLER_EMAIL'         =>'13904710240@163.com',
    )
);
