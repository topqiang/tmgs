<?php
namespace Common\Model;
/**
 * Created by PhpStorm.
 * User: xuexiaofeng
 * Date: 2015-10-20 0019
 * Time: 17:19
 */
class GoodsCommentModel extends BaseModel {

    /**
     * @var array
     * 自动验证规则
     */
    protected $_validate = array (
        array('content', 'require', '请输入评价内容！', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
    );

    function getList($param = array()){
        if(!empty($param['page_size'])) {
            $total      = $this->alias('gc')->where($param['where'])->count();
            $Page       = $this->getPage($total, $param['page_size'], $param['parameter']);
            $page_show  = $Page->show();
        }
        $field = 'gc.id gc_id,gc.*,m.head';
        $model = $this->alias('gc')
            ->where($param['where'])
            ->field($field)
            ->join(C('DB_PREFIX').'member m ON m.id = gc.m_id','LEFT')
            ->order($param['order']);
        !empty($param['page_size'])  ? $model = $model->limit($Page->firstRow,$Page->listRows) : '';
        $list = $model->select();
        return array('list'=>$list,'page'=>!empty($page_show) ? $page_show : '');
    }


    function findRow($param = array()){
        $field = 'gc.id gc_id,gc.*';
        $result = $this->alias('gc')
                ->where($param['where'])
                ->field($field)
                ->find();
        return $result;
    }

    /**
     * @param $id
     * @param string $field
     * @return mixed
     * 返回指定字段
     */
    public function findField($id,$field = ''){
        $row = $this->where(array('id'=>$id))->getField($field);
        return $row;
    }

}

