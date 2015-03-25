<?php
require_once OPS_APPLICATION_PATH . '/controllers/Abstract/Admin.php';

class Ops_Controller_AdminController
    extends Ops_Controller_Abstract_Admin
{
    public function indexAction()
    {
        // Loading message
        $this->_view->renderDirect('page-loading.phtml');

        $formNavigation = Ops_Application::getForm('Admin_Navigation')
            ->load()
            ->import($_POST);

        if ($formNavigation->isValid()) {
            $formNavigation->save();
        } else {
            foreach ($formOptimization->getErrors() as $error) {
                $this->_addMessage($error, 'error');
            }
        }

        $formOptimization = Ops_Application::getForm('Admin_Optimization')
            ->load();
        if ($_POST) {
            $formOptimization->import($_POST);
        }

        $formOptimization->save();

        $this->_view->postQuery =
            Ops_Application::getModel('Post_Query')
                ->setOptions($formNavigation->getValues())
                ->doQuery();

        $this->_view->formNavigation = $formNavigation;
        $this->_view->formOptimization = $formOptimization;

        $this->_view->layout->announcement =
            Ops_Application::getModel('AnnouncementHtml')->get();
    }

    public function resetAction()
    {
        $options = Ops_Application::getModel('Options');

        // Reset posts per page
        $options->unsetValue('posts_per_page');
    }

    public function optionsAction()
    {
        $form = Ops_Application::getForm('Admin_Options')
            ->load();
        if ($_POST) {
            $form->import($_POST);

            if ($form->isValid()) {
                $form->save();
                $this->_addMessage('Settings saved');
            } else {
                foreach ($form->getErrors() as $error) {
                    $this->_addMessage($error, 'error');
                }
            }
        }

        $this->_view->form = $form;
    }

    public function ajaxOptimizeAction()
    {
        ob_start();

        $this->setViewScript(NULL);

        $result = array(
            'status' => FALSE,
            'postId' => -1
        );
        $error = '';

        if (isset($_POST['postId'])) {
            $postId = $_POST['postId'];

            $result['postId'] = $postId;

            $keyword = '';
            if (isset($_POST['keyword'])) {
                $keyword = $_POST['keyword'];
            }

            $factors = array();
            if (isset($_POST['factors'])) {
                $factors = $_POST['factors'];
            }

            $extraContentMode = '';
            if (isset($_POST['extra_content_mode'])) {
                $extraContentMode = $_POST['extra_content_mode'];
            }

            if (isset($_POST['save_factors']) && $_POST['save_factors']) {
                Ops_Application::getModel('Options')
                    ->setValue('factors', $factors)
                    ->setValue('extra_content_mode-mass', $extraContentMode);
            }

            $service = Ops_Application::getService('MassOptimization');
            try {
                $result['status'] = $service->optimizePost($postId, $keyword,
                    $factors, $extraContentMode);
            } catch (Ops_Service_Exception $e) {
                $error = $e->getMessage();
            } catch (Exception $e) {
                $error = "Unexpected error: {$e->getMessage()}. Please contact support.";
            }
        } else {
            $error = 'Invalid Post ID';
        }

        ob_end_clean();

        header('Content-Type: application/json', TRUE);
        echo json_encode(array(
            'error' => $error,
            'result' => $result,
        ));
    }
}