<?php
require_once OPS_APPLICATION_PATH . '/services/Optimization/Analyzer/Abstract/Base.php';

class Ops_Service_Optimization_Analyzer_Link
    extends Ops_Service_Optimization_Analyzer_Abstract_Base
{
    public function __invoke($url)
    {
        $result = $this->_parent->analyzeKeywordInPattern(
            '~<\s*a\s[^>]*\bhref=["\']'
            . preg_quote($url, '~')
            . '["\'>][^>]*>(.*?)<\s*/\s*a\s*>~isu');

        return $result;
    }
}