<?php
require_once OPS_APPLICATION_PATH
    . '/services/Optimization/Factor/Abstract/Base.php';

abstract class Ops_Service_Optimization_Factor_Abstract_Header
    extends Ops_Service_Optimization_Factor_Abstract_Base
{
    protected $_tag;
    protected $_filterName = 'Tag';

    protected function _analyze()
    {
        $result = (int) $this->_parent->analyzeKeywordInTagContent($this->_tag);
        if (Ops_Service_Optimization::STATUS_NO == $result
            && 'disabled' == $this->_parent->getData('extra_content_mode')
        ) {
            $result = Ops_Service_Optimization::STATUS_NA;
        }

        return $result;
    }
}