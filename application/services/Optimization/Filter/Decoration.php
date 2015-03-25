<?php
require_once OPS_APPLICATION_PATH . '/services/Optimization/Filter/Abstract/Content.php';

class Ops_Service_Optimization_Filter_Decoration
    extends Ops_Service_Optimization_Filter_Abstract_Content
{
    public function __invoke($tag)
    {
        if (!isset($tag) || ''== $tag) {
            return $this;
        }

        // Replace keyword in content if any
        $content = preg_replace("~<temp>(.*?)</temp>~isu",
            "<{$tag}>" . '${1}' . "</{$tag}>",
            $this->_parent->getData('content'),
            1, $count);

        if ($count) {
            $this->_parent->setData('content', $content);
        } else {
            $this->_parent->appendArrayData('append_lines', array(
                "<{$tag}>" . $this->_parent->getData('keyword_text') . "</{$tag}>",
                TRUE, //inline element
            ));
        }

        return $this;
    }
}