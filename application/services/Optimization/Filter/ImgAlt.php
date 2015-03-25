<?php
require_once OPS_APPLICATION_PATH . '/services/Optimization/Filter/Abstract/Content.php';

class Ops_Service_Optimization_Filter_ImgAlt
    extends Ops_Service_Optimization_Filter_Abstract_Content
{
    /*
    public function __invoke()
    {
        $content = $this->_parent->getData('content');
        $keywordText = $this->_parent->getData('keyword_text');

        // Try replacing alt attribute
        $result = preg_replace('~(<\s*img\b[^>]*\balt\s*=\s*[\'"]).*?([\'">])~isu',
            '${1}' . $keywordText . '${2}', $content, 1, $count);
        if ($count) {
            $this->_parent->setData('content', $result);
            return $this;
        }

        // Try adding alt attribute
        $result = preg_replace('~<\s*img\b~isu',
            '${0} alt="' . $keywordText . '" ', $content, 1, $count);
        if ($count) {
            $this->_parent->setData('content', $result);
        }

        return $result;
    }
    */

    //
    // Temporary data
    //
    protected $_imageIndex;
    protected $_keywordText;

    public function __invoke()
    {
        $content = $this->_parent->getData('content');

        $this->_imageIndex = 0;
        $this->_keywordText = $this->_parent->getData('keyword_text');

        $content = preg_replace_callback('~(<\s*img\b)([^>]*)([><])~isu',
            array($this, '_replaceImageTag'), $content, -1, $count);

        if ($count) {
            $this->_parent->setData('content', $content);
        }

        return $this;
    }

    /**
    * Processes preg_replace_callback calls from __invoke
    *
    * @param array $match
    */
    public function _replaceImageTag($match)
    {
        $this->_imageIndex++;

        // Try replacing alt attribute
        $content = preg_replace_callback('~(\balt=[\'"])(.*?)([\'"])~isu',
            array($this, '_replaceImageTagAlt'), $match[2], 1, $count);
        if ($count) {
            $match[2] = $content;
        } else {
            // Add alt attribute
            $match[2] .= " alt=\"{$this->_keywordText}\" ";
        }

        return $match[1] . $match[2] . $match[3];
    }

    public function _replaceImageTagAlt($match)
    {
        $origAlt = trim($match[2]);

        if ($this->_parent->analyzeKeywordInText($origAlt, $this->_keywordText)) {
            return $matches[0];
        }

        $match[2] = $this->_keywordText;
        if ($this->_imageIndex > 1 && '' != $origAlt) {
            $match[2] = "{$origAlt} - {$match[2]}";
        }

        return $match[1] . $match[2] . $match[3];
    }
}