<?php

namespace BetterAppointment\Rule;

use Rakit\Validation\Rule;
use ReCaptcha\ReCaptcha;

if(!defined('ABSPATH')) exit;

class ReCaptchaRule extends Rule
{
    protected $message;
    protected $recaptcha;

    public function __construct()
    {
        $this->message = 'The ReCaptcha challenge failed.';

        if(!empty(get_option('ba_recaptcha_secret_key')))
        {
            $this->recaptcha = new Recaptcha(get_option('ba_recaptcha_secret_key'));
        }
    }

    public function check($value) : bool
    {
        return $this->recaptcha
            ->setExpectedAction('schedule')
            ->setScoreThreshold(0.5)
            ->verify($value)
            ->isSuccess();
    }
}