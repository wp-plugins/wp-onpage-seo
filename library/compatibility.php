<?php

if (!function_exists('mb_strtolower')) {
    function mb_strtolower($str, $encoding=NULL) {
        return strtolower($str);
    }
}

if (!function_exists('mb_strlen')) {
    function mb_strlen($str)
    {
        return function_exists('iconv_strlen')
            ? iconv_strlen($str)
            : strlen($str);
    }
}

if (!function_exists('mb_substr')) {
    function mb_substr($str, $start, $length)
    {
        return function_exists('iconv_substr')
            ? iconv_substr($str, $start, $length)
            : substr($str, $start, $length);
    }
}

if (!function_exists('iconv')) {
    function iconv($in_charset, $out_charset, $str)
    {
        return function_exists('mb_convert_encoding ')
            ? mb_convert_encoding ($str, $out_charset, $in_charset)
            : $str;
    }
}