<?php
require_once OPS_APPLICATION_PATH . '/services/Optimization/Helper/Abstract/Base.php';

class Ops_Service_Optimization_Helper_Strings
    extends Ops_Service_Optimization_Helper_Abstract_Base
{
    public function length($string)
    {        
        if (function_exists('mb_strlen')) {
            return mb_strlen($string, 'UTF-8');
        } else if (function_exists('iconv_strlen')) {
            return iconv_strlen($string, 'UTF-8');
        }
        
        return strlen($string); 
    }
}
