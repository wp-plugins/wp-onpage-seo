<?php
require_once OPS_APPLICATION_PATH . '/services/Optimization/Filter/Abstract/Content.php';

class Ops_Service_Optimization_Filter_RelatedTerms
    extends Ops_Service_Optimization_Filter_Abstract_Content
{
    public function __invoke($terms)
    {
        if (!$terms) {
            return $this;
        }

        $result = '<p class"related-terms"><span>Related terms: </span>'
            . $this->_parent->htmlEscape(implode(', ', $terms))
            . '</p>';
        $this->_parent->addStringData('footer', $result);
    }
}