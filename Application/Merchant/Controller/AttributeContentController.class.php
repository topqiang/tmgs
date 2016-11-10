<?php

namespace Merchant\Controller;

/**
 * 行为控制器
 * 后台行为的 增删改查
 */
class AttributeContentController extends BaseController {

    function getIndexRelation(){
    }

    /**
     * 添加
     */
    function add() {
        $this->checkRule(self::$rule);
        if(!IS_POST) {
            $this->getAddRelation();
            $this -> assign('attr_id',$_GET['attr_id']);
            $this->display('update');
        } else {
            $Object = D(CONTROLLER_NAME,'Logic');
            $result = $Object->update(I('post.'));
            if($result) {
                $this->success($Object->getLogicSuccess(), Cookie('__forward__'));
            } else {
                $this->error($Object->getLogicError());
            }
        }
    }
}
