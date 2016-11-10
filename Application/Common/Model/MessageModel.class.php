<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/10/13
 * Time: 13:59
 */

namespace Common\Model;


class MessageModel extends BaseModel{


    /**
     * @var array
     * 自动验证规则
     */
    protected $_validate = array();


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

        if(!empty($param['page_size'])) {
            $total      = $this->alias('m')->where($param['where'])->count();
            $Page       = $this->getPage($total, $param['page_size'], $param['parameter']);
            $page_show  = $Page->show();
        }
        $field = 'm.*';
        $model = $this->alias('m')
                    ->where($param['where'])
                    ->field($field)
                    ->order($param['order']);
        !empty($param['page_size'])  ? $model = $model->limit($Page->firstRow,$Page->listRows) : '';
        $list = $model->select();
        return $list;
    }

    /**
     * @param $param
     * @return mixed
     */
    function findRow($param = array()) {

        $row = $this->alias('m')
                    ->where($param['where'])
                    ->field('m.*')
                    ->find();
        return $row;
    }

}