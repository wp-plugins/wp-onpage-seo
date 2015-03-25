<?php
class Ops_Service_Optimization
{
    protected $_namesapces = array(
        'factor' => 'Optimization_Factor',
        'analyzer' => 'Optimization_Analyzer',
        'filter' => 'Optimization_Filter',
        'helper' => 'Optimization_Helper',
    );

    const STATUS_NO = 0;
    const STATUS_YES = 1;
    const STATUS_NA = 2;

    // Array order defines execution order
    protected $_factorNames = array(
        'MetaTitle',
        'MetaKeywords',
        'MetaDescription',
        'Permalink',
        'H1Contain',
        'H2Contain',
        'H3Contain',
        'ImgNameContain',
        'ImgAltContain',
        'DecorationBold',
        'DecorationItalic',
        'DecorationUnderlined',
        'LinkToPost',
        'LinkToHome',
        'RelatedTerms',
    );

    // Factors not to be selected by default
    protected $_nonDefaultFactors = array(
        'Permalink',
    );

    // Array order defines execution order
    protected $_filterNames = array(
        'MetaTitle',
        'MetaKeywords',
        'MetaDescription',
        'Tag',
        'ImgName',
        'ImgAlt',
        'RelatedTerms',
        'Decoration',
        'Link',
    );

    protected $_factors;
    protected $_helpers = array();
    protected $_analyzers = array();

    protected $_filterQueue;

    protected $_postId;
    protected $_data = array();
    protected $_dynamicData = array();

    protected $_errors = array();

    protected $_extraContentModeOptions = array(
        'throughout' => 'Use extra keywords throughout the content',
        'bottom' => 'Use extra keywords at the bottom of the content',
        'disabled' => 'Don\'t add extra keywords in the content',
    );

    protected $_extraContentModeOptionsShort = array(
        'throughout' => 'throughout the content',
        'bottom' => 'at the bottom of the content',
        'disabled' => 'don\'t add extra keywords',
    );

    protected $_homeHeadFields = array(
        'home_meta_title' => array(
            'name' => 'title',
            'helper' => 'AddHeadTag',
        ),
        'home_meta_description' => array(
            'name' => 'description',
            'helper' => 'AddMetaTag',
        ),
        'home_meta_keywords' => array(
            'name' => 'keywords',
            'helper' => 'AddMetaTag',
        ),
    );

    public function optimize($data)
    {
        $this->clearErrors();

        if (isset($data['keyword']) && '' != trim($data['keyword'])
            && (!isset($data['selected']) || $data['selected'])
        ) {
            if (!isset($data['selected'])) {
                $data['selected'] = $this->getDefaultFactorNames();
            }

            $this->analyze($data)
                ->save();
            return TRUE;
        }

        $this->setPostId($data['post']->ID)
            ->clear();

        return FALSE;
    }

    public function analyze($data)
    {
        $this->clearErrors();

        $this->_loadFactors();

        $this->setPostId($data['post']->ID);

        $data['html'] = $this->purifyHtml(
            Ops_Application::getModel('Post_Html')->get($data['post']));
        $data['url'] =
            Ops_Application::getModel('Post_Url')->get($data['post']);

        $data['post_html'] = $this->purifyPost(
            Ops_Application::getModel('Post_Content')
                ->get($data['post'])
        );
        $data['post_text'] = $this->htmlToText($data['post_html']);

        $data['head'] = $this->ExtractHtmlHead($data['html']);

        $this->_data = $data;
        unset($data);

        $this->_initDynamicData();

        $this->_data['keyword_count'] = $this->getHelper('MarkKeyword')
            ->countKeywords($this->_data['post_html']);

        $this->_filterQueue = array();

        foreach ($this->_factors as $name => $factor) {
            $result = $factor->setSelected(in_array($name, $this->_data['selected']))
                ->analyze();

            if (self::STATUS_NO == $result && $factor->getSelected()) {

                $factorData = $factor->optimize();

                if (is_array($factorData) && $filterName = $factorData['name']) {
                    if (!isset($this->_filterQueue[$filterName])) {
                        $this->_filterQueue[$filterName] = array();
                    }
                    $this->_filterQueue[$filterName][] = (array)$factorData['data'];
                }
            }
        }

        return $this;
    }

    public function clear()
    {
        $meta = Ops_Application::getModel('Post_Meta')->setPostId($this->_postId)
            ->unsetValue('keyword')
            ->unsetValue('data')
            ->unsetValue('filters')
            ->unsetValue('errors')
            ->unsetValue('related_terms_cache')
            ->unsetValue('extra_content_mode');

        return $this;
    }

    protected function _preFilter(&$params)
    {
        if (!current_user_can('administrator')) {
            return FALSE;
        }

        $meta = Ops_Application::getModel('Post_Meta')->setPostId($this->_postId);
        $options = Ops_Application::getModel('Options');

        $keyword = $meta->getValue('keyword');
        if ('' == $keyword) {
            return FALSE;
        }

        $queue = $meta->getValue('filters');
        if (!$queue || !is_array($queue)) {
            return FALSE;
        }

        /**
        * Backwards compatibility
        */
        if ($meta->getValue('filters_version') < '02.03.00') {
            //'related_terms_cache'
            if (isset($queue['RelatedTerms'])) {
                $termsCache = $meta->getValue('related_terms_cache');
                if (isset($termsCache['keyword'])
                    && $keyword == $termsCache['keyword']
                ) {
                    $terms = (array) $termsCache['data'];
                } else {
                    try {
                        $terms = $this->getRelatedTerms($keyword);
                    } catch (Exception $e) {
                        // Supress errors
                        $terms = array();
                    }

                }

                $queue['RelatedTerms'][0] = array($terms);

                $meta->setValue('filters', $queue);
            }
            $meta->unsetValue('related_terms_cache');
            $meta->setValue('filters_version', '02.03.00');
        }

        $params['queue'] = $queue;
        $params['content'] = wp_check_invalid_utf8($params['content'], TRUE);

        $extraContentMode = $meta->getValue('extra_content_mode');
        if ('' == $extraContentMode) {
            $extraContentMode = $options->getValue('extra_content_mode');
        }

        $this->_data = array(
            'keyword' => $keyword,
            'content' => $params['content'],
            'header' => '',
            'append_lines' => array(),
            'footer' => '',
            'extra_content_mode' => $extraContentMode,
            'post' => get_post($this->_postId),
        );

        $this->_initDynamicData();

        return TRUE;
    }

    public function filterHead($params)
    {
        if ($this->_preFilter($params)) {
            $this->_applyFilters($params['queue'], $params['type']);
            return $this->_postFilterHead($this->getData('content'));
        }

        return $params['content'];
    }

    public function filterContent($params)
    {
        if ($this->_preFilter($params)) {
            $marker = $this->markKeyword();

            $this->_applyFilters($params['queue'], $params['type']);

            $marker->cleanUp();

            $content = $this->getData('content');
            if ($appendLines = $this->getData('append_lines')) {
                $content = $this->_mixContent($content, $appendLines);
            }

            return $this->getData('header')
                . $content
                . $this->getData('footer');
        }

        return $params['content'];
    }

    /**
    * Mixes content with additional elements by inserting one element after each
    * paragraph
    *
    * @param string $content
    * @param array $addLines
    */
    protected function _mixContent($content, array $appendLines)
    {
        $mode = $this->getData('extra_content_mode');
        if ('disabled' == $mode) {
            return $content;
        }

        // Break content into paragraphs by end paragraph tag
        $paragraphs = preg_split('~<\s*/\s*p\s*>~isu', trim($content), NULL,
            PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);

        if ('bottom' != $mode) {
            // Iterate through all paragraphs except the last one
            $count = count($paragraphs);
            for ($i = 0; $i < $count-1 && $appendLines; $i++) {
                $lineInfo = array_shift($appendLines);
                $line = $lineInfo[0];
                if ($lineInfo[1]) {
                    // Inline element
                    $line = "<p>{$line}</p>";
                }
                $paragraphs[$i] .= "\n" . $line;
            }
        }

        // If any lines left add them separated with line breaks
        if ($appendLines) {
            $lines = array();
            $blocks = array();
            foreach ($appendLines as $i=>$lineInfo) {
                if ($lineInfo[1]) {
                    $lines[] = $lineInfo[0];
                } else {
                    $blocks[] = $lineInfo[0];
                }
            }

            if ($lines) {
                $lines = implode("<br />\n", $lines);
                $paragraphs[] = "\n<p>{$lines}</p>";
            }

            if ($blocks) {
                $paragraphs[] = implode("\n", $blocks);
            }
        }

        return implode('', $paragraphs) . "\n";
    }

    protected function _applyFilters($queue, $contentType)
    {
        foreach ($this->_filterNames as $name) {
            if (!isset($queue[$name]) || !is_array($queue[$name])) {
                continue;
            }

            $filter = $this->getFilter($name);
            if ($contentType == $filter->getFilterType()) {
                $filter->processQueue($queue[$name]);
            }

            $filter->dispose();
        }
    }

    public function filterHomeHead($params)
    {
        if (!current_user_can('administrator')) {
            return $params['content'];
        }

        $this->_data = $params;

        $options = Ops_Application::getModel('Options');

        foreach ($this->_homeHeadFields as $key=>$info) {
            $value = $options->getValue($key);
            if ('' != $value) {
                $this->getHelper($info['helper'])->__invoke($info['name'], $value);
            }
        }

        return $this->_postFilterHead($this->_data['content']);
    }

    protected function _postFilterHead($head)
    {
        return trim($head) . "\n";
    }

    public function load()
    {
        $this->_loadFactors();

        $meta = Ops_Application::getModel('Post_Meta')->setPostId($this->_postId);
        $data = (array) $meta->getValue('data');
        $this->setData('keyword', $meta->getValue('keyword'));
        $defaultFactors = $this->getDefaultFactorNames();
        foreach ($this->_factors as $name=>$factor) {
            $factor->setValue(isset($data[$name]['value'])
                ? $data[$name]['value']
                : NULL
            );
            $factor->setSelected(isset($data[$name]['selected'])
                ? $data[$name]['selected']
                : in_array($name, $defaultFactors)
            );
        }

        return (bool) $data;
    }

    public function save()
    {
        $data = array();
        foreach ($this->_factors as $name=>$factor) {
            $data[$name] = array(
                'value' => $factor->getValue(),
                'selected' => $factor->getSelected(),
            );
        }

        Ops_Application::getModel('Post_Meta')->setPostId($this->_postId)
            ->setValue('data', $data)
            ->setValue('filters', $this->_filterQueue)
            ->setValue('filters_version', '02.03.00')
            ->setValue('keyword', $this->_data['keyword'])
            ->setValue('extra_content_mode', $this->_data['extra_content_mode']);

        return $this;
    }

    public function getDisplayData()
    {
        $this->_loadFactors();

        $factors = $this->getFactors();

        $result = array();
        $i = 0;
        foreach ($factors as $name=>$factor) {
            if (!$factor->getVisible()
            //    || ($skipNull && is_null($factor->getValue()))
            ) {
                continue;
            }

            $data = $factor->getDisplayData();
            $data['name'] = $this->_factorNames[$i];
            $result[$data['name']] = $data;

            $i++;
        }

        return $result;
    }

    // Plugin object factory
    protected function _loadPlugin($type, $name)
    {
        return Ops_Application::getService($this->_namesapces[$type] . '_' . $name)
            ->setParent($this);
    }

    protected function _loadFactors()
    {
        if (!$this->_factors) {
            foreach ($this->_factorNames as $name) {
                $this->_factors[$name] = $this->_loadPlugin('factor', $name);
            }
        }

        return $this;
    }

    public function getAnalyzer($name)
    {
        $name = ucfirst($name);

        if (!isset($this->_analyzers[$name])) {
            $this->_analyzers[$name] = $this->_loadPlugin('analyzer', $name);
        }

        return $this->_analyzers[$name];
    }

    public function getFilter($name)
    {
        return $this->_loadPlugin('filter', $name);
    }

    public function getHelper($name)
    {
        $name = ucfirst($name);

        if (!isset($this->_helpers[$name])) {
            $this->_helpers[$name] = $this->_loadPlugin('helper', $name);
        }

        return $this->_helpers[$name];
    }

    public function __call($method, $arguments)
    {
        if ('analyze' == substr($method, 0, 7)) {
            $plugin = $this->getAnalyzer(substr($method, 7));
        } else {
            $plugin = $this->getHelper($method);
        }

        return call_user_func_array(array($plugin, '__invoke'), $arguments);
    }

    /**
    * Messages
    */

    public function getErrors()
    {
        return $this->_errors;
    }

    public function clearErrors()
    {
        $this->_errors = array();

        return $this;
    }

    public function addError($message)
    {
        $this->_errors = $message;
    }

    //
    // Getters & setters
    //

    public function getFactorNames()
    {
        return $this->_factorNames;
    }

    public function getDefaultFactorNames($allowOption=TRUE)
    {
        if ($allowOption) {
            $result = Ops_Application::getModel('Options')->getValue('default_factors');
            if (is_array($result)) {
                return $result;
            }
        }

        return array_diff($this->_factorNames, $this->_nonDefaultFactors);
    }

    public function getFactors()
    {
        $this->_loadFactors();
        return $this->_factors;
    }

    public function setPostId($value)
    {
        $this->_postId = $value;
        return $this;
    }

    public function getPostId()
    {
        return $this->_postId;
    }

    public function getData($key)
    {
        if (array_key_exists($key, $this->_dynamicData)) {
            return $this->_dynamicData[$key];
        }

        if (!array_key_exists($key, $this->_data)) {
            throw new Exception("Key '{$key}' not found");
        }

        return $this->_data[$key];
    }

    public function setData($key, $value)
    {
        $this->_data[$key] = $value;

        return $this;
    }

    protected function _initDynamicData()
    {
        $options = Ops_Application::getModel('Options');

        $dynamicData = array();
        if ('{title}' == $this->_data['keyword']) {
            $dynamicData['keyword'] = $this->_data['post']->post_title;
        }
        if (!isset($this->_data['extra_content_mode']) || '' == $this->_data['extra_content_mode']) {
            $dynamicData['extra_content_mode'] = $options->getValue('extra_content_mode');
        }

        $keyword = $this->getData('keyword');
        $dynamicData['keyword_text'] = $this->htmlEscape(ucwords($keyword));
        $dynamicData['keyword_pattern'] = $this->getKeywordPattern($keyword);

        $this->_dynamicData = $dynamicData;
    }

    public function appendArrayData($key, $value)
    {
        $this->_data[$key][] = $value;

        return $this;
    }

    public function addStringData($key, $value, $prepend=FALSE)
    {
        $this->_data[$key] = $prepend
            ? $value . $this->_data[$key]
            : $this->_data[$key] . $value;

        return $this;
    }

    public function getExtraContentModeOptions($short=FALSE)
    {
        return $short
            ? $this->_extraContentModeOptionsShort
            : $this->_extraContentModeOptions;
    }

    public function getDefaultKeyword()
    {
        $options = Ops_Application::getModel('Options');
        $mode = $options->getValue('auto_optimization');
        $result = NULL;
        switch ($mode) {
            case 'title':
                $result = '{title}';
                break;
            case 'keyword':
                $result = $options->getValue('auto_optimization_keyword');
                break;
        }

        return $result;
    }

    //
    // Handlers
    //

    public function dispose()
    {
        foreach ($this->_helpers as &$plugin) {
            $plugin->dispose();
            $plugin = NULL;
        }

        foreach ($this->_analyzers as &$plugin) {
            $plugin->dispose();
            $plugin = NULL;
        }

        if ($this->_factors) {
            foreach ($this->_factors as &$plugin) {
                $plugin->dispose();
                $plugin = NULL;
            }
        }
    }

    //
    // Singleton manifest
    //

    static public function isSingleton()
    {
        return TRUE;
    }
}