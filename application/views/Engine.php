<?php

/**
* Renders HTML pages using template scripts
*/
class Ops_View_Engine
{
    const HELPER_NAMESPACE = 'Ops_View_Helper';

    const TAG_SELF_CLOSING = 0;
    const TAG_OPEN = 1;
    const TAG_CLOSED = 2;

    protected $_data;
    protected $_helpers = array();

    protected $_scriptPath;
    protected $_baseUrl;

    public function __construct($scriptPath)
    {
        $this->_scriptPath = $scriptPath;
        $this->_baseUrl = Ops_WpPlugin::getPluginBaseUrl();
    }

    /**
    * Executes given template script and returns output
    *
    * @param string $script Script file name
    * @return string Script output
    */
    public function render($script)
    {
        ob_start();
        $this->_run($script);
        $result = ob_get_clean();

        return $result;
    }

    /**
    * Executes given template script and immediately sends output
    *
    * @param string $script Script file name
    * @return void
    */
    public function renderDirect($script)
    {
        $this->_run($script);
    }

    /**
    * Renders given template script in its own variable context and returns output
    *
    * @param string $script Script file name
    * @param array $data Variables to pass to the script
    * @return string Script putput
    */
    public function partial($script, array $data=array())
    {
        $oldData = $this->_data;
        $this->_data = $data;

        $result = $this->render($script);

        $this->_data = $oldData;

        return $result;
    }

    /**
    * HTML escape given string
    *
    * @param string $text
    * @return string
    */
    public function escape($text)
    {
        $text = (string) $text;
        if ('' === $text) return '';

        $result = @htmlspecialchars($text, ENT_COMPAT, 'UTF-8');
        if (empty($result)) {
            $result = @htmlspecialchars(utf8_encode($text), ENT_COMPAT, 'UTF-8');
        }

        return $result;
    }

    public function adminUrl($params=NULL, $escape=TRUE)
    {
        if (!$params) {
            $params = array();
        }

        $query = array('page' => Ops_WpPlugin::SLUG);
        foreach (array('controller', 'action') as $key) {
            if (isset($params[$key])) {
                $query[$key] = $params[$key];
                unset($params[$key]);
            }
        }

        ksort($params);

        foreach ($params as $key=>$value) {
            if (is_null($value)) {
                $query[$key] = $value;
            }
        }

        $result = admin_url('/admin.php?' . http_build_query($query, '', '&'));

        if ($escape) {
            $result = $this->escape($result);
        }

        return $result;
    }

    public function url($action=NULL, $controller=NULL, $escape=TRUE)
    {
        $result = Ops_Application::url($action, $controller);
        if ($escape) {
            $result = $this->escape($result);
        }

        return $result;
    }

    /**
    * Exectures view script
    *
    * @param string Script path
    */
    protected function _run()
    {
        require($this->_scriptPath . '/' . func_get_arg(0));
    }

    function getHelper($name)
    {
        $name = ucfirst($name);

        $result = @$this->_helpers[$name];
        if (!$result) {
            require_once(dirname(__FILE__) . '/helpers/' . $name . '.php');
            $helperClass = self::HELPER_NAMESPACE . '_' . $name;
            $result = $this->_helpers[$name] = new $helperClass($this);
        }

        return $result;
    }

    public function __call($method, $arguments)
    {
        $helper = $this->getHelper($method);
        if (count($arguments)) {
            return call_user_func_array(array($helper, '__invoke'), $arguments);
        } else {
            return $helper;
        }
    }

    //
    // Variable storage methods
    //

    public function __set($name, $value)
    {
        $this->_data[$name] = $value;
    }

    public function __get($name)
    {
        return @$this->_data[$name];
    }

    public function __isset($name)
    {
        return array_key_exists($name, $this->_data);
    }

    public function __unset($name)
    {
        unset($this->_data[$name]);
    }
}