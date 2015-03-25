<?php
require_once OPS_APPLICATION_PATH . '/services/Optimization/Helper/Abstract/Base.php';

class Ops_Service_Optimization_Helper_PurifyWords
    extends Ops_Service_Optimization_Helper_Abstract_Base
{
    public function __invoke($text)
    {   
        $text = preg_replace('~[^\w\d]~u', ' ', $text);
        $text = $this->_parent->normalizeWhitespace($text);
        
        return $text;
    }
}
