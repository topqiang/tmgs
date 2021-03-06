<?php
/**
 * 后台公共文件
 * 主要定义后台公共函数库
 */

/**
 * 检测用户是否登录
 * @return integer 0-未登录，大于0-当前登录用户ID
 */
function is_login() {
    $admin = session('admin');
    if (empty($admin)) {
        return 0;
    } else {
        return session('admin_sign') == data_auth_sign($admin) ? $admin['a_id'] : 0;
    }
}

/**
 * @param null $a_id
 * @return boolean true-超管理员，false-非超管理员
 * 是否是超级管理员
 */
function is_administrator($a_id = null) {
    $a_id = is_null($a_id) ? is_login() : $a_id;
    return $a_id && (intval($a_id) === C('USER_ADMINISTRATOR'));
}

/**
 * @param string $type
 * @return mixed
 * 获取属性类型信息
 */
function get_attribute_type($type = '') {
    // TODO 可以加入系统配置
    static $_type = array(
        'num'       =>  array('数字','int(10) UNSIGNED NOT NULL'),
        'string'    =>  array('字符串','varchar(255) NOT NULL'),
        'textarea'  =>  array('文本框','text NOT NULL'),
        'datetime'  =>  array('时间','int(10) NOT NULL'),
        'bool'      =>  array('布尔','tinyint(2) NOT NULL'),
        'select'    =>  array('枚举','char(50) NOT NULL'),
    	'radio'		=>	array('单选','char(10) NOT NULL'),
    	'checkbox'	=>	array('多选','varchar(100) NOT NULL'),
    	'editor'    =>  array('编辑器','text NOT NULL'),
    	'picture'   =>  array('上传图片','int(10) UNSIGNED NOT NULL'),
    	'file'    	=>  array('上传附件','int(10) UNSIGNED NOT NULL'),
    );
    return $type?$_type[$type][0]:$_type;
}

/**
 * 获取对应状态的文字信息
 * @param int $status
 * @return string 状态文字 ，false 未获取到
 */
function get_status_title($status = null) {
    if(!isset($status)) {
        return false;
    }
    switch ($status) {
        case -1 : return    '已删除';   break;
        case 0  : return    '禁用';     break;
        case 1  : return    '正常';     break;
        case 2  : return    '待审核';   break;
        default : return    false;      break;
    }
}

/**
 * 获取性别
 * @param int $status
 * @return string 状态文字 ，false 未获取到
 */
function get_sex($sex = null) {
    if(!isset($sex)) {
        return false;
    }
    switch ($sex) {
        case 0  : return    '待设置';     break;
        case 1  : return    '男';     break;
        case 2  : return    '女';     break;
        default : return    false;      break;
    }
}
/**
 * @param null $status
 * @return bool|string
 * 意见反馈对应状态
 */
function get_feedback_status_title($status = null) {
    if(!isset($status)) {
        return false;
    }
    switch ($status) {
        case 0 : return    '未处理';   break;
        case 1  : return    '已处理';     break;
        default : return    false;      break;
    }
}
/**
 * @param null $status
 * @return bool|string
 * 获取评价状态
 */
function get_comment_status_title($status = null) {
    if(!isset($status)) {
        return false;
    }
    switch ($status) {
        case 0 : return    '未审核';   break;
        case 1  : return    '已审核';     break;
        default : return    false;      break;
    }
}
/**
 * @param null $status
 * @return bool|string
 */
function get_comment_status_name($status = null) {
    if(!isset($status)) {
        return false;
    }
    switch ($status) {
        case 0 : return    '未审核';   break;
        case 1  : return    '已审核';     break;
        default : return    false;      break;
    }
}
/**
 * @param $status
 * @return bool|string
 * 获取数据的状态操作
 */
function show_status_name($status) {
    switch ($status) {
        case 0  : return    '启用';     break;
        case 1  : return    '禁用';     break;
        case 2  : return    '审核';	 break;
        default : return    false;     break;
    }
}

/**
 * @param $status
 * @return bool|string
 * 获取数据的状态操作
 */
function show_status_icon($status) {
    switch ($status) {
        case 0  : return    'icon-ok-sign';       break;
        case 1  : return    'icon-minus-sign';    break;
        case 2  : return    '';		       break;
        default : return    false;               break;
    }
}

/**
 * @param string $table
 * @return string
 * 获取表的中文名称
 */
function get_table_name($table = '') {
    switch ($table) {
        case 'Action'           : return    '行为表';       break;
        case 'ActionLog'        : return    '行为日志表';    break;
        case 'Administrator'    : return    '管理员表';    break;
        default                 : return    '';             break;
    }
}

/**
 * @param $status
 * @return string
 * 获取插件状态名称
 */
function get_plugins_status_title($status) {
    switch ($status) {
        case 1       : return    '启用';    break;
        case 9       : return    '损坏';    break;
        case null    : return    '未安装';  break;
        case 0       : return    '禁用';    break;
        default      : return    '';       break;
    }
}

/**
 * @param $value
 * @param $config
 * @return mixed
 * 获取标记对应的数组类型配置信息
 */
function get_config_title($value, $config) {
    $list = C(''.$config.'');
    return empty($list[$value]) ? '' : $list[$value];
}

/**
 * @param $status
 * @return string
 * 获取发送状态
 */
function get_send_status($status) {
    switch ($status) {
        case 0       : return    '失败';    break;
        case 1       : return    '成功';    break;
        default      : return    '';       break;
    }
}

/**
 * @param $string
 * @return array
 * 分析枚举类型配置值 格式 a:名称1,b:名称2
 */
function parse_config_attr($string) {
    $array = preg_split('/[,;\r\n]+/', trim($string, ",;\r\n"));
    if(strpos($string,':')) {
        $value  =   array();
        foreach ($array as $val) {
            list($k, $v) = explode(':', $val);
            $value[$k]   = $v;
        }
    } else {
        $value  =   $array;
    }
    return $value;
}

/**
 * @param $string
 * @return array
 * 分析枚举类型字段值 格式 a:名称1,b:名称2
 * 暂时和 parse_config_attr功能相同
 * 但请不要互相使用，后期会调整
 */
function parse_field_attr($string) {
    if(0 === strpos($string,':')) {
        // 采用函数定义
        return   eval(substr($string,1).';');
    }
    $array = preg_split('/[,;\r\n]+/', trim($string, ",;\r\n"));
    if(strpos($string,':')) {
        $value  =   array();
        foreach ($array as $val) {
            list($k, $v) = explode(':', $val);
            $value[$k]   = $v;
        }
    } else {
        $value  =   $array;
    }
    return $value;
}

/**
 * @param $str  要执行替换的字符串
 * @param $rep_flag 替换标记
 * @param $tar_str 目标字符
 * @return mixed
 */
function replace($str, $rep_flag, $tar_str) {
    return $str = preg_replace("/{".$rep_flag."}/i", ''.$tar_str.'', $str);
}

/**
 * 创建像这样的查询: "IN('a','b')";
 * @access   public
 * @param    mix      $item_list      列表数组或字符串
 * @param    string   $field_name     字段名称
 * @return   void
 */
function db_create_in($item_list, $field_name = ''){
    if (empty($item_list)) {
        return $field_name . " IN ('') ";
    }
    else {
        if (!is_array($item_list)) {
            $item_list = explode(',', $item_list);
        }
        $item_list = array_unique($item_list);
        $item_list_tmp = '';
        foreach ($item_list AS $item) {
            if ($item !== '') {
                $item_list_tmp .= $item_list_tmp ? ",'$item'" : "'$item'";
            }
        }
        if (empty($item_list_tmp)) {
            return $field_name . " IN ('') ";
        }
        else {
            return $field_name . ' IN (' . $item_list_tmp . ') ';
        }
    }
}

/**
 * 	作用：将xml转为array
 */
function xmlToArray($xml){
    //将XML转为array
    $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    return $array_data;
}

/**
 * 热门
 * @param $status
 * @return string
 */
function get_hots_status($status) {
    switch ($status) {
        case 0       : return    '非热门';    break;
        case 1       : return    '热门';    break;
        default      : return    '';       break;
    }
}

/**
 * 热门
 * @param $status
 * @return string
 */
function get_app_index_type($status) {
    switch ($status) {
        case 0       : return    '不显示';    break;
        case 1       : return    '显示';    break;
        default      : return    '';       break;
    }
}


/**
 * 用户状态显示
 * @param $status
 * @return string
 */
function get_user_type($status) {
    switch ($status) {
        case 0       : return    '禁用';    break;
        case 1       : return    '启用';    break;
        case 2       : return   '待审核';    break;
        case 3      :   return '审核失败'; break;
        case 4      :   return '未填资料'; break;
        default      : return    '';       break;
    }
}

/**
 * 用户操作状态
 * @param $status
 * @return string
 */
function get_user_status($status) {
    switch ($status) {
        case 1       : return    '禁用';    break;
        case 0       : return    '启用';    break;
        default      : return    '';       break;
    }
}
/**
 * 用户操作状态
 * @param $status
 * @return string
 */
function get_check_status($status) {
    switch ($status) {
        case 0       : return    '待审核';  break;
        case 1       : return    '成功';    break;
        case 2       : return    '否决';    break;
        default      : return    '';       break;
    }
}

/**
 * 用户操作状态
 * @param $status
 * @return string
 */
function get_fourteen_status($status) {
    switch ($status) {
        case 0       : return    '支持';  break;
        case 1       : return    '不支持';    break;
        default      : return    '';       break;
    }
}

/**
 * 上架状态
 *  0 下架 1 上架  2后台下架
 * @param $status
 * @return string
 */
function get_shelves_status($status) {
    switch ($status) {
        case 0       : return    '下架';  break;
        case 1       : return    '上架';    break;
        case 2       : return    '后台下架';    break;
        default      : return    '';       break;
    }
}


/**
 * 用户操作状态
 * @param $status
 * @return string
 */
function get_version_type($status) {
    switch ($status) {
        case 0       : return    '全站适用';  break;
        case 1       : return    '中文版';  break;
        case 2       : return    '英文版';    break;
        default      : return    '';       break;
    }
}

/**
 * 订单支付类型
 * @param $status
 * @return string
 * 0 未支付 1 银联 2 微信 3 支付宝 4 余额
 */
function get_order_type($status) {
    switch ($status) {
        case 0       : return    '待支付';  break;
        case 1       : return    '银联';  break;
        case 2       : return    '微信';  break;
        case 3       : return    '支付宝';  break;
        case 4       : return    '余额';  break;
        default      : return    '未获得状态';       break;
    }
}


/**
 * 订单状态
 * 0待支付，
1待接单，
2待发货，
3待收货，
4待评价，
5已完成，
6已取消
 * @param $status
 * @return string
 */
function get_order_status($status) {
    switch ($status) {
        case 0       : return    '待支付';  break;
        case 1       : return    '待接单';  break;
        case 2       : return    '待发货';  break;
        case 3       : return    '待收货';  break;
        case 4       : return    '待评价';  break;
        case 5       : return    '已完成';  break;
        case 6       : return    '已取消';  break;
        case 7       : return    '申请售后';  break;
        default      : return    false;       break;
    }
}
/**
 * 用户操作状态
 * @param $status
 * @return string
 */
function get_out_status($status) {
    switch ($status) {
        case 0       : return    '等待操作';  break;
        case 1       : return    '退款完成';  break;
        case 2       : return    '拒绝退款';  break;

        default      : return    '';       break;
    }
}

/**
 * 用户操作状态
 * @param $status
 * @return string
 * 模板 0.未选择
1.pc模版一
2.pc模板二
3.app模板一
4.app模板二
 */
function floor_mold_status($status) {
    switch ($status) {
        case 0       : return    '未选择';    break;
        case 1       : return    '模板一';  break;
        case 2       : return    '模板二';  break;
        default      : return    '';        break;
    }
}


/**
 * 注册来源 1 微信 2 QQ 3 新浪 4 普通注册
 * @param $status
 * @return string
 */
function userRegisterType($status)
{
    switch($status){
        case 1 : return '微信';break;
        case 2 : return 'QQ';break;
        case 3 : return '新浪';break;
        case 4 : return '普通注册';break;
        default : return false ; break;
    }
}

/**
 * 提现状态
 * @param $status
 * @return string
 */
function withdrawStatus($status)
{
    switch($status){
        case 0 : return '未打款';break;
        case 1 : return '已打款';break;
        default : return false ; break;
    }
}

/**
 * 提现状态
 * @param $status
 * @return string
 */
function rechargeStatus($status)
{
    switch($status){
        case 0 : return '待完成';break;
        case 1 : return '已完成';break;
        default : return false ; break;
    }
}

/**
 * [auditStatus 审核状态]
 * 审核状态 0 未审核(可以卖) 1 审核通过(可以卖) 2 审核未通过{不可以卖}
 * @param  [type] $status [description]
 * @return [type]         [description]
 */
function auditStatus($status)
{
    switch ($status) {
        case 0:return '未审核';break;
        case 1:return '审核通过';break;
        case 2:return '审核未通过';break;
        default:break;
    }

}
/**
 * [rechargeType 充值方式]
 * @param  [type] $status [description]
 * @return [type]         [description]
 *  1  微信  2  支付宝  3  银联
 */
function rechargeType($status)
{
    switch ($status) {
        case 1:return '微信';break;
        case 2:return '支付宝';break;
        case 3:return '银联';break;
        case 4:return '余额';break;
        default:return '未返回';break;
    }
}

function DataSheetType($type)
{
    switch ($type) {
        case 1:return '剩余收益';break;
        case 2:return '平台收益';break;
        default:return '未返回';break;
    }
}

/**
 * 诚信商家状态
 */
function integrity_merchant_status($status)
{
    switch ($status) {
        case 1:return '诚信商家';break;
        case 2:return '未办理';break;
        default:return '未返回';break;
    }
}


/**
 * 服务列表
 * 类型 1 首页广告位 2 签到页广告位 3 发现好商品 4 发现好店 5发现号服务器  6入住积分商城 7诚信商家
 */
function merAdverType($type)
{
    switch ($type) {
        case 1:return '首页广告位';break;
        case 2:return '签到页广告位';break;
        case 3:return '发现好商品';break;
        case 4:return '发现好店';break;
        case 5:return '发现好服务';break;
        case 6:return '云推广';break;
        case 7:return '诚信商家';break;
        default:return '未返回';break;
    }
}


/**
 * 退货流程  1 发起退货(用户) 2 卖家同意退货并确认地址/说明 3代配送 4配送中 5收货并确认 6完成
 */
function orderOutFlow($step)
{
    switch ($step) {
        case 1:return '发起退货';break;
        case 2:return '卖家同意退货';break;
        case 3:return '等配送';break;
        case 4:return '配送中';break;
        case 5:return '商家收货';break;
        case 6:return '完成';break;
        case 9:return '已拒绝';break;
        default:return '未返回';break;
    }
}

/**
 * 用户操作状态
 * @param $status
 * @return string
 */
function get_pay_type($status) {
    switch ($status) {
        case 1       : return    '支付宝';  break;
        case 2       : return    '微信';    break;
        case 3       : return    '银联';    break;
        case 4       : return    '余额';    break;
        default      : return    '';       break;
    }
}
/**
 * [discover 发现]
 */
function discover ($type)
{
    switch ($type) {
        case 3       : return    '发现好商品';    break;
        case 4       : return    '发现好店铺';    break;
        case 5       : return    '发现好服务';    break;
        default      : return    '';       break;
    }
}

function getWcMsgtype($type)
{
    switch ($type) {
        case 0       : return    '<span style="color:red;">空回复</span>';    break;
        case 1       : return    '<span style="color:blue;">关注回复</span>';    break;
        case 2       : return    '<span style="color:green;">文本回复</span>';    break;
        case 3       : return    '<span style="color:rgb(140,74,255)">图文回复</span>';    break;
        default      : return    '';       break;
    }
}