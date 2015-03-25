<?php
class Ops_Model_Post_Url
{ 
    public function get($post)
    {                
        list($permalink, $postName) = get_sample_permalink($post->ID);
        
        return str_replace(array('%pagename%','%postname%'), $postName, $permalink);
    } 
}
