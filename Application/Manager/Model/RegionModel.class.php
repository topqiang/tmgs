<?php
namespace Manager\Model;
/**
 * Created by PhpStorm.
 * User: xuexiaofeng
 * Date: 2015-10-12 0012
 * Time: 16:06
 * 城市相关 模型
 */
class RegionModel extends BaseModel {

    public function getList($param = array()){
        if(!empty($param['page_size'])) {
            $total      = $this->alias('r')->where($param['where'])->count();
            $Page       = $this->getPage($total, $param['page_size'], $param['parameter']);
            $page_show  = $Page->show();
        }
        $model  = $this->alias('r')
            ->field('r.*')
            ->where($param['where'])
            ->order($param['order']);

        //是否分页
        !empty($param['page_size'])  ? $model = $model->limit($Page->firstRow,$Page->listRows) : '';

        $list = $model->select();
        return array('list'=>$list,'page'=>!empty($page_show) ? $page_show : '');
    }

    public function findRow($param = array()){
        $row = $this->alias('r')
            ->field('r.*')
            ->where($param['where'])
            ->find();
        return $row;
    }

}