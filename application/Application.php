<?php
define('OPS_APPLICATION_PATH', dirname(__FILE__));

abstract class Ops_Application
{
    static protected $_namespaces = array(
        'controller' => array(
            'namespace' => 'Ops_Controller',
            'folder'    => 'controllers',
        ),
        'model' => array(
            'namespace' => 'Ops_Model',
            'folder'    => 'models',
        ),
        'service' => array(
            'namespace' => 'Ops_Service',
            'folder'    => 'services',
        ),
        'form' => array(
            'namespace' => 'Ops_Form',
            'folder'    => 'forms',
        ),
    );

    static protected $_singletons = array();

    static public function dispatch($controller='index', $action='index',
        $params = NULL)
    {
        if (empty($controller)) {
            $controller = 'index';
        } else {
            $controller = strtolower($controller);
        }
        if (empty($action)) {
            $action = 'index';
        } else {
            $action = strtolower($action);
        }

        $controllerClass = self::camelize($controller) . 'Controller';
        $controllerObject = self::factory($controllerClass, 'controller');

        $controllerObject->setParams((array) $params);

        $controllerObject->preDispatch($action);

        $actionName = self::camelize($action, FALSE);
        $method = $actionName . ('Action');
        $controllerObject->setViewScript($controller . '/' . $action . '.phtml');

        $result = $controllerObject->$method();

        $viewScript = $controllerObject->getViewScript();
        if (!empty($viewScript)) {
            /**
            * @var Ops_View_Engine
            */
            $view = $controllerObject->getView();

            if (($layout = $view->layout)
                && ($layoutScript = $controllerObject->getLayoutScript())
            ) {
                $layout->content = $view->render($viewScript);
                $layout->renderDirect($layoutScript);
            } else {
                $view->renderDirect($viewScript);
            }
        }

        $controllerObject->postDispatch($action);

        return $result;
    }

    static public function getModel($name)
    {
        return self::factory($name, 'model', TRUE);
    }

    static public function getService($name)
    {
        return self::factory($name, 'service');
    }

    static public function getForm($name)
    {
        return self::factory($name, 'form');
    }

    static public function factory($name, $type, $singleton = FALSE)
    {
        $info = self::$_namespaces[$type];
        $class = $info['namespace'] . '_' . $name;

        require_once OPS_APPLICATION_PATH . '/' . $info['folder'] . '/'
            . str_replace('_', '/', $name) . '.php';
        if (!$singleton) {
            $singleton = method_exists($class, 'isSingleton')
                && call_user_func(array($class, 'isSingleton'));
        }

        if ($singleton && isset(self::$_singletons[$class])) {
            return self::$_singletons[$class];
        }
        $result = new $class;

        if ($singleton) {
            self::$_singletons[$class] = $result;
        }

        return $result;
    }

    static public function unloadSingleton($name, $type)
    {
        $class = self::$_namespaces[$type]['namespace'] . '_' . $name;
        unset(self::$_singletons[$class]);
    }

    static public function camelize($string, $first=TRUE)
    {
        $string = ucwords(str_replace('-', ' ', $string));
        $string = str_replace(' ', '', $string);
        if (!$first) {
            $string = strtolower($string[0]) . substr($string, 1);
        }

        return $string;

    }

    static public function url($parameters=array())
    {
        $parameters = array_merge(array('page' => Ops_WpPlugin::SLUG), $parameters);
        foreach ($parameters as $name=>$value) {
            if (is_null($value)) {
                unset($parameters[$name]);
            }
        }

        return dirname($_SERVER['SCRIPT_NAME']) . '/admin.php?'
            . http_build_query($parameters, '', '&');
    }
}