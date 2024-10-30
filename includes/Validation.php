<?php

namespace BetterAppointment;

use Rakit\Validation\Validator;

if(!defined('ABSPATH')) exit;

/**
 * Custom Post Type Classes
 */

class Validation
{
    protected $validator;

    public function __construct()
    {
        $this->validator = new Validator();
        $this->validator->addValidator('recaptcha', new Rule\ReCaptchaRule());
    }

    public function validate($inputs, $rules, $messages = [])
    {
        return $this->validator->validate($inputs, $rules, $messages);
    }
}