<?php
abstract class Ops_View_Helper_HelperAbstract
{
    protected $_view;

    public final function __construct(Ops_View_Engine $view)
    {
        $this->_view = $view;

        $this->init();
    }

    public function init()
    {
    }

    /*
    public function __invoke()
    {
    }
    */
}
