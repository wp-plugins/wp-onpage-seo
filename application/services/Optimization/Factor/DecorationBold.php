<?php
require_once OPS_APPLICATION_PATH
    . '/services/Optimization/Factor/Abstract/Decoration.php';

class Ops_Service_Optimization_Factor_DecorationBold
    extends Ops_Service_Optimization_Factor_Abstract_Decoration
{
    protected $_label = 'bold';

    protected $_filterName = 'Decoration';
    protected $_filterData = 'strong';

    protected function _analyze()
    {
        $result = (int) $this->_parent->analyzeKeywordDecorated(
            array('strong', 'b'), 'font-weight', 'bold');
        $result = $this->_postAnalyze($result);

        return $result;
    }
}