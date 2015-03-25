<?php
require_once OPS_APPLICATION_PATH . '/services/Optimization/Helper/Abstract/Base.php';

class Ops_Service_Optimization_Helper_GetKeywordPattern
    extends Ops_Service_Optimization_Helper_Abstract_Base
{
    protected $_cache = array();

    protected $_variants = array(
        '\'' => array('&#0?39;', '&apos;',
            '‘', '&#8216;', '&lsquo;',
            '’', '&#8217;', '&rsquo;',
        ),
        ' ' => array(
            '&#8194;', '&ensp;',
            '&#8195;', '&emsp;',
            '&#8201;', '&thinsp;',
            '&#0?160;', '&nbsp;',
        ),
    );

    public function __invoke($keyword=NULL)
    {
        if (is_null($keyword)) {
            $keyword = $this->_parent->getData('keyword');
        }

        if (!isset($this->_cache[$keyword])) {
            return $this->_cache[$keyword] = $this->_generatePattern($keyword);
        }

        return $this->_cache[$keyword];
    }

    protected function _generatePattern($keyword)
    {
        $count = mb_strlen($keyword);
        $result = '';
        for ($i=0; $i < $count; $i++) {
            $char = mb_substr($keyword, $i, 1);
            $quotedChar =  preg_quote($char, '~');
            if (isset($this->_variants[$char])) {
                $result .= '(?:' . $quotedChar . '|'
                    . implode('|', $this->_variants[$char]) . ')';
                continue;
            }

            $encodedChar = htmlentities($char, ENT_QUOTES, 'utf-8');
            if ($char != $encodedChar) {
                $encodedChar = preg_quote($encodedChar, '~');
                $result .= "(?:{$quotedChar}|{$encodedChar})";
                continue;
            }

            $result .= $quotedChar;
        }

        return $result;
    }
}
