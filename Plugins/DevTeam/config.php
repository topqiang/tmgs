<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

return array(
	'title'=>array(//配置在表单中的键名 ,这个会是config[title]
		'title'=>'显示标题',//表单的文字
		'type'=>'text',		 //表单的类型：text、textarea、checkbox、radio、select等
		'value'=>'OneThink开发团队',			 //表单的默认值
	),
	'display'=>array(
		'title'=>'是否显示',
		'type'=>'radio',
		'options'=>array(
			'1'=>'显示',
			'0'=>'不显示'
		),
		'value'=>'1'
	),
    'aaa'=>array(
        'title'=>'是否显示',
        'type'=>'textarea',
        'value'=>'1'
    ),
    'bbb'=>array(
        'title'=>'是否显示',
        'type'=>'checkbox',
        'options'=>array(
            '1'=>'显示',
            '0'=>'不显示'
        ),
        'value'=>'1'
    ),
    'picture'=>array(
        'title'=>'图片',
        'type'=>'picture_union',
        'value'=>''
    ),
    'group'=>array(
        'title'=>'徐昂正在',
        'type'=>'group',
        'options'=>array(
            'youyan'=>array(
                'title'=>'友言配置',
                'options'=>array(
                    'comment_uid_youyan'=>array(
                        'title'=>'账号id',
                        'type'=>'text',
                        'value'=>'90040',
                        'tip'=>'填写自己登录友言后的uid,填写后可进相应官方后台'
                    ),
                    'width１'=>array(
                        'title'=>'显示宽度',
                        'type'=>'select',
                        'options'=>array(
                            '1'=>'1格',
                            '2'=>'2格',
                            '4'=>'4格'
                        ),
                        'value'=>'４'
                    ),
                    'picture_23'=>array(
                        'title'=>'图片',
                        'type'=>'picture_union',
                        'value'=>''
                    ),
                )
            ),
            'duoshuo'=>array(
                'title'=>'多说配置',
                'options'=>array(
                    'comment_short_name_duoshuo'=>array(
                        'title'=>'短域名',
                        'type'=>'text',
                        'value'=>'',
                        'tip'=>'每个站点一个域名'
                    ),
                    'comment_form_pos_duoshuo'=>array(
                        'title'=>'表单位置',
                        'type'=>'radio',
                        'options'=>array(
                            'top'=>'顶部',
                            'buttom'=>'底部'
                        ),
                        'value'=>'buttom'
                    ),
                    'comment_data_list_duoshuo'=>array(
                        'title'=>'单页显示评论数',
                        'type'=>'text',
                        'value'=>'10'
                    ),
                    'comment_data_order_duoshuo'=>array(
                        'title'=>'评论显示顺序',
                        'type'=>'radio',
                        'options'=>array(
                            'asc'=>'从旧到新',
                            'desc'=>'从新到旧'
                        ),
                        'value'=>'asc'
                    )
                )
            )
        )
    )
);
