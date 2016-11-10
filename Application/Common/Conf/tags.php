<?php
/**
 * 初始化事件
 */
return array(
	'app_init'  => array('Common\Behavior\InitHook'), //外部插件导入事件
    'app_begin' => array('Behavior\CheckLang') //语言包检查引入事件
);