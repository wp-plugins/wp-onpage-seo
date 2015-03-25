<?php
require_once OPS_APPLICATION_PATH . '/controllers/Abstract/Metabox.php';

class Ops_Controller_MetaboxController
    extends Ops_Controller_Abstract_Metabox
{
    public function indexAction()
    {
        $post = $this->_view->post = $this->getParam(0);
        $postId = $this->_view->postId = $post->ID;

        $form = $this->_view->form = Ops_Application::getForm('Metabox')
            ->setPostId($postId)
            ->load();

        $meta = Ops_Application::getModel('Post_Meta')
            ->setPostId($postId);
        $this->_view->errors = (array) $meta->getValue('errors');
        $meta->unsetValue('errors');

        $this->_view->errors += $form->getErrors();

        $optimization = $this->_view->optimization = Ops_Application::getService('Optimization')
            ->setPostId($postId);
        $this->_view->hasData = $optimization->load();
    }

    public function handleSavePostAction()
    {
        $this->setViewScript(NULL);

        $postId = $this->getParam(0);
        $post = $this->getParam(1);

        $form = $this->_view->form = Ops_Application::getForm('Metabox')
            ->setPostId($postId)
            ->load();

        foreach ($form->getDataKeys() as $key) {
            $name = Ops_WpPlugin::PREFIX . '_' . $key;
            if (isset($_POST[$name])) {
                $form->setValue($key, $_POST[$name]);
            }
        }

        if ($form->isValid()) {
            $data = $form->getValues();

            $data['post'] = $post;
            $factors = Ops_Application::getService('Optimization')->getFactorNames();

            $data['selected'] = array();
            if (isset($_POST['ops_metabox-factor']) && is_array($_POST['ops_metabox-factor'])) {
                foreach ($factors as $factor) {
                    if (in_array($factor, $_POST['ops_metabox-factor'])) {
                         $data['selected'][] = $factor;
                    }
                }
            }

            $optimization = Ops_Application::getService('Optimization');
            try {
                $optimization->optimize($data);
            } catch (Ops_Service_Exception $e) {
                Ops_Application::getModel('Post_Meta')
                    ->setPostId($postId)
                    ->setValue('errors', array($e->getMessage()));
            }
            if ($errors = $optimization->getErrors()) {
                Ops_Application::getModel('Post_Meta')->setValue('errors',
                    $errors);
            }
        } else {
            Ops_Application::getModel('Post_Meta')
                ->setPostId($postId)
                ->setValue('errors', $form->getErrors());
        }
    }

    public function handleNewPostAction()
    {
        $this->setViewScript(NULL);

        $optimization = Ops_Application::getService('Optimization');
        $keyword = $optimization->getDefaultKeyword();
        if ('' == $keyword) {
            return;
        }

        $postId = $this->getParam(0);
        $post = $this->getParam(1);
        $status = $this->getParam(2);

        if ('auto-draft' == $status) {
            // New post: default values
            Ops_Application::getModel('Post_Meta')
                ->setPostId($postId)
                ->setValue('keyword', $keyword);
        } else {
            // Auto generated post: auto optimize
            if (isset($_POST['ops_metabox_save'])) {
                return;
            }

            $data = array(
                'keyword' => $keyword,
                'post' => $post,
            );

            try {
                $optimization->optimize($data);
            } catch (Ops_Service_Exception $e) {
                Ops_Application::getModel('Post_Meta')
                    ->setPostId($postId)
                    ->setValue('errors', array($e->getMessage()));
            }
            if ($errors = $optimization->getErrors()) {
                Ops_Application::getModel('Post_Meta')->setValue('errors',
                    $errors);
            }
        }
    }
}