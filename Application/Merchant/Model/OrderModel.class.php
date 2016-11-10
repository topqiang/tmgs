<?php
namespace Merchant\Model;

/**
 * @author zhouwei
 * Class OrderModel
 * @package Merchant\Model
 * 订单管理 - 订单列表 - 模型
 */
class OrderModel extends BaseModel {

    /**
     * @var array
     * 自动验证规则
     */
    protected $_validate = array (
    );

    /**
     * @var array
     * 自动完成规则
     */
    protected $_auto = array(
        array('create_time', 'time', self::MODEL_INSERT, 'function'),
        array('update_time', 'time', self::MODEL_UPDATE, 'function'),
    );

    /**
     * @param array $param  综合条件参数
     * @return array
     */
    function getList($param = array()) {
        if(!empty($param['page_size'])) {
            $total      = $this->where($param['where'])->count();
            $Page       = $this->getPage($total, $param['page_size'], $param['parameter']);
            $page_show  = $Page->show();
        }

        $model  = $this->where($param['where'])->order($param['order']);
        //是否分页
        !empty($param['page_size'])  ? $model = $model->limit($Page->firstRow,$Page->listRows) : '';

        $list = $model->select();
        foreach($list as $k=>$v){
            $list[$k]['m_id'] = M('Member') ->where(array('id'=>$v['m_id']))->getField('account');
            $list[$k]['address'] = unserialize($v['address']);
            if($v['status'] == '7'){
                $list[$k]['after_sale'] = M('AfterSale')  -> where(array('order_id'=>$v['id'])) -> getField('status');
            }
        }
        return array('list'=>$list,'page'=>!empty($page_show) ? $page_show : '');
    }

    /**
     * @param array $param
     * @return mixed
        
     */
    function findRow($param = array()) {
        $row = $this->where($param['where'])->find();
        $row['address'] = unserialize($row['address']);
        $str = trim($row['address']['province_id'] .','. $row['address']['city_id'] . ',' .$row['address']['area_id']);
        $row['address']['territory'] = implode(',',array_column(M('region') -> where(array('id'=>array('in',$str))) -> select(),'region_name'));
        $goods_info_serialization = unserialize($row['goods_info_serialization']);
        foreach ($goods_info_serialization['goods'] as $key => $value) {
            $goods_info_serialization['goods'][$key]['goodsDetail']['supply_info'] = M('goods')->where(array('id'=> $goods_info_serialization['goods'][$key]['goodsDetail']['id']))->getField('supply_info');
        }
        $row['goods_info_serialization'] =$goods_info_serialization;
        if($row['status'] == 7){
            $sale_id= M('AfterSale') -> where(array('order_id'=>$row['id'])) -> getField('id');
            $row['after_sale'] = M('AfterSaleLog')->where(array('as_id'=>$sale_id))->order('create_time ASC')->select();
            foreach($row['after_sale'] as $k=>$v){
                $row['after_sale'][$k]['asl_id'] = $v['id'];unset($row['after_sale'][$k]['id']);
                if(!empty($v['content'])){
                    $content = unserialize($v['content']);
                    foreach($content as $kk =>$vv){
                        if($vv['key']){
                            $content[$kk]['list'] = $vv['key'] .':'. $vv['value'] ;
                        }else{
                            if($k == 0){
                                $content[$kk]['list'] = api('System/getFiles',array($vv['value'],array('path')));
                            }else{
                                $content[$kk]['list'] = $vv['value'] ;
                            }
                        }
                        unset($content[$kk]['value'],$content[$kk]['key']);
                    }
                    $row['after_sale'][$k]['content'] = $content;
                }else{
                    $row['after_sale'][$k]['content'] = [];
                }
            }
        }
        return $row;
    }
}