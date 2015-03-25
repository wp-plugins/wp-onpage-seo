<?php
require_once OPS_APPLICATION_PATH . '/forms/Admin.php';

class Ops_Form_Admin_Optimization extends Ops_Form_Admin
{
    protected $_savedDataKeys = array(
        'factors',
        'extra_content_mode-mass'
    );

    protected $_dataKeys = array(
        'factors',
        'selected',
        'keyword',
        'extra_content_mode-mass',
    );

    public function import(array $data)
    {
        // Allow empty factor selection
        if (!isset($data['factors'])) {
            $data['factors'] = array();
        }

        return parent::import($data);
    }

    public function load()
    {
        parent::load();

        $factors = $this->getValue('factors');

        if (!is_array($factors)) {
            // All factors selected by default
            $this->setValue('factors', Ops_Application::getService('Optimization')->getDefaultFactorNames());
        }

        return $this;
    }

    protected function _validate()
    {
        // selected
        if (!$this->getValue('selected')) {
            $this->_errors[] = 'Please select some posts to optimize.';
        }

        // keyword
        $value = (array) $this->getValue('keyword');
        foreach ($value as $id=>$keyword) {
            $value[$id] = $this->_filterKeyword($keyword);
        }
        $this->_values['keyword'] = $value;

        // extra_content_mode
        $value = $this->getValue('extra_content_mode-mass');
        if ('' != $value) {
            $multiOptions = Ops_Application::getService('Optimization')
                ->getExtraContentModeOptions();
            if (!isset($multiOptions[$value])) {
                $this->_errors[] = "Extra Content Keywords: value '{$value}' not supported.";
            }
        }
    }

    public function getPostKeywords()
    {
        $keywords = array();
        foreach ((array)$this->_values['selected'] as $postId) {
            if (isset($this->_values['keyword'][$postId])) {
                $keywords[$postId] = $this->_values['keyword'][$postId];
            }
        }
        return $keywords;
    }

    public function getFactors()
    {
        return (array)$this->_values['factors'];
    }

    protected function _filterKeyword($value)
    {
        return preg_replace('~\s+~u', ' ', trim($value));
    }
}