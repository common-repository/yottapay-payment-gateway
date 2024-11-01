<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * WC_YottaPay_Scheduler class
 */
class WC_YottaPay_Scheduler
{
    /**
     * Return extended array of schedules
     *
     * $interval_value as seconds
     */
    public static function add_schedule_interval($schedules, $interval_key, $interval_value, $display_name)
    {
        try
        {   
            // - Check interval already exists
            if (!array_key_exists($interval_key, $schedules))
            {
                $schedule_interval = [$interval_key => ['interval' => $interval_value, 'display'  => $display_name]];
                $extended_schedules = $schedules + $schedule_interval;

                return $extended_schedules;
            }
            else
            {
	            return $schedules;        
            }
        }
        catch (Exception $e)
        {
            WC_YottaPay_Logger::log('warning', 'WC_YottaPay_Scheduler', 'add_schedule_interval', $e->getMessage());
            
            return $schedules;
        }
    }

    /**
     * Schedule event
     */
    public static function schedule_event($interval_key, $hook)
    {
        try
        {
            if (!wp_next_scheduled($hook))
            {
                wp_schedule_event(current_time('timestamp'), $interval_key, $hook);
            }
        }
        catch (Exception $e)
        {
            WC_YottaPay_Logger::log('warning', 'WC_YottaPay_Scheduler', 'schedule_event', $e->getMessage());
        }
    }
}
