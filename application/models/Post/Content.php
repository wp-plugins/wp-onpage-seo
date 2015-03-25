<?php
class Ops_Model_Post_Content
{
    public function get($post)
    {
        global $wp_query, $wp_the_query;

        $oldQuery = $wp_the_query;

        $wp_query = $wp_the_query = new WP_Query();
        $wp_query->query(array(
            'p' => $post->ID,
            'post_type' => $post->post_type,
            'post_status' => $post->post_status,
            'preview' => TRUE,
        ));

        if (!have_posts()) {
            $msg = "Error getting content of post ID '{$post->ID}'";

            global $wpdb;
            if ('' != $wpdb->last_error) {
                $msg = "Error getting content of post ID '{$post->ID}' due to database error: {$wpdb->last_error}";
            }

            require_once OPS_APPLICATION_PATH . '/services/Exception.php';
            throw new Ops_Service_Exception($msg);
        }

        the_post();

        // To make "add-to-any" plugin happy
        get_the_title();

        ob_start();
        the_content();
        $result = ob_get_clean();

        $wp_query = $wp_the_query = $oldQuery;

        return wp_check_invalid_utf8($result, TRUE);
    }
}