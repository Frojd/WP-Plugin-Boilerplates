<?php
/**
 *
 * @package   Base Plugin WPCLI
 * @author    Fröjd - Martin Sandström
 * @license   GPL-2.0+
 * @link      http://example.com
 * @copyright 2013 Fröjd
 *
 * Plugin Name: Base Plugin WPCLI
 * Plugin URI: http://frojd.se
 * Description: Example
 * Version: 1.0
 * Author: Fröjd - Martin Sandström
 * Author URI: http://frojd.se
 * License: GPLv2 or later
 */

namespace Frojd\Plugin\BasePluginWpCli;

if ( defined('WP_CLI') && WP_CLI ) {
    include __DIR__.'/commands/test-command.php';
}

class BasePluginWPCLI {
    const VERSION = '1.0';

    protected $pluginSlug = 'base_plugin_wpcli';
    protected static $instance = null;

    protected $pluginBase;
    protected $pluginRelBase;

    private function __construct() {
        $this->pluginBase = rtrim(dirname(__FILE__), '/');
        $this->pluginRelBase= dirname(plugin_basename(__FILE__));

        register_activation_hook(__FILE__, array(&$this, 'activationHook'));
        register_deactivation_hook(__FILE__, array(&$this, 'deactivationHook'));
        register_uninstall_hook(__FILE__, array(get_class(), 'uninstallHook'));
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

    /*------------------------------------------------------------------------*
     * Public
     *------------------------------------------------------------------------*/

    /*------------------------------------------------------------------------*
     * Private
     *------------------------------------------------------------------------*/

    private function renderTemplate($name, $vars=array()) {
        foreach ($vars as $key => $val) {
            $$key = $val;
        }

        $path = $this->pluginBase.'/templates/'.$name.'.php';
        if (file_exists($path)) {
            include($path);
        } else {
            echo '<p>Rendering of admin template failed</p>';
        }
    }
}

BasePluginWPCLI::getInstance();
