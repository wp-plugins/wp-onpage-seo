<?php
require_once OPS_APPLICATION_PATH . '/services/Optimization/Helper/Abstract/Base.php';

class Ops_Service_Optimization_Helper_HtmlEscape
    extends Ops_Service_Optimization_Helper_Abstract_Base
{
    public function __invoke($string, $quoteStyle=ENT_QUOTES)
    {
        return htmlspecialchars($string, $quoteStyle, 'UTF-8');
    }
}
