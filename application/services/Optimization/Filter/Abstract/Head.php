<?php
require_once OPS_APPLICATION_PATH . '/services/Optimization/Filter/Abstract/Base.php';

abstract class Ops_Service_Optimization_Filter_Abstract_Head
    extends Ops_Service_Optimization_Filter_Abstract_Base
{
    protected $_filterType = 'head';
}