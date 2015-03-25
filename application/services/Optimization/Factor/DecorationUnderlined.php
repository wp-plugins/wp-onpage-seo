<?php
require_once OPS_APPLICATION_PATH
    . '/services/Optimization/Factor/Abstract/Decoration.php';

class Ops_Service_Optimization_Factor_DecorationUnderlined
    extends Ops_Service_Optimization_Factor_Abstract_Decoration
{
    protected $_label = 'underline';

    protected $_filterName = 'Decoration';
    protected $_filterData = 'u';

    protected function _analyze()
    {
        $result = (int) $this->_parent->analyzeKeywordDecorated(
            'u', 'text-decoration', 'underline');
        $result = $this->_postAnalyze($result);

        return $result;
    }
}
