<?php
require_once OPS_APPLICATION_PATH . '/services/Optimization/Analyzer/Abstract/Base.php';

class Ops_Service_Optimization_Analyzer_KeywordDecorated
    extends Ops_Service_Optimization_Analyzer_Abstract_Base
{
    public function __invoke($tags=array(), $cssProperty, $cssValue,
        $keyword=NULL, $contentType='post_html')
    {
        $content = $this->_parent->getData($contentType);
        if (is_null($keyword)) {
            $keyword = $this->_parent->getData('keyword');
        }

        foreach ((array)$tags as $tag) {
            if ($this->_parent->analyzeKeywordInTagContent($tag, $keyword, $contentType)
            ) {
                return TRUE;
            }
        }

        return $this->_parent->analyzeKeywordInPattern(
            "~<\s*span\s[^>]*\bstyle=[\"'][^\"']*\b{$cssProperty}:\s*{$cssValue}\b[^>]*>(.*?)<\s*/\s*span\s*>~isu",
            $keyword, $contentType
        );
    }
}
