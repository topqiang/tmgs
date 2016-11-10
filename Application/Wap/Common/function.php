<?php
/**
 * 好评中评差评 语句
 * @param $status
 * @return bool|string
 */
function get_evaluate_status_no($status) {
    switch ($status) {
        case 1       : return    '好评';    break;
        case 2       : return    '中评';    break;
        case 3       : return    '差评';    break;
        default      : return    false;    break;
    }
}