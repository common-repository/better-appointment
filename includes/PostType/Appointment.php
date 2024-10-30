<?php

namespace BetterAppointment\PostType;

if(!defined('ABSPATH')) exit;

/**
 * Custom Post Type Classes
 */

class Appointment
{
    /**
     * Register Custom Post Types
     */
    public function register_post_types()
    {
        register_post_type('ba_appointment', [
            'labels' => [
                'name' => 'Appointments',
                'singular_name' => 'Appointment',
                'menu_name' => 'Appointments',
                'name_admmin_bar' => 'Appointment',
                'add_new' => 'Add New',
                'add_new_item' => 'Add New Appointment',
                'new_item' => 'New Appointment',
                'edit_item' => 'Edit Appointment',
                'view_item' => 'View Appointment',
                'all_items' => 'All Appointments',
                'search_items' => 'Search Appointments',
                'not_found' => 'No Appointments Found',
                'not_found_in_trash' => 'No Appointments Found In Trash',
            ],
            'public' => true,
            'exclude_from_search' => true,
            'publicly_queryable' => true,
            'show_ui' => false,
            'show_in_menu' => false,
            'show_in_admin_bar' => false,
            'show_in_rest' => true,
            'capability_type' => 'post',
            'has_archive' => false,
            'hierarchical' => false,
            'supports' => [
                'title',
            ],
        ]);
    }
}