<?php

namespace Manager\Controller;

/**
 * 广告管理控制器
 * Class AdvertController
 * @package Manager\Controller
 */
class AdvertController extends BaseController {

    // 添加
    function getAddRelation()
    {
        $model = M('region') -> where(array('region_type'=>array('neq',3))) -> select();
        $this -> assign('region',$model);
    }
    // 修改
    function getUpdateRelation()
    {
        $model = M('region') -> where(array('region_type'=>array('neq',3))) -> select();
        $this -> assign('region',$model);
    }

    function getRegionList(){
        $regionParentId = $_POST['parentId'];
        $data['region'] = M('region') -> where(array('parent_id'=>$regionParentId))->select();
        if($data['region']){
            $data['status'] = 1;
            $data['msg'] = '返回成功';
        }else{
            $data['status'] = 0;
            $data['msg'] = '返回失败';
        }
        $this->ajaxReturn($data);
    }



}
