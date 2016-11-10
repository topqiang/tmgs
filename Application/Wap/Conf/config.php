<?php

return array(
	'URL_MODEL'             => 0, // URL访问模式,可选参数0、1、2、3,代表以下四种模式：
	/* 模板相关配置 */
	'TMPL_PARSE_STRING' => array(
		'__STATIC__' => __ROOT__ . '/Public/Static',
		'__PLUGINS__' => __ROOT__ . '/Public/' . MODULE_NAME . '/Plugins',
		'__IMG__'    => __ROOT__ . '/Public/' . MODULE_NAME . '/img',
		'__IMAGES__'    => __ROOT__ . '/Public/' . MODULE_NAME . '/images',
		'__CSS__'    => __ROOT__ . '/Public/' . MODULE_NAME . '/css',
		'__JS__'     => __ROOT__ . '/Public/' . MODULE_NAME . '/js',
	),
	'API_URL' => 'http://2.taomim.com'
);