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

class UpdateEmail extends WP_REST_Controller
{
    protected $validation;
    protected $tags_allowed = [];

    public function __construct()
    {
        $this->namespace = 'better-appointment/v1';
        $this->rest_base = 'update-email';
        $this->validation = new Validation();
        
        $this->tags_allowed = [
            'p' => [],
            'strong' => [],
            'em' => [],
            'u' => [],
            's' => [],
            'h1' => [],
            'h2' => [],
            'h3' => [],
            'h4' => [],
            'h5' => [],
            'h6' => [],
            'a' => [
                'href' => [],
            ],
            'ol' => [],
            'ul' => [],
            'li' => [],
        ];
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
                        'update_email',
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
    public function update_email($request)
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

        $admin = [];
        $customer = [];
        $admin['to'] = sanitize_email($_POST['admin']['to']);
        $admin['subject'] = sanitize_text_field($_POST['admin']['subject']);
        $admin['message'] = wp_kses($_POST['admin']['message'], $this->tags_allowed);
        $customer['to'] = sanitize_text_field($_POST['customer']['to']);
        $customer['subject'] = sanitize_text_field($_POST['customer']['subject']);
        $customer['message'] = wp_kses($_POST['customer']['message'], $this->tags_allowed);

        update_option('ba_email_admin', $admin);
        update_option('ba_email_customer', $customer);

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
            'admin.to' => 'required|email|max:255',
            'admin.subject' => 'required|max:255',
            'admin.message' => 'required',
            'customer.to' => 'required|max:255',
            'customer.subject' => 'required|max:255',
            'customer.message' => 'required',
        ];
    }

    protected function get_validation_messages()
    {
        return [
            'admin.to:required' => 'You must provide a valid email.',
            'admin.to:email' => 'You must provide a valid email.',
            'admin.to:max' => 'You must provide a valid email.',      
            'admin.subject:required' => 'You must provide a subject line.',
            'admin.subject:max' => 'The subject line you provided is too long.',
            'admin.message:required' => 'You must provide a message.',
            'customer.to:required' => 'You must provide a valid email.',
            'customer.to:max' => 'You must provide a valid email.',      
            'customer.subject:required' => 'You must provide a subject line.',
            'customer.subject:max' => 'The subject line you provided is too long.',
            'customer.message:required' => 'You must provide a message.',
        ];
    }
}
