<?php
namespace Api\Logic;

/**
 * Class MemberLogic
 * @package Api\Logic
 */
class AddressLogic extends BaseLogic{
    /**
     * @param array $request
     * 新增收货地址
     * 用户ID     m_id
     * 用户姓名   name
     * 联系方式   tel
     * 省ID       province_id
     * 市ID       city_id
     * 区ID       area_id
     * 详细地址   address
     * 是否默认   is_default
     */
    public function addAddress($request = array()){
        //用户ID不能为空
        if(empty($request['m_id'])){
            apiResponse('error','用户ID不能为空');
        }
        //用户姓名不能为空
        if(empty($request['name'])){
            apiResponse('error','请填写用户姓名');
        }
        //联系方式不能为空
        if(empty($request['tel'])){
            apiResponse('error','请填写联系方式');
        }
        //所在地区不能为空
        if(empty($request['province_id'])){
            apiResponse('error','请填写省ID');
        }
        if(empty($request['city_id'])){
            apiResponse('error','请填写市ID');
        }
        if(empty($request['area_id'])){
            apiResponse('error','请填写区ID');
        }
        //详细地址不能为空
        if(empty($request['address'])){
            apiResponse('error','请填写详细地址');
        }
        //是否设值默认  设置为1  不设置为2
        if($request['is_default']!=1&&$request['is_default']!=2){
            apiResponse('error','默认状态操作有误');
        }
        if($request['is_default'] == 1){
            $data['is_default'] = 1;
        }else{
            $data['is_default'] = 2;
        }
        $data['m_id'] = $request['m_id'];
        $data['name'] = $request['name'];
        $data['tel']  = $request['tel'];
        $data['province_id'] = $request['province_id'];
        $data['city_id'] = $request['city_id'];
        $data['area_id'] = $request['area_id'];
        $data['address'] = $request['address'];
        $data['create_time'] = time();
        $result = M('Address') ->add($data);
        if(!$result){
            apiResponse('error','添加地址失败');
        }

        //如果设置为1  其他所有设置为1的全部改为2
        if($data['is_default'] == 1){
            unset($data);
            $where['m_id'] = $request['m_id'];
            $where['id'] = array('neq',$result);
            $data['is_default'] = 2;
            $data['update_time'] = time();
            $address = M('Address') ->where($where) ->data($data) ->save();
        }
        apiResponse('success','添加地址成功');
    }
    /**
     * @param array $request
     * 收货地址列表
     * 用户ID     m_id
     */
    public function addressList($request = array()){
        //用户ID不能为空
        if(empty($request['m_id'])){
            apiResponse('error','用户ID不能为空');
        }
        //查询地址信息
        $where['m_id'] = $request['m_id'];
        $where['status'] = array('neq',9);
        $address_list = M('Address') ->where($where)
            ->field('id as address_id, name, tel, province_id, city_id, area_id, address, is_default')
            ->order('is_default asc') ->select();
        if(!$address_list){
            $address_list = array();
            apiResponse('success','您还未添加收货地址',$address_list);
        }
        //获取地址的名称
        foreach($address_list as $k =>$v){
            $pro = M('Region') ->where(array('id'=>$v['province_id'])) ->field('id as region_id, region_name') ->find();
            $city = M('Region') ->where(array('id'=>$v['city_id'])) ->field('id as region_id, region_name') ->find();
            $area = M('Region') ->where(array('id'=>$v['area_id'])) ->field('id as region_id, region_name') ->find();
            $address_list[$k]['province_id'] = $pro['region_name'];
            $address_list[$k]['city_id'] = $city['region_name'];
            $address_list[$k]['area_id'] = $area['region_name'];
        }
        apiResponse('success','',$address_list);
    }
    /**
     * @param array $request
     * 收货地址详情
     * 用户ID     address_id
     */
    public function addressInfo($request = array()){
        //地址ID不能为空
        if(empty($request['address_id'])){
            apiResponse('error','地址ID不能为空');
        }
        $where['id'] = $request['address_id'];
        $where['status'] = array('neq',9);
        $address_info = M('Address') ->where($where) ->field('id as address_id, name, tel, province_id, city_id, area_id, address, is_default') ->find();
        if(!$address_info){
            apiResponse('error','该地址信息不存在');
        }
        $address_info['province_id'] = M('Region') ->where(array('id'=>$address_info['province_id'])) ->getField('region_name');
        $address_info['city_id'] = M('Region') ->where(array('id'=>$address_info['city_id'])) ->getField('region_name');
        $address_info['area_id'] = M('Region') ->where(array('id'=>$address_info['area_id'])) ->getField('region_name');
        if($address_info['province_id'] == $address_info['city_id']){
            unset($address_info['province_id']);
        }
        apiResponse('success','',$address_info);
    }
    /**
     * @param array $request
     * 修改收货地址
     * 收货地址ID  address_id
     * 用户ID      m_id
     * 用户姓名    name
     * 联系方式    tel
     * 省ID        province_id
     * 市ID        city_id
     * 区ID        area_id
     * 收货地址    address
     * 默认收货地址  is_default
     */
    public function ModifyAddress($request = array()){
        //地址ID不能为空
        if(!$request['address_id']){
            apiResponse('error','地址ID不能为空');
        }
        //用户ID不能为空
        if(!$request['m_id']){
            apiResponse('error','用户ID不能为空');
        }
        //用户姓名可以为空
        if($request['name']){
            $data['name'] = $request['name'];
        }
        //联系方式可以为空
        if($request['tel']){
            $data['tel'] = $request['tel'];
        }
        //选择省ID可以为空
        if($request['province_id']){
            $data['province_id'] = $request['province_id'];
        }
        //选择市ID可以为空
        if($request['city_id']){
            $data['city_id'] = $request['city_id'];
        }
        //选择区ID可以为空
        if($request['area_id']){
            $data['area_id'] = $request['area_id'];
        }
        //收货地址可以为空
        if($request['address']){
            $data['address'] = $request['address'];
        }
        //地址默认可以为空
        if($request['is_default']){
            if($request['is_default'] != 1&&$request['is_default'] != 2){
                apiResponse('error','默认模式有误');
            }
            if($request['is_default'] == 1){
                $data['is_default'] = 1;
            }else{
                $data['is_default'] = 2;
            }
        }

        //修改收货地址
        $where['id'] = $request['address_id'];
        $where['m_id'] = $request['m_id'];
        $where['status'] = array('neq',9);
        $data['update_time'] = time();
        $address = M('Address') ->where($where) ->data($data) ->save();
        if(!$address){
            apiResponse('error','修改地址失败');
        }
        unset($where);
        unset($data);
        if($request['is_default'] == 1){
            $where['m_id'] = $request['m_id'];
            $where['id'] = array('neq',$request['address_id']);
            $data['is_default'] = 2;
            $data['update_time'] = time();
            $result = M('Address') ->where($where) ->data($data) ->save();
        }
        apiResponse('success','修改地址成功');
    }
    /**
     * 删除收货地址
     * 收货地址  address_id
     */
    public function deleteAddress($request = array()){
        //收货地址不能为空
        if(empty($request['address_id'])){
            apiResponse('error','请选择删除地址');
        }
        $where['id'] = $request['address_id'];
        $data['update_time'] = time();
        $data['status']      = '9';
        $res =M('Address') ->where($where) -> data($data)->save();
        if($res){
            apiResponse('success','删除成功');
        }else{
            apiResponse('error','删除失败');
        }
    }

    /**
     * 选择地区三级联动
     * 父级ID    parent_id
     */
    public function address($request = array()){
        //父级ID不能为空
        if(!$request['parent_id']){
            $request['parent_id'] = 1;
        }
        $where['parent_id'] = $request['parent_id'];
        $result = M('Region') ->where($where) ->field('id as region_id, region_name') ->order('letter asc') ->select();
        if(!$result){
            $result = array();
        }
        apiResponse('success','',$result);
    }
}
