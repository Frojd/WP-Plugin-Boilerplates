<?php
/**
 *
 * @package   Base Plugin Admin
 * @author    Fröjd - Martin Sandström
 * @license   GPL-2.0+
 * @link      http://example.com
 * @copyright 2013 Fröjd
 *
 * Plugin Name: Base Plugin Admin
 * Plugin URI: http://frojd.se
 * Description: Example
 * Version: 1.0
 * Author: Fröjd
 * Author URI: http://frojd.se
 * License: GPLv2 or later
 */

namespace Frojd\Plugin\BasePluginAdmin;

class BasePluginAdmin {
    const VERSION = '1.0';

    protected $pluginSlug = 'basePluginAdmin';
    protected static $instance = null;

    protected $pluginBase;
    protected $pluginRelBase;

    private function __construct() {
        $this->pluginBase = rtrim(dirname(__FILE__), '/');
        $this->pluginRelBase = dirname(plugin_basename(__FILE__));

        register_activation_hook(__FILE__, array(&$this, 'activationHook'));
        register_deactivation_hook(__FILE__, array(&$this, 'deactivationHook'));
        register_uninstall_hook(__FILE__, array(get_class(), 'uninstallHook'));

        // Load plugin text domain
        add_action('plugins_loaded', array($this, 'pluginsLoadedHook'));

        // Register admin styles and scripts
        add_action('admin_enqueue_scripts', array($this, 'adminEnqueueScriptsHook'));

        add_action('admin_menu', array($this, 'adminMenuHook'));
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

    public function activationHook($network_wide) {
    }

    public function deactivationHook($network_wide) {
    }

    public static function uninstallHook($network_wide) {
        if (! defined('WP_UNINSTALL_PLUGIN')) {
            die();
        }
    }

    public function pluginsLoadedHook() {
        $this->initTextdomain();
    }

    public function adminEnqueueScriptsHook($page) {
        if ($page == 'settings_page_' . $this->plugin_slug) {
            wp_enqueue_script('base-plugin-admin-script',
                plugins_url($this->plugin_rel_base.'/js/admin.js' ) );
            wp_enqueue_style('base-plugin-admin-styles',
                plugins_url($this->plugin_rel_base.'/css/admin.css'));
        }
    }

    public function adminMenuHook() {
        add_options_page(
            __('Base Plugin Admin'),
            __('Base Plugin Admin'),
            'manage_options',
            'base-plugin-settings',
            array($this, 'settings_page')
        );
    }

    /*------------------------------------------------------------------------*
     * Public
     *------------------------------------------------------------------------*/

    public function settingsPage() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && ! empty($_POST)) {
            if (! wp_verify_nonce($_POST['nonce'], 'base-plugin-settings')) {
                wp_die('Save failed');
            }

            if (isset($_POST['base_plugin_value'])) {
                update_option('base_plugin_value', $_POST['base_plugin_value']);
            }
        }

        $vars = array(
            'base_plugin_value' => get_option('base_plugin_value')
        );

        $this->renderTemplate('admin', $vars);
    }

    /*------------------------------------------------------------------------*
     * Private
     *------------------------------------------------------------------------*/

    private function initTextdomain() {
        load_plugin_textdomain($this->pluginSlug, false,
            $this->pluginRelBase.'/langs/');
    }

    private function renderTemplate($name, $vars=array()) {
        foreach ($vars as $key => $val) {
            $$key = $val;
        }

        $path = $this->plugin_base.'/templates/'.$name.'.php';
        if (file_exists($path)) {
            include($path);
        } else {
            echo '<p>Rendering of admin template failed</p>';
        }
    }
}

BasePluginAdmin::getInstance();
