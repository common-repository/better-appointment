<?php

namespace BetterAppointment\Api;

use WP_REST_Controller;
use WP_REST_Response;
use BetterAppointment\JWTAuth;

if(!defined('ABSPATH')) exit;

/**
 * REST_API Handler
 */

class GetAppointment extends WP_REST_Controller
{
    public function __construct()
    {
        $this->namespace = 'better-appointment/v1';
        $this->rest_base = 'get-appointment/(?P<id>[0-9]+)';
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
                        'get_appointment',
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
     * Get An Appointment
     *
     * @param WP_REST_Request $request Full details about the request.
     *
     * @return WP_REST_Response
     */
    public function get_appointment($request)
    {
        $id = (int)$request['id'];

        if
        (
            !wp_update_post([
                'ID' => $id,
                'post_updated' => strtotime('now'),
            ])
        )
        {
            return WP_REST_Response(
                'Invalid ID',
                400
            );
        }

        $appointment = [
            'ID' => $id
        ];

        $appointment['first_name'] = get_post_meta($id, 'first_name', $single = true);
        $appointment['last_name'] = get_post_meta($id, 'last_name', $single = true);
        $appointment['email'] = get_post_meta($id, 'email', $single = true);
        $appointment['phone'] = get_post_meta($id, 'phone', $single = true);
        $appointment['message'] = get_post_meta($id, 'message', $single = true);
        $appointment['date'] = get_post_meta($id, 'date', $single = true);
        $appointment['time'] = get_post_meta($id, 'time', $single = true);

        return new WP_REST_Response(
            $appointment,
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