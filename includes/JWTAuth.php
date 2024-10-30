<?php

namespace BetterAppointment;

use Firebase\JWT\JWT as JWT;
use Hackzilla\PasswordGenerator\Generator\ComputerPasswordGenerator;

if(!defined('ABSPATH')) exit;

/**
 * JWT Authentication Class
 */

class JWTAuth
{
    protected $secret;

    public function __construct($secret)
    {
        $this->secret = $secret;
    }

    public static function create_secret()
    {
        $computer_password_generator = new ComputerPasswordGenerator();
        
        return $computer_password_generator
            ->setUppercase()
            ->setLowercase()
            ->setNumbers()
            ->setSymbols()
            ->setLength(255)
            ->generatePassword();
    }

    public function create_token($user)
    {
        return JWT::encode([
            'sub' => $user->ID,
            'name' => $user->user_login,
            'iat' => strtotime('now'),
        ], $this->secret);
    }

    public function validate_token($cookie)
    {
        if(!isset($cookie['api_token']))
        {
            return false;
        }

        try
        {
            $payload = JWT::decode($cookie['api_token'], $this->secret, ['HS256']);
        } catch(\Exception $exception) {
            return false;
        }

        if(!$user = get_user_by('id', $payload->sub))
        {
            return false;
        }

        if($user->user_login != $payload->name)
        {
            return false;
        }

        if((strtotime('now') - $payload->iat) > 604800)
        {
            return false;
        }

        return true;
    }
}