<?php
require_once OPS_APPLICATION_PATH . '/services/Optimization/Factor/Abstract/Base.php';

class Ops_Service_Optimization_Factor_ImgNameContain
    extends Ops_Service_Optimization_Factor_Abstract_Base
{
    protected $_label = 'image name';

    protected $_filterName = 'ImgName';

    protected function _analyze()
    {
        if (!$this->_parent->imageExists()) {
            return Ops_Service_Optimization::STATUS_NA;
        }

        return $this->_parent->analyzeImgNameContain();
    }
}