<?php
require_once OPS_APPLICATION_PATH
    . '/services/Optimization/Filter/Abstract/Head.php';

class Ops_Service_Optimization_Filter_MetaTitle
    extends Ops_Service_Optimization_Filter_Abstract_Head
{
    public function __invoke()
    {
        $head = $this->_parent->getData('content');

        if (preg_match("~<\s*title\b[^>]*>(.*?)<\s*/\s*title\s*>~isu", $head,
            $matches)
        ) {
            $title = $matches[1];
        } else {
            $title = wp_title('', FALSE);
        }

        $this->_parent->addHeadTag('title', $this->_parent->addKeyword($title));

        return $this;
    }

}