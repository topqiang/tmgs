<?php
namespace Manager\Model;

/**
 * [意见反馈]
 * @author zhouwei
 * Class FeedbackModel
 * @package Manager\Model
 */
class FeedbackModel extends BaseModel {


    protected $_validate = array (
//        array('unique_code', 'require', '行为标识必须', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
    );


    protected $_auto = array(
        array('create_time', 'time', self::MODEL_INSERT, 'function'),
        array('update_time', 'time', self::MODEL_UPDATE, 'function'),
    );


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
        foreach($list as $k => $v){
            if($v['type'] == 1){
                $list[$k]['object_id'] = M('Member')->where(array('id'=>$v['object_id']))->getField('account');
            }else{
                $list[$k]['object_id'] = M('Merchant')->where(array('id'=>$v['object_id']))->getField('account');
            }
            $list[$k]['type'] = $v['type']== 1 ? '<span style="color:#6392c8;">用户</span>' : '<span style="color:#cf5b56;">商家</span>';
            $list[$k]['create_time'] = date('Y-m-d H:i:s',$v['create_time']);
        }
        return array('list'=>$list,'page'=>!empty($page_show) ? $page_show : '');
    }


    function findRow($param = array()) {
        $this->where($param['where'])->data('status=1')->save();
        $row = $this->where($param['where'])->find();
        return $row;
    }
}