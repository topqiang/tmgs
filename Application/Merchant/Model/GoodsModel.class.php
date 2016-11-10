<?php
namespace Merchant\Model;

/**
 * Class GoodsModel
 * @package Manager\Model
 */
class GoodsModel extends BaseModel {

    /**
     * @var array
     * 自动验证规则
     */
    protected $_validate = array (
        array('cn_goods_name', 'require', '请填写商品名称', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('cn_goods_brands', 'require', '请填写商品品牌', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('fir_g_t_id', 'require', '请选择一级分类', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('sec_g_t_id', 'require', '请选择二级分类', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('thr_g_t_id', 'require', '请选择三级分类', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('unit_id', 'require', '请选择商品单位', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('supply_info', 'require', '请填写供应商信息', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('cn_goods_introduction', 'require', '请填写商品详细信息', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
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
        return array('list'=>$list,'page'=>!empty($page_show) ? $page_show : '');
    }

    /**
     * @param array $param
     * @return mixed
     */
    function findRow($param = array()) {
        $row = $this->where($param['where'])->alias('goods')->find();
        $row['goods_pic'] = api('System/getFiles',array($row['goods_pic']));
        return $row;
    }

}