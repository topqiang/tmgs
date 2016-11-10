<?php
namespace Common\Api;
use Org\Util\Cutf8Py;
/**
 * Created by PhpStorm.
 * User: xuexiaofeng
 * Date: 2015-10-22 0022
 * Time: 10:18
 */
class RegionApi{

    /**
     * 按城市首字母获取城市列表
     * @return mixed
     */
    public static function getCityList(){
        $pinyin = new Cutf8Py();
        //获取城市列表
        $city_list = M('Region')->where(array('region_type'=>2,'is_show'=>1))->field('id,region_name')->order('sort DESC')->select();
        foreach($city_list as $k=>$v) {
            //获取城市拼音并且截取首字母
            $city_list[$k]['uppercase'] = strtoupper(substr($pinyin->encode($v['region_name']), 0, 1));
            if(self::formatPy($v['region_name'])){
                $city_list[$k]['uppercase'] = self::formatPy($v['region_name']);
            }
        }
        foreach($city_list as $k=>$v){
            $mew_city_list[$v['uppercase']][] = $v;
        }
        //按字母顺序重新排序
        ksort($mew_city_list);
        return $mew_city_list;
    }

    /**
     * 获取热门城市列表
     * @return mixed
     */
    public static function getHotCity(){
        $city_list = M('Region')->where(array('region_type'=>2,'is_show'=>1,'is_hot'=>1))->field('id,region_name')->order('sort DESC')->select();
        return $city_list;
    }

    /**
     * 城市拼音多音字处理
     * @param $value
     * @return string
     */
    private static function formatPy($value){
        $uppercase = '';
        switch($value){
            case '重庆' : $uppercase = 'C'; break;
            case '亳州' : $uppercase = 'B'; break;
            case '泸州' : $uppercase = 'L'; break;
            case '衢州' : $uppercase = 'Q'; break;
        }
        return $uppercase;
    }
}
