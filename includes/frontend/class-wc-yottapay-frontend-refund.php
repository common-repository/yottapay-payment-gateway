<?php

if (!defined('ABSPATH'))
{
    exit;
}

/**
 * Yotta Pay Frontend Refund class
 */
class WC_YottaPay_Frontend_Refund
{
    /**
     * Add refund button to order actions
     */
    public static function add_refund_btn_to_actions($actions, $order)
    {
        try
        {
            // - Check order
            if (!$order)
            {
                return $actions;
            }

            // - Check order status
            $order_status = $order->get_status();
            if ($order_status != 'processing' && $order_status != 'completed')
            {
                return $actions;
            }

            // - Check order payment method
            $order_paymentmethod = $order->get_payment_method();
            if ($order_paymentmethod != WC_YottaPay_Common::GATEWAY_ID)
            {
                return $actions;
            }

            // - Check customer
            if (get_current_user_id() == '0' || !is_user_logged_in())
            {
                return $actions;
            }

            // - Add refund action
            $url = esc_url_raw(wc_get_account_endpoint_url('yottapay-refund-request-form') . $order->get_id());
            $actions['yottapay_refund_order_action'] = ['url' => $url, 'name' => 'Yotta Pay refund'];
        }
        catch (Exception $e)
        {
            WC_YottaPay_Logger::log('warning', 'WC_YottaPay_Frontend_Refund', 'add_refund_btn_to_actions', $e->getMessage());
        }
        finally
        {
            return $actions;
        }
    }

    /**
     * Add refund button to order
     */
    public static function add_refund_btn_to_order($order)
    {
        try
        {
            // - Check order
            if (!$order)
            {
                return false;
            }

            // - Check order status
            $order_status = $order->get_status();
            if ($order_status != 'processing' && $order_status != 'completed')
            {
                return;
            }

            // - Check order payment method
            $order_paymentmethod = $order->get_payment_method();
            if ($order_paymentmethod != WC_YottaPay_Common::GATEWAY_ID)
            {
                return;
            }

            // - Check customer
            if (get_current_user_id() == '0' || !is_user_logged_in())
            {
                return;
            }

            // - Add refund button
            $url = esc_url_raw(wc_get_account_endpoint_url('yottapay-refund-request-form') . $order->get_id());
            ?>
                <p>
                    <a href="<?php echo esc_url($url); ?>" class="button"><?php echo esc_html('Yotta Pay refund'); ?></a>
                </p>
            <?php
        }
        catch (Exception $e)
        {
            WC_YottaPay_Logger::log('warning', 'WC_YottaPay_Frontend_Refund', 'add_refund_btn_to_order', $e->getMessage());
        }
    }

    /**
     * Disable admin default refund button
     */
    public static function disable_admin_refund_button($actions, $order)
    {
        try
        {
            // - Check order
            if (!$order)
            {
                return $actions;
            }

            // - Check order payment method
            $order_paymentmethod = $order->get_payment_method();
            if ($order_paymentmethod != WC_YottaPay_Common::GATEWAY_ID)
            {
                return $actions;
            }

            // - Check order status
            $order_status = $order->get_status();

            if ($order_status == 'refunded')
            {
                echo '<style>
                button.refund-items {
                    pointer-events: none !important;
                    border: none !important;
                }
                </style>';
                // jQuery code
                ?>
                    <script type="text/javascript">
                        (function ($) {
                            $(document).ready(function() {
                                var refundButton = $(".button.refund-items");
                                refundButton.prop("disabled", true);
                                refundButton.html('');
                            });
                        })(jQuery);
                    </script>
                <?php
            }
            else
            {
                echo '<style>
                button.refund-items {
                    pointer-events: none !important;
                    border: none !important;
                }
                </style>';
                // jQuery code
                ?>
                    <script type="text/javascript">
                        (function ($) {
                            $(document).ready(function() {
                                var refundButton = $(".button.refund-items");
                                refundButton.replaceWith("<?php echo sprintf( __( 
                                            '<br><br><p style=\"text-align: left;\">'
                                            . 'To make Yotta Pay refund you can use <a href=\"https://www.yottapay.co.uk/faq-questions/what-is-dashboard\">Yotta Pay Dashboard</a>'
                                            . ' tool or ask the customer to request from view-order page if the purchase was made by a client through their account with your site.</p>'
                                        , 'yottapay-payment-gateway' )); 
                                    ?>"
                                );
                            });
                        })(jQuery);
                    </script>
                <?php
            }
        }
        catch (Exception $e)
        {
            WC_YottaPay_Logger::log('warning', 'WC_YottaPay_Frontend_Refund', 'disable_admin_refund_button', $e->getMessage());
        }
        finally
        {
            return $actions;
        }
    }
}
