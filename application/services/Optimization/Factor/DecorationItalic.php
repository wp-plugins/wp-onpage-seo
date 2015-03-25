<?php
require_once OPS_APPLICATION_PATH
    . '/services/Optimization/Factor/Abstract/Decoration.php';

class Ops_Service_Optimization_Factor_DecorationItalic
    extends Ops_Service_Optimization_Factor_Abstract_Decoration
{
    protected $_label = 'italics';

    protected $_filterName = 'Decoration';
    protected $_filterData = 'em';

    protected function _analyze()
    {
        $result = (int) $this->_parent->analyzeKeywordDecorated(
            array('em', 'i'), 'font-style', 'italic');
        $result = $this->_postAnalyze($result);

        return $result;
    }
}
