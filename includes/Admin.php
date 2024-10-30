<?php

namespace BetterAppointment;

if(!defined('ABSPATH')) exit;

/**
 * Admin Pages Handler
 */

class Admin
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'admin_menu']);
    }

    /**
     * Register our menu page
     *
     * @return void
     */
    public function admin_menu()
    {
        global $submenu;
        $capability = 'manage_options';
        $slug = 'better-appointment';
        $hook = add_menu_page(__('Better Appointment', 'better-appointment'), __('Better Appointment', 'better-appointment'), $capability, $slug, [$this, 'plugin_page'], 'dashicons-calendar');

        if(current_user_can($capability))
        {
            $new_appointments = array_filter(get_posts([
                'post_type' => 'ba_appointment',
                'numberposts' => -1,
            ]), function($appointment) {
                return $appointment->post_date == $appointment->post_modified;
            });

            $notification_bubble = (count($new_appointments) > 0)
                ? '<span class="update-plugins count-1"><span class="update-count">' . count($new_appointments) . '</span></span>'
                : '';

            $submenu[$slug][] = [__('Appointments ' . $notification_bubble, 'better-appointment'), $capability, 'admin.php?page=' . $slug . '#/'];
            $submenu[$slug][] = [__('Settings', 'better-appointment'), $capability, 'admin.php?page=' . $slug . '#/settings'];
        }

        add_action('load-' . $hook, [$this, 'init_hooks']);
    }

    /**
     * Initialize our hooks for the admin page
     *
     * @return void
     */
    public function init_hooks()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    /**
     * Load scripts and styles for the app
     *
     * @return void
     */
    public function enqueue_scripts()
    {
        wp_enqueue_style('better-appointment-admin');
        wp_enqueue_script('better-appointment-admin');
    }

    /**
     * Render our admin page
     *
     * @return void
     */
    public function plugin_page()
    {
        echo '<div class="wrap"><div id="better-appointment"></div></div>';
    }
}
