<?php

namespace Home\Controller;
use Think\Controller;
/**
 * 首页控制器
 * Class IndexController
 * User zhouwei
 * @package Home\Controller
 */
class IndexController extends Controller
{
	public function index()
	{
		$this->show('<a href="'.U('/Wap/Wechat/checkSignature').'">1</a>');
		// $this->redirect('/Manager');
	}

	/**
	 *  遍历商品属性中没有商家ID的商品ID
	 */
	public function addGoodsId()
	{
		$model = M('goods_product')->where(array('merchant_id'=>array('eq',0)))->select();
		foreach($model as $k=>$v){
			$data['merchant_id'] = M('Goods')->where(array('id'=>$v['goods_id']))->getField('merchant_id');
			M('goods_product')->where(array('id'=>$v['id']))->data($data)->save();
		}
	}

	/**
	 *  遍历商品 查看每个商品是否删除 如果删除
	 *  则 吧属性status 变为 9
	 */
	public function deleteGoodsStatus()
	{
		$model_goods = M('goods')->where(array('status'=>array('eq',9)))->getField('id',true); // 遍历已经删除的商品
		$model_goods_product = M('goods_product') -> where(array('goods_id'=>array('in',$model_goods)))->getField('id',true); // 获得所有已删除商品的商品属性
		 M('goods_product')->where(array('id'=>array('in',$model_goods_product)))->data(array('status'=>9))->save();
	}


	public function OrderAdd()
	{
		$model = M('order') -> select();
		for($i=0;$i<count($model);$i++){
			unset($model[$i+1]['id']);
			M('order')->data($model[$i+1])->add();
		}
	}

	/**
	 * 批量发送系统消息
	 */
	public function batchMessage()
	{
		$message_ready = M('message_ready')->where(array('status' => 0))->field('id,cn_title,cn_content,m_type')->select();
		if (count($message_ready) > 0) {
			foreach ($message_ready as $msgk => $msgv) {
				$member_ready = M('member')->where(array('status' => array('eq','1')))->getField('id', true);
				foreach ($member_ready as $mk => $mv) {
					M('message')->data(array(
						'm_id' => $mv,
						'type' => '1',
						'cn_title' => $msgv['cn_title'],
						'cn_content' => $msgv['cn_content'],
						'create_time' => time(),
						'status' => '0',
						'm_type' => $msgv['m_type'],
					))->add();
				}
				M('message_ready')->where(array('id' => $msgv['id']))->data(array('status' => 1))->save();
			}
		}
	}

}