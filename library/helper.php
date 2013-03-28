<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of helper
 *
 * @author sinadmin
 */
class helper {

    static public function checkEmail($mail) {
        $mail = (string) $mail;
        return preg_match('/^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/', $mail);
    }

    static public function show_json($array, $callback = false) {
        helper::setHeader();
        if ($callback) {
            echo $callback . "(" . json_encode($array) . ")";
        } else {
            echo json_encode($array);
        }
        exit;
    }

    static public function checkReferer() {
        $check = new checkReffer();
        if (!$check->check_referer()) {
            $result = array("error" => 5000, "msg" => "Error: unaccepted request! Wrong Referer!");
            $callback = isset($_GET['callback']) ? trim($_GET['callback']) : false;
            helper::show_json($result, $callback);
        }
    }

    static function unserializeInfo($value, $key) {
        $value[$key] = unserialize($value[$key]);
        return $value;
    }

    static public function parse_imageName($imagName) {
        if ($imagName && !empty($imagName)) {
            $extend = explode(".", $imagName);
            if (count($extend) < 2) {
                return false;
            }
            $key = count($extend) - 1;
            $key = strtolower($extend[$key]);
            if ($key == 'jpg' || $key == 'jpeg' || $key == 'png' || $key == 'gif') {
                return $key;
            }
        }
        return false;
    }

    static public function parseWeiboText($t) {
        // link URLs
        $t = preg_replace("/(([[:alnum:]]+:\/\/)|www\.)([^[:space:]]*)" . "([[:alnum:]#?\/&=])/i", "<a href=\"\\1\\3\\4\" target=\"_blank\">" . "\\1\\3\\4</a>", $t);
        //link Sina users
        $t = preg_replace("/ *@([\x{4e00}-\x{9fa5}A-Za-z0-9_]*) ?/u", " <a href=\"http://weibo.com/n/\\1\" target=\"_blank\">@\\1</a> ", $t);
        //热门话题
        $t = preg_replace("/ *#(.*?)# ?/u", " <a href=\"http://s.weibo.com/weibo/\\1\" target=\"_blank\">#\\1#</a> ", $t);
        //表情
        $t = self::parseEmotions($t);
        return $t;
    }

    static function parseEmotions($t) {
        $emotions = file_get_contents(realpath(dirname(__FILE__) . "/../data/emotions.json"));
        $emotions = json_decode($emotions);
        if (!empty($emotions)) {
            $aLetter = array();
            $aPic = array();
            foreach ($emotions as $val) {
                $aLetter[] = $val->phrase;
                $aPic[] = "<img alt='" . $val->url . "' src='" . $val->url . "' />";
            }
        }
        $t = str_replace($aLetter, $aPic, $t);
        return $t;
    }

    /**
     * 检查或过滤 IP 地址
     *
     * @param string $network 网段，支持 IP 区间、CIDR及单个 IP 格式
     * @param string $ip 要检查的 IP 地址
     * @return boolean
     * @example Helper::netMatch("210.17.38.1 - 210.17.38.255", $ip)
     */
    static function netMatch($network, $ip) {
        $network = trim($network);
        $ip = trim($ip);
        $result = false;
        // IP range : 174.129.0.0 - 174.129.255.255
        if (false !== ($pos = strpos($network, "-"))) {
            $from = ip2long(trim(substr($network, 0, $pos)));
            $to = ip2long(trim(substr($network, $pos + 1)));
            $ip = ip2long($ip);
            $result = ($ip >= $from and $ip <= $to);
            // CIDR : 174.129.0.0/16
        } else if (false !== strpos($network, "/")) {
            list ($net, $mask) = explode('/', $network);
            $result = (ip2long($ip) & ~((1 << (32 - $mask)) - 1)) == ip2long($net);
            // single IP
        } else {
            $result = $network === $ip;
        }
        return $result;
    }

    static function getIp() {
        if (isset($HTTP_SERVER_VARS["HTTP_X_FORWARDED_FOR"])) {
            $ip = $HTTP_SERVER_VARS["HTTP_X_FORWARDED_FOR"];
        } elseif (isset($HTTP_SERVER_VARS["HTTP_CLIENT_IP"])) {
            $ip = $HTTP_SERVER_VARS["HTTP_CLIENT_IP"];
        } elseif (isset($HTTP_SERVER_VARS["REMOTE_ADDR"])) {
            $ip = $HTTP_SERVER_VARS["REMOTE_ADDR"];
        } elseif (getenv("HTTP_X_FORWARDED_FOR")) {
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        } elseif (getenv("HTTP_CLIENT_IP")) {
            $ip = getenv("HTTP_CLIENT_IP");
        } elseif (getenv("REMOTE_ADDR")) {
            $ip = getenv("REMOTE_ADDR");
        } else {
            $ip = "Unknown";
        }
        return $ip;
    }

    /**
     * UTF-8编码字符串的截取 
     * 
     * @param string $str 
     * @param int $len 
     * @param string $dot 
     * @return string 
     */
    static function SubTitle($String, $Length) {
        if (mb_strwidth($String, 'UTF8') <= $Length) {
            return $String;
        } else {
            $I = 0;
            $len_word = 0;
            while ($len_word < $Length) {
                $StringTMP = substr($String, $I, 1);
                if (ord($StringTMP) >= 224) {
                    $StringTMP = substr($String, $I, 3);
                    $I = $I + 3;
                    $len_word = $len_word + 2;
                } elseif (ord($StringTMP) >= 192) {
                    $StringTMP = substr($String, $I, 2);
                    $I = $I + 2;
                    $len_word = $len_word + 2;
                } else {
                    $I = $I + 1;
                    $len_word = $len_word + 1;
                }
                $StringLast[] = $StringTMP;
            }
            /* raywang edit it for dirk for (es/index.php) */
            if (is_array($StringLast) && !empty($StringLast)) {
                $StringLast = implode("", $StringLast);
                $StringLast .= "...";
            }
            return $StringLast;
        }
    }

    static function tokencode($length, $special = false) {
        $key = "";
        if ($special === true) {
            $pattern = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ,./&amp;lt;&gt;?;#:@~[]{}-_=+)(*&amp;^%$£"!';    //字符池
        } else {
            $pattern = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ';    //字符池
        }
        for ($i = 0; $i < $length; $i++) {
            $key .= $pattern{mt_rand(0, strlen($pattern) - 1)};    //生成php随机数
        }
        return $key;
    }

    static function sendMail($sendto, $from, $subject, $content, $ishtml = true, $charset = "utf-8") {
        $transport = new Zend_Mail_Transport_Smtp('210.17.38.109');
        $mail = new Zend_Mail($charset);
        $mail->addTo($sendto);
        $mail->setFrom($from, "新浪台灣客服中心");
        $mail->setSubject($subject);
        if ($ishtml) {
            $mail->setBodyHtml($content);
        } else {
            $mail->setBodyText($content);
        }
        return $mail->send($transport);
    }

    static function get_ios_version() {
        $ua = $_SERVER['HTTP_USER_AGENT'];

        if (strpos($ua, 'iPhone') === false &&
                strpos($ua, 'iPad') === false &&
                strpos($ua, 'iPod') === false)
            return false;

        $p = strpos($ua, ' OS ') + 4;

        return str_replace('_', '.', substr($ua, $p, strpos($ua, ' ', $p) - $p));
    }

    //瀑布流微博话题搜索
    static function getTopics($topic, $nPage = 1, $haspic = false) {
        $weiboApi = new weiBoApi(false, false, Zend_Json::TYPE_ARRAY);
        //get topic data--start
        $parameters = array(
            'source' => 2667332353,
            'sid' => 'tw_weibo',
            'page' => $nPage,
            'q' => $topic,
            'istag' => 2,
            //'province' => 71,
            'dup' => 0,
            'antispam' => 0,
            'count' => 50,
        );

        if ($haspic) {
            $parameters['haspic'] = 1;
        }

        $weibo_data = $weiboApi->search_statuses($topic, $parameters);

        //get topic data--end
        //convert friend.created_at;
        if (isset($weibo_data['statuses'])) {
            foreach ($weibo_data['statuses'] as $key => $record) {
                if (isset($record['created_at'])) {
                    $record['created_at'] = self::date2before($record['created_at']);
                    $weibo_data['statuses'][$key] = $record;
                };
            };
        };
        return $weibo_data;
//        return array(
//            'statuses' => $weibo_data->statuses,
//            'total_number' => $weibo_data->total_number
//        );
    }

    //友好时间
    static function date2before($inp) {
        $time = strtotime($inp);
        $diff = time() - $time;
        if (date("m.d.y") == date("m.d.y", $time)) {
            return '今天 ' . date('G:i', $time);
        } else {
            if ($diff < 0) {
                return '不久的將來';
            } elseif ($diff < 60) {
                return $diff . '秒前';
            } elseif ($diff < 3600) {
                return floor($diff / 60) . '分鐘前';
            } elseif ($diff < 31536000) {
                return date('n月j日 G:i', $time);
            } else {
                return date('Y-n-j G:i', $time);
            }
        }
    }

    static function initSmarty($template, $compile, $cache, $left = '<{', $right = '}>') {
        set_include_path(implode(":", array(get_include_path(), realpath(dirname(__FILE__) . '/Smarty'))));
        require_once(realpath(dirname(__FILE__) . "/Smarty") . "/Smarty.class.php");
        $smarty = new Smarty();
        $smarty->template_dir = !empty($template) ? $template : TEMPLATE_DIR;
        $smarty->compile_dir = !empty($compile) ? $compile : COMPILER_DIR;
        $smarty->cache_dir = !empty($cache) ? $cache : CACHE_DIR;
        $smarty->left_delimiter = $left;
        $smarty->right_delimiter = $right;
        return $smarty;
    }

    static function checkMobile() {
        $agent = empty($_SERVER['HTTP_USER_AGENT']) ? false : $_SERVER['HTTP_USER_AGENT'];
        if ($agent) {
            
        }
        return false;
    }

    static function cleanText($string) {
        $string = strip_tags($string);
        $string = preg_replace('/\n/is', '', $string);
        $string = preg_replace('/ |　/is', '', $string);
        $string = preg_replace('/&nbsp;/is', '', $string);

        return $string;
    }

    static function setHeader($type = "json", $charset = "UTF-8") {

        switch ($type) {
            case "json":
                header("Content-Type:application/json; charset={$charset}");
                break;
            case "javascript":
                header("Content-Type:text/javascript; charset={$charset}");
                break;
            case "js":
                header("Content-Type:text/javascript; charset={$charset}");
                break;
            case "html":
                header("Content-Type:text/html; charset={$charset}");
                break;
            case "xml":
                header("Content-Type:text/xml; charset={$charset}");
                break;
            default:
                header("Content-Type:application/json; charset={$charset}");
                break;
        }
    }

}

?>