<?php
require_once OPS_APPLICATION_PATH
    . '/services/Optimization/Analyzer/Abstract/Base.php';

class Ops_Service_Optimization_Analyzer_KeywordInMetaTagContent
    extends Ops_Service_Optimization_Analyzer_Abstract_Base
{
    public function __invoke($name, $keyword=NULL, $contentType='head')
    {
        $value = $this->_parent
            ->getMetaTagContent($name, $contentType);

        return '' != $value && $this->_parent->analyzeKeywordInText($value, $keyword);
    }
}
