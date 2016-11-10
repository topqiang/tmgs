<?php

namespace Wap\Model;

class WechatTokenModel extends BaseModel
{

	public function getFind($arr = array())
	{
		return $this->where($arr['where'])->field($arr['field'])->find();
	}

}