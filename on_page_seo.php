<?php

/*
Plugin Name: WP On-Page SEO
Plugin URI: http://www.wpthorp.com/
Description: User Friendly WordPress On-Page SEO plugin to gets your site on page 1 of Google with just few simple steps.
Author: Ataul Ghani
Version: 1.0
Author URI: http://www.wpthorp.com/
Tested up to: 4.1.1
*/

require_once dirname(__FILE__) . '/library/compatibility.php';

//Register plug-in:
Ops_WpPlugin::init();

class Ops_WpPlugin
{
//------------------------------------------------------------------------------
// Static members
//------------------------------------------------------------------------------
    const PLUGIN_BASE_URL = 'http://www.wpthorp.com/';
    const AUTHOR_BASE_URL = 'http://www.wpthorp.com/';

    /**
    * Version tag. Used for CSS & JS versioning.
    */
    const VERSION = '04.00.07';

    /**
    * Unique slug
    */
    const SLUG = 'OnPageSeo';

    /**
    * Product name for auto-update system
    */
    const PRODUCT_NAME = 'on_page_seo';

    /**
    * Unique prefix
    */
    const PREFIX = 'ops';

    /**
    * User-friendly title
    */
    const TITLE = 'WP On-Page SEO';

    /**
    * Menu item text
    */
    const MENU_LABEL = 'On-Page SEO';

    /**
    * Required capability to access plug-in pages
    */
    const CAPABILITY = 'publish_posts';

    const CLOACKED_IMAGE_FOLDER = 'ops-images';

    static protected $_instance;

    static protected $_pluginBaseUrl;
    static protected $_pluginPath;

    static protected $_postTemplateVars;

    static protected $_cloakedImageInfo;

    static protected $_requiredPostMeta = array(
        'filters',
        'keyword'
    );

    /**
    * Temporarily disable the_content filter
    * (used to prevent issues when called from get_the_exceprt)
    *
    * @var bool
    */
    static protected $_disableFilterContent = FALSE;

    static protected $_updater;
    static protected $_updaterCronHook;
    const UPDATE_CHECK_UNTERVAL = 12;

    /**
    * Registers plug-in module
    */
    static public function init()
    {

        self::$_pluginBaseUrl = trailingslashit(get_bloginfo('wpurl')) . PLUGINDIR
            . '/' . dirname(plugin_basename(__FILE__));

        self::$_pluginPath = dirname(__FILE__);

        $class = get_class();

        register_activation_hook(__FILE__, array($class, 'activate'));
        register_deactivation_hook(__FILE__, array($class, 'deactivate'));
        // Admin actions
        if (is_admin()) {
            add_action('admin_init', array($class, 'adminInit'));
            add_action('admin_init', array($class, 'autoCheckUpdates'));
            add_action('admin_menu', array($class, 'adminMenu'));
            add_action('wp_ajax_' . self::PREFIX, array($class, 'adminAjax'));
            add_action('admin_enqueue_scripts', array($class, 'adminEnqueueScripts'));
            add_action('save_post', array($class, 'savePost'), 10, 2);
        } else {
            if (self::$_cloakedImageInfo = self::parseCloakedImageUrl()) {
                add_filter('wp_loaded', array($class, 'cloakedImageRedirect'));
            } else {
                if (!(isset($_GET['ops_preview']) && $_GET['ops_preview'])) {
                    add_action('template_redirect', array($class, 'filterHeadStart'), -500);
                }
            }
        }
        add_action('transition_post_status', array($class, 'transitionPostStatus'), 10, 3);

        self::addLibraryIncludePath();

        // Disable admin bar on retrieving post HTML
        if (!is_admin() && isset($_GET['p']) && isset($_GET['preview'])
            && isset($_GET['admin_bar']) && 'true' == $_GET['preview']
            && 'false' == $_GET['admin_bar']
        ) {
            if (function_exists('show_admin_bar')) {
                show_admin_bar(FALSE);
            }
        }
    }

    /**
    * Plug-in activate hook
    */
    static public function activate()
    {
        if (version_compare(PHP_VERSION, '5.1', '<')) {
            // Deactivate Plugin
            if(function_exists('deactivate_plugins')) {
                deactivate_plugins(plugin_basename(__FILE__), true);
            }

            // Display Error Message
            trigger_error(sprintf('Sorry, but Associate Goliath requires PHP 5.0 or newer. Your version is %s. Please, ask your web host to upgrade to PHP 5.0.', phpversion()), E_USER_ERROR);
        }

        // Requires Wordpress 3.0+
        global $wp_version;
        if (version_compare($wp_version, '3.0', '<' )) {
            // Deactivate Plugin
            if(function_exists('deactivate_plugins')) {
                deactivate_plugins(plugin_basename(__FILE__), true);
            }

            // Display Error Message
            trigger_error(sprintf('Sorry, but Associate Goliath requires Wordpress 3.0 or newer. Your version is %s. Please, upgrade to the latest version of Wordpress.', $wp_version), E_USER_ERROR);
        }
    }

    /**
    * Plug-in deactivation hook
    */
    static public function deactivate()
    {

    }

    static public function loadApplication()
    {
        self::addLibraryIncludePath();
        require_once(self::$_pluginPath . '/application/Application.php');
    }

    static public function addLibraryIncludePath()
    {
        $includePaths = explode(PATH_SEPARATOR, get_include_path());
        $path = realpath(dirname(__FILE__) . '/library');
        if (!in_array($path, $includePaths)) {
            foreach ($includePaths as $key=>$item) {
                if ('.' == $item) {
                    unset($includePaths[$key]);
                    break;
                }
            }
            array_unshift($includePaths, $path);
            array_unshift($includePaths, '.');
            set_include_path(implode(PATH_SEPARATOR, $includePaths));
        }
    }

    static public function adminInit()
    {
    }

    static public function autoCheckUpdates()
    {
        $updater = self::$_updater;

        $shouldCheck = (empty($state)
            || !isset($state->lastCheck)
            || ((time() - $state->lastCheck) >= (self::UPDATE_CHECK_UNTERVAL * 3600)))
            && !wp_next_scheduled(self::$_updaterCronHook);

        if ($shouldCheck) {
            wp_schedule_single_event(time(), self::$_updaterCronHook);
            spawn_cron();
        }
    }

    /**
    * Hooks
    */
    static public function adminMenu()
    {
        self::getInstance()
            ->_registerAdminPages()
            ->_registerMetaBoxes();
    }

    static public function adminAjax()
    {
        self::getInstance()->dispatchAjax();
    }

    static public function savePost($postId, $post)
    {
        // Ignore new posts, autosave and revisions:
        if (wp_is_post_autosave($postId)
            || wp_is_post_revision($postId)
            || 'auto-draft' == $post->post_status
            || 'trash' == $post->post_status
            || (isset($_POST['action']) && 'autosave' == $_POST['action'])
            || 'nav_menu_item' == $post->post_type
        ) {
            return;
        }
        if (isset($_POST['ops_metabox_save']) && $_POST['ops_metabox_save'] == 1){
            unset($_POST['ops_metabox_save']);
            self::getInstance()->dispatch__metabox__handle_save_post($postId, $post);
        }
    }

    static public function transitionPostStatus($new, $old, $post)
    {
        if ('new' != $old || 'inherit' == $new) {
            return;
        }

        $mode = get_option(self::PREFIX . '_auto_optimization');
        if ('' == $mode || 'disabled' == $mode) {
            return;
        }

        if ('auto-draft' != $new && isset($_POST['ops_metabox_save'])) {
            return;
        }

        self::getInstance()->dispatch__metabox__handle_new_post($post->ID, $post, $new);
    }

    static public function checkPost()
    {
        if (!is_singular()) {
            return FALSE;
        }

        $postId = Ops_WpPlugin::getCurrentPostId();
        foreach (self::$_requiredPostMeta as $metaName) {
            if ('' == get_post_meta($postId, self::PREFIX . '_' . $metaName, TRUE)) {
                return FALSE;
            }
        }

        return TRUE;
    }

    static public function filterHeadStart()
    {
        if (self::checkPost()) {
            $class = get_class();
            add_action('wp_head', array($class, 'filterHeadStop'), 1000);
            add_filter('the_content', array($class, 'filterContent'), 99);
        } else if (is_home() && get_option(self::PREFIX . '_filter_home_head')) {
            $class = get_class();
            add_action('wp_head', array($class, 'filterHeadStopHome'), 1000);
        } else {
            return;
        }

        self::getInstance()->dispatch('filter', 'head-start');
    }

    static public function filterHeadStop()
    {
         self::getInstance()->dispatch('filter', 'head-stop');
    }

    static public function filterHeadStopHome()
    {
         self::getInstance()->dispatch('filter', 'head-stop-home');
    }

    static public function filterContent($content)
    {
        if (self::$_disableFilterContent) {
            return $content;
        }

        return self::getInstance()
            ->dispatch('filter', 'content', array('content' => $content));
    }

    static public function setDisableFilterContent($value)
    {
        self::$_disableFilterContent = $value;
    }

    static public function cloakedImageRedirect()
    {
        $postId = self::$_cloakedImageInfo['post_id'];
        $imageIndex = self::$_cloakedImageInfo['image_index'];
        if (!get_post($postId)) {
            header("HTTP/1.0 404 Not found");
            exit ("Post with ID {$postId} not found");
        }

        self::loadApplication();

        $postContent = Ops_Application::getModel('Post_Content')
            ->get(get_post($postId));
        $realUrl = Ops_Application::getService('ImageCloak')
            ->getRealImageUrl($postContent, $imageIndex);

        if (!$realUrl) {
            header("HTTP/1.0 404 Not found");
            exit ("Image redirect URL not found");
        }

        if (!parse_url($realUrl, PHP_URL_SCHEME)) {
           $realUrl = Ops_Application::getService('AbsoluteUrl')
               ->makeAbsolute(get_permalink(), $realUrl);
        }

        header('Location: ' . $realUrl, TRUE, 302);
        exit();
    }

    static public function parseCloakedImageUrl()
    {
         $uri = substr($_SERVER['REQUEST_URI'], strlen(self::getHomePath()));
         $parts = explode('/', $uri);

         if (!$parts) {
             return NULL;
         }

         if ('index.php' == $parts[0]) {
            array_shift($parts);
         }

         if (self::CLOACKED_IMAGE_FOLDER != $parts[0] || !in_array(count($parts), array(3, 4))) {
            return NULL;
         }

         $result = array(
            'post_id' => (int)$parts[1],
         );

         // Backwords compatibility to keep old links working
         if (4 == count($parts)) {
            $result['image_index'] = $parts[2];
            return $result;
         }

         $imageName = pathinfo($parts[2], PATHINFO_FILENAME);
         $nameParts = explode('--', $imageName, 2);

         $result['image_index'] = isset($nameParts[1]) && $nameParts[1] > 0
            ? (int)$nameParts[1] : 1;

         return $result;
    }

    static public function getHomePath()
    {
         return trailingslashit((string) parse_url(site_url(), PHP_URL_PATH));
    }

    static public function adminEnqueueScripts($hook)
    {
        $pageSlug = $pageSlug = str_replace(' ', '-', strtolower(self::MENU_LABEL));

        switch ($hook) {
            case 'toplevel_page_' . self::SLUG:
                // Admin page
                wp_enqueue_style(self::PREFIX . '_admin',
                    self::$_pluginBaseUrl . '/css/admin.css', array(),
                    self::VERSION);
                wp_enqueue_script(self::PREFIX . '_admin',
                    self::$_pluginBaseUrl . '/js/admin.js', array(),
                    self::VERSION);

                wp_enqueue_style(self::PREFIX . '_ui',
                    self::$_pluginBaseUrl . '/css/ui.css', array(),
                    self::VERSION);
                wp_enqueue_script('jquery');
            case $pageSlug . '_page_' . self::SLUG . '-options':
                // Options page
                wp_enqueue_style(self::PREFIX . '_admin',
                    self::$_pluginBaseUrl . '/css/admin.css', array(),
                    self::VERSION);
                wp_enqueue_script(self::PREFIX . '_admin',
                    self::$_pluginBaseUrl . '/js/admin.js', array(),
                    self::VERSION);
                break;

            case 'post.php':
            case 'post-new.php':
                // Post editor
                wp_enqueue_style(self::PREFIX . '_metabox',
                    self::$_pluginBaseUrl . '/css/metabox.css', array(),
                    self::VERSION);
                wp_enqueue_script(self::PREFIX . '_metabox',
                    self::$_pluginBaseUrl . '/js/metabox.js', array(),
                    self::VERSION);
                break;

            default:
                // Do not load common stuff for other pages
                return;
        }

        // Common stuff
        wp_enqueue_style(self::PREFIX . '_help',
            self::$_pluginBaseUrl . '/css/help.css', array(),
            self::VERSION);
        wp_enqueue_script(self::PREFIX . '_help',
            self::$_pluginBaseUrl . '/js/help.js', array(),
            self::VERSION);
    }

    /**
    * Misc Helpers
    */

    /**
    * Returns array of available post types
    */
    static protected function getPostTypes()
    {
        return function_exists('get_post_types')
            ? get_post_types('','names')
            : array('post', 'page');
    }

    /**
    * Singleton pattern
    *
    * @return Ops_WpPlugin Singleton instance
    */
    static public function getInstance()
    {
        if (!self::$_instance) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }

    /**
    * Static accessors
    */

    static public function getPluginBaseUrl()
    {
        return self::$_pluginBaseUrl;
    }

    static public function getPluginPath()
    {
        return self::$_pluginPath;
    }

    static public function getCurrentPostId()
    {
        $result = get_the_ID();
        if (!$result) {
            global $wp_query;
            if (isset($wp_query->post->ID)) {
                $result = $wp_query->post->ID;
            }
        }

        return $result? $result : NULL;
    }

//------------------------------------------------------------------------------
// Object members
//------------------------------------------------------------------------------
    /**
    * Register plug-in admin pages and menus
    */
    /**
    * Register plug-in admin pages and menus
    */
    protected function _registerAdminPages()
    {
        add_menu_page(self::TITLE, self::MENU_LABEL,
            self::CAPABILITY, self::SLUG,
            array($this, 'dispatchAdmin'),
            self::getPluginBaseUrl() . '/images/icon.png'
        );

        add_submenu_page(self::SLUG, self::TITLE,
            'Admin', self::CAPABILITY,
            self::SLUG,
            array($this, 'dispatchAdmin')
        );

        add_submenu_page(self::SLUG, self::TITLE,
            'Options', self::CAPABILITY,
            self::SLUG . '-options',
            array($this, 'dispatch__admin__options')
        );

        return $this;
    }

    /**
    * Register plug-in admin metaboxes
    */
    protected function _registerMetaBoxes()
    {
        foreach(self::getPostTypes() as $type) {
            add_meta_box(self::PREFIX . '_main', self::TITLE, array($this, 'dispatch__metabox__index'), $type, 'side', 'high' );
        }

        return $this;
    }

    /**
    * Intercepts WP requests
    */
    public function __call($method, $arguments)
    {
        if ('dispatch__' == substr($method, 0, 10)) {
            $parts = explode('__', $method);
            $controller = @$parts[1];
            $action = @$parts[2];

            $controller = $this->_prepareMvcName($controller);
            $action = $this->_prepareMvcName($action);

            return $this->dispatch($controller, $action, $arguments);
        }
        else {
            throw new ErrorException("Method '{$method}' not found.");
        }
    }

    /**
    * Dispatches user request to WP Module
    *
    * @param string $controller Controller name
    * @param string $action Action name
    */
    public function dispatch($controller='index', $action='index',
        $arguments = NULL)
    {
        self::loadApplication();

        return Ops_Application::dispatch($controller, $action, $arguments);
    }

    public function dispatchAdmin()
    {
        $controller = @$_GET['controller'];
        if (empty($controller)) $controller = 'admin';
        $action = @$_GET['action'];
        if (empty($action)) $action = 'index';

        $this->dispatch($controller, $action);
    }

    public function dispatchAjax()
    {
       /* $controller = @$_POST['controller'];
        if (empty($controller)) $controller = 'index';
        $action = @$_POST['ajaxAction'];
        if (empty($action)) $action = 'index';

        $this->dispatch($controller, $action);
        exit;*/

        $controller = @$_POST['controller'];
        if (empty($controller)) {
            $controller = 'index';
        }
        $action = @$_POST['controller_action'];
        if (empty($action)) {
            $action = 'index';
        }

        $this->dispatch($controller, $action);

        exit();
    }

    protected function _prepareMvcName($name)
    {
        return str_replace('_', '-', $name);
    }
}