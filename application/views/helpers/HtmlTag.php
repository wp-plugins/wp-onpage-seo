<?php
require_once(dirname(__FILE__) . '/HtmlTagAbstract.php');

class Ops_View_Helper_HtmlTag
    extends Ops_View_Helper_HtmlTagAbstract
{
    public function __invoke($tag, $content='', $attributes=array())
    {
        return $this->_renderTag($tag, $content, $attributes);
    }
}
