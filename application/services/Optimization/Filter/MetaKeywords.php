<?php
require_once OPS_APPLICATION_PATH
    . '/services/Optimization/Filter/Abstract/Head.php';

class Ops_Service_Optimization_Filter_MetaKeywords
    extends Ops_Service_Optimization_Filter_Abstract_Head
{
    public function __invoke()
    {
        $value = $this->_parent
            ->getMetaTagContent('keywords', 'content');

        if (!is_null($value)) {
            $keywords = $this->_parent->parseMetaKeywords($value);
        } else {
            $keywords = wp_get_post_tags($this->_parent->getPostId(),
                array('fields' => 'names'));
            foreach ($keywords as &$item) {
                $item = mb_strtolower($this->_parent->htmlDecode($item));
            }
        }

        $keyword = $this->_parent->getData('keyword');
        if (!in_array($keyword, $keywords)) {
            $keywords[] = $keyword;
        }
        sort($keywords);
        $keywords = implode(', ', $keywords);

        $this->_parent->addMetaTag('keywords', $this->_parent->htmlEscape($keywords));

        return $this;
    }
}
