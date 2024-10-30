<?php

namespace BetterAppointment\Api;

use WP_REST_Controller;
use WP_REST_Response;
use BetterAppointment\JWTAuth;
use BetterAppointment\Validation;

if(!defined('ABSPATH')) exit;

/**
 * REST_API Handler
 */

class UpdateSuccess extends WP_REST_Controller
{
    protected $validation;

    public function __construct()
    {
        $this->namespace = 'better-appointment/v1';
        $this->rest_base = 'update-success';
        $this->validation = new Validation();
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
                    'methods' => 'post',
                    'callback' => [
                        $this,
                        'update_success',
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
     * Saves a setting to the WP Options Table.
     *
     * @param WP_REST_Request $request Full details about the request.
     *
     * @return WP_REST_Response
     */
    public function update_success($request)
    {
        $validation = $this->validation->validate(
            $_POST,
            $this->get_validation_rules(),
            $this->get_validation_messages()
        );

        if($validation->fails())
        {
            return new WP_REST_Response(
                $validation->errors()->firstOfAll(),
                400
            );
        }

        update_option('ba_success_page_url', esc_url_raw($_POST['url']));
        update_option('ba_success_page_datetime', sanitize_text_field($_POST['datetime']));

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

    protected function get_validation_rules()
    {
        return [
            'url' => 'max:255',
            'datetime' => 'required|in:0,1',
        ];
    }

    protected function get_validation_messages()
    {
        return [
            'url:max' => 'The URL you provided is too long.',
            'datetime:required' => 'You must provide a valid choice.',
            'datetime:in' => 'You must provide a valid choice.',            
        ];
    }
}
