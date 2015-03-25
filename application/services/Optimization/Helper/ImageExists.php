<?php
require_once OPS_APPLICATION_PATH . '/services/Optimization/Helper/Abstract/Base.php';

class Ops_Service_Optimization_Helper_ImageExists
    extends Ops_Service_Optimization_Helper_Abstract_Base
{
    public function __invoke()
    {
        return $this->_parent->tagExists('~<\s*img\b~isu', NULL, 'post_html') ;
    }
}