<?php
require_once OPS_APPLICATION_PATH . '/controllers/Abstract/Action.php'; 

abstract class Ops_Controller_Abstract_Admin
    extends Ops_Controller_Abstract_Action
{        
    protected $_layoutScript = 'admin.phtml'; 
    
    public function init()
    {
        parent::init();
            
        //Init layout
        $this->_view->layout = new Ops_View_Engine(OPS_APPLICATION_PATH 
            . '/views/layouts'); 
    }
    
    protected function _redirect($parameters=array())
    {
        $this->_redirectUrl(
            Ops_Application::url($parameters));   
    } 
    
    protected function _redirectUrl($url)
    {
        $this->_viewScript = 'redirect.phtml';   
        $this->_view->url = $url;   
    }
    
    protected function _addMessage($message, $class='updated')
    {
        $messages = is_array($this->_view->layout->messages)
            ? $this->_view->layout->messages
            : array();    
        
        $messages[] = array(
            'class' => $class,
            'text' => $message,
        );  
        
        $this->_view->layout->messages = $messages;
    }
}