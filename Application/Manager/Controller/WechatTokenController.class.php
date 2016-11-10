<?php

namespace Manager\Controller;

/**
 * =============API模块=============
 */
class WechatTokenController extends BaseController {

	/**
	 * [ApiInterface API接口]
	 */
	public function index()
	{
		$this->checkRule(self::$rule);
        $Object = D('WechatToken','Logic');
        $row = $Object->findRow(I('get.'));                
        if ($row) {
            $this->getUpdateRelation();
            $this->assign('row', $row);
        } else {
            $this->error($Object->getLogicError());
        }
        $this->display();
	}
}
