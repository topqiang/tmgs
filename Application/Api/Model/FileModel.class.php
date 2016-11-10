<?php

namespace Api\Model;

use Think\Model;

class FileModel extends Model
{
    /*
     * 获得一张图
     */
    public function getFind($id='')
    {
        if(empty($id) || !isset($id)) return '';

        $pic =C('API_URL') . $this -> where(array('id'=>$id)) -> getField('path');

        return $pic;
    }
}