<?php
require_once OPS_APPLICATION_PATH
    . '/services/Optimization/Helper/Abstract/Base.php';

class Ops_Service_Optimization_Helper_AddHeadTag
    extends Ops_Service_Optimization_Helper_Abstract_Base
{
    public function __invoke($name, $value)
    {
        $head = $this->_parent->getData('content');
        $head = preg_replace("~<\s*{$name}\b[^>]*>.*?<\s*/\s*{$name}\s*>~isu", '', $head);
        $head = rtrim($head);
        $head .= "\n<{$name}>{$value}</{$name}>";

        $this->_parent->setData('content', $head);

        return $this;
    }
}