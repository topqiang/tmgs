<?php
namespace Common\Model;

/**
 * Created by PhpStorm.
 * User: xuexiaofeng
 * Date: 2015-10-23 0023
 * Time: 10:08
 */
class CartModel extends BaseModel{

    protected $_validate = array(
        array('m_id','require','请登陆',self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('goods_id','require','请选择商品',self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
    );

    function getList($param = array()){
        if(!empty($param['page_size'])) {
            $total      = $this->alias('c')->where($param['where'])->count();
            $Page       = $this->getPage($total, $param['page_size'], $param['parameter']);
            $page_show  = $Page->show();
        }
        $field = 'c.*,f.abs_url AS goods_cover,g.is_hot,g.is_seckill';
        $model = $this->alias('c')
            ->where($param['where'])
            ->field($field)
            ->join(C('DB_PREFIX').'file f ON f.id = c.goods_img','LEFT')
            ->join(C('DB_PREFIX').'goods g ON g.id = c.goods_id','LEFT')
            ->order($param['order']);
        !empty($param['page_size'])  ? $model = $model->limit($Page->firstRow,$Page->listRows) : '';
        $list = $model->select();
        return array('list'=>$list,'page'=>!empty($page_show) ? $page_show : '');
    }


    function findRow($param = array()){
        $field = 'g.id,g.goods_name,g.market_price,g.goods_price';
        $result = $this->alias('g')
            ->where($param['where'])
            ->field($field)
            ->find();
        return $result;
    }
}