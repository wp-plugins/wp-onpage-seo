<?php
/**
* HTML purification helper class
*/

require_once OPS_APPLICATION_PATH 
    . '/services/Optimization/Helper/Abstract/Purify.php';

class Ops_Service_Optimization_Helper_PurifyPost
    extends Ops_Service_Optimization_Helper_Abstract_Purify
{        
    public function __invoke($html)
    {
        $html = strip_shortcodes($html);
        $html = $this->_parent->purifyHtml($html);
        
        return $html;
    }
}
