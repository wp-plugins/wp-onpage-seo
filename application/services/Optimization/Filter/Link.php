<?php
require_once OPS_APPLICATION_PATH . '/services/Optimization/Filter/Abstract/Content.php';

class Ops_Service_Optimization_Filter_Link
    extends Ops_Service_Optimization_Filter_Abstract_Content
{
    public function __invoke($type)
    {
        $content = $this->_parent->getData('content');

        if ('home' == $type) {
            $url = get_home_url();
        } else if ('post' == $type) {
            $url = get_permalink($this->_parent->getPostId());
        } else {
            throw new Exception("Unknown URL type '{$type}'");
        }

        $content = preg_replace(
            "~<temp>(.*?)</temp>~isu",
            "<a href=\"{$url}\">\${1}</a>",
            $content, 1, $count);
        if ($count) {
            $this->_parent->setData('content', $content);
            return $this;
        }

        $this->_parent->appendArrayData('append_lines', array(
            "<a href=\"{$url}\">{$this->_parent->getData('keyword_text')}</a>",
            TRUE,
        ));

        return $this;
    }
}
