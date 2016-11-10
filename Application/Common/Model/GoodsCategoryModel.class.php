<?php
namespace Common\Model;

/**
 * Class FileModel
 * @package Common\Model
 * 文件模型
 */
class GoodsCategoryModel extends BaseModel {

    protected $_validate = array();


    function getList($param = array()){

        if(!empty($param['page_size'])) {
            $total      = $this->alias('cate')->where($param['where'])->count();
            $Page       = $this->getPage($total, $param['page_size'], $param['parameter']);
            $page_show  = $Page->show();
        }
        $field = 'cate.id cate_id,cate.name,cate.description,f.path AS icon,cate.cover';
        $model = $this->alias('cate')
            ->where($param['where'])
            ->field($field)
            ->join(C('DB_PREFIX').'file f ON f.id = cate.icon','LEFT')
            ->order($param['order']);
        !empty($param['page_size'])  ? $model = $model->limit($Page->firstRow,$Page->listRows) : '';
        $list = $model->select();
        return array('list'=>$list,'page'=>!empty($page_show) ? $page_show : '');
    }


}