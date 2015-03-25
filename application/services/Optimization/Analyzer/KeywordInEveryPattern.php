<?php
require_once OPS_APPLICATION_PATH
    . '/services/Optimization/Analyzer/Abstract/Base.php';

/**
* Same as KeywordInPattern but requires keyword to be found in all occurences
*/
class Ops_Service_Optimization_Analyzer_KeywordInEveryPattern
    extends Ops_Service_Optimization_Analyzer_Abstract_Base
{
    public function __invoke($pattern, $keyword=NULL, $contentType='html')
    {
        $content = $this->_parent->getData($contentType);

        if (is_null($keyword)) {
            $keyword = $this->_parent->getData('keyword');
        }

        if (!preg_match_all($pattern, $content, $matches)) {
            return FALSE;
        }

        foreach ($matches[1] as $match) {
            if (!$this->_parent
                ->analyzeKeywordInText($match, $keyword)
            ) {
                return FALSE;
            }
        }

        return TRUE;
    }
}