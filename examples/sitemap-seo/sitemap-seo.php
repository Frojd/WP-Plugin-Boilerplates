<?php
/**
 *
 * @package   Sitemap & SEO
 * @author    Fröjd
 * @license   Fröjd Interactive AB (All Rights Reserved).
 * @link      http://frojd.se
 * @copyright Fröjd Interactive AB
 *
 * Plugin Name: Sitemap & SEO
 * Plugin URI: http://frojd.se
 * Description: This plugin allows the user to edit the robots.txt file and choose which post types to be included in the sitemap.
 * Version: 1.2.1
 * Author: Fröjd
 * Author URI: http://frojd.se
 * License: Fröjd Interactive AB (All Rights Reserved).
 */

class SitemapSeo {
    const VERSION = '1.2.1';
    const SEO_KEYWORDS_FIELD = 'seo_post_keywords';

    protected $plugin_slug = 'sitemap_seo';
    protected static $instance = null;

    protected $supported_types = array("post", "page");
    protected $plugin_base;
    protected $plugin_rel_base;


    private function __construct() {
        $this->plugin_base = rtrim(dirname(__FILE__), '/');
        $this->plugin_rel_base = dirname(plugin_basename(__FILE__));

        register_activation_hook(__FILE__, array(&$this, 'activation_hook'));
        register_activation_hook(__FILE__, array(&$this, 'deactivation_hook'));
        register_uninstall_hook(__FILE__, array(get_class(), 'uninstall_hook'));

        add_action('plugins_loaded', array( $this, 'plugins_loaded_hook'));
        add_action('admin_init', array($this, 'admin_init_hook'));
        add_action('init', array($this, 'init_hook'));
        add_filter('generate_rewrite_rules',
            array($this, 'generate_rewrite_rules_hook'));
        add_action('parse_request', array($this, 'parse_request_hook'));
        add_action('do_feed_sitemap', array($this, 'do_feed_sitemap_hook'), 10, 1 );
        add_action('do_meta_boxes', array($this, 'do_meta_boxes_hook'));
        add_action('save_post', array($this, 'save_post_hook'));
    }

    public static function get_instance() {
        if (self::$instance == null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /*------------------------------------------------------------------------*
     * Hooks
     *------------------------------------------------------------------------*/

    public function activation_hook($network_wide) {
    }

    public function deactivation_hook($network_wide) {
    }

    public static function uninstall_hook($network_wide) {
        if (! defined('WP_UNINSTALL_PLUGIN')) {
            die();
        }
    }

    public function plugins_loaded_hook() {
        $this->init_textdomain();
    }

    public function admin_init_hook() {
        $this->register_seo_description_settings();
        $this->register_robot_settings();
    }

    public function init_hook() {
        $this->flush_rules();
    }

    public function generate_rewrite_rules_hook() {
        global $wp_rewrite;

        $feed_rules = array(
            '.*sitemap.xml$' => 'index.php?feed=sitemap'
        );

        $wp_rewrite->rules = $feed_rules + $wp_rewrite->rules;
    }

    public function parse_request_hook() {
        global $wp;

        if (preg_match('/robots.txt$/i', $wp->request)) {
            $robots = get_option('robots_txt', '');
            die($robots);
        }
    }

    public function do_feed_sitemap_hook() {
        $template_dir = dirname(__FILE__);
        $this->render_template('sitemap-template');
    }

    public function do_meta_boxes_hook() {
        foreach ($this->supported_types as $screen) {
            add_meta_box(
                self::SEO_KEYWORDS_FIELD,
                __('SEO keywords', 'sitemap-seo'),
                array($this, 'seo_post_keywords_metabox'),
                $screen,
                'normal',
                'default'
            );
        }
    }

    public function save_post_hook() {
        global $post;

        if (! in_array($post->post_type, $this->supported_types)) {
            return;
        }

        if (isset($_POST[self::SEO_KEYWORDS_FIELD])) {
            update_post_meta($post->ID, self::SEO_KEYWORDS_FIELD,
                $_POST[self::SEO_KEYWORDS_FIELD]);
        }
    }

    /*------------------------------------------------------------------------*
     * Public
     *------------------------------------------------------------------------*/

    public function register_seo_description_settings() {
        // Add the section to reading settings so we can add our
        // fields to it
        add_settings_section('seo_setting_section',
            'SEO settings',
            array($this, 'seo_setting_section_callback'),
            'general');

        // Add the field with the names and function to use for our new
        // settings, put it in our new section
        add_settings_field('seo_setting_description',
            'Site description',
            array($this, 'seo_setting_description_callback'),
            'general',
            'seo_setting_section');

        add_settings_field('seo_setting_keywords_enabled',
            'Enable keywords',
            array($this, 'seo_setting_keywords_enabled_callback'),
            'general',
            'seo_setting_section');

        add_settings_field('seo_setting_keywords',
            'Keywords',
            array($this, 'seo_setting_keywords_callback'),
            'general',
            'seo_setting_section');

        // Register our setting so that $_POST handling is done for us and
        // our callback function just has to echo the <input>
        register_setting('general', 'seo_setting_description');
        register_setting('general', 'seo_setting_keywords_enabled');
        register_setting('general', 'seo_setting_keywords');
    }

    public function register_robot_settings() {
        register_setting(
            'reading',
            'robots_txt',
            'esc_attr'
        );

        add_settings_field(
            'robots_txt',
            '<label for="robots_txt">'.__('Robots.txt', 'sitemap-seo' ).'</label>',
            array($this, 'robots_txt_fields_html'),
            'reading'
        );

        register_setting(
            'reading',
            'sitemap_settings',
            array($this, 'convert_setting_format')
        );

        add_settings_field('sitemap_settings',
            '<label for="sitemap_settings">'
            .__('Sitemap settings', 'sitemap-seo').'</label>',
            array($this, 'robots_txt_sitemap_settings_html'),
            'reading'
        );
    }

    public function convert_setting_format() {
        $sitemap_settings = implode(',', $_POST['sitemap_settings']);
        return $sitemap_settings;
    }

    public function robots_txt_sitemap_settings_html() {
        $sitemap_post_types = array();
        $sitemap_post_types = get_option( 'sitemap_settings', '' );
        $sitemap_post_types = explode(',', $sitemap_post_types);
        $post_types = get_post_types(array( 'show_ui' => true ), 'objects');

        echo '<select id="sitemap_settings" name="sitemap_settings[]" multiple>';
        foreach($post_types as $post_type) {
            $selected = false;

            if (array_search($post_type->name, $sitemap_post_types, true) !== false) {
                $selected = true;
            }

            echo '<option value="'.$post_type->name.'"'
                .($selected ? 'selected' : '').'>'
                .$post_type->labels->name
                .'</option>';
        }
        echo '</select>';
    }

    public function robots_txt_fields_html() {
        $value = get_option( 'robots_txt', '' );
        echo '<textarea rows="10" cols="100" id="robots_txt" name="robots_txt">'
            .$value
            .'</textarea>';
    }

    public function seo_setting_section_callback() {
        echo '<p>Site description is used as meta description on the front page.</p>';
    }

    public function seo_setting_description_callback() {
        echo sprintf('<textarea name="seo_setting_description"
            id="seo_setting_description" class="large-text code">%s</textarea>',
            get_option('seo_setting_description')
        );
    }

    public function seo_setting_keywords_enabled_callback() {
        echo sprintf('<input type="checkbox" name="seo_setting_keywords_enabled"
            id="seo_setting_keywords_enabled" value="1" %s>',
            (get_option('seo_setting_keywords_enabled') ? ' checked' : '')
        );
    }

    public function seo_setting_keywords_callback() {
        echo sprintf('<textarea name="seo_setting_keywords"
            id="seo_setting_keywords" class="large-text code">%s</textarea>',
            get_option('seo_setting_keywords')
        );
    }

    public function seo_post_keywords_metabox() {
        global $post;

        $value = get_post_meta($post->ID, self::SEO_KEYWORDS_FIELD, true);

        echo sprintf('<textarea id="%1$s" name="%1$s"
            class="custom-text-field large-text">%2$s</textarea>',
            self::SEO_KEYWORDS_FIELD,
            $value
        );
   }

    /*------------------------------------------------------------------------*
     * Private
     *------------------------------------------------------------------------*/

    private function init_textdomain() {
        load_plugin_textdomain($this->plugin_slug, false,
            $this->plugin_rel_base.'/langs/');
    }

    private function flush_rules() {
        $rules = get_option( 'rewrite_rules' );
        if ( !isset( $rules['.*sitemap.xml$'] ) ) {
            global $wp_rewrite;
            $wp_rewrite->flush_rules();
        }
    }

    private function render_template($name, $vars=array()) {
        foreach ($vars as $key => $val) {
            $$key = $val;
        }

        $template_path = $this->plugin_base.'/templates/'.$name.'.php';
        if (file_exists($template_path)) {
            include($template_path);
        } else {
            echo '<p>Rendering of admin template failed</p>';
        }
    }
}

SitemapSeo::get_instance();
