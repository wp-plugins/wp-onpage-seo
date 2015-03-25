<?php
require_once OPS_APPLICATION_PATH . '/controllers/Abstract/Generic.php'; 

abstract class Ops_Controller_Abstract_Ajax
    extends Ops_Controller_Abstract_Generic
{
    // Do not support view scripts
    public function setViewScript($value)
    {
        //$this->_viewScript = $value;
        return $this;
    }        
}