<?php
/**
 *
 * @package   Base Plugin CPT
 * @author    Fröjd - Martin Sandström
 * @license   GPL-2.0+
 * @link      http://example.com
 * @copyright 2013 Fröjd
 *
 * Plugin Name: Base Plugin CPT
 * Plugin URI: http://frojd.se
 * Description: Example
 * Version: 1.0
 * Author: Fröjd - Martin Sandström
 * Author URI: http://frojd.se
 * License: GPLv2 or later
 */

namespace Frojd\Plugin\BasePluginCPT;

class BasePluginCPT {
    const VERSION = '1.0';

    protected $pluginSlug = 'base_plugin_cpt';
    protected static $instance = null;

    protected $postName = 'custom post';
    protected $postType = 'custom_post_type';

    protected $pluginBase;
    protected $pluginRelBase;

    private function __construct() {
        $this->pluginBase = rtrim(dirname(__FILE__), '/');
        $this->pluginRelBase = dirname(plugin_basename(__FILE__));

        register_activation_hook(__FILE__, array(&$this, 'activationHook'));
        register_deactivation_hook(__FILE__, array(&$this, 'deactivationHook'));
        register_uninstall_hook(__FILE__, array(get_class(), 'uninstallHook'));

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

    public function initHook() {
        $this->registerCpt();
    }

    /*------------------------------------------------------------------------*
     * Public
     *------------------------------------------------------------------------*/

    /*------------------------------------------------------------------------*
     * Private
     *------------------------------------------------------------------------*/

    private function registerCpt() {
       $labels = array(
            'name' => __($this->postName.'s', $this->pluginSlug),
            'singular_name' => __($this->postName, $this->pluginSlug),
        );

        $args = array(
            'labels' => $labels,
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'query_var' => true,
            'capability_type' => 'post',
            'has_archive' => false,
            'hierarchical' => false,
            'menu_position' => null,
            'supports' => array('title', 'thumbnail')
        );

        register_post_type($this->postType, $args);
    }

    private function renderTemplate($name, $vars=array()) {
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
}

BasePluginCPT::getInstance();
