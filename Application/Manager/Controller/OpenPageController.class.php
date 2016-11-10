<?php
namespace Manager\Controller;


class OpenPageController extends BaseController {
	function update() {
		$this->checkRule(self::$rule);
		if(!IS_POST) {
			$Object = D(CONTROLLER_NAME,'Logic');
			$row = $Object->findRow(I('get.'));
			if ($row) {
				$this->getUpdateRelation($row);
				$this->assign('row', $row);
			} else {
				$this->error($Object->getLogicError());
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
