<?php
abstract class Ops_Form_AbstractForm
{
    protected $_values = array();
    protected $_errors = array();

    public function isValid()
    {
        $this->_errors = array();
        $this->_validate();

        return !$this->_errors;
    }

    protected function _validate()
    {
        //extensions
    }

    public function getValues()
    {
        return $this->_values;
    }

    public function setValues(array $data)
    {
        foreach ($data as $key=>$value) {
            $this->_values[$key] = $value;
        }

        return $this;
    }

    public function getValue($key)
    {
        return isset($this->_values[$key])? $this->_values[$key] : NULL;
    }

    public function setValue($key, $value)
    {
        $this->_values[$key] = $value;

        return $this;
    }

    public function getErrors()
    {
        return $this->_errors;
    }

    public function getError($key)
    {
        return isset($this->_errors[$key])? $this->_errors[$key] : NULL;
    }
}