<?php
require_once OPS_APPLICATION_PATH . '/services/Optimization/Analyzer/Abstract/Base.php';

class Ops_Service_Optimization_Analyzer_KeywordInText
    extends Ops_Service_Optimization_Analyzer_Abstract_Base
{
    public function __invoke($text, $keyword=NULL)
    {
        return (bool) preg_match(
            '~(?:^|[^\w\d])'
                . $this->_parent->getKeywordPattern($keyword)
                . '(?:$|[^\w\d])~iu',
            $text);
    }
}
