<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * WC_YottaPay_Common class
 */
class WC_YottaPay_Common
{
    const GATEWAY_ID = 'yottapay';
    const PLATFORM_PREFIX = 'WOO';

    /**
     * Get new UUID
     */
    public static function get_uuid($lenght = 16)
    {
        if (function_exists("random_bytes"))
        {
            $bytes = random_bytes($lenght);
        }
        elseif (function_exists("openssl_random_pseudo_bytes"))
        {
            $bytes = openssl_random_pseudo_bytes($lenght);
        }
        else
        {
            return uniqid();
        }

        return bin2hex($bytes);
    }   
}
