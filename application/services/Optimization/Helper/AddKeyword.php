<?php
require_once OPS_APPLICATION_PATH
    . '/services/Optimization/Helper/Abstract/Base.php';

class Ops_Service_Optimization_Helper_AddKeyword
    extends Ops_Service_Optimization_Helper_Abstract_Base
{
    public function __invoke($text, $prepend=FALSE, $keyword=NULL,
        $separator=' - ')
    {
        if (is_null($keyword)) {
            $keyword = $this->_parent->getData('keyword');
        }

        if (!$this->_parent->analyzeKeywordInText($text, $keyword)) {
            if ('' != $text) {
                $text = $prepend
                    ? $this->_parent->htmlEscape($keyword) . $separator . $text
                    : $text . $separator . $this->_parent->htmlEscape($keyword);
            } else {
                $text = $this->_parent->htmlEscape($keyword);
            }
        }

        return $text;
    }
}