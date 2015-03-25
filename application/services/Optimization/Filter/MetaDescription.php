<?php
require_once OPS_APPLICATION_PATH
    . '/services/Optimization/Filter/Abstract/Head.php';

class Ops_Service_Optimization_Filter_MetaDescription
    extends Ops_Service_Optimization_Filter_Abstract_Head
{
    public function __invoke()
    {
        $description = $this->_parent
            ->getMetaTagContent('description', 'content');

        if ('' == $description) {
            $description = preg_replace(
                '~<a[^>]*>.*?</a>~isu', '', get_the_excerpt());
            $description = $this->_parent->htmlEscape(trim($description));
        }

        $this->_parent->addMetaTag('description',
            $this->_parent->addKeyword($description, TRUE));

        return $this;
    }

}
