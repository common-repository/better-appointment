<?php

namespace BetterAppointment\Api;

use WP_REST_Controller;
use WP_REST_Response;
use BetterAppointment\JWTAuth;

if(!defined('ABSPATH')) exit;

/**
 * REST_API Handler
 */

class GetAppointments extends WP_REST_Controller
{
    public function __construct()
    {
        $this->namespace = 'better-appointment/v1';
        $this->rest_base = 'get-appointments';
    }

    /**
     * Register the routes
     *
     * @return void
     */
    public function register_routes()
    {
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base,
            [
                [
                    'methods' => 'get',
                    'callback' => [
                        $this,
                        'get_appointments',
                    ],
                    'permission_callback' => [
                        $this,
                        'permission_callback',
                    ],
                ],
            ]
        );
    }

    /**
     * Gets All Appointments
     *
     * @param WP_REST_Request $request Full details about the request.
     *
     * @return WP_REST_Response
     */
    public function get_appointments($request)
    {
        $appointments = array_map(function($a) {
            $appointment = [
                'ID' => $a->ID,
            ];

            $appointment['first_name'] = get_post_meta($a->ID, 'first_name', $single = true);
            $appointment['last_name'] = get_post_meta($a->ID, 'last_name', $single = true);
            $appointment['email'] = get_post_meta($a->ID, 'email', $single = true);
            $appointment['phone'] = get_post_meta($a->ID, 'phone', $single = true);
            $appointment['message'] = get_post_meta($a->ID, 'message', $single = true);
            $appointment['date'] = get_post_meta($a->ID, 'date', $single = true);
            $appointment['time'] = get_post_meta($a->ID, 'time', $single = true);

            return $appointment;
        }, get_posts([
            'post_type' => 'ba_appointment',
            'numberposts' => -1,
        ]));

        return new WP_REST_Response(
            $appointments,
            200
        );
    }

    /**
     * Checks if a given request has access.
     *
     * @param  WP_REST_Request $request Full details about the request.
     *
     * @return true|WP_REST_Response True if the request has read access, WP_REST_Response object otherwise.
     */
    public function permission_callback($request)
    {
        $jwt = new JWTAuth(get_option('ba_jwt_secret'));
        return $jwt->validate_token($_COOKIE);
    }
}