<?php defined('IN_FATE') or exit('Access denied!');

        /**
         * @brief 加密解密
         **/	

        class ICrypt
        {
                /**
                 * @brief 动态解密函数
                 * @param  String $string 字符串
                 * @param  String $key    解密私钥
                 * @param  Int    $expiry 保留时间，默认为0，即为不限制
                 * @return String $str    解密后的字符串
                 **/
                public static function decode($string, $key='fate', $expiry=0)
                {
                        return self::code($string,'decode', $key, $expiry);
                }

                /**
                 * @brief 动态解密函数
                 * @param  String  $string 字符串
                 * @param  String  $key    加密私钥
                 * @param  Int     $expiry 保留时间，默认为0，即为不限制
                 * @return $string String  加密后的字符串
                 **/
                public static function encode($string, $key='fate', $expiry=0)
                {
                        return self::code($string,'encode', $key, $expiry);
                }

                /**
                 * @brief 加密解密算法
                 * @param  String  $string  要处理的字符串
                 * @param  String  $op      处理方式，加密或者解密，默认为decode即为解密
                 * @param  Int     $expiry  保留时间，默认为0即为不限制
                 * @return String  $string  处理后的字符串
                 **/
                private static function code($string, $op="decode", $key='fate', $expiry=0)
                {
                        $op=strtolower($op);
                        $key_length=10;
                        $key=md5($key);
                        //生成256长度的密码
                        $key_1=md5(substr($key,0,4));
                        $key_2=md5(substr($key,4,4));
                        $key_3=md5(substr($key,8,4));
                        $key_4=md5(substr($key,12,4));
                        $key_5=md5(substr($key,16,4));
                        $key_6=md5(substr($key,20,4));
                        $key_7=md5(substr($key,24,4));
                        $key_8=md5(substr($key,28,4));
                        $key_e= $key_length ? ($op == 'decode' ? substr($string, 0, $key_length): substr(md5(microtime()), -$key_length)) : '';
                        $cryptkey = md5($key_1|$key_e).md5($key_3|$key_e).md5($key_5|$key_e).md5($key_7|$key_e).md5($key_8|$key_e).md5($key_6|$key_e).md5($key_4|$key_e).md5($key_2|$key_e);
                        $cryptkey_length = strlen($cryptkey);
                        $string = $op == 'decode' ? base64_decode(substr($string, $key_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$key_5), 0, 22).$string;
                        $string_length = strlen($string);
                        $result="";
                        //通过循环的方式异或的方式加密，异或方式是加密中常用的一种处理方式
                        for($i = 0; $i < $string_length; $i++)
                        {
                                $result .= chr(ord($string[$i]) ^ ($cryptkey[$i % 256]));
                        }
                        //解码部分
                        if($op == 'decode')
                        {
                                if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 22) == substr(md5(substr($result, 32).$key_5), 0, 22))
                                {
                                        return substr($result, 32);
                                } else
                                {
                                        return '';
                                }
                        }
                        else
                        {
                                return $key_e.str_replace('=', '',base64_encode($result));
                        }
                }
        }
?>