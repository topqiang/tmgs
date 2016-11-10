<?php

namespace Manager\Logic;

/**
 * 商家排序
 * @author zhouwei
 * Class MerchantAdverLogic
 * @package Manager\Logic
 */
class MerchantAdverLogic extends BaseLogic {


    function getList($request = array()) {
        $param['where']['status']   = array('lt',9);        //状态
        $param['order']             = 'type DESC,money DESC,create_time DESC';   //排序
        $param['page_size']         = C('LIST_ROWS');        //页码
        $param['parameter']         = $request;             //拼接参数

        $result = D('MerchantAdvertising')->getList($param);

        return $result;
    }


    function findRow($request = array()) {
        if(!empty($request['id'])) {
            $param['where']['id'] = $request['id'];
        } else {
            $this->setLogicError('参数错误！'); return false;
        }
        $param['where']['status'] = array('lt',9);
        $row = D('MerchantAdvertising')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        return $row;
    }

    /**
     * @param array $request
     * @return bool|mixed
     * 新增 或 修改
     * type 1 首页广告位 2 签到页广告位 3 发现好商品 4 发现好店 5发现号服务器  6云推广 7 诚信商家
     */
    function update($request = array()) {
        //执行前操作
        if(!$this->beforeUpdate($request)) { return false; }
        $model = $request['model'];
        unset($request['model']);
        //获取数据对象
        $data = D($model)->create($request);
        if(!$data) {
            $this->setLogicError(D($model)->getError()); return false;
        }
        //处理数据
        $data = $this->processData($data);
        //判断增加还是修改
        if(empty($data['id'])) {
           $count =  $this->getAdverCount(array('type'=>$data['type']));
            //新增数据
            switch($data['type']){
                case '1':
                    if($count >= 5){
                        $this->setLogicError('首页广告位已满'); return false;
                    }
                    $data['title'] = '首页广告位';
                    $data['content'] = '首页广告位';
                    break;
                case '2':
                    if($count >= 5){
                        $this->setLogicError('签到页广告位已满'); return false;
                    }
                    $data['title'] = '签到页广告位';
                    $data['content'] = '签到页广告位';
                    break;
                case '3':
                    if($count >= 5){
                        $this->setLogicError('发现好商品已满'); return false;
                    }
                    $data['title'] = '发现好商品';
                    $data['content'] = '发现好商品';
                    break;
                case '4';
                    if($count >= 5){
                        $this->setLogicError('发现好店已满'); return false;
                    }
                    $data['title'] = '发现好店';
                    $data['content'] = '发现好店';
                    break;
                case '5':
                    if($count >= 5){
                        $this->setLogicError('发现好服务已满'); return false;
                    }
                    $data['title'] = '发现好服务';
                    $data['content'] = '发现好服务';
                    break;
                case '6':
                    if($count >= 1){
                        $this->setLogicError('云推广已添加'); return false;
                    }
                    $data['title'] = '云推广';
                    $data['content'] = '云推广';
                    break;
                case '7':
                    if($count >= 3){
                        $this->setLogicError('诚信商家已满'); return false;
                    }
                    break;
            }
            $result = D($model)->data($data)->add();
            if(!$result) {
                $this->setLogicError('新增时出错！'); return false;
            }
            //行为日志
            api('Manager/ActionLog/actionLog', array('add',$model,$result,AID));
        } else {
            //创建修改参数
            $where['id'] = $request['id'];
            $result = D($model)->where($where)->data($data)->save();
            if(!$result) {
                $this->setLogicError('您未修改任何值！'); return false;
            }
            //行为日志
            api('Manager/ActionLog/actionLog', array('edit',$model,$data['id'],AID));
        }
        //执行后操作
        if(!$this->afterUpdate($result,$request)) { return false; }

        $this->setLogicSuccess($data['id'] ? '更新成功！' : '新增成功！'); return true;
    }

    public function getAdverCount($where=array())
    {
        $adverModel = M('MerchantAdvertising')->where($where)->count();
        return $adverModel;
    }

}