<?php

namespace Manager\Controller;

/**
 * [权限添加 AuthRule]
 * @author  zhouwei
 * Class AuthRuleController
 * @package Manager\Controller
 */
class AuthRuleController extends BaseController {

	public function getIndexRelation()
	{
	  	if($_GET['parid']){
            $this->assign('parid',$_GET['parid']);
        }
	}

	public function getAddRelation()
	{
		$list = M('AuthRule')->where(array('parent_id'=>0))->field('id,title')->select();
		if($_GET['parid']){
			 $this->assign('parid',$_GET['parid']);
		}	
		$this->assign('ruleList',$list);
	}

	public function getUpdateRelation()
	{
		$list = M('AuthRule')->where(array('parent_id'=>0))->field('id,title')->select();
		$this->assign('ruleList',$list);
	}

}
