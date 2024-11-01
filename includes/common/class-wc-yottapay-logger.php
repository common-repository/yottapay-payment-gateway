<?php

if (!defined('ABSPATH'))
{
    exit;
}

/**
 * Yotta Pay Logger class
 */
class WC_YottaPay_Logger
{
    private static $logger = null;
    private static $wc_log_filename = 'yottapay-payment-gateway';

    /**
     * Log message
     */
    public static function log($level, $class, $func, $arg_0, $arg_1 = '', $arg_2 = '', $arg_3 = '')
    {
        // - Set log context
        $context = ['source' => self::$wc_log_filename];

        // - Build log message
        $message = 'YottaPay' . ' | ' . $class . ' | ' . $func . ' | ' . $arg_0;        

        if($arg_1 != '')
        {
            $message = $message . ' | ' . $arg_1;
        }
        if($arg_2 != '')
        {
            $message = $message . ' | ' . $arg_2;
        }
        if($arg_3 != '')
        {
            $message = $message . ' | ' . $arg_3;
        }

        // - Compare level and log message
        if ($level == 'debug')
        {
            self::get_logger()->debug($message, $context);
        }
        elseif ($level == 'info')
        {
            self::get_logger()->info($message, $context);
        }
        elseif ($level == 'warning')
        {
            self::get_logger()->warning($message, $context);
        }
        else
        {
            self::get_logger()->error($message, $context);
        }
    }

    /**
     * Access logger instance
     */
    private static function get_logger()
    {
        if (self::$logger == null)
        {
            self::$logger = wc_get_logger();
        }

        return self::$logger;
    }
}
