<?php
require_once OPS_APPLICATION_PATH . '/services/Optimization/Filter/Abstract/Content.php';

class Ops_Service_Optimization_Filter_ImgName
    extends Ops_Service_Optimization_Filter_Abstract_Content
{
    protected $_replaceBaseUrl;

    protected $_allowedExtensions = array('jpg', 'jpeg', 'png', 'gif');
    protected $_defaultExtension = 'jpeg';

    //
    // Temporary data
    //
    protected $_imageIndex;
    protected $_keywordSlug;

    public function __invoke()
    {
        $content = $this->_parent->getData('content');

        $this->_keywordSlug = $this->_parent->prepareForUri($this->_parent->getData('keyword'));
        $this->_imageIndex = 0;

        $content = preg_replace_callback('~(<\s*img\b[^>]*\bsrc\s*=\s*[\'"])(.*?)([\'"><])~isu',
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

        $path = @parse_url($match[2], PHP_URL_PATH);
        if ('' == $path) {
            return $match[0];
        }

        $name = @pathinfo($path, PATHINFO_FILENAME);
        if ('' == $name) {
            return $match[0];
        }

        if ($this->_parent->analyzeKeywordInText($name, $this->_keywordSlug)) {
            return $match[0];
        }

        $ext = @pathinfo($path, PATHINFO_EXTENSION);
        if (!in_array($ext, $this->_allowedExtensions)) {
            $ext = $this->_defaultExtension;
        }

        $imageName = $this->_keywordSlug;
        if ($this->_imageIndex > 1) {
            $imageName .= "--{$this->_imageIndex}";
        }

        $url = $this->getReplaceBaseUrl() . $this->_parent->getPostId() . '/'
            . $imageName . '.' . $ext;

        return $match[1] . $url . $match[3];
    }

    public function getReplaceBaseUrl()
    {
        if (is_null($this->_replaceBaseUrl)) {
            $path = array(site_url());

            global $wp_rewrite;
            if (!$wp_rewrite->using_mod_rewrite_permalinks()) {
                $path[] = $wp_rewrite->index;
            }

            $path[] = Ops_WpPlugin::CLOACKED_IMAGE_FOLDER;

            $this->_replaceBaseUrl = implode('/', $path) . '/';
        }

        return $this->_replaceBaseUrl;
    }
}