<?php
require_once OPS_APPLICATION_PATH
    . '/services/Optimization/Factor/Abstract/Header.php';

class Ops_Service_Optimization_Factor_H1Contain
    extends Ops_Service_Optimization_Factor_Abstract_Header
{
    protected $_tag = 'h1';
    protected $_label = 'H1';
    protected $_filterData = array('h1', 'header');
}
