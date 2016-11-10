<?php

namespace Manager\Logic;

/**
 * 交易明细
 */
class DealDetailLogic extends BaseLogic {

   /**
    * [getList 列表]
    *
    * @param  array  $request [description]
    *
    * @return [type]          [description]
    */
    function getList($request = array()) {
        if($_GET['type']){
            $param['where']['type'] = $_GET['type'];
        }
        if($_POST['type'] && !empty($request['account'])){
            if($request['type'] == 2){
                $mer = M('Merchant') -> where(array('account'=>array('like','%'.trim($request['account']).'%')))->field('id')->select();
                $param['where']['object_id']  = array('in',implode(',',array_column($mer,'id')));
                $param['where']['type'] = 2;
            }else{
                $mem = M('Member') -> where(array('account'=>array('like','%'.trim($request['account']).'%')))->field('id')->select();
                $param['where']['object_id']  = array('in',implode(',',array_column($mem,'id')));
                $param['where']['type'] = 1;
            }
        }
        if(!empty($request['create_time'])){
            $param['where']['create_time'] = array(array('egt',strtotime(trim($request['create_time']))),array( 'elt',strtotime('+1day',strtotime(trim($request['create_time']))) ),'AND');
        }
        // 交易标题
        if(!empty($request['title'])){
            $param['where']['title'] = array('like','%'.trim($request['title']).'%');
        }
        // 交易内容
        if(!empty($request['content'])){
            $param['where']['content'] = array('like','%'.trim($request['content']).'%');
        }
        $param['order']             = 'create_time DESC';   //排序
        $param['page_size']         = C('LIST_ROWS');        //页码
        $param['parameter']         = $request;             //拼接参数

        $result = D('PayLog')->getList($param);

        return $result;
    }

    /**
     * @param array $request
     * @return mixed
     */
    function findRow($request = array()) {}
}