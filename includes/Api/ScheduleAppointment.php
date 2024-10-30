<?php

namespace BetterAppointment\Api;

use WP_REST_Controller;
use WP_REST_Response;
use BetterAppointment\Validation;

if(!defined('ABSPATH')) exit;

/**
 * REST_API Handler
 */

class ScheduleAppointment extends WP_REST_Controller
{
    protected $validation;

    public function __construct()
    {
        $this->namespace = 'better-appointment/v1';
        $this->rest_base = 'schedule-appointment';
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
                        'schedule_appointment',
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
    public function schedule_appointment($request)
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

        $new_appointment = wp_insert_post([
            'post_title' => 'New Appointment',
            'post_type' => 'ba_appointment',
            'post_status' => 'publish',
        ], $wp_error = false);

        $appointment = [];
        $appointment['first_name'] = sanitize_text_field($_POST['first_name']);
        $appointment['last_name'] = sanitize_text_field($_POST['last_name']);
        $appointment['email'] = sanitize_email($_POST['email']);
        $appointment['phone'] = sanitize_text_field($_POST['phone']);
        $appointment['message'] = sanitize_textarea_field($_POST['message']);
        $appointment['date'] = sanitize_text_field($_POST['date']);
        $appointment['time'] = sanitize_text_field($_POST['time']);

        foreach($appointment as $name => $value)
        {
            add_post_meta($new_appointment, $name, $value);
        }

        $this->send_email(get_option('ba_email_admin'), $appointment);
        $this->send_email(get_option('ba_email_customer'), $appointment);
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
        return true;
    }

    protected function send_email($settings, $inputs)
    {
        $search = [
            '[first_name]',
            '[last_name]',
            '[email]',
            '[phone]',
            '[message]',
            '[date]',
            '[time]',
        ];

        $replace = [
            $inputs['first_name'],
            $inputs['last_name'],
            $inputs['email'],
            $inputs['phone'],
            $inputs['message'],
            $inputs['date'],
            $inputs['time'],
        ];

        wp_mail(
            $to = str_replace($search, $replace, $settings['to']),
            $subject = str_replace($search, $replace, $settings['subject']),
            $message = str_replace($search, $replace, $settings['message']),
            $headers = [
                'Content-Type: text/html',
            ]
        );
    }

    protected function get_validation_rules()
    {
        return [
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|max:255',
            'message' => 'max:1000',
            'date' => 'required|max:255',
            'time' => 'required|max:255',
            'recaptcha_response' => (
                !empty(get_option('ba_recaptcha_site_key')) &&
                !empty(get_option('ba_recaptcha_secret_key'))
            ) ? 'required|recaptcha' : '',
            'terms' => (get_option('ba_form_terms'))
                ? 'required|in:true' : '',
        ];
    }

    protected function get_validation_messages()
    {
        $messages = [
            'first_name:required' => 'You must provide a valid first name.',
            'first_name:max' => 'The first name you provided is too long.',
            'last_name:required' => 'You must provide a valid last name.',
            'last_name:max' => 'The last name you provided is too long.',
            'email:required' => 'You must provide a valid email.',
            'email:email' => 'You must provide a valid email.',
            'email:max' => 'The email you provided is too long.',
            'phone:required' => 'You must provide a valid phone.',
            'phone:max' => 'The phone you provided is too long.',
            'message:max' => 'The message you provided is too long.',
            'date:required' => 'You must provide a valid date.',
            'date:max' => 'You must provide a valid date.',
            'time:required' => 'You must provide a valid time.',
            'time:max' => 'You must provide a valid time.',
        ];

        if
        (
            !empty(get_option('ba_recaptcha_site_key')) &&
            !empty(get_option('ba_recaptcha_secret_key'))
        )
        {
            $messages['recaptcha_response:required'] = 'You must provide a valid ReCaptcha response.';
            $messages['recaptcha_response:recaptcha'] = 'You must provide a valid ReCaptcha response.';
        }

        if(get_option('ba_form_terms'))
        {
            $messages['terms:required'] = 'We cannot schedule an appointment without agreement to our Terms & Conditions.';
            $messages['terms:in'] = 'We cannot schedule an appointment without agreement to our Terms & Conditions.';
        }

        return $messages;
    }
}
