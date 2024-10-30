<?php

namespace BetterAppointment;

if(!defined('ABSPATH')) exit;

/**
 * Frontend Pages Handler
 */

class Frontend {

    public function __construct()
    {
        new Frontend\DateTime();
        new Frontend\Form();
    }
}
