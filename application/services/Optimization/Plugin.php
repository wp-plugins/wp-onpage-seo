<?php
abstract class Ops_Service_Optimization_Plugin
{
    /**
    * @var Ops_Service_Optimization
    */
    protected $_parent;

    public function __construct()
    {
        $this->init();
    }

    public function init()
    {
        // Extensions
    }

    public function setParent($parent)
    {
        $this->_parent = $parent;

        return $this;
    }

    /**
    * Remove cyclic references to free memory in PHP < 5.2
    */
    public function dispose()
    {
        $this->_parent = NULL;
    }
}