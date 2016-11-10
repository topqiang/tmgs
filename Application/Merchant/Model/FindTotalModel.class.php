<?php
namespace Merchant\Model;

/**
 * [发现模块]
 * @author zhouwei
 * Class FindTotalModel
 * @package Merchant\Model
 */
class FindTotalModel extends BaseModel {

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

        $model  = $this->where($param['where'])->order($param['order']);

        //是否分页
        !empty($param['page_size'])  ? $model = $model->limit($Page->firstRow,$Page->listRows) : '';

        $list = $model->select();
        foreach($list as $k=>$v){
            if($v['type'] != '3'){
                // 应该返回object_id 为 商家
                $list[$k][object_id] ='本商家';
            }else{
                $list[$k][object_id] =M('Goods')->where(array('id'=>$v['object_id']))->getField('cn_goods_name');
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
        $time = explode(',',trim($row['time'],','));
        sort($time);
        $row['time'] = '';
        if($row['type'] != '3'){
            // 应该返回object_id 为 商家
            $row[object_id] ='本商家';
        }else{
            $row[object_id] =M('Goods')->where(array('id'=>$row[object_id]))->getField('cn_goods_name');
        }
        foreach($time as $k =>$v){
            if(strtotime($v) > strtotime('-1 day')){
                $row['time'] .= '<div class="" style="padding:1px;height: 20px;width: 74px;border:1px solid #CCCCCC;float: left;margin-left: 5px;margin-bottom:3px;" >'.$v.'</div>';
            }else{
                $row['time'] .= '<div class="" style="color:#CCCCCC;padding:1px;height: 20px;width: 74px;border:1px solid #CCCCCC;float: left;margin-left: 5px;margin-bottom:3px;" >'.$v.'</div>';
            }
        }
        return $row;
    }
}