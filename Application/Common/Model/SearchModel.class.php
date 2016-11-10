<?php
namespace Common\Model;

/**
 * Created by PhpStorm.
 * User: xuexiaofeng
 * Date: 2015-10-23 0023
 * Time: 10:08
 */
class SearchModel extends BaseModel{



    function getList($param = array()){
        if(!empty($param['page_size'])) {
            $total      = $this->alias('s')->where($param['where'])->count();
            $Page       = $this->getPage($total, $param['page_size'], $param['parameter']);
            $page_show  = $Page->show();
        }
        $field = 's.id s_id,s.m_id,s.goods_name,s.search_num';
        $model = $this->alias('s')
            ->where($param['where'])
            ->field($field)
            ->order($param['order']);
        !empty($param['page_size'])  ? $model = $model->limit($Page->firstRow,$Page->listRows) : '';
        $list = $model->select();
        return array('list'=>$list,'page'=>!empty($page_show) ? $page_show : '');
    }


    function findRow($param = array()){

    }
}