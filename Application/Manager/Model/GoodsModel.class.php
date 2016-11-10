<?php
namespace Manager\Model;

/**
 * Class GoodsModel
 * @package Manager\Model
 */
class GoodsModel extends BaseModel {

    /**
     * @var array
     * 自动验证规则
     */
    protected $_validate = array ();

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

        $model  = $this
                    ->where($param['where'])
                    ->order($param['order']);

        //是否分页
        !empty($param['page_size'])  ? $model = $model->limit($Page->firstRow,$Page->listRows) : '';
        $list = $model->select();
        foreach($list as $k=>$v){
            $list[$k]['merchant_name'] = M('merchant') ->where(array('id'=>$v['merchant_id']))->getField('merchant_name');
        }
//        dump($list);
        return array('list'=>$list,'page'=>!empty($page_show) ? $page_show : '');
    }

    /**
     * @param array $param
     * @return mixed
     */
    function findRow($param = array()) {
        $row = $this->where($param['where'])->alias('goods')->find();
        $row['goods_pic'] = $this -> pic_for(api('System/getFiles',array($row['goods_pic'])));
        $row['product'] = M('goods_product') -> where(array('goods_id' => $row['id'])) -> select();
        foreach($row['product'] as $k=>$v){
           $row['product'][$k]['attr_key_group'] =  $this -> get_list($v['attr_key_group']);
        }
        return $row;
    }

    function pic_for($data){
        foreach($data as $k => $v){
            $result[] = '<a style="display:block;width:100px;float:left;" class="test2" herf="'.$v["path"].'"><img src="'.$v['path'].'"/></a>';
        }
        return $result;
    }

    function get_list($data){
        $param['where']['id'] = (false !== strpos($data,',')) ? array('IN',$data) : $data;
        $model = M('attribute_content') -> where($param['where']) ->field('cn_attr_con_name') -> select();
        $c = '';
        foreach($model as $k=>$v){
            $c .=  $v['cn_attr_con_name'];
        }
        return $c;
    }

}