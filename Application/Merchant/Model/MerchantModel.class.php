<?php
namespace Merchant\Model;

/**
 * 商家
 * Class MerchantModel
 * @package Manager\Model
 */
class MerchantModel extends BaseModel {

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
            $total      = $this->alias('mer')->where($param['where'])->count();
            $Page       = $this->getPage($total, $param['page_size'], $param['parameter']);
            $page_show  = $Page->show();
        }
        $field = 'mer.*,member.account';
        $model  = $this
                    -> alias('mer')
                    ->where($param['where'])
                    -> join(C('DB_PREFIX').'member as member ON member.id=mer.m_id')
                    ->field($field)
                    ->order($param['order']);

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
        $row =M('merchant')-> where($param['where'])->find();
        $row['hold_card_pic'] = api('System/getFiles',array($row['hold_card_pic']));
        $row['back_card_pic'] = api('System/getFiles',array($row['back_card_pic']));
        $row['head_pic'] = api('System/getFiles',array($row['head_pic']));
        $row['hand_idcard_pic'] = api('System/getFiles',array($row['hand_idcard_pic']));
        return $row;
    }



}