<?php
/**
 * 前台配置文件
 * 所有除开系统级别的前台配置
 */
return array(
        /* 数据缓存设置 */
        'DATA_CACHE_PREFIX'        => 'toocms_', // 缓存前缀
        'DATA_CACHE_TYPE'          => 'File', // 数据缓存类型
        
        /* 文件上传相关配置 */
        'ATTACHMENT_UPLOAD'        => array(
            'mimes'                    => '', //允许上传的文件MiMe类型
            'maxSize'                  => 5*1024*1024, //上传的文件大小限制 (0-不做限制)
            'exts'                     => 'zip,rar,tar,gz,7z,doc,docx,txt,xml,xls', //允许上传的文件后缀
            'autoSub'                  => true, //自动子目录保存文件
            'subName'                  => array('date', 'Y-m-d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
            'rootPath'                 => './Uploads/', //保存根路径
            'savePath'                 => '', //保存路径
            'saveName'                 => array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
            'saveExt'                  => '', //文件保存后缀，空则使用原后缀
            'replace'                  => false, //存在同名是否覆盖
            'hash'                     => true, //是否生成hash编码
            'callback'                 => false, //检测文件是否存在回调函数，如果存在返回文件信息数组
        ), //下载模型上传配置（文件上传类配置）
        'ATTACHMENT_UPLOAD_DRIVER' =>'local',
        
        /* 图片上传相关配置 */
        'PICTURE_UPLOAD'           => array(
            'mimes'                    => '', //允许上传的文件MiMe类型
            'maxSize'                  => 2*1024*1024, //上传的文件大小限制 (0-不做限制)
            'exts'                     => 'jpg,gif,png,jpeg', //允许上传的文件后缀
            'autoSub'                  => true, //自动子目录保存文件
            'subName'                  => array('date', 'Y-m-d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
            'rootPath'                 => './Uploads/', //保存根路径
            'savePath'                 => '', //保存路径
            'saveName'                 => array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
            'saveExt'                  => '', //文件保存后缀，空则使用原后缀
            'replace'                  => false, //存在同名是否覆盖
            'hash'                     => true, //是否生成hash编码
            'callback'                 => false, //检测文件是否存在回调函数，如果存在返回文件信息数组
        ), //图片上传相关配置（文件上传类配置）
        'PICTURE_UPLOAD_DRIVER'    =>'local',
        
        //本地上传文件驱动配置
        'UPLOAD_LOCAL_CONFIG'      =>array(),
        'UPLOAD_BCS_CONFIG'        =>array(
            'AccessKey'                =>'',
            'SecretKey'                =>'',
            'bucket'                   =>'',
            'rename'                   =>false
        ),
        
        /* 模板相关配置 */
        'TMPL_PARSE_STRING'        => array(
            '__STATIC__'               => __ROOT__ . '/Public/Static',
            '__PLUGINS__'              => __ROOT__ . '/Public/' . MODULE_NAME . '/Plugins',
            '__IMG__'                  => __ROOT__ . '/Public/' . MODULE_NAME . '/img',
            '__CSS__'                  => __ROOT__ . '/Public/' . MODULE_NAME . '/css',
            '__JS__'                   => __ROOT__ . '/Public/' . MODULE_NAME . '/js',
        ),
        
        /* SESSION 和 COOKIE 配置 */
        'SESSION_PREFIX'           => 'toocms_mer', //session前缀
        'COOKIE_PREFIX'            => 'toocms_mer_', // Cookie前缀 避免冲突
        'VAR_SESSION_ID'           => 'session_id',	//修复uploadify插件无法传递session_id的bug
        
        /* 后台错误页面模板 */
        //'TMPL_ACTION_ERROR'      =>  MODULE_PATH.'View/Public/error.html', // 默认错误跳转对应的模板文件
        //'TMPL_ACTION_SUCCESS'    =>  MODULE_PATH.'View/Public/success.html', // 默认成功跳转对应的模板文件
        //'TMPL_EXCEPTION_FILE'    =>  MODULE_PATH.'View/Public/exception.html',// 异常页面的模板文件
        
        'URL_HTML_SUFFIX'          =>  'shtml',  // URL伪静态后缀设置
        
        //扩展配置
        'LOAD_EXT_CONFIG'          => 'menus',
        'LOAD_EXT_FILE'            => '',
        
        //是否开发者模式
        'IS_DEVELOPER'             => false,
        
        'ENGLISH_LETTER'           => array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z',),
        //    百度API AK 值
        'BAIDU_AK'                 => 'sN9nrcCnIzhryaZXSqfQSTVP',
        //    楼层模板
        '__PCONE__'                => __ROOT__ . '/Public/' . MODULE_NAME . '/img/floor/PC1.png',
        '__PCTWO__'                => __ROOT__ . '/Public/' . MODULE_NAME . '/img/floor/PC2.png',
        '__APPONE__'               => __ROOT__ . '/Public/' . MODULE_NAME . '/img/floor/APP1.png',
        '__APPTWO__'               => __ROOT__ . '/Public/' . MODULE_NAME . '/img/floor/APP2.png',
        
        'MAXTEMPLATE'              => 1,
        'TITLENAME'                => '淘米公社商家后台管理系统',
        'ADMINCOPYRIGHT'           => '沧州金雨电子商务有限公司',

        /*1 支付宝 2 微信 3 银联 4 余额*/
        'PAYTYPE'                  => array(
            array('type_title'=>'支付宝','type_key'=>'1'),
            array('type_title'=>'微信','type_key'=>'2'),
            array('type_title'=>'银联','type_key'=>'3'),
            array('type_title'=>'余额','type_key'=>'4')
        )
);
