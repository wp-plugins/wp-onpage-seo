<?php
require_once OPS_APPLICATION_PATH . '/forms/Admin.php';

class Ops_Form_Admin_Navigation extends Ops_Form_Admin
{
    //const MAX_POSTS_PER_PAGE = 1000;

    protected $_savedDataKeys = array(
        'posts_per_page',
        'post_type',
        'order',
        'orderby'
    );

    protected $_dataKeys = array(
        'posts_per_page',
        'post_type',
        's',
        'paged',
        'cat',
        'post_status',
        'order',
        'orderby'
    );

    protected $_resetPageFields = array(
        's',
        'cat',
        'order',
        'orderby',
        'post_status',
    );

    public function import(array $data)
    {
        parent::import($data);

        // Resets
        if (isset($data['old']) && is_array($data['old'])) {
            $old = $data['old'];

            // Changed post type: reset paged, s, cat
            if (isset($old['post_type'])
                && $old['post_type'] != $this->getValue('post_type')
            ) {
                $this->_values['paged'] = 1;
                $this->_values['s'] = '';
                $this->_values['cat'] = 0;
            } else {
                foreach ($this->_resetPageFields as $field) {
                    if (isset($old[$field])
                        && $old[$field] != $this->getValue($field)
                    ) {
                        $this->_values['paged'] = 1;
                        break;
                    }
                }
            }
        }

        return $this;
    }

    protected function _validate()
    {
        // filtering data
        foreach ($this->_dataKeys as $key) {
            $value = $this->getValue($key);
            if (!is_null($value) && !is_array($value)) {
                $value = str_replace(array("\n", "\r"), '', $value);
                $value = trim($value);
                $this->setValue($key, $value);
            }
        }

        $value = (int)$this->getValue('posts_per_page');
        if ($value <= 0) {
            $value = Ops_Application::getModel('Options')->getDefault('posts_per_page');
        } /*else if ($value > self::MAX_POSTS_PER_PAGE) {
            $value = self::MAX_POSTS_PER_PAGE;
        } */
        $this->setValue('posts_per_page', $value);
    }
}