<?php
require_once OPS_APPLICATION_PATH . '/services/Optimization/Factor/Abstract/Base.php';

class Ops_Service_Optimization_Factor_ImgAltContain
    extends Ops_Service_Optimization_Factor_Abstract_Base
{
    protected $_label = 'image alt';

    protected $_filterName = 'ImgAlt';

    protected function _analyze()
    {
        if (!$this->_parent->imageExists()) {
            return Ops_Service_Optimization::STATUS_NA;
        }

        return (int) $this->_parent->analyzeKeywordInEveryPattern(
            '~<\s*img\b[^>]*\balt\s*=\s*[\'"](.*?)[\'">]~isu',
            NULL, 'post_html');
    }
}