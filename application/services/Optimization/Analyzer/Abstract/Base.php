<?php
require_once OPS_APPLICATION_PATH . '/services/Optimization/Plugin.php';

abstract class Ops_Service_Optimization_Analyzer_Abstract_Base
    extends Ops_Service_Optimization_Plugin  
{
    /** 
    * Performs factor analysis 
    */
    public function __invoke()
    {
        return Ops_Service_Optimization::STATUS_NA;
    }
}