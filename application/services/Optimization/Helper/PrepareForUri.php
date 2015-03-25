<?php
require_once OPS_APPLICATION_PATH . '/services/Optimization/Helper/Abstract/Base.php';

class Ops_Service_Optimization_Helper_PrepareForUri
    extends Ops_Service_Optimization_Helper_Abstract_Base
{
    public function __invoke($text)
    {
        $text = strtolower($text);
        $text = preg_replace('~[^\w\d]+~u', '-', $text);
        $text = trim($text, '-');
        $text = urlencode($text);

        return $text;
    }
}