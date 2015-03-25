<?php
require_once OPS_APPLICATION_PATH
    . '/services/Optimization/Analyzer/Abstract/Base.php';

class Ops_Service_Optimization_Analyzer_KeywordInMetaKeywords
    extends Ops_Service_Optimization_Analyzer_Abstract_Base
{
    public function __invoke()
    {
        $keywords = $this->_parent->parseMetaKeywords(
            $this->_parent->getMetaTagContent('keywords'));

        return in_array($this->_parent->getData('keyword'), $keywords);
    }
}
