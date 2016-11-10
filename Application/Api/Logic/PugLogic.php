<?php
namespace Api\Logic;

/**
 * Class PugLogic
 * @package Api\Logic
 */
class PugLogic extends BaseLogic{
    /**
     * 足迹列表
     * 传递参数的方式：post
     * 需要传递的参数：
     * 语言版本：language：cn 中文  ue英文
     * 用户ID  m_id
     * 商家ID  merchant_id
     */
    public function pugList($request = array()){
        //判断语言版本
        $this -> checkLanguage($request['language']);
        if(empty($request['m_id'])){
            $message = $request['language']=='cn'?'用户信息有误,请重新登录':'The user information is incorrect, please re login';
            apiResponse('error',$message);
        }
        if(empty($request['p'])){
            $message = $request['language']=='cn'?'分页信息不能为空':'Paging information cannot be empty';
            apiResponse('error',$message);
        }
        //统计未读消息数量
        $data['m_id'] = $request['m_id'];
        $un_read_num = $this->getUnReadMessageNum($data['m_id']);
        $data['un_read_num'] = '' . $un_read_num;
        $where['u_id'] = $request['m_id'];
        $res = M('Pug') ->where($where) ->field('id as pug_id,goods_id') ->order('create_time desc') ->page($request['p'].',15') ->select();
        $index = 0;
        foreach ($res as $k =>$v){
            unset($where);
            $where['id'] = $v['goods_id'];
            $where['is_shelves'] = 1;
            if($request['language'] == 'cn'){
                $result[$index] = M('Goods') ->where($where) ->field('id as goods_id, cn_goods_name as goods_name, goods_pic, cn_price as price') ->find();
            }else{
                $result[$index] = M('Goods') ->where($where) ->field('id as goods_id, ue_goods_name as goods_name, goods_pic, ue_price as price') ->find();
            }
            if(empty($result)){
                continue;
            }
            $goods_pic = explode(',',$result[$index]['goods_pic']);
            $path = M('File') -> where(array('id'=>$goods_pic[0])) ->getField('path');
            $result[$index]['goods_pic'] = $path?C('API_URL').$path:'';
            unset($where);
            $where['goods_id'] = $result[$index]['goods_id'];
            $where['status'] = array('neq',9);
            if($request['language'] == 'cn'){
                $price = M('GoodsProduct') ->where($where) ->field('id as product_id, cn_price as price') ->order('cn_price asc') ->find();
            }else{
                $price = M('GoodsProduct') ->where($where) ->field('id as product_id, ue_price as price') ->order('ue_price asc') ->find();
            }
            if($price){
                $result[$index]['price'] = $price['price'];
            }
            $index += 1;
        }
        if(!empty($result)){
            $result['m_id'] = $data['m_id'];
            $result['un_read_num'] = $data['un_read_num'];
            $message = $request['language']=='cn'?'成功获取我的足迹':'Succeeded in getting my footprints.';
            apiResponse('success',$message,$result);
        }
        $message = $request['language']=='cn'?'获取我的足迹失败':'Failed to get my footprints.';
        apiResponse('error',$message);
    }
}