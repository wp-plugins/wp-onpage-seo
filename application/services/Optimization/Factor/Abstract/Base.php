<?php
require_once OPS_APPLICATION_PATH . '/services/Optimization/Plugin.php';

abstract class Ops_Service_Optimization_Factor_Abstract_Base
    extends Ops_Service_Optimization_Plugin
{
    protected $_visible = TRUE;
    protected $_label;
    protected $_value;
    protected $_selected;

    protected $_filterName;
    protected $_filterData = array();

    /**
    * Performs factor analysis
    */
    final public function analyze()
    {
        $result = $this->_analyze();
        $this->setValue($result);

        return $result;
    }

    protected function _analyze()
    {
        // Implementation
        return Ops_Service_Optimization::STATUS_NA;
    }

    public function optimize()
    {
    	return array(
    	   'name' => $this->_filterName,
    	   'data' => $this->_filterData,
    	);
    }

    public function prepareFilter()
    {
        return $this;
    }

    //
    // Getters & setters
    //

    public function getLabel()
    {
        return $this->_label;
    }

    public function getSelected() {
        return $this->_selected;
    }

    public function getValue()
    {
        return $this->_value;
    }

    public function setValue($value)
    {
        $this->_value = $value;

        return $this;
    }

    public function setSelected($value)
    {
        $this->_selected = $value;

        return $this;
    }

    public function getVisible()
    {
        return $this->_visible;
    }

    public function getFilterName()
    {
        return $this->_filterName;
    }

    public function getFilterData()
    {
        return $this->_filterData;
    }

    public function isConfigured()
    {
        return TRUE;
    }

    public function getDisplayData()
    {
        return array(
            'label'         => $this->getLabel(),
            'value'         => $this->getValue(),
            'selected'      => $this->getSelected(),
            'configured'    => $this->isConfigured(),
        );
    }
}