<?php
$json = '{"access_token":"ACCESS_TOKEN","expires_in":7200}';

$arr = json_decode($json, 1);

var_dump($arr);

