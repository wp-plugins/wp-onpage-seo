<?php
require_once OPS_APPLICATION_PATH
    . '/services/Optimization/Factor/Abstract/Base.php';

class Ops_Service_Optimization_Factor_MetaKeywords
    extends Ops_Service_Optimization_Factor_Abstract_Base
{
    protected $_label = 'meta keywords';

    protected $_filterName = 'MetaKeywords';

    protected function _analyze()
    {
       return $this->_parent->analyzeKeywordInMetaKeywords();
    }
}
