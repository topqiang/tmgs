<?php

namespace Manager\Controller;

/**
 * 菜单
 */
class WechatMenuController extends BaseController {
	/**
	 * [createMenu 创建菜单]
	 */
	public function index()
	{
		$this->checkRule(self::$rule);
		$Object = D('WechatMenu','Logic');
		$result = $Object->getList(I('request.'));
		if($result) {
			$tok = M('WechatAtoken')->limit(1)->field('app_id,app_secret')->find();
			$this->assign('tok',$tok);
			$this->assign('page', $result['page']);
			$this->assign('list', $result['list']);
		} else {
			$this->error($Object->getLogicError());
		}
		// 记录当前列表页的cookie
		Cookie('__forward__',$_SERVER['REQUEST_URI']);
		$this->getIndexRelation();
		$this->display();
	}

	/**
	 * [updateMenu 修改]
	 */
	public function update()
	{
		$this->checkRule(self::$rule);
		if(!IS_POST) {
			if ($_GET['id']) {
				$Object = D('WechatMenu','Logic');
				$row = $Object->findRow(I('get.'));
				$menuList = M('WechatMenu')->where(array('parent_id'=>0))->field('id,name')->select();
				if ($row) {
					$this->getUpdateRelation();
					$this->assign('menuList',$menuList);
					$this->assign('row', $row);
				} else {
					$this->error($Object->getLogicError());
				}
			}
			$this->display('update');
		} else {
			
			$Object = D('WechatMenu','Logic');
			$result = $Object->update(I('post.'));
			if($result) {
				$this->success($Object->getLogicSuccess(), Cookie('__forward__'));
			} else {
				$this->error($Object->getLogicError());
			}
		}
	}
	/**
	 * [addMenu 添加]
	 */
	public function add()
	{
		  $this->checkRule(self::$rule);
        if(!IS_POST) {
        	$menuList = M('WechatMenu')->where(array('parent_id'=>0))->field('id,name')->select();
            $this->getAddRelation();
            $this->assign('menuList',$menuList);
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

	/**
	 * [assToken 获取token]
	 * @author zhouwei
	 * @return [type] [description]
	 */
	public function assToken()
	{
		$model = M('WechatAtoken');
		$tok = $model->limit(1)->field('app_id,app_secret,id,expire_time,access_token')->find();
		if($tok['expire_time'] > time()){
			$aToken = $tok['access_token'];
		}else{
			$ch = curl_init("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$tok['app_id']."&secret=".$tok['app_secret']);
			curl_setopt($ch, CURLOPT_BINARYTRANSFER, true) ; // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回 
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1) ; // 获取数据返回
			$access_token = curl_exec($ch);
			$at = json_decode($access_token,true);
			if(!isset($at['errcode'])){
				$model -> where(array('id'=>$tok['id'])) -> data(array('access_token'=>$at['access_token'],'expire_time'=>time() + 1*$at['expires_in'])) -> save();
				$aToken = $at['access_token'];
			}else{
				$this->ajaxReturn('4');return false; // 获取ACCESS_TOKEN失败，检查您的AppId或AppSecret是否正确
			}
			curl_close($ch);
		}
		return $aToken;
		
	}

	public function upMenu()
	{
		if(M('WechatMenu') -> count() == 0){
			$this->ajaxReturn('1');return false; // 您还没有创建任何菜单
		}else{
			$menu = $this->getData();
			$aToken = $this->assToken();
			$result = $this -> createMenuApi($menu,$aToken);
			$arrResult = json_decode($result,true);
			if($arrResult['errcode'] == '0'){
				$this->ajaxReturn('2');return false; // 创建菜单成功，需重新关注或隔天才能看到效果
			}elseif($arrResult['errcode'] == '40018'){
				$this->ajaxReturn('3');return false; // 创建菜单失败，稍后请重试

			}
		}
	}


	/**
	 * [upInfo 修改微信开发者ID信息]
	 * @author zhouwei
	 * @return [type] [description]
	 */
	public function upInfo()
	{
		$Model = M('WechatAtoken');
		$find = $Model->limit(1)->order('create_time DESC')->find();
		if(!$find){
			$data = $_POST;
			$data['create_time'] = time();
			$data['access_token'] = '';
			$data['expire_time'] = 0;
			$add = $Model -> data($data) -> add();
			if($add){
				$this->ajaxReturn('1');
			}
		}else{
			$data = $_POST;
			$data['create_time'] = time();
			$data['access_token'] = '';
			$data['expire_time'] = 0;
			$save = $Model ->where(array('id'=>$find['id']))  -> data($data) -> save();
			if($save){
				$this->ajaxReturn('2');
			}
		}
	}


	 /**
     * 格式化 菜单数据
     */
    public function getData(){
    	$model = M('WechatMenu');
        $data = '{"button" : [';
        $root_menu_list = $model -> where(array('parent_id'=>0)) -> order('sort DESC') -> select();
        if($root_menu_list){
            foreach($root_menu_list as $key1=>$root){
            	$son_menu_list = $model -> where(array('parent_id'=>$root['id'])) -> order('sort DESC') -> select();
                if($son_menu_list){//二级分类
                    $data .= '{"name" : "'.$root['name'].'","sub_button" : [';
                    foreach($son_menu_list as $key2=>$son){
                        if(!empty($son['url'])){
                            $data .= '{
                                          "type" : "view",
                                          "name" : "'.$son['name'].'",
                                          "url"  : "'.$son['url'].'"
                                       }';
                        }if(empty($son['url']) && !empty($son['keywords'])){
                            $data .= '{
                                          "type" : "click",
                                          "name" : "'.$son['name'].'",
                                          "key"  : "'.$son['keywords'].'"
                                       }';
                        }
                        if(count($son_menu_list)-$key2 != 1){
                            $data .= ",";
                        }
                    }
                    $data .= "]}";
                    if(count($root_menu_list)-$key1 != 1){
                        $data .= ",";
                    }
                }else{
                    if(!empty($root['url'])){
                        $data .= '{
                                      "type" : "view",
                                      "name" : "'.$root['name'].'",
                                      "url"  : "'.$root['url'].'"
                                   }';
                    }if(empty($root['url']) && !empty($root['keywords'])){
                        $data .= '{
                                      "type" : "click",
                                      "name" : "'.$root['name'].'",
                                      "key"  : "'.$root['keywords'].'"
                                   }';
                    }
                    if(count($root_menu_list)-$key1 != 1){
                        $data .= ",";
                    }
                }
            }
            return $data."]}";
        }else{
            return  '';
        }
    }

      /**
     * 创建菜单方法
     */
    public function createMenuApi($data,$access_token){
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$access_token);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $tmpInfo = curl_exec($ch);
        if (curl_errno($ch)) {
            return curl_error($ch);
        }
        curl_close($ch);
        return $tmpInfo;
    }
}