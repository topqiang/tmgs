<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/10/10
 * Time: 16:52
 */

namespace Common\Model;

class MemberModel extends BaseModel{


    protected $_validate = array(
        array('account','require','手机号不能为空！！',self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('new_account','require','新手机号不能为空！！',self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('password','require','密码不能为空！！',self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('password','6,18','密码长度在6--18位！！',self::EXISTS_VALIDATE, 'length', self::MODEL_BOTH),
        array('password','re_password','确认密码与密码输入不一致！！',self::EXISTS_VALIDATE, 'confirm', self::MODEL_BOTH),
        array('nickname','require','昵称不能为空！！',self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('head','require','头像不能为空！！',self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('old_password','require','请输入原密码！！',self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('new_password','require','请输入新密码！！',self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('new_password','6,18','新密码长度在6--18位！！',self::EXISTS_VALIDATE, 'length', self::MODEL_BOTH),
        array('new_password','re_new_password','确认新密码与新密码不一致！！',self::EXISTS_VALIDATE, 'confirm', self::MODEL_BOTH),
    );


    /**
     * @var array
     * 自动完成规则
     */
    protected $_auto = array (
        array('create_time', 'time', self::MODEL_INSERT, 'function'),
        array('update_time', 'time', self::MODEL_UPDATE, 'function'),
    );


    /**
     * @param array $param  综合条件参数
     * @return array
     */
    function getList($param = array()) {}


    function findRow($param = array()){
        $field = 'm.*,f.abs_url AS head';
        $result = $this->alias('m')
                        ->where($param['where'])
                        ->field($field)
                        ->join(C('DB_PREFIX').'file f ON f.id = m.head','LEFT')
                        ->find();
        

        return $result;
    }

}