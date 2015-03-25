<?php
require_once OPS_APPLICATION_PATH
    . '/services/Optimization/Factor/Abstract/Decoration.php';

class Ops_Service_Optimization_Factor_LinkToHome
    extends Ops_Service_Optimization_Factor_Abstract_Decoration
{
    protected $_label = 'add link to homepage';

    protected $_filterName = 'Link';
    protected $_filterData = 'home';

    protected function _analyze()
    {
        $result = (int)$this->_parent->analyzeLink(home_url());
        $result = $this->_postAnalyze($result);

        return $result;
    }
}