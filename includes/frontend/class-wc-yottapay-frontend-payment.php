<?php

if (!defined('ABSPATH'))
{
    exit;
}

/**
 * Yotta Pay Frontend Payment class
 */
class WC_YottaPay_Frontend_Payment
{
    /**
     * Add script on checkout page
     */
    public static function print_scripts()
    {
        // Script to disable incorrect blockUI when back to checkout from Yotta Pay payment page
        ?>
        <script type="text/javascript">
            (function ($) {
                var reloadTimeoutId = -1;
                function yottapayReloadCheckoutPage() {
                    if (reloadTimeoutId && (reloadTimeoutId != -1)) {
                        clearTimeout(reloadTimeoutId);
                        reloadTimeoutId = -1;
                        location.reload(true);
                    }
                }
                var checkout_form = $('form.checkout');
                checkout_form.on('checkout_place_order', function () {
                    try {
                        var checkedYottaPayPaymentMethod = $('form[name="checkout"]').find(':input[id="payment_method_yottapay"]:checked');
                        if (checkedYottaPayPaymentMethod.length) {
                            reloadTimeoutId = window.setTimeout(yottapayReloadCheckoutPage, 20000);
                        }
                    } catch (e) {
                        console.error(e);
                    } finally {
                        return true;
                    }
                });
                $(document.body).on('checkout_error', function () {
                    if ($('.woocommerce-error li').length) {
                        if (reloadTimeoutId && (reloadTimeoutId != -1)) {
                            clearTimeout(reloadTimeoutId);
                            reloadTimeoutId = -1;
                        }
                    }
                });
                window.addEventListener('pageshow', function (event) {
                    var historyTraversal = event.persisted || (typeof window.performance != 'undefined' && window.performance.navigation.type === 2);
                    if (historyTraversal) {
                        if (reloadTimeoutId && (reloadTimeoutId != -1)) {
                            clearTimeout(reloadTimeoutId);
                            reloadTimeoutId = -1;
                        }
                        location.reload(true);
                    }
                });
            })(jQuery);
        </script>
        <?php
    }
}
