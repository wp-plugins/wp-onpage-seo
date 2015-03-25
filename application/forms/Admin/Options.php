<?php
require_once OPS_APPLICATION_PATH . '/forms/Admin.php';

class Ops_Form_Admin_Options extends Ops_Form_Admin
{
    protected $_dataKeys = array(
        'bing_api_key',
        'extra_content_mode',
        'auto_optimization',
        'auto_optimization_keyword',
        'default_factors',
        'home_meta_title',
        'home_meta_description',
        'home_meta_keywords',
    );

    protected $_savedDataKeys = array(
        'bing_api_key',
        'extra_content_mode',
        'auto_optimization',
        'auto_optimization_keyword',
        'default_factors',
        'home_meta_title',
        'home_meta_description',
        'home_meta_keywords',
        'filter_home_head',
    );

    protected $_autoOptimizationOptions = array(
        'disabled' => 'Don\'t optimize future posts and pages automatically',
        'title' => 'Optimize future posts and pages automatically, for their title',
        'keyword' => 'Optimize future posts and pages automatically for this keyword',
    );

    protected function _validate()
    {
        // bing_api_key
        $value = $this->getValue('bing_api_key');
        $this->_values['bing_api_key'] = trim(str_replace(array("\r", "\n"), '', $value));

        // extra_content_mode
        $value = $this->getValue('extra_content_mode');
        if ('' == $value) {
            $this->_errors[] = 'Extra Content Keywords: the value is required and cannot be empty.';
        } else {
            $multiOptions = Ops_Application::getService('Optimization')
                ->getExtraContentModeOptions();
            if (!isset($multiOptions[$value])) {
                $this->_errors[] = "Extra Content Keywords: value '{$value}' not supported.";
            }
        }

        // auto_optimization
        $value = $this->getValue('auto_optimization');
        if (!isset($this->_autoOptimizationOptions[$value])) {
            $this->_errors['auto_optimization'] = "Automatic Future SEO: value '{$value}' not supported.";
            $value = 'disabled';
        }
        $this->_values['auto_optimization'] = $value;

        // auto_optimization_keyword
        if ('keyword' == $this->getValue('auto_optimization')) {
            $value = $this->getValue('auto_optimization_keyword');
            $value = trim(str_replace(array("\r", "\n"), '', $value));
            $value = $this->_filterKeyword($value);
            if ('' == $value) {
                $this->_errors['auto_optimization_keyword'] = 'Optimize future posts and pages automatically for this keyword: This value is required and cannot be empty.';
            }
        } else {
            $value = NULL;
        }
        $this->_values['auto_optimization_keyword'] = $value;

        // default_factors
        $value = (array) $this->getValue('default_factors');
        $factors = Ops_Application::getService('Optimization')->getFactorNames();
        foreach ($value as $key=>$factor) {
            if (!in_array($factor, $factors)) {
                unset($value[$key]);
            }
        }
        $this->_values['default_factors'] = $value;

        if (!$value) {
            $this->_errors['default_factors'] = 'Default SEO Factors: At least one factor must be selected.';
        }

        // meta fields
        $enabled = FALSE;
        $fields = array(
            'home_meta_title',
            'home_meta_description',
            'home_meta_keywords',
        );
        foreach ($fields as $key) {
            $value = $this->getValue($key);
            $value = trim(str_replace(array("\r", "\n"), '', $value));
            if ('' == $value) {
                $value = NULL;
            } else {
                $enabled = TRUE;
            }
            $this->_values[$value] = $value;
        }
        $this->_values['filter_home_head'] = $enabled;
    }

    //
    // Overrides
    //

    public function load()
    {
        parent::load();

        $factors = $this->getValue('default_factors');

        if (!is_array($factors)) {
            // All factors selected by default
            $this->setValue('default_factors',
                Ops_Application::getService('Optimization')->getDefaultFactorNames(FALSE));
        }

        return $this;
    }

    public function save()
    {
        $options = Ops_Application::getModel('Options');
        $oldFactors = (array) $options->getValue('default_factors');

        parent::save();

        $factors = $this->getValue('default_factors');

        if ($oldFactors != $factors) {
            $options->setValue('factors', $factors);
        }

        return $this;
    }

    public function import(array $data)
    {
        if (!isset($data['default_factors'])) {
            $data['default_factors'] = array();
        }

        return parent::import($data);
    }

    //
    // Helpers
    //

    protected function _filterKeyword($value)
    {
        return preg_replace('~\s+~u', ' ', trim($value));
    }

    //
    // Getters and setters
    //

    public function getAutoOptimizationOptions()
    {
        return $this->_autoOptimizationOptions;
    }
}