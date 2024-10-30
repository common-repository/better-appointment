<?php

namespace BetterAppointment\Frontend;

if(!defined('ABSPATH')) exit;

/**
 * Frontend Pages Handler
 */

class DateTime
{
    public function __construct()
    {
        add_shortcode('better-appointment-datetime', [
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
        if(!isset($_GET['date']) || !isset($_GET['time']))
        {
            return 'No Call Scheduled';
        }

        $time = date('g:i A', strtotime($_GET['time']));
        $date = date('F j, Y', strtotime($_GET['date']));
        return $time . ' On ' . $date;
    }
}