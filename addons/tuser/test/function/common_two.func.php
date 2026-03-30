<?php

/**
 * ajax返回格式化
 * @param type $error_code
 * @param type $error_msg
 */
function ajaxReturnFormat($error_code, $error_msg, $error_data = '') {
    $result = array(
        'error_code' => $error_code,
        'error_msg' => $error_msg,
        'data' => $error_data,
    );
    echo json_encode($result);
    exit;
}

/**
 * model返回格式化
 * @param type $error_code
 * @param type $error_msg
 */
function modelReturnFormat($error_code, $error_msg, $error_data = '') {
    $result = array(
        'error_code' => $error_code,
        'error_msg' => $error_msg,
        'data' => $error_data,
    );
    return $result;
}

?>
