<?php
require_once OPS_APPLICATION_PATH
    . '/services/Optimization/Helper/Abstract/Base.php';

class Ops_Service_Optimization_Helper_AddMetaTag
    extends Ops_Service_Optimization_Helper_Abstract_Base
{
    public function __invoke($name, $value)
    {
        $head = $this->_parent->getData('content');
        $head = preg_replace("~<\s*meta\b[^>]*name\s*=\s*\"{$name}\"[^>]*>~isu", '', $head);
        $head = rtrim($head);
        $head .= "\n<meta name=\"{$name}\" content=\"{$value}\" />";

        $this->_parent->setData('content', $head);

        return $this;
    }
}