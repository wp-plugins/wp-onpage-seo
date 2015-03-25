<?php
require_once OPS_APPLICATION_PATH
    . '/services/Optimization/Analyzer/Abstract/Base.php';

class Ops_Service_Optimization_Analyzer_KeywordInTagContent
    extends Ops_Service_Optimization_Analyzer_Abstract_Base
{
    public function __invoke($tag, $keyword=NULL, $contentType='html')
    {
        $result = $this->_parent->analyzeKeywordInPattern(
            "~<\s*{$tag}\b[^>]*>(.*?)<\s*/\s*{$tag}\s*>~isu",
            $keyword, $contentType);

        return $result;
    }
}
