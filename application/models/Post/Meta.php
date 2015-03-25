<?php
class Ops_Model_Post_Meta
{
    const PREFIX = Ops_WpPlugin::PREFIX;     
    protected $_defaults = array();
    
    protected $_postId;
    
    public function getValue($name)
    {
        $this->_checkPostId();
        
        $result = get_post_meta($this->_postId, self::PREFIX . '_' . $name, TRUE);  
        if ('' == $result) {
            $result = $this->getDefault($name);
        }  
        
        return $result;
    }
    
    public function setValue($name, $value)
    {
        $this->_checkPostId();
        
        update_post_meta($this->_postId, self::PREFIX . '_' . $name, $value);
        
        return $this;
    }
    
    public function unsetValue($name)
    {
        $this->_checkPostId();
        
        delete_post_meta($this->_postId, self::PREFIX . '_' . $name);
        
        return $this;
    }
    
    public function setPostId($value)
    {
        $this->_postId = $value;
        return $this;
    }
    
    public function getPostId()
    {
        return $this->_postId;
    }
    
    protected function _checkPostId()
    {
        if (!$this->_postId) {
            throw new Exception('Post ID not specified');
        }    
    }
    
    public function getDefaults()
    {
        return $this->_defaults;
    }
    
    public function getDefault($name)
    {
        return isset($this->_defaults[$name])
            ? $this->_defaults[$name] 
            : NULL;    
    }
}

