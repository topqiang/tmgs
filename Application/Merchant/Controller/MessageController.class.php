<?php

namespace Merchant\Controller;

/**
 * [消息]
 * @author zhouwei
 * Class MessageController
 * @package Merchant\Controller
 */
class MessageController extends BaseController {
    /**
     * 频道列表页
     */
    function orderIndex() {
        $Object = D(CONTROLLER_NAME,'Logic');
        $result = $Object->getOrderList(I('request.'));
        if($result) {
            $this->assign('page', $result['page']);
            $this->assign('list', $result['list']);
        } else {
            $this->error($Object->getLogicError());
        }
        // 记录当前列表页的cookie
        Cookie('__forward__',$_SERVER['REQUEST_URI']);
        $this->getIndexRelation();
        $this->display('orderIndex');
    }

    /**
     * 修改
     */
    function update() {
//        $this->checkRule(self::$rule);
        if(!IS_POST) {
            if ($_GET['id']) {
                M('Message') -> where(array('id'=>$_GET['id'])) -> data(array('status'=>'1')) -> save();
                $Object = D(CONTROLLER_NAME,'Logic');
                $row = $Object->findRow(I('get.'));
                if ($row) {
                    $this->getUpdateRelation();
                    $this->assign('row', $row);
                } else {
                    $this->error($Object->getLogicError());
                }
            }
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
