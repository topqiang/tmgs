<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: luofei614 <weibo.com/luofei614>　
// +----------------------------------------------------------------------
namespace Think;
/**
 * 权限认证类
 * 功能特性：
 * 1，是对规则进行认证，不是对节点进行认证。用户可以把节点当作规则名称实现对节点进行认证。
 *      $auth=new Auth();  $auth->check('规则名称','用户id')
 * 2，可以同时对多条规则进行认证，并设置多条规则的关系（or或者and）
 *      $auth=new Auth();  $auth->check('规则1,规则2','用户id','and')
 *      第三个参数为and时表示，用户需要同时具有规则1和规则2的权限。 当第三个参数为or时，表示用户值需要具备其中一个条件即可。默认为or
 */

class Auth{

    //默认配置
    protected $_config = array(
        'AUTH_ON'           => true,                // 认证开关
        'AUTH_TYPE'         => 1,                   // 认证方式，1为实时认证；2为登录认证。
        'AUTH_GROUP'        => 'auth_group',        // 用户组数据表名
        'AUTH_RULE'         => 'auth_rule',         // 权限规则表
        'AUTH_USER'         => 'administrator'      // 管理员信息表
    );

    public function __construct() {
        $prefix = C('DB_PREFIX');
        $this->_config['AUTH_GROUP'] = $prefix.$this->_config['AUTH_GROUP'];
        $this->_config['AUTH_RULE'] = $prefix.$this->_config['AUTH_RULE'];
        $this->_config['AUTH_USER'] = $prefix.$this->_config['AUTH_USER'];
        if (C('AUTH_CONFIG')) {
            //可设置配置项 AUTH_CONFIG, 此配置项为数组。
            $this->_config = array_merge($this->_config, C('AUTH_CONFIG'));
        }
    }

    /**
      * 检查权限
      * @param string|array name   需要验证的规则列表,支持逗号分隔的权限规则或索引数组
      * @param int $uid             认证用户的id
      * @param int $type
      * @param string $mode        执行check的模式
      * @param string $relation     如果为 'or' 表示满足任一条规则即通过验证;如果为 'and'则表示需满足所有规则才能通过验证
      * @return boolean           通过验证返回true;失败返回false
     */
    public function check($name, $uid = 0, $type = 1, $mode = 'url', $relation = 'or') {
        if (!$this->_config['AUTH_ON'])
            return true;
        //获取用户需要验证的所有有效规则列表
        $authList = $this->getAuthList($uid,$type);
        if (is_string($name)) {
            $name = strtolower($name);
            if (strpos($name, ',') !== false) {
                $name = explode(',', $name);
            } else {
                $name = array($name);
            }
        }
        //保存验证通过的规则名
        $list = array();
        if ($mode == 'url') {
            $REQUEST = unserialize( strtolower(serialize($_REQUEST)) );
        }
        foreach ( $authList as $auth ) {
            $query = preg_replace('/^.+\?/U','',$auth);
            if ($mode == 'url' && $query != $auth ) {
                //解析规则中的param
                parse_str($query,$param);
                $intersect = array_intersect_assoc($REQUEST,$param);
                $auth = preg_replace('/\?.*$/U','',$auth);
                //如果节点相符且url参数满足
                if ( in_array($auth,$name) && $intersect == $param ) {
                    $list[] = $auth ;
                }
            } else if (in_array($auth , $name)) {
                $list[] = $auth ;
            }
        }
        if ($relation == 'or' and !empty($list)) {
            return true;
        }
        $diff = array_diff($name, $list);
        if ($relation == 'and' and empty($diff)) {
            return true;
        }
        return false;
    }

    /**
     * @param $uid 管理员ID
     * @return mixed
     * 根据用户id获取用户组,返回值为数组
     */
    public function getGroups($uid) {
        static $groups = array();
        if (isset($groups[$uid]))
            return $groups[$uid];
        $user_groups = M()
            ->table($this->_config['AUTH_USER'] . ' admin')
            ->where("admin.id='$uid' and auth_group.status='1'")
            ->join($this->_config['AUTH_GROUP']." auth_group on admin.group_id=auth_group.id")
            ->field('auth_group.id,auth_group.title,auth_group.rules')->select();
        $groups[$uid]=$user_groups?:array();
        return $groups[$uid];
    }

    /**
     * @param $uid 管理员ID
     * @param $type
     * @return array
     * 获得权限列表
     */
    protected function getAuthList($uid,$type) {
        static $_authList = array(); //保存用户验证通过的权限列表
        $t = implode(',',(array)$type);
        if (isset($_authList[$uid.$t])) {
            return $_authList[$uid.$t];
        }
        if( $this->_config['AUTH_TYPE']==2 && isset($_SESSION['_AUTH_LIST_'.$uid.$t])){
            return $_SESSION['_AUTH_LIST_'.$uid.$t];
        }

        //读取用户所属用户组
        $groups = $this->getGroups($uid);
        $ids = array();//保存用户所属用户组设置的所有权限规则id
        foreach ($groups as $g) {
            $ids = array_merge($ids, explode(',', trim($g['rules'], ',')));
        }
        $ids = array_unique($ids);
        if (empty($ids)) {
            $_authList[$uid.$t] = array();
            return array();
        }

        $map=array(
            'id'=>array('in',$ids),
            'type'=>$type,
            'status'=>1,
        );
        //读取用户组所有权限规则
        $rules = M()->table($this->_config['AUTH_RULE'])->where($map)->field('condition,name')->select();

        //循环规则，判断结果。
        $authList = array();
        foreach ($rules as $rule) {
            //只要存在就记录
            $authList[] = strtolower($rule['name']);
        }
        $_authList[$uid.$t] = $authList;
        if($this->_config['AUTH_TYPE']==2){
            //规则列表结果保存到session
            $_SESSION['_AUTH_LIST_'.$uid.$t]=$authList;
        }
        return array_unique($authList);
    }
}
