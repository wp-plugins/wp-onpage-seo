<?php
require_once OPS_APPLICATION_PATH
    . '/services/Optimization/Factor/Abstract/Base.php';

class Ops_Service_Optimization_Factor_MetaTitle
    extends Ops_Service_Optimization_Factor_Abstract_Base
{
    protected $_label = 'meta title';

    protected $_filterName = 'MetaTitle';

    protected function _analyze()
    {
        return (int) $this->_parent->analyzeKeywordInTagContent('title', NULL,
            'head');
    }
}
