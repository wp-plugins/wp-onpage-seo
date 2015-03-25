<?php
require_once OPS_APPLICATION_PATH . '/services/Optimization/Helper/Abstract/Base.php';

class Ops_Service_Optimization_Helper_ParseMetaKeywords
    extends Ops_Service_Optimization_Helper_Abstract_Base
{
    public function __invoke($text)
    {
        $result = explode(',', $text);
        foreach ($result as &$item) {
            $item = trim(mb_strtolower($this->_parent->htmlDecode($item)));
        }

        return $result;
    }
}