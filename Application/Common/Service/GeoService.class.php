<?php
namespace Common\Service;

/**
 * Class GeoService
 * @package Common\Service
 * 高德地图
 */
class GeoService extends BaseService{

    private $key = '2b5862366010689fd02990db1900e487';

    /**
     * 转换坐标 单点
     */
    public function conCoord($lat,$lng){
        $str = 'http://restapi.amap.com/v3/assistant/coordinate/convert?locations='.$lng.','.$lat.'&coordsys=gps&output=JSON&key='.$this->key;
        $coord = json_decode($this -> httpGet($str),true);
        if($coord['status'] == 1){
            $var = explode(',',$coord['locations']);
            $return['lng'] = $var[0];
            $return['lat'] = $var[1];
            return $return;
        }
    }

    /**
     * 坐标反查
     */
    public function reGeo($lat,$lng)
    {
        $output = 'json';  // 返回格式 json xml
        $location= "$lng,$lat"; // 坐标
        $key = $this->key; // key
        $radius = 1000; // 半径 取值范围：0~3000,单位：米
        $extensions = 'all'; //返回结果控制
        $batch = 'false'; // true : 批量 false : 单点
        $str = "http://restapi.amap.com/v3/geocode/regeo?output=$output&location=$location&key=$key&radius=$radius&extensions=$extensions&batch=$batch";
        $result = $this -> httpGet($str);
        $coord = json_decode($result,true);
        return $coord;
    }
    /**
     * http传输
     */
    public function httpGet($url){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_URL, $url);

        $res = curl_exec($curl);
        curl_close($curl);
        return $res ;
    }
}

?>