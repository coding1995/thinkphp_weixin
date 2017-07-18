<?php
include './wxModel.php';
//define your token
define("TOKEN", "weixin");
$wechatObj = new wxModel();
// $b = $wechatObj->getAccessToken();
// var_dump($_SESSION['access_token']);
if (isset($_GET['echostr'])) {
    $wechatObj->valid();
} else {
    // 接收微信服务器发送过来的xml
    $wechatObj->responseMsg();
}

//http://www.webjust.org/
//https://github.com/webjust
//http://haveashow.net/
