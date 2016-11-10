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
                'group' => array('title'=>'资料管理','icon'=>'icon-user','class'=>'','url'=>'javascript:void(0);','is_developer'=>0),
                '_child' => array(
                    array('title'=>'基本资料','url'=>'Merchant/update','class'=>'','is_developer'=>0),
                    array('title'=>'认证资料','url'=>'Merchant/attestation','class'=>'','is_developer'=>0),
                )
            ),
            array(
                'group' => array('title'=>'商品管理','icon'=>'icon-truck','class'=>'','url'=>'javascript:void(0);','is_developer'=>0),
                '_child' => array(
                    array('title'=>'商品管理','url'=>'Goods/Index','class'=>'','is_developer'=>0),
                    array('title'=>'属性管理','url'=>'Attribute/Index','class'=>'','is_developer'=>0),
                )
            ),
            array(
                'group' => array('title' => '退货管理', 'icon' => 'icon-refresh', 'class' => '', 'url' => 'OrderOut/index', 'is_developer' => 0),
                '_child' => array()
            ),
            array(
                'group' => array('title'=>'订单管理','icon'=>'icon-tag','class'=>'','url'=>'javascript:void(0);','is_developer'=>0),
                '_child' => array(
                    array('title'=>'未完成订单列表','url'=>'UnfinishedOrder/index','class'=>'','is_developer'=>0),
                    array('title'=>'已完成订单列表','url'=>'AlreadyOrder/index','class'=>'','is_developer'=>0),
                    array('title'=>'订单总列表','url'=>'Order/index','class'=>'','is_developer'=>0),
                    array('title'=>'评价列表','url'=>'Evaluate/index','class'=>'','is_developer'=>0),
                )
            ),
            array(
                'group' => array('title'=>'财务管理','icon'=>'icon-money','class'=>'','url'=>'javascript:void(0);','is_developer'=>0),
                '_child' => array(
                    array('title'=>'银行管理','url'=>'MemberCard/index','class'=>'','is_developer'=>0),
                    array('title'=>'提现管理','url'=>'Withdraw/index','class'=>'','is_developer'=>0),
                    array('title'=>'账单明细','url'=>'PayLog/index','class'=>'','is_developer'=>0),
                )
            ),
            array(
                'group' => array('title'=>'消息管理','icon'=>'icon-money','class'=>'','url'=>'javascript:void(0);','is_developer'=>0),
                '_child' => array(
                    array('title'=>'系统消息','url'=>'Message/index','class'=>'','is_developer'=>0),
                    array('title'=>'订单消息','url'=>'Message/orderIndex','class'=>'','is_developer'=>0),
                )
            ),
            array(
                'group' => array('title'=>'服务定制','icon'=>'icon-coffee','class'=>'','url'=>'javascript:void(0);','is_developer'=>0),
                '_child' => array(
                    array('title'=>'诚信商家','url'=>'Security/index','class'=>'','is_developer'=>0),
                    array('title'=>'积分商城服务','url'=>'IntegralMall/index','class'=>'','is_developer'=>0),
                    array('title'=>'云推广服务','url'=>'CloudSpread/index','class'=>'','is_developer'=>0),
                    array('title'=>'轮播图广告','url'=>'AdPosition/index','class'=>'','is_developer'=>0),
                    array('title'=>'发现模块','url'=>'FindTotal/index','class'=>'','is_developer'=>0),
                )
            ),

            array(
                'group' => array('title' => '意见反馈', 'icon' => 'icon-envelope-alt', 'class' => '', 'url' => 'Feedback/update', 'is_developer' => 0),
                '_child' => array()
            ),
        ),
    );