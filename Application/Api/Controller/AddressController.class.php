<?php
namespace Api\Controller;
use Think\Controller;

/**
 * Class MemberController
 * @package Api\Controller
 * 收货管理模块
 */
class AddressController extends BaseController
{

    public function _initialize()
    {
        parent::_initialize();
    }
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
    public function addAddress(){
        D('Address','Logic')->addAddress(I('post.'));
    }
    /**
     * @param array $request
     * 收货地址列表
     * 用户ID     m_id
     */
    public function addressList(){
        D('Address','Logic')->addressList(I('post.'));
    }
    /**
     * @param array $request
     * 收货地址详情
     * 用户ID     address_id
     */
    public function addressInfo(){
        D('Address','Logic')->addressInfo(I('post.'));
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
    public function ModifyAddress(){
        D('Address','Logic')->ModifyAddress(I('post.'));
    }
    /**
     * 删除收货地址
     * 收货地址  address_id
     */
    public function deleteAddress(){
        D('Address','Logic')->deleteAddress(I('post.'));
    }

    /**
     * 选择地区三级联动
     */
    public function address(){
        D('Address','Logic')->address(I('post.'));
    }
}