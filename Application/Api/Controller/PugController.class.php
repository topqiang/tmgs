<?php
namespace Api\Controller;
use Think\Controller;

/**
 * Class PugController
 * @package Api\Controller
 * 足迹模块
 */
class PugController extends BaseController{

    public function _initialize(){
        parent::_initialize();
    }

    /**
     * 足迹列表
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户ID  m_id
     * 分页参数：p
     */
    public function pugList(){
        D('Pug','Logic')->pugList(I('post.'));
    }
    /**
     * 足迹删除
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户ID  m_id
     * 足迹ID  pug_id   可以为空    为空清除全部    不为空清除单条记录
     */
    public function deletePug(){
        D('Pug','Logic')->deletePug(I('post.'));
    }
}