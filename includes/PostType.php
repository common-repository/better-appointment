<?php

namespace BetterAppointment;

if(!defined('ABSPATH')) exit;

/**
 * Custom Post Type Classes
 */

class PostType
{
    public function __construct()
    {
        $this->register_post_types();
    }

    /**
     * Register Custom Post Types
     */
    public function register_post_types()
    {
        (new PostType\Appointment())->register_post_types();
    }
}