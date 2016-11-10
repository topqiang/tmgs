<?php
namespace Api\Controller;
use Think\Controller;

/**
 * Class PugController
 * @package Api\Controller
 * 关系模块
 */
class RelationController extends BaseController{

    public function _initialize(){
        parent::_initialize();
    }
    /**
     * 我的邀请
     */
    public function invitation(){
        D('Relation','Logic') ->invitation(I('post.'));
    }
    /**
     * 我的级别
     */
    public function grade(){
        D('Relation','Logic') ->grade(I('post.'));
    }
    /**
     * 我的分享
     */
    public function share(){
        D('Relation','Logic') ->share(I('post.'));
    }
    /**
     * 我的B级别列表
     */
    public function gradeB(){
        D('Relation','Logic') ->gradeB(I('post.'));
    }
}