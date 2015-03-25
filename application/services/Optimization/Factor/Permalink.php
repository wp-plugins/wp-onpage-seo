<?php
require_once OPS_APPLICATION_PATH
    . '/services/Optimization/Factor/Abstract/Base.php';

class Ops_Service_Optimization_Factor_Permalink
    extends Ops_Service_Optimization_Factor_Abstract_Base
{
    protected $_label = 'permalink';

   // protected $_filterName = 'Permalink';
   // protected $_filterData = array('permalink');

    protected function _analyze()
    {
        return $this->_parent->analyzePermalink();
    }

    public function optimize()
    {
        $post = $this->_parent->getData('post');
        $post->post_name = $this->_parent->prepareForUri(
            $this->_parent->getData('keyword'));

        $result = wp_update_post($post);

        if (is_a($result, 'WP_Error')) {
            $this->_throwError("Oops! Wordpress didn't allow this post to be saved due to error: {$result->get_error_message()}. Please contact your hosting support.");
        }
        if (!$result) {
            global $wpdb;
            if ('' != $wpdb->last_error) {
                $this->throwError("Oops! This post was not saved due to MySQL error: {$wpdb->last_error}. Please contact your hosting support.");
            }
            $this->throwError('Oops! Unexpected error: This post was not saved. Please contact support.');
        }

        $this->_value = Ops_Service_Optimization::STATUS_YES;

        return NULL;
    }
}
