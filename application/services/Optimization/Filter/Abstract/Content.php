<?php
require_once OPS_APPLICATION_PATH . '/services/Optimization/Filter/Abstract/Base.php';

abstract class Ops_Service_Optimization_Filter_Abstract_Content
    extends Ops_Service_Optimization_Filter_Abstract_Base
{
    protected $_filterType = 'content';
}