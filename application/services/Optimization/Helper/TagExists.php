<?php
require_once OPS_APPLICATION_PATH . '/services/Optimization/Helper/Abstract/Base.php';

class Ops_Service_Optimization_Helper_TagExists
    extends Ops_Service_Optimization_Helper_Abstract_Base
{
    public function __invoke($pattern, $keyword=NULL, $contentType='html')
    {
        $content = $this->_parent->getData($contentType);

        if (preg_match($pattern, $content)) {
            return true;
        }
        return false;
    }
}