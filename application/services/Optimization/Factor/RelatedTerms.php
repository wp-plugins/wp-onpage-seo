<?php
require_once OPS_APPLICATION_PATH . '/services/Optimization/Factor/Abstract/Base.php';

class Ops_Service_Optimization_Factor_RelatedTerms
    extends Ops_Service_Optimization_Factor_Abstract_Base
{
    protected $_label = 'add related search terms';

    protected $_filterName = 'RelatedTerms';

    protected function _analyze()
    {
        $analyzer = $this->_parent->analyzeRelatedTerms();

        $result = $analyzer->getResult();
        $this->_filterData = array($analyzer->getTerms());

        return $result;
    }

    public function isConfigured()
    {
        return '' != Ops_Application::getModel('Options')
            ->getValue('bing_api_key');
    }
}