<?php
require_once OPS_APPLICATION_PATH
    . '/services/Optimization/Factor/Abstract/Header.php';

class Ops_Service_Optimization_Factor_H3Contain
    extends Ops_Service_Optimization_Factor_Abstract_Header
{
    protected $_tag = 'h3';
    protected $_label = 'H3';
    protected $_filterData = array('h3');
}
