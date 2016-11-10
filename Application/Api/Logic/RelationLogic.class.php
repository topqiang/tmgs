<?php
namespace Api\Logic;

/**
 * Class RelationLogic
 * @package Api\Logic
 */
class RelationLogic extends BaseLogic{
    /**
     * 我的邀请
     * 用户ID   m_id
     */
    public function invitation($request = array()){
        if(!$request['m_id']){
            apiResponse('error','用户ID不能为空');
        }

        $member = M('Member') ->where(array('id'=>$request['m_id'])) ->field('id as m_id, balance') ->find();
        if(!$member){
            apiResponse('error','用户信息有误');
        }
        $result_b     = $this ->bitw($request['m_id']);
        $count        = 0;
        $money        = 0;
        $result_money = 0;
        $count1       = 0;
        $money1       = 0;
        $result_money1 = 0;
        $count2       = 0;
        $money2       = 0;
        $result_money2 = 0;
        if($result_b['relation']){
            foreach($result_b['relation'] as $k => $v){
                $result_c = $this ->bitw($request['m_id'],$v['m_id']);
                $count = $count + $result_c['count'];
                $money = $money + $result_c['money'];
                $result_money = $result_money + $result_c['result_money'];
                if($result_c['relation']){
                    foreach($result_c['relation'] as $key => $val){
                        $result_d = $this ->bitw($request['m_id'],$val['m_id']);
                        $count1 = $count1 + $result_d['count'];
                        $money1 = $money1 + $result_d['money'];
                        $result_money1 = $result_money1 + $result_d['result_money'];
                        if($result_d['relation']){
                            foreach($result_d['relation'] as $keys => $value){
                                $result_e = $this ->bitw($request['m_id'],$value['m_id']);
                                $count2 = $count2 + $result_e['count'];
                                $money2 = $money2 + $result_e['money'];
                                $result_money2 = $result_money2 + $result_e['result_money'];
                            }
                        }
                    }
                }
            }
        }
        $result['b_count'] = $result_b['count']?''.$result_b['count']:'0';
        $result['b_money'] = $result_b['money']?$result_b['money'].'':'0.00';
        $result['b_result_money'] = $result_b['result_money']?$result_b['result_money'].'':'0.00';
        $result['c_count'] = $count?$count.'':'0';
        $result['c_money'] = $money?$money.'':'0.00';
        $result['c_result_money'] = $result_money?$result_money.'':'0.00';
        $result['d_count'] = $count1?$count1.'':'0';
        $result['d_money'] = $money1?$money1.'':'0.00';
        $result['d_result_money'] = $result_money1?$result_money1.'':'0.00';
        $result['e_count'] = $count2?$count2.'':'0';
        $result['e_money'] = $money2?$money2.'':'0.00';
        $result['e_result_money'] = $result_money2?$result_money2.'':'0.00';
        $result['count'] = $result['b_count']+$result['c_count']+$result['d_count']+$result['e_count']?$result['b_count']+$result['c_count']+$result['d_count']+$result['e_count'].'':'0';
        $result['result_money'] = $result['b_result_money']+$result['c_result_money']+$result['d_result_money']+$result['e_result_money']?$result['b_result_money']+$result['c_result_money']+$result['d_result_money']+$result['e_result_money'].'':'0.00';
        $result['balance'] = $member['balance']?$member['balance'].'':'0.00';
        $result['month_money'] = $result_b['month_money'] ?$result_b['month_money'].'' :'0.00';
        $result['year_money']  = $result_b['year_money'] ?$result_b['year_money'].'':'0.00';
        apiResponse('success','',$result);
    }
    /**
     * 我的级别
     * 用户ID   m_id
     */
    public function grade($request = array()){
        //用户ID不能为空
        if(!$request['m_id']){
            apiResponse('error','用户ID不能为空');
        }
        //查询等级
        $where['id'] = $request['m_id'];
        $where['status'] = array('neq',9);
        $member = M('Member') ->where($where) ->field('id as m_id, invite_code, grade') ->find();
        //各种计算   然后整合统一
        $result_b = $this ->dean($request['m_id']);
        $count  = 0;
        $count1 = 0;
        $count2 = 0;
//        $count3 = 0;
        foreach($result_b['relation'] as $k =>$v){
            $result_c = $this ->dean($v['m_id']);
            $count    = $count + $result_c['count'];
            foreach($result_c['relation'] as $key => $val){
                $result_d = $this ->dean($val['m_id']);
                $count1   = $count1 + $result_d['count'];
                foreach($result_d['relation'] as $keys => $value){
                    $result_e = $this ->dean($value['m_id']);
                    $count2   = $count2 + $result_e['count'];
//                    foreach($result_e['relation'] as $keyes => $values){
//                        $result_f = $this ->dean($values['m_id']);
//                        $count3   = $count3 + $result_f['count'];
//                    }
                }
            }
        }
        $result['grade']   = $member['grade'];
        $result['invite_code']   = $member['invite_code'];
        $result['b_count'] = $result_b['count'].'';
        $result['c_count'] = $count.'';
        $result['d_count'] = $count1.'';
        $result['e_count'] = $count2.'';
        $max = array();
        $max[] = $result['b_count'];
        $max[] = $result['c_count'];
        $max[] = $result['d_count'];
        $max[] = $result['e_count'];
        $result['max'] = max($max).'';
//        $result['f_count'] = $count3;
        apiResponse('success','',$result);
    }
    /**
     * 我的分享
     * 用户ID    m_id
     */
    public function share($request = array()){
        if(!$request['m_id']){
            apiResponse('error','用户ID不能为空');
        }
        $where['id'] = $request['m_id'];
        $where['status'] = array('neq',9);
        $invite_code = M('Member') ->where($where) ->getField('invite_code');
        if(!$invite_code){
            apiResponse('error','该页面已被删除');
        }
        $result = "http://www.taomim.com/index.php/Api/WapRegister/wapRegister/invite_code/".$invite_code;
        apiResponse('success','',$result);
    }
    /**
     * 我的B级别列表
     */
    public function gradeB($request = array()){
        if(!$request['m_id']){
            apiResponse('error','用户ID不能为空');
        }
        $where['parent_id'] = $request['m_id'];
        $result = M('Relation') ->where($where) ->field('id as relation_id, m_id, create_time') ->select();
        if(!$result){
            $result = array();
            apiResponse('success','',$result);
        }
        foreach($result as $k => $v){
            $result[$k] = M('Member') ->where(array('id'=>$v['m_id'])) ->field('id as m_id, nickname, head_pic') ->find();
            $path = M('File') ->where(array('id'=>$result[$k]['head_pic'])) ->getField('path');
            $result[$k]['head_pic'] = $path?C('API_URL').$path:C('API_URL').'/Uploads/Member/default.png';
            $result[$k]['create_time'] = date('m.d',$v['create_time']);
            unset($where);
            $where['m_id'] = $v['m_id'];
            $where['status'] = array('IN',array(4,5));
            $res = M('Order') ->where($where) ->find();
            if($res){
                $total_price = M('Order') ->where($where) ->getField('SUM(totalprice) as totalprice');
            }else{
                $total_price = '0.00';
            }
            $result[$k]['total_price'] = $total_price?'￥'.$total_price.'':'￥0.00';
            unset($where);
            $where['parent_id'] = $request['m_id'];
            $where['m_id'] = $result[$k]['m_id'];
            $res_data = M('Contribution') ->where($where) ->find();
            if($res_data){
                $return_price = M('Contribution') ->where($where) ->getField('SUN(result_money) as result_money');
            }else{
                $return_price = '0.00';
            }
            $result[$k]['return_price'] = $return_price?'￥'.$return_price.'':'￥0.00';
        }
        apiResponse('success','',$result);
    }
    public function bitw($parent_id = '',$m_id = ''){

        $month_start_time  = strtotime(date('Y-M-01'));
        $month_end_time    = strtotime(date('Y-M-01') .'+1 month -1 day');
        $year_start_time   = strtotime(date('Y-01-01'));
        $year_end_time     = strtotime(date('Y-01-01') .'+1 year -1 day');
        if(!$m_id){
            $result['relation'] = M('Relation')->where(array('parent_id'=>$parent_id)) ->select();
            $result['count']    = M('Relation')->where(array('parent_id'=>$parent_id)) ->count();
            $money         = M('Contribution') ->where(array('parent_id'=>$parent_id)) ->getField('SUM(money) as money');
        }else{
            $result['relation'] = M('Relation') ->where(array('parent_id'=>$m_id)) ->select();
            $result['count']    = M('Relation') ->where(array('parent_id'=>$m_id)) ->count();
            $money          = M('Contribution') ->where(array('parent_id'=>$m_id)) ->getField('SUM(money) as money');
        }

        $result_money = 0;
        foreach($result['relation'] as $k => $v){
            $where['parent_id'] = $parent_id;
            $where['m_id']      = $v['m_id'];
            $res_money = M('Contribution') ->where($where) ->getField('SUM(result_money) as result_money');
            $result_money = $result_money + $res_money;
        }
        unset($where);
        $where['parent_id'] = $parent_id;
        $where['create_time'] = array('elt',$month_end_time);
        $where['create_time'] = array('egt',$month_start_time);
        $month_money  = M('Contribution')->where($where) ->getField('SUM(result_money) as result_money');

        unset($where);
        $where['parent_id'] = $parent_id;
        $where['create_time'] = array('elt',$year_end_time);
        $where['create_time'] = array('egt',$year_start_time);
        $year_money  = M('Contribution')->where($where) ->getField('SUM(result_money) as result_money');
        $result['money'] = $money?$money:0.00;
        $result['result_money'] = $result_money?$result_money:0.00;
        $result['month_money']  = $month_money ?$month_money :0.00;
        $result['year_money']   = $year_money  ?$year_money  :0.00;
        return  $result;
    }
    public function dean($m_id){
        $result['relation'] = M('Relation')->where(array('parent_id'=>$m_id)) ->select();
        $result['count']    = M('Relation')->where(array('parent_id'=>$m_id)) ->count();
        return  $result;
    }
}