<?php
require_once OPS_APPLICATION_PATH . '/services/Optimization/Plugin.php';

abstract class Ops_Service_Optimization_Filter_Abstract_Base
    extends Ops_Service_Optimization_Plugin
{
    protected $_filterType;

    /**
    * Performs content filtering
    */
    /*
    public function __invoke()
    {
        //Implementation
    }
    */

    public function processQueue(array $queue)
    {
        foreach ($queue as $item) {
            call_user_func_array(array($this, '__invoke'), (array) $item);
        }

        return $this;
    }

    public function getFilterType()
    {
    	return $this->_filterType;
    }
}