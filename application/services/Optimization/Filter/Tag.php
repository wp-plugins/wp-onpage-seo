<?php
require_once OPS_APPLICATION_PATH . '/services/Optimization/Filter/Abstract/Content.php';

class Ops_Service_Optimization_Filter_Tag
    extends Ops_Service_Optimization_Filter_Abstract_Content
{
    public function __invoke($tag, $part=NULL, $inline=FALSE)
    {
        // Backwards compatibility: footer changed to append_lines
        if ('footer' == $part) {
            $part = NULL;
        }

        $result = $this->_parent->getData('keyword_text');
        $this->_parent->htmlEscape($result);
        $result = "<{$tag}>{$result}</{$tag}>";

        if (is_null($part)) {
            $this->_parent->appendArrayData('append_lines', array(
                $result,
                $inline,
            ));
        } else {
            $this->_parent->addStringData($part, $result . "\n");
        }

        return $this;
    }
}