<?php

/**
* composeUrl() capability not used and could be removed with all related stuff
*/

class Ops_Model_Post_Query
{   
    // Parameter vocabulary
    // The order of elements defines the order parameters appear in URL 
     
    protected $_optionInfo = array(
        // URL params
        'paged' => array(
            'default' => 1,
            'url' => TRUE,
        ),
        'orderby' => array(
            'default' => 'date',
            'url' => TRUE,
            'multiOptions' => array('ID', 'title', 'keyword', 'date'),
            'allowDefaultInUrl' => TRUE,
        ),
        'order' => array(
            'default' => 'desc',
            'url' => TRUE,
            'multiOptions' => array('asc', 'desc'),
            'allowDefaultInUrl' => TRUE,
        ),
        'post_type' => array(
            'default' => 'post',
            'url' => TRUE,
            'allowDefaultInUrl' => TRUE,
        ),
        'cat' => array(
            'default' => 0,
            'url' => TRUE,
        ),
        'post_status' => array(
            'default' => 'any',
            'url' => TRUE,
        ), 
        's' => array(
            'default' => '',
            'url' => TRUE,
        ),
        
        // non-URL params
        'meta_value' => array(
            'default' => '',
            'url' => FALSE,
        ), 
        'suppress_filters' => array(
            'default' => TRUE,
            'url' => FALSE,
        ),
        'ignore_sticky_posts' => array(
            'default' => TRUE,
            'url' => FALSE,
        ),
    );
    
    protected $_options = array();
    
    // Sort orders that have to use meta fields (orderName => metaName)
    protected $_metaOrders = array(
        'keyword' => 'ocs_keyword',
    );
    
    protected $_queryObject;

    public function composeUrl($params=array())
    {
        $query = array();
        foreach ($this->_optionInfo as $name=>$info) {
            if (!$info['url']) {
                // Not allowed in URL
                continue;
            }
            
            if (array_key_exists($name, $params)) {
                $value = $params[$name];
                
                // Ignore default values:
                if (!is_null($value) && isset($info['default'])
                    && $value == $info['default']
                    && (!isset($info['allowDefaultInUrl']) || !$info['allowDefaultInUrl'])
                ) {
                    continue;
                }
                
                $query[$name] = $params[$name];
                continue;    
            }                
            
            if (isset($this->_options[$name])) {
                $query[$name] = $this->_options[$name];  
            }
        }

        return Ops_Application::url($query);
    }

    public function doQuery()
    {      
        $vars = array();
        foreach ($this->_optionInfo as $key=>$info) {
            $vars[$key] = $this->getOption($key);   
        }
        
        $optionsModel = Ops_Application::getModel('Options');
        $vars['posts_per_page'] = $optionsModel->getValue('posts_per_page');
        $vars['post_type'] = $optionsModel->getValue('post_type');  //?
        
        if (isset($this->_metaOrders[$vars['orderby']])) {
            $orderName = $vars['orderby'];
            $vars['meta_key'] = $this->_metaOrders[$vars['orderby']];
            $vars['orderby'] = 'meta_value';
            add_filter('get_meta_sql', array($this, 'filterMetaSql'), 10, 5);
        }

        $this->_queryObject = new WP_Query;         
        $this->_queryObject->query($vars);

        if ('meta_value' == $vars['orderby']) {           
            remove_filter('get_meta_sql', array($this, 'filterMetaSql'));
        }      
        
        // Wrong page
        if ($vars['paged'] > $this->_queryObject->max_num_pages) {
            unset ($this->_options['paged']);
            unset($vars['paged']);
            $this->_queryObject->query($vars);
        }

        return $this;
    }

    //
    // Filters
    //
    
    public function filterMetaSql(array $data, $meta_query, $meta_type, $primary_table, $primary_id_column)
    {      
        $str = trim($data['join']) . ' ' . $data['where'];        
        $data = array(
            'join' => str_replace('INNER', 'LEFT', $str),
            'where' => '',
        );   
        return $data;    
    } 
    
    //
    // Getters & setters
    //
    
    public function setOptions($options)
    {
        // paged have to be >= 1
        if (isset($options['paged'])) {
            $value = (int) $options['paged'];
            if ($value <= 0) {
                $value = NULL; 
            }
            
            $options['paged'] = $value; 
        }

        // cat have to be existing category id
        if (isset($options['cat']) && $options['cat']) {
            $value = (int) $options['cat'];
            $category = get_category($value);
            if (is_wp_error($category)) {
                $value = NULL;
            } 
            
            $options['cat'] = $value;  
        }
        
        foreach ($options as $name=>$value) {
            $this->setOption($name, $value);
        }

        return $this;
    }
    
    public function setOption($name, $value) 
    {
        if (!isset($this->_optionInfo[$name])) {
            // Option not supported
            return $this; 
        }   
        
        if (is_null($value)) {
            // Reset to default
            unset($this->_options[$name]);
        }
        
        $info = $this->_optionInfo[$name];
        if (isset($info['multiOptions']) 
            && !in_array($value, $info['multiOptions'])
        ) {
            // Value not allowed
            return $this; 
        }
        
        if (isset($this->_optionInfo[$name]['default']) 
            && $value == $info['default']
        ) {
            // Reset to default by removing option value
            unset ($this->_options[$name]);
            return $this; 
        }
        
        $this->_options[$name] = $value;
    }
    
    public function getOption($name)
    {        
        if (isset($this->_options[$name])) {
            return $this->_options[$name];
        } 
        
        if (isset($this->_optionInfo[$name]['default'])) {
            return $this->_optionInfo[$name]['default'];    
        }
        
        return NULL;
    }

    public function getQueryObject()
    {
        return $this->_queryObject;
    }
}