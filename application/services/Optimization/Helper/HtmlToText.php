<?php
require_once OPS_APPLICATION_PATH . '/services/Optimization/Helper/Abstract/Base.php';

class Ops_Service_Optimization_Helper_HtmlToText
    extends Ops_Service_Optimization_Helper_Abstract_Base
{
    public function __invoke($string)
    {
        $string = strip_tags($string);
        $string = str_replace('&nbsp;', ' ', $string);
        $string = $this->_parent->htmlDecode($string);
        $string = $this->_parent->normalizeWhitespace($string);

        return $string;
    }
}
