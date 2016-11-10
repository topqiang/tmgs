<?php

/**
 * 系统配文件
 * 所有系统级别的配置
 */
return array(
    /* 模块相关配置 */
    'AUTOLOAD_NAMESPACE' => array('Plugins' => PLUGIN_PATH), //扩展模块列表
    'DEFAULT_MODULE'     => 'Home',
    'MODULE_DENY_LIST'   => array('Common'),
    'MODULE_ALLOW_LIST'  => array('Manager','Home','Api','Wap','Merchant'),

    /* 系统数据加密设置 */
    'DATA_AUTH_KEY' => 'Too%Q$L!I#P(3)%@%&J[%$D+a(v5`ni}W|N^o@4c^9<G=VK%cms', //默认数据加密KEY

    /* 调试配置 */
    'SHOW_PAGE_TRACE' => false,

    /* 用户相关设置 */
    'USER_MAX_CACHE'     => 1000, //最大缓存用户数
    'USER_ADMINISTRATOR' => 1, //管理员用户ID

    /* URL配置 */
    'URL_CASE_INSENSITIVE' => false, //默认false 表示URL区分大小写 true则表示不区分大小写
    'URL_MODEL'            => 2, //URL模式 2重写模式
    'VAR_URL_PARAMS'       => '', // PATHINFO URL参数变量
    'URL_PATHINFO_DEPR'    => '/', //PATHINFO URL分割符

    /* 全局过滤配置 */
    'DEFAULT_FILTER' => 'htmlspecialchars', //全局过滤函数

    /* 数据库配置 */
    'DB_TYPE'   => 'Mysql', // 数据库类型
    'DB_HOST'   => '127.0.0.1', // 服务器地址
    'DB_NAME'   => 'tmgs2', // 数据库名
    'DB_USER'   => 'root', // 用户名
    'DB_PWD'    => 'tmgs2016',  // 密码
    'DB_PORT'   => '3306', // 端口
    'DB_PREFIX' => 'txunda_', // 数据库表前缀

    'LANG_SWITCH_ON'    => true,    // 开启语言包功能
    'LANG_AUTO_DETECT'  => false,    // 自动侦测语言 开启多语言功能后有效
    'LANG_LIST'         => 'zh-cn,en-us,zh-tw', // 允许切换的语言列表 用逗号分隔
    'VAR_LANGUAGE'      => 'l',     // 默认语言切换变量
    'DEFAULT_LANG'      => 'zh-cn',

    //扩展配置
    'LOAD_EXT_CONFIG'       => 'regular,alipay',
    'LOAD_EXT_FILE'         => '',
    
    'API_URL'               => 'http://2.taomim.com',
);

