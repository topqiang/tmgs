<?php

namespace Manager\Controller;

/**
 * 微信文章
 */
class WechatArticleController extends BaseController {

    /**
     * 频道列表页
     */
    function index() {
        $this->checkRule(self::$rule);
        $Object = D(CONTROLLER_NAME,'Logic');
        $result = $Object->getList(I('request.'));
        if($result) {
            $this->assign('page', $result['page']);
            $this->assign('list', $result['list']);
            $this->assign('count', $result['count']);
        } else {
            $this->error($Object->getLogicError());
        }
        // 记录当前列表页的cookie
        Cookie('__forward__',$_SERVER['REQUEST_URI']);
        $this->getIndexRelation();
        $this->display('index');
    }


    /**
     * 添加
     */
    function add() {
        $this->checkRule(self::$rule);
        if(!IS_POST) {
            $this->getAddRelation();
            if($_GET['type'] == 0) $this->display('update0'); 
            if($_GET['type'] == 1) $this->display('update1'); 
            if($_GET['type'] == 2) $this->display('update2'); 
            if($_GET['type'] == 3) $this->display('update3'); 
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


     /**
     * 修改
     */
    function update() {
        $this->checkRule(self::$rule);
        if(!IS_POST) {
            if ($_GET['id']) {
                $Object = D(CONTROLLER_NAME,'Logic');
                $row = $Object->findRow(I('get.'));                
                if ($row) {
                    $this->getUpdateRelation();
                    $this->assign('row', $row);
                } else {
                    $this->error($Object->getLogicError());
                }
            }
            if($_GET['type'] == 0) $this->display('update0'); 
            if($_GET['type'] == 1) $this->display('update1'); 
            if($_GET['type'] == 2) $this->display('update2'); 
            if($_GET['type'] == 3) $this->display('update3'); 
            // $this->display('update');
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
