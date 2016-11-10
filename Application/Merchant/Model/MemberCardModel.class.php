<?php
namespace Merchant\Model;

/**
 * @author zhouwei
 * Class MemberCardModel
 * @package Merchant\Model
 * 财务管理-银行卡管理-模型
 */
class MemberCardModel extends BaseModel {

    /**
     * @var array
     * 自动验证规则
     */
    protected $_validate = array (
        array('name', 'require', '请输入持卡人姓名', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('card_number', 'require', '请输入银行账号', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('id_card', 'require', '请输入身份证号', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('phone', 'require', '请填写联系电话', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
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
        $row = $this->where($param['where'])->find();
        return $row;
    }
}