<?php

namespace application\library;

use woodlsy\phalcon\library\Helper;

class HelperExtend extends Helper
{
    public static function dealRegular($str)
    {
        $str = addslashes($str);
        $str = preg_replace('/\//', '\/', $str);
        $str = preg_replace('/\(/', '\(', $str);
        $str = preg_replace('/\)/', '\)', $str);
        $str = preg_replace('/\./', '\.', $str);
        $str = preg_replace('/\?/', '\?', $str);
        $str = preg_replace('/(\r\n)|(\n)/', '[\r\n|\n]*', $str);
        $str = preg_replace('/@/', '&nbsp;', $str);
        if (strpos($str, '!!!!')) {
            $str    = explode('!!!!', $str);
            $str[3] = '([^<>]*)';
        } elseif (strpos($str, '$$$$')) {
            $str    = explode('$$$$', $str);
            $str[3] = '(\d*)';
        } elseif (strpos($str, '~~~~')) {
            $str    = explode('~~~~', $str);
            $str[3] = '([^<>\'\"]*)';
        } elseif (strpos($str, '^^^^')) {
            $str    = explode('^^^^', $str);
            $str[3] = '([^<>\d]*)';
        } elseif (strpos($str, '****')) {
            $str    = explode('****', $str);
            $str[3] = '([\w\W]*)';
        }
        for ($i = 0; $i <= 1; $i++) {
            $str[$i] = preg_replace('/(?<!\])\*/', '[\s\S]*', $str[$i]);
            $str[$i] = preg_replace('/(?<!\<)!/', '[^<>]*', $str[$i]);
            $str[$i] = preg_replace('/\$/', '\d*', $str[$i]);
            $str[$i] = preg_replace('/~/', '[^<>\'\"]*', $str[$i]);
            $str[$i] = preg_replace('/(?<!\[)\^/', '[^<>\d]*', $str[$i]);
            //$str[$i] = preg_replace('/\s*/', '\s*', $str[$i]);
        }
        return $str;
    }

    /**
     * 补全链接地址
     *
     * @author yls
     * @param $links
     * @param $URI
     * @return string|string[]|null
     */
    public static function expandlinks($links, $URI, $host)
    {

        preg_match("/^[^\?]+/", $URI, $match);

        $match      = preg_replace("|/[^\/\.]+\.[^\/\.]+$|", "", $match[0]);
        $match      = preg_replace("|/$|", "", $match);
        $match_part = parse_url($match);
        $match_root =
            $match_part["scheme"] . "://" . $match_part["host"];

        $search = array("|^http://" . preg_quote($host) . "|i",
                        "|^(\/)|i",
                        "|^(?!http://)(?!mailto:)|i",
                        "|/\./|",
                        "|/[^\/]+/\.\./|"
        );

        $replace = array("",
                         $match_root . "/",
                         $match . "/",
                         "/",
                         "/"
        );

        $expandedLinks = preg_replace($search, $replace, $links);

        return $expandedLinks;
    }

    /**
     * 处理htmlspecialchars_decode后的文章内容
     *
     * @author yls
     * @param string $content 未处理的文章内容
     * @param string $type
     * @return string|string[]|null 已处理的文章内容
     */
    public static function doWriteContent(string $content, $type = 'add')
    {
        $content = stripslashes($content);
        if ($type == 'add') {
            $content = preg_replace("/[\s]?style[\s]?=[\s]?\"[^\"]*\"/i", '', $content);
        }
        $content = preg_replace("/<\/p>/i", "</p>\r\n", $content);
        $content = addslashes($content);
        return $content;
    }

    /**
     * 获取随机字符串
     *
     * @author yls
     * @param int $length
     * @return bool|string
     */
    public static function randString(int $length)
    {
        $strs = "QWERTYUIOPASDFGHJKLZXCVBNM1234567890qwertyuiopasdfghjklzxcvbnm";
        return substr(str_shuffle($strs), mt_rand(0, strlen($strs) - ($length + 1)), $length);
    }

    /**
     * 取数组其中一个字段的值来做索引
     *
     * @author yls
     * @param array  $arr
     * @param string $field
     * @return array
     */
    public static function indexArray(array $arr, string $field)
    {
        if (empty($arr)) {
            return $arr;
        }
        $data = [];
        foreach ($arr as $key => $val) {
            $data[($val[$field] ?? '')] = $val;
        }

        return $data;
    }

    /**
     * 转化为展示前端的键值对
     *
     * @author yls
     * @param array       $data
     * @param string|null $key
     * @param string|null $value
     * @return array
     */
    public static function showPair(array $data, string $key = null, string $value = null) : array
    {
        if (empty($data)) {
            return $data;
        }
        $arr = [];
        foreach ($data as $k => $v) {
            if (null === $key || null === $value) {
                $arr[] = ['key' => $k, 'value' => $v];
            } else {
                $arr[] = ['key' => $v[$key], 'value' => $v[$value]];
            }
        }
        return $arr;
    }
}