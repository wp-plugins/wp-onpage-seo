<?php
class Ops_Model_Options
{
    const PREFIX = Ops_WpPlugin::PREFIX;
    protected $_defaults = array(
        'posts_per_page' => 20,
        'post_type' => 'post',
        'extra_content_mode' => 'disabled',
        'auto_optimization' => 'disabled',
    );

    public function getValue($name)
    {
        return get_option(self::PREFIX . '_' . $name, $this->getDefault($name));
    }

    public function setValue($name, $value)
    {
        update_option(self::PREFIX . '_' . $name, $value);

        return $this;
    }

    public function unsetValue($name)
    {
        delete_option(self::PREFIX . '_' . $name);

        return $this;
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