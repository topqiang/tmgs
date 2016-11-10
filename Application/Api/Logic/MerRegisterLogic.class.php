<?php
namespace Api\Logic;

/**
 * Class MerRegisterLogic
 * @package Api\Logic
 */
class MerRegisterLogic extends BaseLogic{
    /**
     * @param array $request
     * 发送验证码
     * 用户手机号   account
     * 验证码类型  meractivity  注册  merbind  绑定  merreset找回  保障金解冻security
     */
    public function sendVerify($request = array()){
        //手机号码不能为空
        if(empty($request['account'])){
            apiResponse('error','手机号码不能为空');
        }
        //手机号码格式不能为空
        if(!preg_match(C('MOBILE'),$request['account'])) {
            apiResponse('error', '手机号码格式错误');
        }
        //判断发送验证码类型
        if(!in_array($request['type'],array('meractivity','merbind','merreset','security'))){
            apiResponse('error','类型有误');
        }

        //判断商家是否注册
        if($request['type'] == 'meractivity'){
            $where['account'] = $request['account'];
            $where['status'] = array('neq', 9);
            $member_info = M('Merchant')->where($where)->find();
            if($member_info){
                apiResponse('error','该手机号已被注册');
            }
        }

        if($request['type'] == 'merbind' && $request['judge_type'] == 1){
            $where['account'] = $request['account'];
            $where['status'] = array('neq', 9);
            $member_info = M('Merchant')->where($where)->find();
            if($member_info){
                apiResponse('error','该手机号已被注册');
            }
        }
        //判断商家是否注册
        if($request['type'] == 'merreset'){
            $where['account'] = $request['account'];
            $where['status'] = array('neq', 9);
            $member_info = M('Merchant')->where($where)->find();
            if(!$member_info){
                apiResponse('error', '该手机号尚未注册，不能执行找回密码功能');
            }
        }
        $result = D('Sms')->sendVerify($request['account'],$request['type']);
        if($result['success']){
            apiResponse('success',$result['success']);
        }else{
            apiResponse('error',$result['error']);
        }
    }

    /**
     * @param array $request
     * 注册
     * 用户手机号    account
     * 账号密码      password
     * 确认密码      sec_password
     * 验证码        verify
     */
    public function register($request = array()){
        //用户账号不能为空
        if(empty($request['account'])){
            apiResponse('error','用户账号不能为空');
        }
        //判断手机格式
        if(!preg_match(C('MOBILE'),$request['account'])) {
            apiResponse('error', '手机号码格式错误');
        }
        //账号密码不能为空
        if(empty($request['password'])){
            apiResponse('error','账号密码不能为空');
        }
        //确认密码不能为空
        if(empty($request['sec_password'])){
            apiResponse('error','请再次输入密码');
        }
        //验证码不能为空
        if(empty($request['verify'])){
            apiResponse('error','请输入验证码');
        }
        $password = strlen($request['password']);
        if($password<6){
            apiResponse('error','密码长度不得小于6位');
        }
        if($request['password'] != $request['sec_password']){
            apiResponse('error','两次密码输入不一致');
        }

        //检测该账号是否已经被注册
        $where['account'] = $request['account'];
        $res = M('Merchant')->where($where)->count();
        if($res>0){
            apiResponse('error','该手机号已被注册');
        }
        //检查验证码输入是否正确
        $this ->_checkVerify($request['account'],$request['verify'],'meractivity');

        //注册环信
        $easemob_reg_info = $this->easemobRegister();
        if($easemob_reg_info['flag'] == 'error'){
            apiResponse('error','注册失败');
        }

        //后台注册
        $data['account']         = $request['account'];
        $data['password']        = md5($request['password']);
        $data['easemob_account'] = $easemob_reg_info['easemob_account'];
        $data['easemob_password']= $easemob_reg_info['easemob_password'];
        $data['create_time']     = time();
        $data['status']          = 4;
        $member_res = M('Merchant') ->data($data) ->add();
        if(!$member_res){
            apiResponse('error','注册失败');
        }
        //返回商家ID  商家账号  商家密码  商家头像  未读消息数量  环信账号  环信密码  商家状态  商家销量  商品数量  收藏人数  等等
        $result_data['merchant_id']     = $member_res;
        $result_data['account']  = $request['account'];
        $result_data['password'] = md5($request['password']);
        $result_data['head_pic'] = C('API_URL').'/Uploads/Merchart/default.png';
        $result_data['un_read_num'] = '0';
        $result_data['easemob_account'] = ''.$easemob_reg_info['easemob_account'];
        $result_data['easemob_password'] = ''.$easemob_reg_info['easemob_password'];
        $result_data['status']   = '4';
        $result_data['sales']    = '0';
        $result_data['goods_num'] = '0';
        $result_data['collect_num'] = '0';
        $result_data['merchant_name'] = '';
        $result_data['integrity_merchant_status'] = '2';
        $result_data['integrity_merchant_cost'] = '0';
        $result_data['integrity_merchant_type'] = array();
        apiResponse('success','注册成功',$result_data);
    }

    /**
     * @param array $request
     * 用户账号登录
     * 账号：account
     * 密码：password
     */
    public function login($request = array()){
        //用户账号不能为空
        if(empty($request['account'])){
            apiResponse('error','账号不能为空');
        }
        //判断手机格式
        if(!preg_match(C('MOBILE'),$request['account'])) {
            apiResponse('error', '手机号码格式错误');
        }
        //用户密码不能为空
        if(empty($request['password'])){
            apiResponse('error','密码不能为空');
        }
        //用户账号不能为空
        $where['account']  = $request['account'];
        $where['password'] = md5($request['password']);
        //查询用户信息
        $member_info = M('Merchant')
            ->where($where)
            ->field('id as merchant_id, account, password, head_pic, easemob_account, easemob_password, status, merchant_name, integrity_merchant_status, integrity_merchant_cost, integrity_merchant_type')
            ->find();

        if(empty($member_info)){
            apiResponse('error','用户账号或密码错误');
        }
        //操作用户头像
        $path = M('File')->where(array('id'=>$member_info['head_pic']))->getField('path');
        $member_info['head_pic'] = $path?C('API_URL').$path:C('API_URL').'/Uploads/Merchant/default.png';

        //获取未读消息的条数
        $un_read_num = $this->getUnReadMessageNum($member_info['m_id']);
        $member_info['un_read_num'] = ''.$un_read_num;
        $goods = M('Goods') ->where(array('merchant_id'=>$member_info['merchant_id'],'status'=>array('neq',9))) ->select();
        $goods_num = M('Goods') ->where(array('merchant_id'=>$member_info['merchant_id'],'status'=>array('neq',9))) ->count();
        $collect_num = M('Collect') ->where(array('type'=>1,'handle_id'=>$member_info['merchant_id'],'status'=>array('neq',9))) ->count();
        $sales = 0;
        foreach($goods as $k => $v){
            $sales = $sales + $v['sales'];
        }
        if($member_info['integrity_merchant_type'] == 0){
            $member_info['integrity_merchant_type'] = array();
        }else{
            $member_info['integrity_merchant_type'] = M('MerchantAdvertising') ->where(array('id'=>$member_info['integrity_merchant_type']))
                -> field('id as m_a_id, title, pic') ->find();
            $path = M('File') ->where(array('id'=>$member_info['integrity_merchant_type']['pic'])) ->getField('path');
            $member_info['integrity_merchant_type']['pic'] = $path?C("API_URL").$path:'';
        }

        $member_info['sales']     = $sales?$sales:'0';
        $member_info['goods_num'] = $goods_num?$goods_num.'':'0';
        $member_info['collect_num'] = $collect_num?$collect_num.'':'0';

        apiResponse('success','登陆成功',$member_info);
    }

    /**
     * @param array $request
     *  忘记密码接口
     * 邮箱账号：account
     * 验证码：verify
     * 新密码：new_password
     * 第二个密码：sec_password
     */
    public function resetPassword($request = array()){
        //手机账号不能为空
        if(empty($request['account'])){
            apiResponse('error','手机账号不能为空');
        }
        //判断手机格式
        if(!preg_match(C('MOBILE'),$request['account'])) {
            apiResponse('error', '手机号码格式错误');
        }
        //验证码不能为空
        if(empty($request['verify'])){
            apiResponse('error','验证码不能为空');
        }
        //新密码不能为空
        if(empty($request['new_password'])){
            apiResponse('error','请输入密码');
        }
        //再次输入密码
        if(empty($request['sec_password'])){
            apiResponse('error','请再次输入密码');
        }
        //密码不能小于6位
        $new_password = strlen($request['new_password']);
        if($new_password<6){
            apiResponse('error','密码长度不能小于6位');
        }
        if($request['new_password'] != $request['sec_password']){
            apiResponse('error','两次密码输入不一致');
        }

        //检查账号是否正确
        $res_count = M('Merchant')->where(array('account'=>$request['account'],'status'=>array('neq',9)))->count();
        if($res_count <= 0){
            apiResponse('error','用户信息不存在');
        }

        //检测验证码
        $this->_checkVerify($request['account'],$request['verify'],'merreset');

        $where['account'] = $request['account'];
        $data['password']    = md5($request['new_password']);
        $data['update_time'] = time();
        $res = M('Merchant') -> where($where) ->data($data)->save();
        if($res){
            apiResponse('success','找回密码成功');
        }else{
            apiResponse('error',' 找回密码失败');
        }
    }

    /**
     * @param $verify
     * @param $type
     * 检查验证码是否正确
     */
    public function _checkVerify($account,$verify,$type){
        $where['way']  = $account;
        $where['vc']   = $verify;
        $where['type'] = $type;
        //检查验证码是否错误
        $sms_info = M('Sms')->where($where)->find();
        if(empty($sms_info)){
            apiResponse('error','验证码错误');
        }

        //检查验证码是否过期
        if($sms_info['expire_time']<time()){
            apiResponse('error','验证码已过期');
        }
    }

    /**
     * @param array $request
     * 填写商家信息
     * 商家ID      merchant_id
     * 商家名称    merchant_name
     * 真实名字    really_name
     * 联系电话    contact_mobile
     * 商家性别    sex
     * 商家邮箱    contact_email
     * 商家地址    address
     * 商家身份证  ID_card
     * 商家省地区  province_id
     * 商家市地区  city_id
     * 商家区地区  area_id
     * 上传头像    head_pic
     * 上传身份证前面照  hold_card_pic
     * 上传身份证背面照  back_card_pic
     * 上传手持身份证    hand_idcard_pic
     */
    public function merchantInformation($request = array()){
        //商家ID不能为空
        if(!$request['merchant_id']){
            apiResponse('error','商家ID不能为空');
        }
        //商家名称不能为空
        if(!$request['merchant_name']){
            apiResponse('error','商家名称不能为空');
        }
        //真实姓名不能为空
        if(!$request['really_name']){
            apiResponse('error','真实姓名不能为空');
        }
        //联系电话不能为空
        if(!$request['contact_mobile']){
            apiResponse('error','联系电话不能为空');
        }
        //商家性别不能为空
        if(!$request['sex']){
            apiResponse('error','商家性别不能为空');
        }
        //商家邮箱不能为空
        if(!$request['contact_email']){
            apiResponse('error','商家邮箱不能为空');
        }
        //商家地址不能为空
        if(!$request['address']){
            apiResponse('error','商家地址不能为空');
        }
        //商家身份证不能为空
        if(!$request['id_card']){
            apiResponse('error','商家身份证不能为空');
        }
        //商家所在省不能为空
        if(!$request['province_id']){
            apiResponse('error','商家所在省不能为空');
        }
        //商家所在市不能为空
        if(!$request['city_id']){
            apiResponse('error','商家所在市不能为空');
        }
        //商家所在区不能为空
        if(!$request['area_id']){
            apiResponse('error','商家所在区不能为空');
        }
        if($_FILES['head_pic']['name'] || $_FILES['hold_card_pic']['name'] || $_FILES['back_card_pic']['name'] || $_FILES['hand_idcard_pic']['name'] ){
            $res = api('UploadPic/upload', array(array('save_path' => "Merchant")));
            foreach ($res as $k => $value) {
                if($value['key'] == 'head_pic'){
                    $data['head_pic'] = $value['id'];
                }
                if($value['key'] == 'hold_card_pic'){
                    $data['hold_card_pic'] = $value['id'];
                }
                if($value['key'] == 'back_card_pic'){
                    $data['back_card_pic'] = $value['id'];
                }
                if($value['key'] == 'hand_idcard_pic'){
                    $data['hand_idcard_pic'] = $value['id'];
                }
            }
        }
        //商家推荐人不能为空
        if($request['mem_account']){
            $res = M('Member') ->where(array('account'=>$request['mem_account'],'status'=>array('neq',9))) ->find();
            if(!$res){
                apiResponse('error','推荐人信息不存在');
            }
        }
        $where['id'] = $request['merchant_id'];
        $data['merchant_name'] = $request['merchant_name'];
        $data['really_name']   = $request['really_name'];
        $data['contact_mobile'] = $request['contact_mobile'];
        $data['sex']           = $request['sex'];
        $data['contact_email'] = $request['contact_email'];
        $data['address']       = $request['address'];
        $data['id_card']       = $request['id_card'];
        $data['province']      = $request['province_id'];
        $data['city']          = $request['city_id'];
        $data['district']      = $request['area_id'];
        $data['status']        = 2;
        $data['update_time']   = time();
        $result = M('Merchant') ->where($where) ->data($data) ->save();
        if(!$result){
            apiResponse('error','填写资料失败');
        }

        unset($data);
        if($res){
            $data['parent_id'] = $res['id'];
            $data['m_id']      = $result;
            $data['type']      = 2;
            $result_relation   = M('Relation') ->add($data);
        }

        $result_data['status'] = '2';
        apiResponse('success','填写资料成功',$result_data);
    }

    /**
     * @param array $request
     * 修改商家信息
     * 商家ID      merchant_id
     * 商家名称    merchant_name
     * 真实名字    really_name
     * 联系电话    contact_mobile
     * 商家性别    sex
     * 商家邮箱    contact_email
     * 商家地址    address
     * 商家身份证  ID_card
     * 商家省地区  province_id
     * 商家市地区  city_id
     * 商家区地区  area_id
     * 上传头像    head_pic
     * 上传身份证前面照  hold_card_pic
     * 上传身份证背面照  back_card_pic
     * 上传手持身份证    hand_idcard_pic
     */
    public function modifyMerchantInformation($request = array()){
        //商家ID不能为空
        if(!$request['merchant_id']){
            apiResponse('error','商家ID不能为空');
        }
        //商家名称可以为空
        if($request['merchant_name']){
            $data['merchant_name'] = $request['merchant_name'];
        }
        //真是姓名可以为空
        if($request['really_name']){
            $data['really_name'] = $request['really_name'];
        }
        //联系电话可以为空
        if($request['contact_mobile']){
            $data['contact_mobile'] = $request['contact_mobile'];
        }
        //商家性别可以为空
        if($request['sex']){
            $data['sex'] = $request['sex'];
        }
        //商家邮箱可以为空
        if($request['contact_email']){
            $data['contact_email'] = $request['contact_email'];
        }
        //商家地址可以为空
        if($request['address']){
            $data['address'] = $request['address'];
        }
        //商家身份证可以为空
        if($request['id_card']){
            $data['id_card'] = $request['id_card'];
        }
        //商家省ID可以为空
        if($request['province_id']){
            $data['province'] = $request['province_id'];
        }
        //商家市ID可以为空
        if($request['city_id']){
            $data['city'] = $request['city_id'];
        }
        //商家区ID可以为空
        if($request['area_id']){
            $data['district'] = $request['area_id'];
        }

        if($_FILES['head_pic']['name'] || $_FILES['hold_card_pic']['name'] || $_FILES['back_card_pic']['name'] || $_FILES['hand_idcard_pic']['name'] ){
            $res = api('UploadPic/upload', array(array('save_path' => "Merchant")));
            foreach ($res as $k => $value) {
                if($value['key'] == 'head_pic'){
                    $data['head_pic'] = $value['id'];
                }
                if($value['key'] == 'hold_card_pic'){
                    $data['hold_card_pic'] = $value['id'];
                }
                if($value['key'] == 'back_card_pic'){
                    $data['back_card_pic'] = $value['id'];
                }
                if($value['key'] == 'hand_idcard_pic'){
                    $data['hand_idcard_pic'] = $value['id'];
                }
            }
        }

        $where['id'] = $request['merchant_id'];
        $data['update_time']   = time();
        $result = M('Merchant') ->where($where) ->data($data) ->save();
        if(!$result){
            apiResponse('error','修改资料失败');
        }
        apiResponse('success','修改资料成功');
    }
}