<?php
namespace Common\Model;
/**
 * Created by PhpStorm.
 * User: xuexiaofeng
 * Date: 2015-10-20 0019
 * Time: 17:19
 */
class GoodsModel extends BaseModel {

    protected $_validate = array();


    function getList($param = array()){
        if(!empty($param['page_size'])) {
            $total      = $this->alias('g')->where($param['where'])->count();
            $Page       = $this->getPage($total, $param['page_size'], $param['parameter']);
            $page_show  = $Page->show();
        }
        $field = 'g.id goods_id,g.goods_name,g.market_price,g.goods_price,g.seckill_price,g.goods_number,g.goods_volume,g.goods_brief,g.is_seckill,g.is_hot,g.buy_number,g.seckill_number,f.path AS goods_cover,g.goods_hot_img,g.unit,g.goods_type_s';
        $model = $this->alias('g')
            ->where($param['where'])
            ->field($field)
            ->join(C('DB_PREFIX').'file f ON f.id = g.goods_img','LEFT')
            ->order($param['order']);
        !empty($param['page_size'])  ? $model = $model->limit($Page->firstRow,$Page->listRows) : '';
        $list = $model->select();
        return array('list'=>$list,'page'=>!empty($page_show) ? $page_show : '');
    }


    function findRow($param = array()){
        $field = 'g.id goods_id,g.goods_name,g.goods_number,g.goods_volume,g.market_price,g.goods_price,g.seckill_price,g.goods_brief,g.seckill_price,g.goods_gallery,g.is_seckill,g.is_hot,g.story,g.goods_type_s,f.path AS goods_cover';
        $result = $this->alias('g')
                ->where($param['where'])
                ->field($field)
                ->join(C('DB_PREFIX').'file f ON f.id = g.goods_img','LEFT')
                ->find();
        return $result;
    }

    /**
     * @param $goods_id
     * @return mixed
     * 获取商品的多选属性
     */
    function getGoodsAttr($goods_id){
        $result = M('GoodsAttribute')->alias('ga')
            ->where("ga.goods_id=$goods_id AND ga.attr_id=a.id")
            ->field('ga.id,ga.attr_id,ga.attr_value,ga.attr_price,a.attr_name')
            ->join(C('DB_PREFIX').'attribute a ON a.id = ga.attr_id')
            ->select();
        return $result;
    }

    /**
     * @param $goods_id
     * @param string $field
     * @return mixed
     * 返回指定字段
     */
    public function findField($goods_id,$field = ''){
        $row = $this->where(array('id'=>$goods_id))->getField($field);
        return $row;
    }

}

