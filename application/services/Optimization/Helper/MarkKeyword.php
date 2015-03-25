<?php
/**
* Filtration helper class
*/
require_once OPS_APPLICATION_PATH . '/services/Optimization/Helper/Abstract/Base.php';

class Ops_Service_Optimization_Helper_MarkKeyword
    extends Ops_Service_Optimization_Helper_Abstract_Base
{
    const TAG_OPEN = '<temp>';
    const TAG_CLOSE = '</temp>';

    protected $_restrictedTags = array('a', 'h1', 'h2', 'h3', 'h4', 'script');

    public function __invoke()
    {
        $content = $this->filterContent($this->_parent->getData('content'));

        $this->_parent->setData('content', $content);

        return $this;
    }

    public function filterContent($content)
    {
        $keywordPattern = $this->_parent->getData('keyword_pattern');

        $content = preg_replace("~(^|[^\w\d])({$keywordPattern})($|[^\w\d])~isu",
            '${1}' . self::TAG_OPEN . '${2}' . self::TAG_CLOSE . '${3}',
            $content
        );

        // clean temp tag in <>
        $content = $this->_multiPregReplace(
            '~(<[^>]*?)'
            . self::TAG_OPEN . '(.*?)' . self::TAG_CLOSE
            . '([^>]*?>)~isu',
            '${1}${2}${3}',
            $content
        );

        // clean temp tag in restricted tags
        foreach ($this->_restrictedTags as $tag) {
            $content = $this->_removeKeywordMarkFromTag($tag, $content);
        }

        return $content;
    }

    public function cleanUp()
    {
        // clean remaining temp tags
        $content = preg_replace('~' . self::TAG_OPEN . '(.*?)' . self::TAG_CLOSE . '~isu',
           '${1}',
           $this->_parent->getData('content')
        );

        $this->_parent->setData('content', $content);

        return $this;
    }

    public function countKeywords($content)
    {
        $content = $this->filterContent($content);
        $result = preg_match_all('~' . self::TAG_OPEN . '~u', $content, $matches);

        return $result;
    }

    /**
    * Continues regex replace until pattern not found in subject
    *
    * @param string $pattern
    * @param string $subject
    */
    protected function _multiPregReplace($pattern, $replacement, $subject)
    {
        do {
            $subject = preg_replace($pattern, $replacement, $subject, -1, $count);
        } while ($count > 0);

        return $subject;
    }

    protected function _removeKeywordMarkFromTag($tag, $subject)
    {
        if (preg_match_all("~(<\s*{$tag}(?:\b[^>]*)?>)(.+?)(<\s*/\s*{$tag}\s*>)~isu",
            $subject, $matches, PREG_SET_ORDER)
        ) {
            foreach ($matches as $match) {
                $replace = preg_replace('~' . self::TAG_OPEN . '(.*?)' . self::TAG_CLOSE . '~isu',
                    '${1}', $match[2]);
                if ($replace != $match[2]) {
                    $subject = str_replace($match[0], $match[1] . $replace . $match[3], $subject);
                }
            }
        }

        return $subject;
    }
}
