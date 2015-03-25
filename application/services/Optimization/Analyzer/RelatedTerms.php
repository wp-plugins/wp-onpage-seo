<?php
require_once OPS_APPLICATION_PATH . '/services/Optimization/Analyzer/Abstract/Base.php';

class Ops_Service_Optimization_Analyzer_RelatedTerms
    extends Ops_Service_Optimization_Analyzer_Abstract_Base
{
    protected $_result;
    protected $_terms;

    public function __invoke()
    {
        try {
            $this->_terms = $this->_parent->getRelatedTerms();
        } catch (Ops_Service_Exception $e) {
            $this->_parent->addError("Error gettng related terms: {$e->getMessage()}");
        }
        $this->_result = $this->_analyze();

        return $this;
    }

    protected function _analyze()
    {
        if (!$this->_terms) {
            return Ops_Service_Optimization::STATUS_NA;
        }

        $text = $this->_parent->getData('post_text');
        foreach ($this->_terms as $term) {
            if (!preg_match('~\b(' . $this->_parent->getKeywordPattern($term) . ')\b~iu', $text)) {
                // At least one of the terms not found
                return Ops_Service_Optimization::STATUS_NO;
            }
        }

        return Ops_Service_Optimization::STATUS_YES;
    }

    public function getResult()
    {
        return $this->_result;
    }

    public function getTerms()
    {
        return $this->_terms;
    }
}