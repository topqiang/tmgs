<?php
/**
 * 菜单配置列表
 * group  父菜单 title标题名称  icon改组图标 class是否选中 默认为空 url链接地址  is_developer 0都可见 1开发者模式可见
 * child 子菜单 同上
 */
    return array(
        'MENUS' => array(
            array(
                'group' => array('title' => '首页', 'icon' => 'icon-home', 'class' => '', 'url' => 'Index/index', 'is_developer' => 0),
                '_child' => array()
            ),
            array(
                'group' => array('title'=>'用户管理','icon'=>'icon-user','class'=>'','url'=>'javascript:void(0);','is_developer'=>0),
                '_child' => array(
                    array('title'=>'用户列表','url'=>'Member/index','class'=>'','is_developer'=>0),
                )
            ),
            array(
                'group' => array('title'=>'商家管理','icon'=>'icon-user-md','class'=>'','url'=>'javascript:void(0);','is_developer'=>0),
                '_child' => array(
                    array('title'=>'商家列表','url'=>'Merchant/index','class'=>'','is_developer'=>0),
                )
            ),

            array(
                'group' => array('title'=>'商品分类管理','icon'=>'icon-align-justify','class'=>'','url'=>'javascript:void(0);','is_developer'=>0),
                '_child' => array(
                    array('title'=>'添加商品分类','url'=>'GoodsType/add','class'=>'','is_developer'=>0),
                    array('title'=>'商品分类列表','url'=>'GoodsType/index','class'=>'','is_developer'=>0),
                )
            ),

            array(
                'group' => array('title'=>'商品管理','icon'=>'icon-th-large','class'=>'','url'=>'javascript:void(0);','is_developer'=>0),
                '_child' => array(
                    array('title'=>'商品单位','url'=>'Unit/index','class'=>'','is_developer'=>0),
                    array('title'=>'商品列表','url'=>'Goods/index','class'=>'','is_developer'=>0),
                    array('title'=>'未审核商品','url'=>'Goods/notIndex','class'=>'','is_developer'=>0),

                )
            ),

            array(
                'group' => array('title'=>'交易管理','icon'=>'icon-barcode','class'=>'','url'=>'javascript:void(0);','is_developer'=>0),
                '_child' => array(
                    array('title'=>'订单列表','url'=>'Order/index','class'=>'','is_developer'=>0),
                    array('title'=>'退货管理','url'=>'OrderOut/index','class'=>'','is_developer'=>0),
                )
            ),

             array(
                 'group' => array('title'=>'消息管理','icon'=>'icon-comment','class'=>'','url'=>'javascript:void(0);','is_developer'=>0),
                 '_child' => array(
                     array('title'=>'短信模板设置','url'=>'SendTemplate/index','class'=>'','is_developer'=>0),
                     array('title'=>'短信发送记录','url'=>'SendLog/index','class'=>'','is_developer'=>0),
                     array('title'=>'系统消息','url'=>'MessageReady/index','class'=>'','is_developer'=>0)
                 )
             ),


             array(
                 'group' => array('title'=>'广告管理','icon'=>'icon-volume-up','class'=>'','url'=>'javascript:void(0);','is_developer'=>0),
                 '_child' => array(
                     array('title'=>'发布广告','url'=>'Advert/add','class'=>'','is_developer'=>0),
                     array('title'=>'广告列表','url'=>'Advert/index','class'=>'','is_developer'=>0),
                 )
             ),
            array(
                'group' => array('title'=>'活动管理','icon'=>'icon-th','class'=>'','url'=>'javascript:void(0);','is_developer'=>0),
                '_child' => array(
                    array('title'=>'服务定价','url'=>'MerchantAdver/index','class'=>'','is_developer'=>0),
                    // 积分抽奖设置
                    array('title'=>'抽奖设置','url'=>'Lottery/index','class'=>'','is_developer'=>0),
                    // 积分抽奖记录
                    array('title'=>'抽奖记录','url'=>'LotteryOrder/index','class'=>'','is_developer'=>0),
                    // 积分抽奖背景
                    array('title'=>'抽奖背景','url'=>'LotteryBackground/update?id=1','class'=>'','is_developer'=>0),
                    array('title'=>'积分商城','url'=>'IntegralMall/index','class'=>'','is_developer'=>0),
                    array('title'=>'诚信商家','url'=>'Security/index','class'=>'','is_developer'=>0),
                    array('title'=>'轮播图广告','url'=>'AdPositionTotal/index','class'=>'','is_developer'=>0),
                    array('title'=>'云推广服务','url'=>'CloudSpread/index','class'=>'','is_developer'=>0),
                    array('title'=>'发现订单','url'=>'FindTotal/index','class'=>'','is_developer'=>0),

                )
            ),

             array(
                'group' => array('title'=>'楼层管理','icon'=>'icon-th','class'=>'','url'=>'javascript:void(0);','is_developer'=>0),
                '_child' => array(
                    array('title'=>'楼层管理','url'=>'Floor/index','class'=>'','is_developer'=>0),

                )
            ),

             array(
                'group' => array('title'=>'爱心帮扶','icon'=>'icon-heart','class'=>'','url'=>'javascript:void(0);','is_developer'=>0),
                '_child' => array(
                    array('title'=>'爱心帮扶','url'=>'DonateLove/index','class'=>'','is_developer'=>0),
                    array('title'=>'爱心轮播','url'=>'LoveMap/update?id=1','class'=>'','is_developer'=>0),
                )
            ),

            array(
                'group' => array('title'=>'财务管理','icon'=>'icon-money','class'=>'','url'=>'javascript:void(0);','is_developer'=>0),
                '_child' => array(
                    array('title'=>'充值记录','url'=>'Recharge/index','class'=>'','is_developer'=>0),
                    array('title'=>'提现记录','url'=>'Withdraw/index','class'=>'','is_developer'=>0),
                    array('title'=>'交易明细','url'=>'DealDetail/index','class'=>'','is_developer'=>0)
                )
            ),

            array(
                'group' => array('title'=>'帮助中心','icon'=>'icon-question-sign','class'=>'','url'=>'Help/index','is_developer'=>0),
            ),
            array(
                'group' => array('title'=>'意见反馈','icon'=>'icon-envelope-alt','class'=>'','url'=>'Feedback/index','is_developer'=>0),
            ),

            array(
                'group' => array('title'=>'文章管理','icon'=>'icon-file','class'=>'','url'=>'javascript:void(0);','is_developer'=>0),
                '_child' => array(
                    array('title'=>'文章列表','url'=>'Article/index','class'=>'','is_developer'=>0),
                    array('title'=>'文章分类','url'=>'ArticleCategory/index','class'=>'','is_developer'=>0)
                )
            ),


             array(
                 'group' => array('title'=>'平台统计','icon'=>'icon-th','class'=>'','url'=>'javascript:void(0);','is_developer'=>0),
                 '_child' => array(
                     array('title'=>'用户注册量统计','url'=>'Statistics/userRegSta','class'=>'','is_developer'=>0),
                     array('title'=>'平台销售额统计','url'=>'Statistics/saleSta','class'=>'','is_developer'=>0)
                 )
             ),

           // array(
               // 'group' => array('title'=>'信息管理','icon'=>'icon-envelope','class'=>'','url'=>'javascript:void(0);','is_developer'=>0),
               // '_child' => array(
                   // array('title'=>'短信模板设置','url'=>'SendTemplate/index','class'=>'','is_developer'=>0),
                   // array('title'=>'短信发送记录','url'=>'SendLog/index','class'=>'','is_developer'=>0),
               // )
           // ),
            array(
                'group' => array('title'=>'管理员操作','icon'=>'icon-user','class'=>'','url'=>'javascript:void(0);','is_developer'=>0),
                '_child' => array(
                    array('title'=>'管理员信息','url'=>'Administrator/index','class'=>'','is_developer'=>0),
                    array('title'=>'分组权限','url'=>'AuthGroup/index','class'=>'','is_developer'=>0),
                    array('title'=>'添加权限','url'=>'AuthRule/index','class'=>'','is_developer'=>0),
                    // array('title'=>'行为信息','url'=>'Action/index','class'=>'','is_developer'=>0),
                    // array('title'=>'行为日志','url'=>'ActionLog/index','class'=>'','is_developer'=>0),
                )
            ),

            array(
                'group' => array('title'=>'微信管理','icon'=>'icon-sitemap','class'=>'','url'=>'javascript:void(0);','is_developer'=>0),
                '_child' => array(
                    array('title'=>'接口管理','url'=>'WechatToken/index','class'=>'','is_developer'=>0),
                    array('title'=>'创建菜单','url'=>'WechatMenu/index','class'=>'','is_developer'=>0),
                    array('title'=>'消息设置','url'=>'WechatArticle/index','class'=>'','is_developer'=>0),
                )
            ),
            // array(
                // 'group' => array('title'=>'数据管理','icon'=>'icon-tasks','class'=>'','url'=>'javascript:void(0);','is_developer'=>1),
                // '_child' => array(
                    // array('title'=>'数据备份','url'=>'Export/index','class'=>'','is_developer'=>1),
                    // array('title'=>'数据还原','url'=>'Import/index','class'=>'','is_developer'=>1)
                // )
            // ),
            array(
                'group' => array('title'=>'系统设置','icon'=>'icon-wrench','class'=>'','url'=>'javascript:void(0);','is_developer'=>0),
                '_child' => array(
                    array('title'=>'网站设置','url'=>'ConfigSet/index?config_group=1','class'=>'','is_developer'=>0),
                    array('title'=>'签到积分设置','url'=>'IntegralRule/update?id=1','class'=>'','is_developer'=>0),
                    array('title'=>'分成比例设置','url'=>'Divide/update?id=1','class'=>'','is_developer'=>0),
                    array('title'=>'配置管理','url'=>'Config/index','class'=>'','is_developer'=>0),
					array('title'=>'热门搜索词','url'=>'hotSearch/index','class'=>'','is_developer'=>0),
                    array('title'=>'APP开启页','url'=>'OpenPage/update','class'=>'','is_developer'=>0),
                )
            ),
//             array(
//                 'group' => array('title'=>'扩展管理','icon'=>'icon-hdd','class'=>'','url'=>'javascript:void(0);','is_developer'=>0),
//                 '_child' => array(
//                     array('title'=>'插件管理','url'=>'Plugins/index','class'=>'','is_developer'=>0),
//                     array('title'=>'钩子管理','url'=>'Hooks/index','class'=>'','is_developer'=>0)
//                 )
//             ),
        ),
    );