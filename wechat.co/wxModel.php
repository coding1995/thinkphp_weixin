<?php
class wxModel
{
    /*
     * 接口配置信息，此信息需要你有自己的服务器资源，填写的URL需要正确响应微信发送的Token验证*/
    public function valid()
    {
        $echoStr = $_GET["echostr"];

        //valid signature , option
        if ($this->checkSignature()) {
            echo $echoStr;
            exit;
        }
    }

    /*
     * 微信发送消息，开发者服务器接收xml格式数据，然后进行业务的逻辑处理*/
    public function responseMsg()
    {
        // < 5.6       $GLOBALS
        // PHP > 7.0   file_get_contents()
        // file_put_contents('data.txt', $postStr);
        // $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];    < 5.6       $GLOBALS      // POST数据
        $postStr = file_get_contents('php://input');// PHP > 7.0

        // 使用 Medoo 类 把xml数据写入数据库
        // include './db.php';
        // $data = array(
        //     'xml' => $postStr,
        // );
        // $database->insert('xml', $data);

        if (!empty($postStr)) {
            /* libxml_disable_entity_loader is to prevent XML eXternal Entity Injection,
               the best way is to check the validity of xml by yourself */
            // Disable the ability to load external entities
            libxml_disable_entity_loader(true);

            // 接收到微信服务器发送过来的xml数据：分为：时间、消息，按照 msgType 分，转换为对象
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);

            $tousername = $postObj->ToUserName;
            $fromusername = $postObj->FromUserName;
            $msgtype = $postObj->MsgType;
            $keyword = trim($postObj->Content);

            // 图文  -》 返回图文列表    其他任何关键   默认
            if ($msgtype == 'text') {
                // 判断关键字，根据关键字来自定义回复的消息
                if ($keyword == "图文") {
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
                                <ArticleCount>%s</ArticleCount>
                                <Articles>
EOT;

                    $str = "";
                    foreach ($arr as $v) {
                        $str .= "<item>";
                        $str .= "<Title><![CDATA[" . $v['title'] . "]]></Title>";
                        $str .= "<Description><![CDATA[" . $v['description'] . "]]></Description>";
                        $str .= "<PicUrl><![CDATA[" . $v['picUrl'] . "]]></PicUrl>";
                        $str .= "<Url><![CDATA[" . $v['url'] . "]]></Url>";
                        $str .= "</item>";
                    }

                    $textTpl .= $str;
                    $textTpl .= "</Articles></xml>";

                    $time = time();
                    $msgtype = 'news';
                    $nums = count($arr);

                    // Return a formatted string
                    $retStr = sprintf($textTpl, $fromusername, $tousername, $time, $msgtype, $nums);
                    echo $retStr;
                }

                // 接收到的关键字：美女，返回美图图片
                if ($keyword == "美女") {
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
                    $time = time();
                    $msgtype = 'image';
                    $mediaid = 'oDv3jUvvf1mRrUFUQHEUMPWUw8ULu2DeBMPyKQ1V4Al9LLlVc3q9EGfJvKjRINS3';

                    $retStr = sprintf($textTpl, $fromusername, $tousername, $time, $msgtype, $mediaid);
                    echo $retStr;
                }

                if (substr($keyword, 0, 6) == '天气') {

                    $city = substr($keyword, 6, strlen($keyword));

                    $json = $this->getWeather($city);
                    $res = json_decode($json, 1);
                    
                    $str = '城市：'.$res['result']['today']['city']."\n";
                    $str .= "日期：".$res['result']['today']['date_y'].'-'.$res['result']['today']['week']."\n"; 
                    $str .= "今日温度：".$res['result']['today']['temperature']."\n"; 
                    $str .= "今日天气：".$res['result']['today']['weather']."\n";
                    $str .= "穿衣指数：".$res['result']['today']['dressing_advice']."\n";
                    // 发送天气的消息
                    $textTpl = "<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[%s]]></MsgType>
                            <Content><![CDATA[%s]]></Content>
                            <FuncFlag>0</FuncFlag>
                            </xml>";
                    $time = time();
                    $msgtype = 'text';
                    $content = $str;

                    /*
                    广州今天的天气信息：\n
                    温度：\n
                    气候：\n
                    适宜：\n
                    2017-6-5
                     */

                    $retStr = sprintf($textTpl, $fromusername, $tousername, $time, $msgtype, $content);
                    echo $retStr;
                }
            }

            // 判断是否发生了事件推送
            if ($msgtype == 'event') {
                $event = $postObj->Event;
                // 订阅事件
                if ($event == 'subscribe')
                {
                    // 订阅后，发送的文本消息
                    $textTpl = "<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[%s]]></MsgType>
                            <Content><![CDATA[%s]]></Content>
                            <FuncFlag>0</FuncFlag>
                            </xml>";
                    $time = time();
                    $msgtype = 'text';
                    $content = "欢迎来到songqiphp.xin，请输入美女，查看图片(有效期仅限今天)";

                    $retStr = sprintf($textTpl, $fromusername, $tousername, $time, $msgtype, $content);
                    echo $retStr;
                }
            }

            $time = time();
            $msgtype = $postObj->MsgType;
            $content = "欢迎来到微信公众号的开发世界！songqiphp.xin";

            /*
            <xml>
            <ToUserName><![CDATA[toUser]]></ToUserName>
            <FromUserName><![CDATA[fromUser]]></FromUserName>
            <CreateTime>12345678</CreateTime>
            <MsgType><![CDATA[text]]></MsgType>
            <Content><![CDATA[你好]]></Content>
            </xml>
            */
            // 发送消息的xml模板：文本消息
            $textTpl = "<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[%s]]></MsgType>
                            <Content><![CDATA[%s]]></Content>
                            <FuncFlag>0</FuncFlag>
                            </xml>";

            $time = time();
            $msgtype = 'text';
            $content = "欢迎来到微信公众号的开发世界！";

            // Return a formatted string
            $retStr = sprintf($textTpl, $fromusername, $tousername, $time, $msgtype, $content);
            echo $retStr;

        } else {
            echo "";
            exit;
        }
    }

    /*
     * 验证服务器地址的有效性*/
    private function checkSignature()
    {
        /*
        1）将token、timestamp、nonce三个参数进行字典序排序
        2）将三个参数字符串拼接成一个字符串进行sha1加密
        3）开发者获得加密后的字符串可与signature对比，标识该请求来源于微信
         */
        // you must define TOKEN by yourself
        if (!defined("TOKEN")) {
            throw new Exception('TOKEN is not defined!');
        }

        $signature = $_GET["signature"];

        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        $token = TOKEN;

        $tmpArr = array($token, $timestamp, $nonce);
        // use SORT_STRING rule
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);

        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
    }

    /*
     * curl请求，获取返回的数据
     * */
    public function getData($url)
    {
        // 1. cURL初始化
        $ch = curl_init();

        // 2. 设置cURL选项
        /*
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        */
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        // 3. 执行cURL请求
        $ret = curl_exec($ch);

        // 4. 关闭资源
        curl_close($ch);

        return $ret;
    }

    /*
     * JSON 转化为数组
     * */
    public function jsonToArray($json)
    {
        $arr = json_decode($json, 1);
        return $arr;
    }

    public function getAccessToken()
    {
        // redis  memcache SESSION
        session_start();

        if ($_SESSION['access_token'] && (time()-$_SESSION['expire_time']) < 7000 )
        {
            return $_SESSION['access_token'];
        } else {
            $appid = "wx2849a2f3d756d808";
            $appsecret = "71fe970e5b746e4fcd02c0b79f18903b";

            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$appsecret;
            $access_token = $this->jsonToArray($this->getData($url))['access_token'];

            // 写入SESSION
            $_SESSION['access_token'] = $access_token;
            $_SESSION['expire_time'] = time();
            return $access_token;
        }
    }

    // $wechatObj = new wxModel();
    // $b = $wechatObj->getAccessToken();
    // var_dump($_SESSION['access_token']);
    
    public function getWeather($city)
    {
        $appkey = '3d92eb3623d5cc1ec6c85f596cc58054';
        $url = "http://v.juhe.cn/weather/index?format=2&cityname=" . $city . "&key=" . $appkey;
        // $appkey = '45ff5d357e8a4fcf9102242d21485487';
        // http://apis.haoservice.com/weather?cityname=北京&key=您申请的KEY
        // $url = "http://apis.haoservice.com/weather?cityname=" . $city . "&key=" . $appkey;
        return $this->getData($url);
    }

    public function getUserOpenIdList()
    {
        $url = "https://api.weixin.qq.com/cgi-bin/user/get?access_token=" . $this->getAccessToken();
        return $this->getData($url);
    }

    // 网页授权的接口，获取用户信息
    public function getUserInfo()
    {
        $appid = $this->appid;
        $redirect_uri = urlencode('http://wechat.bls666.club/login.php');
        $scope = 'snsapi_userinfo';

        // $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $appid . "&redirect_uri=" . $redirect_uri . "&response_type=" . $response_type . "&scope=" . $scope . "&state=STATE#wechat_redirect";

        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $appid . "&redirect_uri=" . $redirect_uri . "&response_type=code&scope=" . $scope . "&state=STATE#wechat_redirect";
        header('location:' . $url);
        // return $url;
    }

    // 拉取用户信息
    public function getUserDetail()
    {
        // 通过code换取网页授权access_token
        $code = $_GET['code'];
        $appid = $this->appid;
        $secret = $this->appsecret;

        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . $appid . "&secret=" . $secret . "&code=" . $code . "&grant_type=authorization_code";

        $access_token_arr = $this->jsonToArray($this->getData($url));

        $access_token = $access_token_arr['access_token'];
        $open_id = $access_token_arr['openid'];


        // 获取用户的详细信息
        $url = "https://api.weixin.qq.com/sns/userinfo?access_token=" . $access_token . "&openid=" . $open_id . "&lang=zh_CN";
        return json_decode($this->getData($url), 1);
    }

    public function geiIp()
    {
        $url = "https://api.weixin.qq.com/cgi-bin/getcallbackip?access_token=" . $this->getAccessToken();
        return $this->getData($url);
    }

    // 创建二维码ticket：临时
    public function getQrCode()
    {
        // 1. 创建二维码ticket
        $url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=" . $this->getAccessToken();
        $postStr = '{"expire_seconds": 604800, "action_name": "QR_SCENE", "action_info": {"scene": {"scene_id": 888}}}';
        $ret = $this->getData($url, 'POST', $postStr);
        $arr = $this->jsonToArray($ret);
        $ticket = $arr['ticket'];

        // 2.通过ticket换取二维码
        // 提醒：1. TICKET记得进行UrlEncode
        // ticket正确情况下，http 返回码是200，是一张图片，可以直接展示或者下载。(不需要curl请求)
        $imgUrl = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=" . urlencode($ticket);
        // $imgUrl = $this->getData($url);
        return $imgUrl;
        // echo $imgUrl;
    }

    private function getJsApiTicket()
    {
        // jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例
        $data = json_decode($this->get_php_file("jsapi_ticket.php"));
        if ($data->expire_time < time()) {
            $accessToken = $this->getAccessToken();
            // 如果是企业号用以下 URL 获取 ticket
            // $url = "https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token=$accessToken";
            // https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
            $res = json_decode($this->httpGet($url));
            $ticket = $res->ticket;
            if ($ticket) {
                $data->expire_time = time() + 7000;
                $data->jsapi_ticket = $ticket;
                $this->set_php_file("jsapi_ticket.php", json_encode($data));
            }
        } else {
            $ticket = $data->jsapi_ticket;
        }

        return $ticket;
    }

    private function httpGet($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        // 为保证第三方服务器与微信服务器之间数据传输的安全性，所有微信接口采用https方式调用，必须使用下面2行代码打开ssl安全校验。
        // 如果在部署过程中代码在此处验证失败，请到 http://curl.haxx.se/ca/cacert.pem 下载新的证书判别文件。
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true);
        curl_setopt($curl, CURLOPT_URL, $url);

        $res = curl_exec($curl);
        curl_close($curl);

        return $res;
    }

    private function createNonceStr($length = 16)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    private function get_php_file($filename)
    {
        return trim(substr(file_get_contents($filename), 15));
    }

    private function set_php_file($filename, $content)
    {
        $fp = fopen($filename, "w");
        fwrite($fp, "<?php exit();?>" . $content);
        fclose($fp);
    }

    public function getSignPackage()
    {
        $jsapiTicket = $this->getJsApiTicket();

        // 注意 URL 一定要动态获取，不能 hardcode.
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

        $timestamp = time();
        $nonceStr = $this->createNonceStr();

        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

        $signature = sha1($string);

        $signPackage = array(
            "appId" => $this->appid,
            "nonceStr" => $nonceStr,
            "timestamp" => $timestamp,
            "url" => $url,
            "signature" => $signature,
            "rawString" => $string
        );
        return $signPackage;
    }
}
