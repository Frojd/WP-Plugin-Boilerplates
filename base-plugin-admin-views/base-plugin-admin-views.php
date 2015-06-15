<?php
/**
 *
 * @package   Base Plugin Admin Views
 * @author    Fröjd - Martin Sandström
 * @license   Fröjd Interactive AB (All Rights Reserved).
 * @link      http://example.com
 * @copyright Fröjd Interactive AB (All Rights Reserved).
 *
 * Plugin Name: Base Plugin Admin Views
 * Plugin URI: http://frojd.se
 * Description: Example
 * Version: 1.0.0
 * Author: Fröjd - Martin Sandström
 * Author URI: http://frojd.se
 * License: Fröjd Interactive AB (All Rights Reserved).
 */

namespace Frojd\Plugin\BasePluginAdminViews;

include __DIR__.'/views/admin.php';

class BasePluginAdminViews {
    const VERSION = '1.0.0';

    protected $pluginSlug = 'basePluginAdminViews';
    protected static $instance = null;

    public $pluginBase;
    public $pluginRelBase;

    private function __construct() {
        $this->pluginBase = rtrim(dirname(__FILE__), '/');
        $this->pluginRelBase = dirname(plugin_basename(__FILE__));

        register_activation_hook(__FILE__, array(&$this, 'activationHook'));
        register_deactivation_hook(__FILE__, array(&$this, 'deactivationHook'));
        register_uninstall_hook(__FILE__, array(get_class(), 'uninstallHook'));

        // Load plugin text domain
        add_action('plugins_loaded', array($this, 'pluginsLoadedHook'));

        add_action('init', array($this, 'initHook'));
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /*------------------------------------------------------------------------*
     * Hooks
     *------------------------------------------------------------------------*/

    public function activationHook($networkWide) {
    }

    public function deactivationHook($networkWide) {
    }

    public static function uninstallHook($networkWide) {
        if (! defined('WP_UNINSTALL_PLUGIN')) {
            die();
        }
    }

    public function pluginsLoadedHook() {
        $this->initTextdomain();
    }

    public function initHook() {
        new Views\Admin($this);
    }

    /*------------------------------------------------------------------------*
     * Public
     *------------------------------------------------------------------------*/

    public function renderTemplate($name, $vars=array()) {
        foreach ($vars as $key => $val) {
            $$key = $val;
        }

        $path = $this->pluginBase.'/templates/'.$name.'.php';
        if (file_exists($path)) {
            include($path);
        } else {
            echo '<p>Rendering of template failed</p>';
        }
    }

    /*------------------------------------------------------------------------*
     * Private
     *------------------------------------------------------------------------*/

    private function initTextdomain() {
        load_plugin_textdomain($this->pluginSlug, false,
            $this->pluginRelBase.'/langs/');
    }
}

BasePluginAdminViews::getInstance();
