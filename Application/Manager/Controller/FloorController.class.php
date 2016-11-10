<?php

namespace Manager\Controller;

/**
 * 楼层总控制
 * Class FloorController
 * @package Manager\Controller
 */
class FloorController extends BaseController {

    function floorpic() {
        $this->checkRule(self::$rule);
        $Object = D('FloorPicture','Logic');
        $result = $Object->getList(I('request.'));
        if($result) {
            $this->assign('floor_id',I('get.id'));
            $this->assign('mold',I('get.mold'));
            $this->assign('page', $result['page']);
            $this->assign('list', $result['list']);
        } else {
            $this->error($Object->getLogicError());
        }
        // 记录当前列表页的cookie
        Cookie('__forward__',$_SERVER['REQUEST_URI']);
        $this->getIndexRelation();
        $this->display('floorpic');
    }

    /**
     * 添加
     */
    function picadd() {
        $this->checkRule(self::$rule);
        if(!IS_POST) {
            $this->getAddRelation();
            $this->assign('floor_id',I('floor_id'));
            $this->assign('mold',I('mold'));
            $this->assign('count',M('floor_picture')->where(array('floor_id'=>I('floor_id')))->getField('MAX(grade)'));
            if(I('mold') == 1){
                $this->assign('num',8);
            }else{
                $this->assign('num',2);
            }
            $this->display('picupdate');
        } else {
            $Object = D('Floor','Logic');
            $result = $Object->update(I('post.'));
            if($result) {
                $this->success($Object->getLogicSuccess(), Cookie('__forward__'));
            } else {
                $this->error($Object->getLogicError());
            }
        }
    }

    /**
     * 图片
     */
    function picupdate() {
        $this->checkRule(self::$rule);
        if(!IS_POST) {
            if ($_GET['id']) {
                $Object = D('FloorPicture','Logic');
                $row = $Object->findRow(I('get.'));
                if ($row) {
                    $this->getUpdateRelation();
                    $this->assign('floor_id',$row['floor_id']);
                    $this->assign('mold',$row['mold']);
                    if(I('mold') == 1){
                        $this->assign('num',8);
                    }else{
                        $this->assign('num',2);
                    }
                    $this->assign('row', $row);
                } else {
                    $this->error($Object->getLogicError());
                }
            }
            $this->display('picupdate');
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
            $this->display('update');
        } else {
            if(!I('post.id'))if(I('post.mold') == 1){
                    $mold = M('Floor') -> where(array('type'=>2,'mold'=>I('post.mold'))) -> count();
                    if($mold >= C('MAXTEMPLATE'))$this->error('您所选择该模板达到上限请选择其他模板上传');
            }
            if(!I('post.id'))if(I('post.mold') == 2){
                    $mold = M('Floor') -> where(array('type'=>2,'mold'=>I('post.mold'))) -> count();
                    if($mold >= C('MAXTEMPLATE'))$this->error('您所选择该模板达到上限请选择其他模板上传');
            }
            if(I('post.grade'))if(M('floor_picture')->where(array('floor_id'=>$_POST['floor_id'],'grade'=>$_POST['grade']))->count() >= 1){
                $this->error('当前选择的图片位置已存在!');
            }
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
