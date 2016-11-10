<?php
namespace Merchant\Model;

/**
 * [积分商城]
 * @author zhouwei
 * Class IntegralMallModel
 * @package Merchant\Model
 */
class IntegralMallModel extends BaseModel {

    /**
     * @var array
     * 自动验证规则
     */
    protected $_validate = array (
       array('pic', 'require', '请上传图片', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
       array('goods_name', 'require', '请填写商品名称', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
       array('integral', 'require', '请填写兑换积分', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
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
            $list[$k]['pic'] = api('System/getFiles',array($v['pic']));
        }
        return array('list'=>$list,'page'=>!empty($page_show) ? $page_show : '');
    }
    /**
     * @param array $param
     * @return mixed
     */
    function findRow($param = array()) {
        $row = $this->where($param['where'])->find();
        $row['pic'] = api('System/getFiles',array($row['pic']));

        return $row;
    }
}