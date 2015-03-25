<?php
require_once OPS_APPLICATION_PATH . '/controllers/Abstract/Filter.php';

class Ops_Controller_FilterController
    extends Ops_Controller_Abstract_Filter
{
    public function headStartAction()
    {
        ob_start();
    }

    public function headStopAction()
    {
        $content = ob_get_clean();

        /**
        * Temporarily disable content filtering that might be called from get_the_excerpt
        */
        Ops_WpPlugin::setDisableFilterContent(TRUE);

        echo Ops_Application::getService('Optimization')
            ->setPostId(Ops_WpPlugin::getCurrentPostId())
            ->filterHead(array(
                'type'    => 'head',
                'content' => $content,
            ));

        /**
        * Now enable content filtering back
        */
        Ops_WpPlugin::setDisableFilterContent(FALSE);
    }

    public function headStopHomeAction()
    {
        $content = ob_get_clean();

        echo Ops_Application::getService('Optimization')
            ->filterHomeHead(array(
                'content' => $content,
            ));
    }

    public function contentAction()
    {
        $service = Ops_Application::getService('Optimization')
            ->setPostId(Ops_WpPlugin::getCurrentPostId());

        $result = $service->filterContent(array(
            'type'    => 'content',
            'content' => $this->getParam('content'),
        ));

        /*
        $service->dispose();
        Ops_Application::unloadSingleton('Optimization', 'service');
        */

        return $result;
    }
}