<?php

namespace BetterAppointment\Api;

use WP_REST_Controller;
use WP_REST_Response;
use BetterAppointment\JWTAuth;

if(!defined('ABSPATH')) exit;

/**
 * REST_API Handler
 */

class GetSettings extends WP_REST_Controller
{
    protected $tags_allowed_label;
    protected $tags_allowed_message;

    public function __construct()
    {
        $this->namespace = 'better-appointment/v1';
        $this->rest_base = 'get-settings';

        $this->tags_allowed_label = [
            'a' => [
                'href',
            ],
        ];

        $this->tags_allowed_message = [
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
                    'methods' => 'get',
                    'callback' => [
                        $this,
                        'get_settings',
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
     * Gets the settings from the WP Options Table.
     *
     * @param WP_REST_Request $request Full details about the request.
     *
     * @return WP_REST_Response
     */
    public function get_settings($request)
    {
        $jwt = new JWTAuth(get_option('ba_jwt_secret'));

        $settings = [
            'email' => [
                'admin' => (!empty(get_option('ba_email_admin')))
                    ? [
                        'to' => get_option('ba_email_admin')['to'],
                        'subject' => get_option('ba_email_admin')['subject'],
                        'message' => wp_kses(get_option('ba_email_admin')['message'], $this->tags_allowed_message),
                    ]
                    : [
                        'to' => '',
                        'subject' => '',
                        'message' => '',
                    ],
                'customer' => (!empty(get_option('ba_email_customer')))
                    ? [
                        'to' => get_option('ba_email_customer')['to'],
                        'subject' => get_option('ba_email_customer')['subject'],
                        'message' => wp_kses(get_option('ba_email_customer')['message'], $this->tags_allowed_message),
                    ]
                    : [
                        'to' => '',
                        'subject' => '',
                        'message' => '',
                    ],
            ],
            'form' => [
                'terms' => (!empty(get_option('ba_form_terms')))
                    ? [
                        'label' => wp_kses(get_option('ba_form_terms')['label'], $this->tags_allowed_label),
                        'use' => get_option('ba_form_terms')['use'],
                    ]
                    : [
                        'label' => '',
                        'use' => 0,
                    ],
                'theme' => (!empty(get_option('ba_form_theme')))
                    ? esc_attr(get_option('ba_form_theme'))
                    : 'box',
            ],
            'recaptcha' => ($jwt->validate_token($_COOKIE))
                ? [
                    'site_key' => (!empty(get_option('ba_recaptcha_site_key')))
                        ? get_option('ba_recaptcha_site_key')
                        : '',
                    'secret_key' => (!empty(get_option('ba_recaptcha_secret_key')))
                        ? get_option('ba_recaptcha_secret_key')
                        : '',
                ] : [
                    'site_key' => (!empty(get_option('ba_recaptcha_site_key')))
                        ? get_option('ba_recaptcha_site_key')
                        : '',
                ],
            'success_page' => [
                'datetime' => (!empty(get_option('ba_success_page_datetime')))
                    ? get_option('ba_success_page_datetime')
                    : 0,
                'url' => (!empty(get_option('ba_success_page_url')))
                    ? esc_url(get_option('ba_success_page_url'))
                    : '',
            ],
        ];

        return new WP_REST_Response(
            $this->sanitize_array($settings),
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
        return true;
    }

    protected function sanitize_array($array)
    {
        return array_map(function($row) {
            if(is_array($row))
            {
                return $this->sanitize_array($row);
            }

            if(is_numeric($row))
            {
                return (int)$row;
            }

            return stripslashes($row);
        }, $array);
    }
}