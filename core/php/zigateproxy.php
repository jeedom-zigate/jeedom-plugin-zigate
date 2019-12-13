<?php 
/**
 * Copyright (c) 2018 Jeedom-ZiGate contributors
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

$method = $_SERVER['REQUEST_METHOD'];


if ($_GET && $_GET['url']) {
    $headers = getallheaders();
    $headers_str = [];
    #$url = $_GET['url'];
    $url = 'http://localhost'.$_GET['url'];
    
    foreach ($headers as $key => $value){
        if($key == 'Host')
            continue;
        $headers_str[]=$key.":".$value;
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_PORT, 9998);

    curl_setopt($ch,CURLOPT_URL, $url);
    if ($method !== 'GET') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    }

    if ($method == "PUT" || $method == "PATCH" || ($method == "POST" && empty($_FILES))) {
        $data_str = file_get_contents('php://input');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_str);
        //error_log($method.': '.$data_str.serialize($_POST).'\n',3, 'err.log');
    }
    elseif ($method == "POST") {
        $data_str = array();
        if (!empty($_FILES)) {
            foreach ($_FILES as $key => $value) {
                $full_path = realpath( $_FILES[$key]['tmp_name']);
                $data_str[$key] = '@'.$full_path;
            }
        }
        //error_log($method.': '.serialize($data_str+$_POST).'\n',3, 'err.log');

        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_str+$_POST);
    }

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers_str );

    $result = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    
    header('Content-Type: '.$info[CURLINFO_CONTENT_TYPE]);
    http_response_code($info[CURLINFO_RESPONSE_CODE]);
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT');
    echo $result;
}
else {
    echo $method;
    var_dump($_POST);
    var_dump($_GET);
    $data_str = file_get_contents('php://input');
    echo $data_str;
}
