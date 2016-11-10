<?php

namespace Merchant\Model;

/**
 * Class AttributeModel
 * @package Manager\Model
 */
class AttributeModel extends BaseModel {
    /**
     * @var array
     * 自动验证规则
     */
    protected $_validate = array (
        array('cn_attr_name', 'require', '中文属性名字必须填写', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('cn_attr_name', '1,50', '中文属性长度不能超过50个字符', self::MUST_VALIDATE, 'length', self::MODEL_BOTH),

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
        $session = session('merInfo');
        if(!empty($param['page_size'])) {
            $total      = $this->alias('attr')
                            ->where($param['where'])
                ->join(C('DB_PREFIX').'goods_type as gt ON attr.thr_g_t_id=gt.id')
                ->join(C('DB_PREFIX').'attribute_content as ac ON attr.id=ac.attr_id AND ac.status != 9 AND ac.is_merchant ='.$session['mer_id'])
                ->field('attr.*,gt.cn_type_name,COUNT(ac.id) as value_num')
                ->group('attr.id')
                            ->count();
            $Page       = $this->getPage($total, $param['page_size'], $param['parameter']);
            $page_show  = $Page->show();
        }
        $model  = $this
                ->alias('attr')
                ->where($param['where'])
                ->join(C('DB_PREFIX').'goods_type as gt ON attr.thr_g_t_id=gt.id')
                ->join(C('DB_PREFIX').'attribute_content as ac ON attr.id=ac.attr_id AND ac.status != 9 AND ac.is_merchant ='.$session['mer_id'],'LEFT')
                ->field('attr.*,gt.cn_type_name,COUNT(ac.id) as value_num')
                ->group('attr.id')
                ->order('is_merchant DESC , value_num DESC');

        //是否分页
        !empty($param['page_size'])  ? $model = $model->limit($Page->firstRow,$Page->listRows) : '';

        $list = $model->select();
//        foreach ($list as $k=>$v){
//            $list[$k]['thr_g_t_id'] = D('goods_type') -> where(array('id'=>$v['thr_g_t_id'])) -> getField('cn_type_name');
//        }
        return array('list'=>$list,'page'=>!empty($page_show) ? $page_show : '');
    }

    /**
     * @param array $param
     * @return mixed
     */
    function findRow($param = array()) {
        $row = $this->where($param['where'])->find();
        return $row;
    }
}