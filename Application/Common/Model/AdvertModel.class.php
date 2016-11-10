<?php
namespace Common\Model;
/**
 * Created by PhpStorm.
 * User: xuexiaofeng
 * Date: 2015-10-20 0019
 * Time: 17:19
 */
class AdvertModel extends BaseModel {

    function getList($param = array()){
        if(!empty($param['page_size'])) {
            $total      = $this->alias('a')->where($param['where'])->count();
            $Page       = $this->getPage($total, $param['page_size'], $param['parameter']);
            $page_show  = $Page->show();
        }
        $model = $this->alias('a')
            ->where($param['where'])
            ->field('a.id ad_id,a.ad_url,a.type,a.goods_id,a.cate_id,f.path AS ad_pic')
            ->join(C('DB_PREFIX').'file f ON f.id = a.ad_pic','LEFT')
            ->order($param['order']);
        !empty($param['page_size'])  ? $model = $model->limit($Page->firstRow,$Page->listRows) : '';
        $list = $model->select();
        return array('list'=>$list,'page'=>!empty($page_show) ? $page_show : '');
    }


    function findRow($param = array()){
        $result = $this->alias('a')
            ->where($param['where'])
            ->field('a.id ad_id,a.ad_url,a.type,a.goods_id,a.cate_id,f.path AS ad_pic')
            ->join(C('DB_PREFIX').'file f ON f.id = a.ad_pic','LEFT')
            ->find();
        return $result;
    }

}

