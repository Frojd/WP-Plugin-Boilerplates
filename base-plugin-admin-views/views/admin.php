<?php

namespace Frojd\BasePluginAdminViews\Views;

class Admin {
    protected $plugin;

    function __construct ($plugin) {
        $this->plugin = $plugin;

        add_action('admin_menu',
            array($this, 'adminMenuHook'));

        add_action('admin_enqueue_scripts',
            array($this, 'adminEnqueueScriptsHook'));
    }

    /*------------------------------------------------------------------------*
     * Hooks
     *------------------------------------------------------------------------*/

    public function adminMenuHook() {
        add_options_page(__('Base Plugin Admin'),
            __('Base Plugin Admin'),
            'manage_options',
            'base-plugin-settings',
            array($this, 'settingsPage')
        );
    }

    public function adminEnqueueScriptsHook() {
        if($page == 'settings_page_' . $this->plugin_slug) {
            wp_enqueue_script('base-plugin-admin-script',
                plugins_url($this->plugin_rel_base.'/js/admin.js' ) );
            wp_enqueue_style('base-plugin-admin-styles',
                plugins_url($this->plugin_rel_base.'/css/admin.css'));
        }
    }

    /*------------------------------------------------------------------------*
     * Public
     *------------------------------------------------------------------------*/

    public function settingsPage() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && ! empty($_POST)) {
            if (! wp_verify_nonce($_POST['nonce'], 'base-plugin-settings')) {
                wp_die("Save failed");
            }

            if (isset($_POST["base_plugin_value"])) {
                update_option('base_plugin_value', $_POST['base_plugin_value']);
            }
        }

        $vars = array(
            "base_plugin_value" => get_option('base_plugin_value')
        );

        $this->plugin->render_template("admin", $vars);
    }

    /*------------------------------------------------------------------------*
     * Private
     *------------------------------------------------------------------------*/
}
