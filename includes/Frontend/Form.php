<?php

namespace BetterAppointment\Frontend;

if(!defined('ABSPATH')) exit;

/**
 * Frontend Pages Handler
 */

class Form
{
    public function __construct()
    {
        add_shortcode('better-appointment', [
            $this,
            'render',
        ]);
    }

    /**
     * Render Shortcode
     *
     * @param  array $atts
     * @param  string $content
     *
     * @return string
     */
    public function render()
    {
        wp_enqueue_style('better-appointment-frontend');
        wp_enqueue_script('better-appointment-frontend');
        return '<div id="better-appointment"></div>';
    }
}