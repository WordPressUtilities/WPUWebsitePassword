<?php

/*
Plugin Name: WPU Website Password
Plugin URI: https://github.com/WordPressUtilities/wpuwebsitepassword
Description: Add a single password requirement to your website
Version: 0.8.2
Author: Darklg
Author URI: http://darklg.me/
License: MIT License
License URI: http://opensource.org/licenses/MIT
*/

class WPUWebsitePassword {
    public $plugin_version = '0.8.2';
    public $option;
    public $has_user_password = false;
    public $settings;
    public $settings_details;
    private $options = array(
        'plugin_id' => 'wpuwebsitepassword'
    );

    public function __construct() {
        add_action('plugins_loaded', array(&$this, 'load_translation'));
        add_action('init', array(&$this, 'set_options'));
        add_action('init', array(&$this, 'init'));
        add_action('template_redirect', array(&$this, 'trigger_password_prompt'));
        add_action('wpuwebsitepassword_before_prompt', array(&$this, 'test_password_prompt'), 10);
        add_action('wpuwebsitepassword_before_template', array(&$this, 'load_default_template'), 90);
        add_filter('wpuwebsitepassword_tpl_form__title', array(&$this, 'load_default_title'), 90, 2);
        add_filter('rest_endpoints', array(&$this, 'disable_rest_endpoints'), 10, 1);
        add_filter('wputh_post_metas_boxes', array(&$this, 'wputh_post_metas_boxes'), 10, 1);
        add_filter('wputh_post_metas_fields', array(&$this, 'wputh_post_metas_fields'), 10, 1);
    }

    public function load_translation() {
        load_plugin_textdomain('wpuwebsitepassword', false, dirname(plugin_basename(__FILE__)) . '/lang/');
    }

    public function set_options() {
        /* Options */
        $this->settings_details = array(
            'create_page' => true,
            'plugin_basename' => plugin_basename(__FILE__),
            'parent_page' => 'options-general.php',
            'plugin_name' => __('Website Password', 'wpuwebsitepassword'),
            'plugin_id' => $this->options['plugin_id'],
            'option_id' => $this->options['plugin_id'] . '_options',
            'sections' => array(
                'protection' => array(
                    'name' => __('Protection', 'wpuwebsitepassword')
                ),
                'user' => array(
                    'name' => __('User')
                ),
                'password' => array(
                    'name' => __('Password')
                ),
                'template' => array(
                    'name' => __('Default page options', 'wpuwebsitepassword')
                )
            )
        );
        $this->settings = array(
            'enable_protection' => array(
                'label' => __('Protection', 'wpuwebsitepassword'),
                'label_check' => __('Enable protection', 'wpuwebsitepassword'),
                'type' => 'checkbox'
            ),
            'redirect_homepage' => array(
                'label' => __('Redirect to home', 'wpuwebsitepassword'),
                'label_check' => __('Redirect unauthenticated access to the homepage.', 'wpuwebsitepassword'),
                'type' => 'checkbox'
            ),
            'user_protection' => array(
                'label' => __('User Access', 'wpuwebsitepassword'),
                'label_check' => __('Protect via login rather than simple password.', 'wpuwebsitepassword'),
                'type' => 'checkbox',
                'section' => 'user'
            ),
            'password' => array(
                'label' => __('Password', 'wpuwebsitepassword'),
                'help' => __('Visitors will have to type this password to access your website.', 'wpuwebsitepassword'),
                'section' => 'password'
            ),
            'case_sensitive' => array(
                'label' => __('Case sensitive', 'wpuwebsitepassword'),
                'label_check' => __('Visitors should type this password with uppercase or lowercase letters if presents.', 'wpuwebsitepassword'),
                'type' => 'checkbox',
                'section' => 'password'
            ),
            'cookie_duration' => array(
                'label' => __('Cookie Duration', 'wpuwebsitepassword'),
                'type' => 'number',
                'help' => __('Visitors will be automatically logged out after this number of seconds.', 'wpuwebsitepassword'),
                'section' => 'password'
            ),
            'load_assets' => array(
                'label' => __('Load assets', 'wpuwebsitepassword'),
                'label_check' => __('Site CSS will be loaded.', 'wpuwebsitepassword'),
                'type' => 'checkbox',
                'section' => 'template'
            ),
            'load_default_style' => array(
                'label' => __('Load default style', 'wpuwebsitepassword'),
                'label_check' => __('Default CSS will be loaded.', 'wpuwebsitepassword'),
                'type' => 'checkbox',
                'section' => 'template'
            ),
            'load_header_image' => array(
                'label' => __('Load header image', 'wpuwebsitepassword'),
                'label_check' => __('Default header image will be loaded.', 'wpuwebsitepassword'),
                'type' => 'checkbox',
                'section' => 'template'
            )
        );
    }

    public function init() {

        include dirname(__FILE__) . '/inc/WPUBaseUpdate/WPUBaseUpdate.php';
        $this->settings_update = new \wpuwebsitepassword\WPUBaseUpdate(
            'WordPressUtilities',
            'wpuwebsitepassword',
            $this->plugin_version);

        include dirname(__FILE__) . '/inc/WPUBaseSettings/WPUBaseSettings.php';
        $wpubasesettings = new \wpuwebsitepassword\WPUBaseSettings($this->settings_details, $this->settings);

        $this->option = $wpubasesettings->get_settings();
    }

    public function disable_rest_endpoints($endpoints) {
        if ($this->need_password_prompt()) {
            $endpoints = array();
        }
        return $endpoints;
    }

    public function need_password_prompt() {
        /* Is protection enabled ? */
        if ($this->option['enable_protection'] != '1') {
            return false;
        }

        /* Disabled on admin */
        if (is_admin()) {
            return false;
        }

        /* Disable on login/register page */
        if (in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php'))) {
            return false;
        }

        /* Disable via a hook */
        if (apply_filters('wpuwebsitepassword_prevent_prompt', false)) {
            return false;
        }

        /* Disabled if user is logged-in */
        if (is_user_logged_in()) {
            return false;
        }

        /* Disable for a post */
        if (is_singular() && get_post_meta(get_the_ID(), 'wpuwebsitepassword_disable_protection', 1)) {
            return false;
        }

        return true;
    }

    public function trigger_password_prompt() {

        if (!$this->need_password_prompt()) {
            return;
        }

        /* Trigger prompt */
        do_action('wpuwebsitepassword_before_prompt');

        /* Check user password */
        if ($this->has_user_password || $this->has_user_password()) {
            /* Refresh password */
            $this->set_user_password();
            return;
        }

        if ($this->get_redirect_homepage() && !is_home() && !is_front_page()) {
            wp_redirect(home_url());
            die;
        }

        do_action('wpuwebsitepassword_before_template');

    }

    public function test_password_prompt() {
        // Check nonce
        if (!isset($_POST['wpuwebsitepassword_nonce']) || !wp_verify_nonce($_POST['wpuwebsitepassword_nonce'], 'wpuwebsitepassword_form')) {
            return;
        }

        if (!isset($_POST['password'])) {
            return;
        }

        $tmp_password = esc_html(stripslashes($_POST['password']));

        if ($this->test_password($this->hash_password($tmp_password))) {
            $this->has_user_password = 1;
        }

    }

    public function load_default_template() {
        $wpuwebsitepassword_styles = '';
        if (isset($this->option['load_assets']) && $this->option['load_assets'] == '1') {
            ob_start();
            wp_head();
            $out = ob_get_clean();
            /* Extract all stylesheets from wp_head */
            preg_match_all('/<link rel=\'stylesheet([^>]*)\/>/', $out, $matches);
            $wpuwebsitepassword_styles = implode('', $matches[0]);
        }

        include dirname(__FILE__) . '/tpl/base.php';
        die;
    }

    public function load_default_title($title, $context) {
        if ($context != 'title' && isset($this->option['load_header_image']) && $this->option['load_header_image'] == '1' && has_header_image()) {
            $title = '<img src="' . get_header_image() . '" alt="' . esc_attr($title) . '" />';
        }
        return $title;
    }

    /* ----------------------------------------------------------
      Post settings
    ---------------------------------------------------------- */

    public function wputh_post_metas_boxes($boxes) {
        $boxes['box_wpuwebsitepassword'] = array(
            'name' => __('Website Password', 'wpuwebsitepassword'),
            'post_type' => array('post', 'page')
        );
        return $boxes;
    }

    public function wputh_post_metas_fields($fields) {
        $fields['wpuwebsitepassword_disable_protection'] = array(
            'box' => 'box_wpuwebsitepassword',
            'name' => __('Disable Password Protection', 'wpuwebsitepassword'),
            'type' => 'select'
        );
        return $fields;
    }

    /* ----------------------------------------------------------
      Password settings
    ---------------------------------------------------------- */

    public function get_hashed_password() {
        $password = get_bloginfo('name');
        if (isset($this->option['password']) && !empty($this->option['password'])) {
            $password = $this->get_case_sensitive() ? $this->option['password'] : strtolower($this->option['password']);
        }
        return $this->hash_password($password);
    }

    public function hash_password($password) {
        $password = $this->get_case_sensitive() ? $password : strtolower($password);
        return md5(get_bloginfo('name') . $password);
    }

    public function test_password($password = '') {
        /* Format the submitted password */
        $user_password = $this->get_case_sensitive() ? $password : strtolower($password);

        /* Is password correct ? */
        return $user_password == $this->get_hashed_password();
    }

    public function get_case_sensitive() {
        return isset($this->option['case_sensitive']) && $this->option['case_sensitive'];
    }

    public function get_redirect_homepage() {
        return isset($this->option['redirect_homepage']) && $this->option['redirect_homepage'];
    }

    public function get_cookie_duration() {
        if (isset($this->option['cookie_duration']) && is_numeric($this->option['cookie_duration']) && $this->option['cookie_duration']) {
            return intval($this->option['cookie_duration'], 10);
        }
        return 1800;
    }

    public function set_user_password() {
        setcookie($this->options['plugin_id'], $this->get_hashed_password(), time() + $this->get_cookie_duration(), '/');
        $_COOKIE[$this->options['plugin_id']] == $this->get_hashed_password();
    }

    public function has_user_password() {
        /* Check for correct cookie informations */
        if (empty($_COOKIE) || !is_array($_COOKIE) || !isset($_COOKIE[$this->options['plugin_id']])) {
            return false;
        }

        return $this->test_password($_COOKIE[$this->options['plugin_id']]);
    }

}

$WPUWebsitePassword = new WPUWebsitePassword();
