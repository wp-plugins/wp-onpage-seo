<?php
require_once OPS_APPLICATION_PATH
    . '/services/Optimization/Factor/Abstract/Base.php';

class Ops_Service_Optimization_Factor_MetaDescription
    extends Ops_Service_Optimization_Factor_Abstract_Base
{
    protected $_label = 'meta description';

    protected $_filterName = 'MetaDescription';

    protected function _analyze()
    {
       return $this->_parent->analyzeKeywordInMetaTagContent('description');
    }
}
