<?php
require_once OPS_APPLICATION_PATH
    . '/services/Optimization/Factor/Abstract/Header.php';

class Ops_Service_Optimization_Factor_H2Contain
    extends Ops_Service_Optimization_Factor_Abstract_Header
{
    protected $_tag = 'h2';
    protected $_label = 'H2';
    protected $_filterData = array('h2');
}
