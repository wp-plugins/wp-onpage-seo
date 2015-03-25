<?php
require_once(dirname(__FILE__) . '/HelperAbstract.php');

class Ops_View_Helper_LimitStringLength
    extends Ops_View_Helper_HelperAbstract
{
    public function __invoke($string, $length, $ellipsis='...')
    {
        if ($this->_strlen($string) <= $length) {
            return $string;
        }

        return $this->_substr($string, 0, $length - $this->_strlen($ellipsis))
            . $ellipsis;
    }

    protected function _strlen($string)
    {
        return function_exists('mb_strlen')
            ? mb_strlen($string, 'utf-8')
            : strlen($string);
    }

    protected function _substr($string, $start, $length=NULL)
    {
        return function_exists('mb_substr')
            ? mb_substr($string, $start, $length, 'utf-8')
            : substr($string, $start, $length);
    }
}
