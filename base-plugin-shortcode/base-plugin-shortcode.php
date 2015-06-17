<?php
/**
 *
 * @package   Base Plugin Shortcode
 * @author    Fröjd - Martin Sandström
 * @license   Fröjd Interactive AB (All Rights Reserved).
 * @link      http://frojd.se
 * @copyright Fröjd Interactive AB (All Rights Reserved).
 *
 * Plugin Name: Base Plugin Shortcode
 * Plugin URI: http://frojd.se
 * Description: Example
 * Version: 1.0.0
 * Author: Fröjd - Martin Sandström
 * Author URI: http://frojd.se
 * License: Fröjd Interactive AB (All Rights Reserved).
 */

namespace Frojd\Plugin\BasePluginShortcode;

class BasePluginShortcode {
    const VERSION = '1.0.0';

    protected $pluginSlug = 'base_plugin_shortcode';
    protected static $instance = null;

    protected $pluginBase;
    protected $pluginRelBase;

    private function __construct() {
        $this->pluginBase = rtrim(dirname(__FILE__), '/');
        $this->pluginRelBase = dirname(plugin_basename(__FILE__));

        register_activation_hook(__FILE__, array(&$this, 'activationHook'));
        register_deactivation_hook(__FILE__, array(&$this, 'deactivationHook'));
        register_uninstall_hook(__FILE__, array(get_class(), 'uninstallHook'));

        add_shortcode('example', array(&$this, 'exampleShortcode'));
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
     * Shortcodes
     *------------------------------------------------------------------------*/

    public function exampleShortcode($atts) {
        extract(shortcode_atts(array(
            'label' => "Default"
        ), $atts));

        $vars = array(
            'label' => $label
        );

        $this->renderTemplate('example', $vars);
    }

    /*------------------------------------------------------------------------*
     * Public
     *------------------------------------------------------------------------*/

    /*------------------------------------------------------------------------*
     * Private
     *------------------------------------------------------------------------*/

    private function renderTemplate($name, $vars = array()) {
        foreach ($vars as $key => $val) {
            $$key = $val;
        }

        $path = $this->pluginBase.'/templates/'.$name.'.php';
        if (file_exists($path)) {
            include($path);
        } else if (defined("WP_DEBUG") && WP_DEBUG) {
            echo '<p>Rendering of template '.$path.' failed</p>';
        }
    }
}

BasePluginShortcode::getInstance();

