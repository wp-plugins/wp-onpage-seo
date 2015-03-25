<?php
require_once OPS_APPLICATION_PATH . '/forms/AbstractForm.php';

class Ops_Form_Metabox extends Ops_Form_AbstractForm
{
    protected $_dataKeys = array(
        'keyword',
        'extra_content_mode',
    );
    protected $_postId;

    protected $_keywordReplaces = array(
        '’' => '\'',
    );

    public function load()
    {
        /**
        * @var Ops_Model_Options
        */
        $model = Ops_Application::getModel('Post_Meta')
            ->setPostId($this->_postId);

        $this->_values = array();

        $found = FALSE;
        foreach ($this->_dataKeys as $key) {
            $value = $model->getValue($key);
            if (!is_null($value)) {
                $found = TRUE;
            }
            $this->_values[$key] = $value;
        }

        if ($found) {
            $this->_validate();
        }

        return $this;
    }

    protected function _validate()
    {
        // keyword
        $value = $this->getValue('keyword');
        $this->_values['keyword'] = $this->_filterKeyword($value);

        // extra_content_mode
        $value = $this->getValue('extra_content_mode');
        if ('' != $value) {
            $multiOptions = Ops_Application::getService('Optimization')
                ->getExtraContentModeOptions();
            if (!isset($multiOptions[$value])) {
                $this->_errors[] = "Extra Content Keywords: value '{$value}' not supported.";
            }
        }
    }

    protected function _filterKeyword($value)
    {
        $value = stripslashes($value);
        $value = str_replace(array_keys($this->_keywordReplaces),
            array_values($this->_keywordReplaces), $value);
        return preg_replace('~\s+~u', ' ', trim($value));
    }

    /**
    * Accessors
    */

    public function setPostId($value)
    {
        $this->_postId = $value;
        return $this;
    }

    public function getPostId()
    {
        return $this->_postId;
    }

    public function getDataKeys()
    {
        return $this->_dataKeys;
    }
}