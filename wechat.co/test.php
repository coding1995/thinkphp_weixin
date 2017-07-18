<?php
//echo mt_rand(0,9); die;

include './wxModel.php';
$model = new wxModel();
echo $model->getAccessToken();

die;
$postStr = <<<EOT
<xml>
 <ToUserName><![CDATA[隔壁老王]]></ToUserName>
 <FromUserName><![CDATA[fromUser]]></FromUserName>
 <CreateTime>1348831860</CreateTime>
 <MsgType><![CDATA[image]]></MsgType>
 <PicUrl><![CDATA[this is a url]]></PicUrl>
 <MediaId><![CDATA[media_id]]></MediaId>
 <MsgId>1234567890123456</MsgId>
 </xml>
EOT;

var_dump($postStr);

// file_put_contents('data.txt', $postStr);

// xml 数据如何转换成为数组
$postObj = simplexml_load_string($postStr, "SimpleXMLElement", LIBXML_NOCDATA);
var_dump($postObj);

$tousername = $postObj->ToUserName;         // 开发者
$fromusername = $postObj->FromUserName;     // 用户
$createtime = $postObj->CreateTime;
$msgtype = $postObj->MsgType;
$picurl = $postObj->PicUrl;
$mediaid = $postObj->MediaId;
$msgid = $postObj->MsgId;

//echo $tousername;

// php + mysql    读取数据库 拿到文章列表的数据
$arr = array(
    array(
        'title' => "套路太深！唯品会对清空微博作出解释 网友：这广告6到飞",
        'date' => "2017-6-2",
        'url' => "http://www.chinaz.com/news/quka/2017/0602/715449.shtml",
        'description' => '日前，唯品会清空了官方微博，成功的引起了众人的注意。',
        'picUrl' => "http://upload.chinaz.com/2017/0602/6363201407728157524057839.jpeg"
    ),
    array(
        'title' => "刘强东章泽天向中国人民大学捐赠3亿 设人大京东基金",
        'date' => "2017-6-2",
        'url' => "http://www.chinaz.com/news/2017/0602/715434.shtml",
        'description' => '京东集团创始人、董事局主席兼首席执行官及京东集团今天下午在中国人民大学宣布',
        'picUrl' => "http://upload.chinaz.com/2017/0602/6363201407728157524057839.jpeg"
    ),
    array(
        'title' => "高通发布 QC 4+ 快充技术，让努比亚 Z17 当了一次“业界领先”",
        'date' => "2017-6-2",
        'url' => "http://www.chinaz.com/mobile/2017/0602/715429.shtml",
        'description' => '充电 5 分钟，通话 2 小时这句广告词',
        'picUrl' => "http://upload.chinaz.com/2017/0602/6363201407728157524057839.jpeg"
    )
);
$textTpl = <<<EOT
<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[%s]]></MsgType>
<Image>
<MediaId><![CDATA[%s]]></MediaId>
</Image>
</xml>
EOT;

//$str = "";
//foreach ($arr as $v)
//{
//    $str .= "<item>";
//    $str .= "<Title><![CDATA[".$v['title']."]]></Title>";
//    $str .= "<Description><![CDATA[".$v['description']."]]></Description>";
//    $str .= "<PicUrl><![CDATA[".$v['picUrl']."]]></PicUrl>";
//    $str .= "<Url><![CDATA[".$v['url']."]]></Url>";
//    $str .= "</item>";
//}
//
//$textTpl .= $str;
//$textTpl .= "</Articles></xml>";

$time = time();
$msgtype = 'image';
$nums = count($arr);
$content = "欢迎来到微信公众号的开发世界！__GZPHP27";
$mediaid = 'HMqPQ6if9l18ISY8k7fblAsVHCXyinnzACY7eJP_NNqOzBAxKPVYh-z6wFHyqJa3';

// Return a formatted string
$retStr = sprintf($textTpl, $fromusername, $tousername, $time, $msgtype, $mediaid);

// file_put_contents('data.txt', $retStr);

var_dump($retStr);
