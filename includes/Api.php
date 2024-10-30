<?php

namespace BetterAppointment;

use WP_REST_Controller;

if(!defined('ABSPATH')) exit;

/**
 * REST_API Handler
 */

class Api extends WP_REST_Controller
{
    public function __construct()
    {
        add_action('rest_api_init', [
            $this,
            'register_routes',
        ]);
    }

    /**
     * Register the API routes
     *
     * @return void
     */
    public function register_routes()
    {
        (new Api\DeleteAppointment())->register_routes();
        (new Api\GetAppointments())->register_routes();
        (new Api\GetAppointment())->register_routes();
        (new Api\SaveAppointment())->register_routes();
        (new Api\ScheduleAppointment())->register_routes();
        (new Api\UpdateAppointment())->register_routes();
        (new Api\GetSettings())->register_routes();
        (new Api\UpdateEmail())->register_routes();
        (new Api\UpdateForm())->register_routes();
        (new Api\UpdateReCaptcha())->register_routes();
        (new Api\UpdateSuccess())->register_routes();
    }
}
