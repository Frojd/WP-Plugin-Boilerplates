<?php
/**
 *
 * @package   Base Plugin Tests
 * @author    Fröjd - Martin Sandström
 * @license   Fröjd Interactive AB (All Rights Reserved).
 * @link      http://example.com
 * @copyright Fröjd Interactive AB
 *
 * Plugin Name: Base Plugin Tests
 * Plugin URI: http://frojd.se
 * Description: Example
 * Version: 1.0
 * Author: Fröjd - Martin Sandström
 * Author URI: http://frojd.se
 * License: Fröjd Interactive AB (All Rights Reserved).
 */

namespace Frojd\Plugin\BasePluginTests;

class BasePluginTests {
    const VERSION = '1.0';

    protected $pluginSlug = 'base_plugin_tests';
    protected static $instance = null;

    protected $pluginBase;
    protected $pluginRelBase;

    private function __construct() {
        $this->plugin_base = rtrim(dirname(__FILE__), '/');
        $this->plugin_rel_base = dirname(plugin_basename(__FILE__));

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
        $this->checkRequirements();
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
}

BasePluginTests::getInstance();
