<?php
require_once OPS_APPLICATION_PATH
    . '/services/Optimization/Analyzer/Abstract/Base.php';

/**
* Same as KeywordInPattern but requires keyword to be found in all occurences
*/
class Ops_Service_Optimization_Analyzer_ImgNameContain
    extends Ops_Service_Optimization_Analyzer_Abstract_Base
{
    public function __invoke()
    {
        $content = $this->_parent->getData('post_html');

        $keyword = $this->_parent->getData('keyword');
        $keyword = $this->_parent->prepareForUri($keyword);

        if (!preg_match_all('~<\s*img\b[^>]*\bsrc\s*=\s*[\'"](.*?)[\'">]~isu',
            $content, $matches)
        ) {
            return FALSE;
        }

        foreach ($matches[1] as $match) {
            $name = @pathinfo(parse_url($match, PHP_URL_PATH), PATHINFO_FILENAME);
            if (!$name) {
                continue;
            }
            if (!$this->_parent->analyzeKeywordInText($name, $keyword)) {
                return FALSE;
            }
        }

        return TRUE;
    }
}