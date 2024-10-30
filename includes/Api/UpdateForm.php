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

class UpdateForm extends WP_REST_Controller
{
    protected $validator;
    protected $tags_allowed = [];

    public function __construct()
    {
        $this->namespace = 'better-appointment/v1';
        $this->rest_base = 'update-form';
        $this->validation = new Validation();

        $this->tags_allowed = [
            'a' => [
                'href',
            ],
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
                        'update_form',
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
    public function update_form($request)
    {
        $validation = $this->validation->validate(
            $_POST,
            $this->get_validation_rules(),
            $this->get_validation_messages()
        );

        if($validation->fails())
        {
            return new WP_REST_Response(
                $validation
                    ->errors()
                    ->firstOfAll(),
                400
            );
        }

        $terms = [];
        $terms['label'] = wp_kses($_POST['terms']['label'], $this->tags_allowed);
        $terms['use'] = (int)sanitize_text_field($_POST['terms']['use']);
        update_option('ba_form_terms', $terms);
        update_option('ba_form_theme', sanitize_text_field($_POST['theme']));

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
            'terms.label' => 'required_if:terms.use,1|max:255',
            'terms.use' => 'required|in:0,1',
            'theme' => 'required|in:box,flat',
        ];
    }

    protected function get_validation_messages()
    {
        return [
            'terms.label:required_if' => 'You must provide a valid label.',
            'terms.label:max' => 'The label you provided is too long.',
            'terms.use:required' => 'You must provide a valid choice.',
            'terms.use:in' => 'You must provide a valid choice.',
            'theme:required' => 'You must choose a valid theme.',
            'theme:in' => 'You must choose a valid theme.',
        ];
    }
}
