<?php
require_once OPS_APPLICATION_PATH . '/forms/AbstractForm.php';

abstract class Ops_Form_Admin extends Ops_Form_AbstractForm
{
    protected $_dataKeys = array();
    protected $_savedDataKeys = array();

    public function getDataKeys()
    {
        return $this->_dataKeys;
    }

    public function import(array $data)
    {
        foreach ($this->_dataKeys as $key) {
            if (isset($data[$key])) {
                $this->setValue($key, $data[$key]);
            }
        }

        return $this;
    }

    public function load()
    {
        /**
        * @var Ops_Model_Options
        */
        $model = Ops_Application::getModel('Options');

        $this->_values = array();
        foreach ($this->_savedDataKeys as $key) {
            $this->_values[$key] = $model->getValue($key);
        }

        return $this;
    }

    public function save()
    {
        $model = Ops_Application::getModel('Options');

        foreach ($this->_savedDataKeys as $key) {
            $model->setValue($key, $this->getValue($key));
        }

        return $this;
    }
}