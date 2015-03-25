<?php
require_once OPS_APPLICATION_PATH
    . '/services/Optimization/Helper/Abstract/Base.php';

class Ops_Service_Optimization_Helper_GetMetaTagContent
    extends Ops_Service_Optimization_Helper_Abstract_Base
{
    public function __invoke($name, $contentType='head')
    {
        $content = $this->_parent->getData($contentType);
        if (preg_match_all("~<\s*meta\b([^>]*)>~isu", $content, $matches)) {
            foreach ($matches[1] as $meta) {
                if (preg_match("~\s+name\s*=\s*[\"|']{$name}[\"|']~isu", $meta)
                    && preg_match("~\s+content\s*=\s*[\"|'](.*?)[\"|']~isu", $meta, $values)
                ) {
                    return $values[1];
                }
            }
        }
        return NULL;
    }
}