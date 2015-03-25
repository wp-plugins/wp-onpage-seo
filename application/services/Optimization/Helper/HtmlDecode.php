<?php
require_once OPS_APPLICATION_PATH . '/services/Optimization/Helper/Abstract/Base.php';

class Ops_Service_Optimization_Helper_HtmlDecode
    extends Ops_Service_Optimization_Helper_Abstract_Base
{
    public function __invoke($string, $quoteStyle=ENT_QUOTES)
    {
        return html_entity_decode($string, $quoteStyle, 'UTF-8');
    }
}
