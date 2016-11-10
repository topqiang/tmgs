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
     * 用户ID  m_id
     * 分页参数：p
     */
    public function pugList($request = array()){
        //用户ID不能为空
        if(empty($request['m_id'])){
            apiResponse('error','用户ID不能为空');
        }
        //分页参数不能为空
        if(empty($request['p'])){
            apiResponse('error','分页参数不能为空');
        }
        //统计未读消息数量
        $un_read_num = $this->getUnReadMessageNum($request['m_id']);
        $result['un_read_num'] = '' . $un_read_num;
        $where['u_id'] = $request['m_id'];
        $res = M('Pug') ->where($where) ->field('id as pug_id,good_id') ->order('create_time desc') -> page($request['p'] .',15') ->select();
        //获取商品详情  图片  名称  价格
        $index = 0;
        foreach ($res as $k =>$v){
            unset($where);
            $where['id']             = $v['good_id'];
            $where['is_shelves']     = 1;
            $result['goods'][$index] = M('Goods') ->where($where) ->field('id as goods_id, cn_goods_name as goods_name, is_shelves, goods_pic') ->find();

            if(empty($result['goods'][$index])){
                unset($result['goods'][$index]);
                continue;
            }

            $goods_pic = explode(',',$result['goods'][$index]['goods_pic']);
            $path = M('File') -> where(array('id'=>$goods_pic[0])) ->getField('path');
            $result['goods'][$index]['goods_pic'] = $path?C('API_URL').$path:'';
            $result['goods'][$index]['pug_id'] = $v['pug_id'];
            unset($where);
            $where['goods_id'] = $result['goods'][$index]['goods_id'];
            $where['status'] = array('neq',9);
            $price = M('GoodsProduct') ->where($where) ->field('id as product_id, cn_price as price') ->order('cn_price asc') ->find();

            $result['goods'][$index]['price'] = $price['price']?$price['price']:'0.00';

            $index += 1;
        }
        if(!$result['goods']){
            $result['goods'] = array();
        }
        apiResponse('success','',$result);
    }

    /**
     * 足迹删除
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户ID  m_id
     * 足迹ID  pug_id   可以为空    为空清除全部    不为空清除单条记录
     */
    public function deletePug($request = array()){
        //用户id不能为空
        if(empty($request['m_id'])){
            apiResponse('success','用户ID不能为空');
        }
        //足迹id可以为空   为空时清空列表   不为空时清空单条记录
        if(empty($request['pug_id'])){
            $where['u_id'] = $request['m_id'];
            $result = M('Pug') ->where($where) ->delete();
            if($result){
                apiResponse('success','清除足迹成功');
            }
            apiResponse('error','清除足迹失败');
        }
        $where['u_id'] = $request['m_id'];
        $where['id'] = $request['pug_id'];
        $result = M('Pug') ->where($where) -> delete();
        if($result){
            apiResponse('success','删除足迹成功');
        }
        apiResponse('error','删除足迹失败');
    }


    /**
     * @param $list
     * @param string $pk
     * @param string $pid
     * @param string $child
     * @param int $root
     * @return array
     * 把返回的数据集转换成Tree
     */
    function list_to_tree($list, $root = 0, $pk = 'id', $pid = 'parent_id', $child = '_child') {
// 创建Tree
        $tree = array();
        if(is_array($list)) {
// 创建基于主键的数组引用
            $refer = array();
            foreach ($list as $key => $data) {
//以主键为键值的数组
                $refer[$data[$pk]] =& $list[$key];
            }
            foreach ($list as $key => $data) {
// 判断是否存在parent
                $parentId =  $data[$pid];
//当前分类的父级分类是否等于父根节点
                if ($root == $parentId) {
                    $tree[] =& $list[$key];
                } else {
                    if (isset($refer[$parentId])) {
//当前分类的伤及分类 引用
                        $parent =& $refer[$parentId];
//存入上级分类的子分类中
                        $parent[$child][] =& $list[$key];
                    }
                }
            }
        }
        return $tree;
    }
}
