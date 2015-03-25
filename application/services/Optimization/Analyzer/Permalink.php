<?php
require_once OPS_APPLICATION_PATH . '/services/Optimization/Analyzer/Abstract/Base.php';

class Ops_Service_Optimization_Analyzer_Permalink
    extends Ops_Service_Optimization_Analyzer_Abstract_Base
{
    public function __invoke()
    {
        return (bool) preg_match('~^'
            . preg_quote($this->_parent->prepareForUri($this->_parent->getData('keyword')), '~')
                . '(?:-\d+)?$~iu',
            $this->_parent->getData('post')->post_name);
    }
}