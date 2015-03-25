<?php
require_once OPS_APPLICATION_PATH
    . '/services/Optimization/Factor/Abstract/Base.php';

abstract class Ops_Service_Optimization_Factor_Abstract_Decoration
    extends Ops_Service_Optimization_Factor_Abstract_Base
{
    protected function _postAnalyze($result)
    {
        if (Ops_Service_Optimization::STATUS_NO == $result
            && 'disabled' == $this->_parent->getData('extra_content_mode')
        ) {
            $count = $this->_parent->getData('keyword_count');
            if ($count <= 0) {
                $result = Ops_Service_Optimization::STATUS_NA;
            } else if ($this->_selected) {
                $this->_parent->setData('keyword_count', $count-1);
            }
        }

        return $result;
    }
}