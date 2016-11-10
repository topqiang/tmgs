<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/10/13
 * Time: 13:59
 */

namespace Common\Model;


class FeedbackModel extends BaseModel{


    /**
     * @var array
     * 自动验证规则
     */
    protected $_validate = array(
        array('content','require','内容不能为空！！',self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('phone','require','请填写手机号！！',self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
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
    function getList($param = array()) {

    }

    /**
     * @param $param
     * @return mixed
     */
    function findRow($param = array()) {

    }

}