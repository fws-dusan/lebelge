<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * General Settings class
 */
class EC_General_Settings
{
    public function __construct()
    {

    }
    public function loadSettings()
    {
      //for adding new link to the plugins page
      add_filter('plugin_row_meta', array($this, 'plugins_Links'), 10, 2);

      //settings link
      add_filter('plugin_action_links_' . EC_WOO_BUILDER_PLUGIN_SLUG, array($this, 'settings_link'));

      add_action('admin_menu', array($this, 'woo_add_menu_page'));

      $this->load_registered_scripts();
    }

    public function plugins_Links($links, $file)
    {
        return $links;
    }

    public function settings_link($links)
    {
        $settings_link = '<a href="' . admin_url('admin.php?page=' . EC_WOO_BUILDER_SLUG . '') . '">Go to builder</a>';
        array_push($links, $settings_link);
        return $links;
    }

    public function woo_add_menu_page()
    {
        $page_title = __('Email Customizer', EC_WOO_BUILDER_TEXTDOMAIN);
        $menu_title = $page_title;
        add_submenu_page('woocommerce', $page_title, $menu_title, 'manage_options', EC_WOO_BUILDER_SLUG, array($this, 'menu_page_html'));
    }

    public function menu_page_html()
    {
        require_once EC_WOO_BUILDER_PATH . 'pages/admin-page.php';
    }

    public function load_registered_scripts()
    {
        if (isset($_REQUEST["page"]) && sanitize_text_field($_REQUEST["page"]) == EC_WOO_BUILDER_SLUG) {
            add_action('wp_print_scripts', array($this, 'register_script_style'), 102);
            add_action('admin_enqueue_scripts', array($this, 'register_script_style'));
        }
    }

    public function register_script_style()
    {
        $version = EC_WOO_BUILDER_VERSION;
        //TODO: split small parts. make it simple

        /*
        * Styles
        */

        wp_register_style('ec-builder-css-main', EC_WOO_BUILDER_URL . 'assets/css/builder.bundle.css', array(), $version);
        wp_enqueue_style('ec-builder-css-main');

        wp_register_style('ec-builder-css-font-awesome', EC_WOO_BUILDER_URL . 'assets/vendor/components-font-awesome/css/font-awesome.min.css', array(), $version);
        wp_enqueue_style('ec-builder-css-font-awesome');

        wp_register_style('ec-builder-css-animate', EC_WOO_BUILDER_URL . 'assets/vendor/animate.css/animate.min.css', array(), $version);
        wp_enqueue_style('ec-builder-css-animate');

        wp_register_style('ec-builder-css-colorpicker', EC_WOO_BUILDER_URL . 'assets/vendor/colorpicker/css/colorpicker.css', array(), $version);
        wp_enqueue_style('ec-builder-css-colorpicker');

        wp_register_style('ec-builder-css-select2', EC_WOO_BUILDER_URL . 'assets/vendor/select2/dist/css/select2.min.css', array(), $version);
        wp_enqueue_style('ec-builder-css-select2');

        wp_register_style('ec-builder-css-intro', EC_WOO_BUILDER_URL . 'assets/vendor/intro/intro.min.css', array(), $version);
        wp_enqueue_style('ec-builder-css-intro');


        wp_register_style('ec-builder-css-toast', EC_WOO_BUILDER_URL . 'assets/vendor/iziToast/iziToast.min.css', array(), $version);
        wp_enqueue_style('ec-builder-css-toast');

        /*
        * Scripts
        */

        wp_localize_script('ec-builder-js-builder', 'woo_ec_vars', array(
            'home_url' => get_home_url(),
            'admin_url' => admin_url(),
            'plugin_url' => EC_WOO_BUILDER_URL,
            'ajax_url' => admin_url('admin-ajax.php'),
            'version' => EC_WOO_BUILDER_VERSION,
            'preview' => EC_WOO_BUILDER_PREVIEW_PAGE,
            'is_demo' => false,
            'is_rtl' => is_rtl(),
            'show_activate_updates' => get_option('ec_woo_show_activate_updates', EC_WOO_BUILDER_SHOW_ACTIVATE) == 'no' ? false : true
            //TODO: also check purchase code
        ));
        wp_enqueue_script("jquery");
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script("jquery-ui-draggable");
        wp_enqueue_script("jquery-ui-sortable");

        wp_enqueue_media();



        wp_register_script('ec-builder-js-intro', EC_WOO_BUILDER_URL . 'assets/vendor/intro/intro.min.js', array('jquery'), $version);
        wp_enqueue_script('ec-builder-js-intro');

        wp_register_script('ec-builder-js-colorpicker', EC_WOO_BUILDER_URL . 'assets/vendor/colorpicker/js/colorpicker.js', array('jquery'), $version);
        wp_enqueue_script('ec-builder-js-colorpicker');

        wp_register_script('ec-builder-js-colorpicker1', EC_WOO_BUILDER_URL . 'assets/vendor/colorpicker/js/eye.js', array('jquery'), $version);
        wp_enqueue_script('ec-builder-js-colorpicker1');

        wp_register_script('ec-builder-js-colorpicker2', EC_WOO_BUILDER_URL . 'assets/vendor/colorpicker/js/utils.js', array('jquery'), $version);
        wp_enqueue_script('ec-builder-js-colorpicker2');

        wp_register_script('ec-builder-js-tinymce', EC_WOO_BUILDER_URL . 'assets/vendor/tinymce/tinymce.min.js', array(), $version);
        wp_enqueue_script('ec-builder-js-tinymce');

        wp_register_script('ec-builder-js-select2', EC_WOO_BUILDER_URL . 'assets/vendor/select2/dist/js/select2.full.min.js', array('jquery'), $version);
        wp_enqueue_script('ec-builder-js-select2');

        wp_register_script('ec-builder-js-underscore', 'https://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.9.1/underscore-min.js', array('jquery'), $version);
        wp_enqueue_script('ec-builder-js-underscore');

        wp_register_script('ec-builder-js-toast', EC_WOO_BUILDER_URL . 'assets/vendor/iziToast/iziToast.min.js', array('jquery'), $version);
        wp_enqueue_script('ec-builder-js-toast');

        wp_register_script('ec-builder-js-builder', EC_WOO_BUILDER_URL . 'assets/js/builder.bundle.min.js', array('jquery'), $version);
        wp_enqueue_script('ec-builder-js-builder');

        if (is_rtl()) {
            wp_register_style('ec-builder-css-rtl', EC_WOO_BUILDER_URL . 'assets/css/builder-rtl.bundle.css', array(), $version);
            wp_enqueue_style('ec-builder-css-rtl');
        }
    }
}