<?php
namespace Manager\Model;

/**
 * Class FileModel
 * @package Manager\Model
 * 文件模型
 */
class FileModel extends BaseModel {

    /**
     * @param array $param  综合条件参数
     * @return array
     */
    function getList($param = array()) {
        if(!empty($param['page_size'])) {
            $total      = $this->alias('file')->where($param['where'])->count();
            $Page       = $this->getPage($total, $param['page_size'], $param['parameter']);
            $page_show  = $Page->show();
        }

        $model  = $this->alias('file')
                        ->field('file.*,file_ext.description,file_ext.sort')
                        ->where($param['where'])
                        ->join(array(
                            'LEFT JOIN '.C('DB_PREFIX').'file_extend file_ext ON file_ext.file_id = file.id',
                        ))
                        ->order($param['order']);

        //是否分页
        !empty($param['page_size'])  ? $model = $model->limit($Page->firstRow,$Page->listRows) : '';

        $list = $model->select();

        return array('list'=>$list,'page'=>!empty($page_show) ? $page_show : '');
    }

    /**
     * @param $param
     * @return mixed
     */
    function findRow($param = array()) {
        $row = $this->alias('file')
                    ->field('file.*,file_ext.description,file_ext.sort')
                    ->where($param['where'])
                    ->join(array(
                        'LEFT JOIN '.C('DB_PREFIX').'file_extend file_ext ON file_ext.file_id = file.id',
                    ))
                    ->find();
        return $row;
    }
}