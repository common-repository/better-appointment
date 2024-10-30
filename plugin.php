<?php

/**
 * Plugin Name: Better Appointment
 * Plugin URI: https://gitlab.com/demayoweb/better-appointment
 * Description: An Appointment Scheduler For Wordpress That Gets Out Of Your Way
 * Version: 1.0
 * Author: deMayo Web Development
 * Author URI: https://demayowebdevelopment.com
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: better-appointment
 * Domain Path: /languages
 */

/**
 * Copyright (c) 2020 Gus deMayo (email: gus@demayowebdevelopment.com). All rights reserved.
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * **********************************************************************
 */

use BetterAppointment\JWTAuth;

require_once __DIR__ . '/vendor/autoload.php';

if(!defined('ABSPATH')) exit;

/**
 * Better_Appointment class
 *
 * @class Better_Appointment The class that holds the entire Better_Appointment plugin
 */
final class Better_Appointment
{
    /**
     * Plugin version
     *
     * @var string
     */
    public $version = '1.0';

    /**
     * Holds various class instances
     *
     * @var array
     */
    private $container = [];

    /**
     * Constructor for the Better_Appointment class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     */
    public function __construct()
    {
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);
        add_action('plugins_loaded', [$this, 'init_plugin']);
        add_action('wp_login', [$this, 'login'], 10, 2);
        add_action('wp_logout', [$this, 'logout']);
    }

    /**
     * Initializes the Better_Appointment() class
     *
     * Checks for an existing Better_Appointment() instance
     * and if it doesn't find one, creates it.
     */
    public static function init()
    {
        static $instance = false;

        if(!$instance)
        {
            $instance = new Better_Appointment();
        }

        return $instance;
    }

    /**
     * Magic getter to bypass referencing plugin.
     *
     * @param $prop
     *
     * @return mixed
     */
    public function __get($prop)
    {
        if(array_key_exists($prop, $this->container))
        {
            return $this->container[$prop];
        }

        return $this->{$prop};
    }

    /**
     * Magic isset to bypass referencing plugin.
     *
     * @param $prop
     *
     * @return mixed
     */
    public function __isset($prop)
    {
        return isset($this->{$prop}) || isset($this->container[$prop]);
    }

    /**
     * Load the plugin after all plugis are loaded
     *
     * @return void
     */
    public function init_plugin()
    {
        $this->init_hooks();
    }

    /**
     * Activation Function
     *
     */
    public function activate()
    {
        if(is_plugin_active('better-appointment-pro/plugin.php'))
        {
            deactivate_plugins('better-appointment-pro/plugin.php');
        }

        if(!get_option('ba_installed'))
        {
            update_option('ba_installed', time());
        }

        update_option('ba_version', $this->version);

        if(!get_option('ba_jwt_secret'))
        {
            update_option(
                'ba_jwt_secret',
                JWTAuth::create_secret()
            );
        }

        update_option('ba_success_page_datetime', true);

        if(!get_option('ba_form_terms'))
        {
            update_option('ba_form_terms', [
                'use' => 1,
                'label' => 'I agree to the Terms & Conditions',
            ]);
        }

        if(!get_option('ba_email_admin'))
        {
            update_option('ba_email_admin', [
                'to' => get_option('admin_email'),
                'subject' => 'New Appointment',
                'message' => 'You have a new appointment scheduled for [date] at [time].',
            ]);
        }

        if(!get_option('ba_email_customer'))
        {
            update_option('ba_email_customer', [
                'to' => '[email]',
                'subject' => 'New Appointment',
                'message' => 'Thank you for scheduling an appointment. Your appointment is set for [date] at [time].',
            ]);
        }

        $jwt = new JWTAuth(get_option('ba_jwt_secret'));
        setcookie('api_token', $jwt->create_token(wp_get_current_user()), 0, '/');
    }

    /**
     * Deactivation Function
     *
     * Nothing being called here yet.
     */
    public function deactivate()
    {
        setcookie('api_token', null);
    }

    /**
     * Login Function
     */
    public function login($user_login, $user)
    {
        $jwt = new JWTAuth(get_option('ba_jwt_secret'));
        setcookie('api_token', $jwt->create_token($user), 0, '/');
    }

    /**
     * Logout Function
     */
    public function logout()
    {
        setcookie('api_token', null);
    }

    /**
     * Initialize the hooks
     *
     * @return void
     */
    public function init_hooks()
    {
        add_action('init', [$this, 'init_classes']);
        add_action('init', [$this, 'localization_setup']);
    }

    /**
     * Instantiate the required classes
     *
     * @return void
     */
    public function init_classes() {

        if($this->is_request('admin'))
        {
            $this->container['admin'] = new BetterAppointment\Admin();
        }

        if($this->is_request('frontend'))
        {
            $this->container['frontend'] = new BetterAppointment\Frontend();
        }

        $this->container['api'] = new BetterAppointment\Api();
        $this->container['assets'] = new BetterAppointment\Assets(__FILE__);
        $this->container['post_type'] = new BetterAppointment\PostType();
    }

    /**
     * Initialize plugin for localization
     *
     * @uses load_plugin_textdomain()
     */
    public function localization_setup()
    {
        load_plugin_textdomain('better_appointment', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }

    /**
     * What type of request is this?
     *
     * @param  string $type admin, ajax, cron or frontend.
     *
     * @return bool
     */
    private function is_request($type)
    {
        switch($type)
        {
            case 'admin':
                return is_admin();
            case 'ajax':
                return defined('DOING_AJAX');
            case 'cron':
                return defined('DOING_CRON');
            case 'frontend':
                return(!is_admin() || defined('DOING_AJAX')) && !defined('DOING_CRON');
            case 'rest':
                return defined('REST_REQUEST');
        }
    }
}

$better_appointment = Better_Appointment::init();
