<?php

namespace BetterAppointment\Api;

use WP_REST_Controller;
use WP_REST_Response;
use BetterAppointment\JWTAuth;

if(!defined('ABSPATH')) exit;

/**
 * REST_API Handler
 */

class DeleteAppointment extends WP_REST_Controller
{
    public function __construct()
    {
        $this->namespace = 'better-appointment/v1';
        $this->rest_base = 'delete-appointment/(?P<id>[0-9]+)';
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
                    'methods' => 'delete',
                    'callback' => [
                        $this,
                        'delete_appointment',
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
     * Deletes An Appointment
     *
     * @param WP_REST_Request $request Full details about the request.
     *
     * @return WP_REST_Response
     */
    public function delete_appointment($request)
    {
        $id = (int)$request['id'];

        if(!get_post_status($id))
        {
            return WP_REST_Response(
                'Invalid ID',
                400
            );
        }

        wp_delete_post($id);

        return new WP_REST_Response(200);
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