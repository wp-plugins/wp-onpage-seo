<?php
require_once OPS_APPLICATION_PATH
    . '/services/Optimization/Factor/Abstract/Decoration.php';

class Ops_Service_Optimization_Factor_LinkToPost
    extends Ops_Service_Optimization_Factor_Abstract_Decoration
{
    protected $_label = 'add link to this post';

    protected $_filterName = 'Link';
    protected $_filterData = 'post';

    protected function _analyze()
    {
        $result = (int)$this->_parent->analyzeLink(
            get_permalink($this->_parent->getPostId()));
        $result = $this->_postAnalyze($result);

        return $result;
    }
}