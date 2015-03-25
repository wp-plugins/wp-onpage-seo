<?php
require_once OPS_APPLICATION_PATH
    . '/services/Optimization/Helper/Abstract/Base.php';

class Ops_Service_Optimization_Helper_ExtractHtmlHead
    extends Ops_Service_Optimization_Helper_Abstract_Base
{
    public function __invoke($html)
    {
        if (preg_match('~<\s*head\b[^>]*>(.*)(?:<\s*/\s*head\s*>|<\s*body\b\s*>)~isu', $html, $matches)) {
            return $matches[1];
        }

        return '';
    }
}
