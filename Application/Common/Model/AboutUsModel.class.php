<?php

namespace Common\Model;


class AboutUsModel extends BaseModel{



    /**
     * @param array $param  综合条件参数
     * @return array
     */
    function getList($param = array()) {
        $field = 'ab.id ad_id,ab.title,ab.content,ab.create_time,ab.update_time,ab.status';
        $model = $this->alias('ab')
                    ->where($param['where'])
                    ->field($field)
                    ->order($param['order']);
        $list = $model->select();
        return $list;
    }

    /**
     * @param $param
     * @return mixed
     */
    function findRow($param = array()) {

    }

}