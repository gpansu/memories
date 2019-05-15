<?php

// src/Service/SecurityService.php
namespace App\Service;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class SecurityService
{
    private $captchaSecret;
    private $passwordEncoder;

    /**
     * SecurityService constructor.
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param $captcha_secret
     */
    public function __construct(UserPasswordEncoderInterface $passwordEncoder, $captcha_secret)
    {
        $this->captchaSecret = $captcha_secret;
        $this->passwordEncoder = $passwordEncoder;
    }


    # get success response from recaptcha and return it to controller
    function captchaverify($recaptcha)
    {
        $secret = "6Lc7NIkUAAAAABCpmRo5n-XGNoALG3bEG39x8o_i";
        $response = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $secret . '&response=' . $recaptcha);
        $data = json_decode($response);

        return $data->success;
    }

    function generatePassword()
    {
        $permitted_chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $random_string = '';
        for($i = 0; $i < 8; $i++) {
            $random_character = $permitted_chars[mt_rand(0,  25)];
            $random_string .= $random_character;
        }

        return $random_string;
    }

    function encode(UserInterface $user, String $plainPassword)
    {
        return $this->passwordEncoder->encodePassword($user, $plainPassword);
    }
}