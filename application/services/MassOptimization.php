<?php
/**
* @unused
*/
class Ops_Service_MassOptimization
{
    protected $_counters = array();

    protected $_form;

    public function optimize($keywords, $factors)
    {
        @set_time_limit(600);

        $this->_counters = array(
            'cleared' => 0,
            'optimized' => 0,
        );

        foreach ($keywords as $postId=>$keyword) {
            $this->optimizePost($postId, $keyword, $factors);

            // Count no factors as clearing:
            if ('' == $keyword || !$factors) {
                $this->_counters['cleared']++;
            } else {
                $this->_counters['optimized']++;
            }
        }

        return $this;
    }

    public function optimizePost($postId, $keyword, $factors, $extraContentMode)
    {
        $data['keyword'] = $keyword;
        $data['post'] = get_post($postId);
        $data['extra_content_mode'] = $extraContentMode;

        if (!($data['post'])) {
            require_once OPS_APPLICATION_PATH . '/services/Exception.php';
            throw new Ops_Service_Exception("Invalid post ID {$postId}");
        }
        if ('trash' == $data['post']->post_status) {
            require_once OPS_APPLICATION_PATH . '/services/Exception.php';
            throw new Ops_Service_Exception("Cannot process post with ID {$postId}. The post has been moved to trash.");
        }

        $optimization = Ops_Application::getService('Optimization');

        $allFactors = $optimization->getFactorNames();

        $data['selected'] = array();
        foreach ($allFactors as $factor) {
            if (in_array($factor, $factors)) {
                $data['selected'][] = $factor;
            }
        }

        return $optimization->optimize($data);
    }

    public function getCounters()
    {
        return $this->_counters;
    }
}