<?php
    if (!defined('ABSPATH')) {
        exit;
    }
?>

<div class="wcrw-new-request-wrapper">
    <?php
        try
        {
            $order_id = get_query_var('yottapay-refund-request-form');
            $order = wc_get_order($order_id);
            if (!$order)
            {
                throw new Exception('Invalid order');
            }
            ?>
            <h3>Yotta Pay refund for order <a href="<?php echo $order->get_view_order_url(); ?>">#<?php echo $order->get_id(); ?></a></h3>
            <form id="yottapay-refund-request-form" method="post" enctype="multipart/form-data">
                <div>
                    <p class="form-row form-row-wide" id="refund_receiver_full_name_p">
                        <label for="refund_receiver_full_name">
                            Account holder name
                            <span class="required">*</span>
                        </label>
                        <span class="woocommerce-input-wrapper">
                            <input class="woocommerce-Input input-text" name="refund_receiver_full_name"  id="refund_receiver_full_name" maxlength="64" required></input>
                        </span>
                    </p>
                    <p class="form-row form-row-wide" id="refund_receiver_account_number_p">
                        <label for="refund_receiver_account_number">
                            Account number
                            <span class="required">*</span>
                        </label>
                        <span class="woocommerce-input-wrapper">
                            <input class="woocommerce-Input input-text" name="refund_receiver_account_number" id="refund_receiver_account_number" minlength="8" maxlength="8" required></input>
                        </span>
                    </p>
                    <p class="form-row form-row-wide" id="refund_receiver_sort_code_p">
                        <label for="refund_receiver_sort_code">
                            Sort code
                            <span class="required">*</span>
                        </label>
                        <span class="woocommerce-input-wrapper">
                            <input class="woocommerce-Input input-text" name="refund_receiver_sort_code" id="refund_receiver_sort_code" minlength="6" maxlength="6" required></input>
                        </span>
                    </p>
                    <p class="form-row form-row-wide" id="refund_receiver_comment_p">
                        <label for="refund_receiver_comment">
                            Reason for request
                            <span class="required">*</span>
                        </label>
                        <span class="woocommerce-input-wrapper">
                            <textarea name="refund_receiver_comment" maxlength="1000" id="refund_receiver_comment" required></textarea>
                        </span>
                    </p>
                    <p class="form-row form-row-wide" id="refund_amount_p" align="right">
                        <span class="woocommerce-input-wrapper">
                            Refund amount: <b><?php echo $order->get_formatted_order_total(); ?></b>
                        </span>                       
                    </p>
                    <p class="form-row form-row-wide form-submit-wrapper" id="yottapay-refund-request-form-submit" align="right">
                        <input type="hidden" name="order_id" value="<?php echo $order->get_id(); ?>">
                        <?php wp_nonce_field('yottapay_refund_request_form_nonce_action', 'yottapay_refund_request_nonce'); ?>
                        <input type="submit" class="button" name="create_refund_request" value="Send Request">
                    </p>
                </div>
            </form>
            <?php
        }
        catch (Exception $e)
        {
            wc_print_notice($e->getMessage(), 'error');
        }
    ?>
</div>
<script>
    (function ($) {
            var errorContainer = $('div.woocommerce-notices-wrapper').first();
            $('#refund_receiver_account_number').keypress(function (e) {
                var charCode = (e.which) ? e.which : event.keyCode
                if (String.fromCharCode(charCode).match(/[^0-9]/g))
                    return false;
            });
            $('#refund_receiver_sort_code').keypress(function (e) {
                var charCode = (e.which) ? e.which : event.keyCode
                if (String.fromCharCode(charCode).match(/[^0-9]/g))
                    return false;
            });
            $('#yottapay-refund-request-form').submit(function(e) {
                errorContainer.html('');
                e.preventDefault(); // prevent from submitting form directly
                $.ajax({
                    url: '/wc-api/yottapay_refund',
                    method: 'post',
                    async: false,
                    data: $("#yottapay-refund-request-form").serializeArray()
                }).done(function(response){
                    if (response.status == '1') {
                        $(':input','#yottapay-refund-request-form') // reset the form
                        .not(':button, :submit, :reset, :hidden')
                        .val('');
                        window.location.href = "<?php echo $order->get_view_order_url(); ?>";
                    } else {
                        $(errorContainer).html('<ul class="woocommerce_error woocommerce-error wc-stripe-error"><li/></ul>');
			            $(errorContainer).find('li').text(response.error);
                    }
                }).fail(function(error){  
                    $(errorContainer).html('<ul class="woocommerce_error woocommerce-error wc-stripe-error"><li/></ul>');
			        $(errorContainer).find('li').text('Refund request has been failed. Please contact the store owner to solve the problem');
                });
            })
    })(jQuery);
</script>
