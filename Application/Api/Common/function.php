<?php
/**
 * API返回信息格式函数
 * @param string $flag
 * @param string $message
 * @param string $data
 */
function apiResponse($flag = 'error', $message = '',$data = array()){
    $result = array('flag'=>$flag,'message'=>$message,'data'=>$data);
    print json_encode($result);exit;
}

/**
 * @param $lat1
 * @param $lng1
 * @param $lat2
 * @param $lng2
 * @param $precision
 * @return float
 * 根据经纬度计算距离
 */
function getDistance($lat1, $lng1, $lat2, $lng2,$precision) {
    $earthRadius = 6367000; //approximate radius of earth in meters

    /*
    Convert these degrees to radians
    to work with the formula
    */

    $lat1 = ($lat1 * pi() ) / 180;
    $lng1 = ($lng1 * pi() ) / 180;

    $lat2 = ($lat2 * pi() ) / 180;
    $lng2 = ($lng2 * pi() ) / 180;

    $calcLongitude = $lng2 - $lng1;
    $calcLatitude = $lat2 - $lat1;
    $stepOne = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2);
    $stepTwo = 2 * asin(min(1, sqrt($stepOne)));
    $calculatedDistance = $earthRadius * $stepTwo;

    return round($calculatedDistance/1000,$precision);
}


/**
 * @param string $flag 'char'标记 获取字符串   'num' 标记获取数字
 * @param int $num 验证标识的个数
 * @return string
 */
function getVc($flag = '', $num = 0){
    /**获取验证标识**/
    $arr = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z',1,2,3,4,5,6,7,8,9,0);
    $vc = '';
    //字符串
    if($flag == 'char'){
        for($i = 0; $i < $num; $i++){
            $index = rand(0,61);
            $vc .= $arr[$index];
        }
        $vc .= time();
    }elseif($flag == 'num'){  //数字
        for($i = 0; $i < $num; $i++){
            $index = rand(52,61);
            $vc .= $arr[$index];
        }
    }
    return $vc;
}


function montageTime($time = '')
{
    $time =  trim($time,',');
    $arrTime = explode(',',$time);
    $time = '';
    $tmpTime = '';
    foreach($arrTime as $k => $v){
        if(strtotime($v) + 86400 == strtotime($arrTime[$k+1])){
            if(empty($tmpTime))$tmpTime = date('Y.m.d',strtotime($v));
        }else{
            $time .= trim($tmpTime .'-'. date('Y.m.d',strtotime($v)) .' ','-');
            $tmpTime = '';
        }
    }
    $time = trim(trim($time,'-'));
    return $time;
}

/**
 * 退货流程  1  发起退货并等待同意退货  2  等待商家确认收货地址  3  等待买家配送  4  等待卖家收货  5  退货完成   6  完成退货 9拒绝
 */
function orderOutStatus($status = ''){
    switch ($status) {
        case 1       : return    '等待同意退货';  break;
        case 2       : return    '等待确认收货地址';  break;
        case 3       : return    '等待买家配送';  break;
        case 4       : return    '等待卖家收货';  break;
        case 5       : return    '退货完成';  break;
        case 6       : return    '完成退货';  break;
        case 9       : return    '拒绝';  break;
        default      : return    '';        break;
    }
}
