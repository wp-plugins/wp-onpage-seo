<?php
abstract class Ops_Controller_Abstract_Generic
{        
    protected $_view;
    protected $_viewScript;
    protected $_layoutScript;
    
    protected $_params = array();
    
    public final function __construct()
    {       
        //Init view engine
        require_once(OPS_APPLICATION_PATH . '/views/' . 'Engine.php');
        $this->_view = new Ops_View_Engine(OPS_APPLICATION_PATH . '/views/scripts');
        
        $this->init();
    }
    
    public function init()
    {       
    }
    
    public function preDispatch(&$action)
    {
    }
    
    public function postDispatch($action)
    {
    }
    
    //
    // Accessors
    //
    
    public function getView()
    {
        return $this->_view;
    }
    
    public function getLayoutScript()
    {
        return $this->_layoutScript;
    }
    
    public function setViewScript($value)
    {
        $this->_viewScript = $value;
        return $this;
    }
    
    public function getViewScript()
    {
        return $this->_viewScript;
    }
    
    public function getParams()
    {
        return $this->_params;
    }
    
    public function getParam($key)
    {
        return isset($this->_params[$key])? $this->_params[$key] : NULL;    
    }
    
    public function setParams(array $value)
    {
        $this->_params = $value;
    }
}