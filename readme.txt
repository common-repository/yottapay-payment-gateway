=== Yotta Pay Payment Gateway ===
Contributors: yottapay
Tags: checkout, online payments, online banking, yotta pay, ecommerce, woocommerce, payment request, instant payments
Requires at least: 5.3
Tested up to: 6.6
Requires PHP: 7.0
Stable tag: 3.0.2
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Yotta Pay is the fastest and most secure way to accept payments via UK bank account.

== Description ==

Start accepting instant payments directly on your store with Yotta Pay Payment Gateway for WooCommerce. Eliminate the fuss of online transactions. No card numbers, expiry dates or security codes. Customers pay with UK mobile banking.

= Built to make e-commerce simple =

Superfast frictionless online/mobile checkout. Win more customers with a perfect online experience. Payment done in a few seconds.

Try it out yourself [here](https://yottapay.co.uk/).

= Provide seamless online experience while saving money =

Based on Open Banking, Yotta Pay Payment Gateway is faster and more reliable than any traditional payment gateway. Go cardless to forget fraud, chargebacks, setup fees, and sky-high acquiring charges.

* Up to 10x cheaper than card payment processors
* Fees as low as 0.19%
* No lock-in contract. Cancel anytime
* Save thousands of pounds per year

Learn more about the [pricing](https://yottapay.co.uk/pricing).

= Flexible, safe and robust integration with bank-grade security =

Yotta Pay Payment Gateway provides account-to-account transfers which means no card details required, no bank details retained. Once the payment is done, the money transfers to your account instantly. We also offer our customers constant online support.

== Installation ==

= Requirements =

* WordPress Version 5.3 or newer (installed)
* WooCommerce Version 3.9 or newer (installed and activated)
* PHP Version 7.0 or newer

= Automatic installation =

This is the easiest option, as WordPress will transfer files automatically and there is no need to leave your browser. To perform an automatic installation of Yotta Pay Payment Gateway plugin:

1. Log in to WordPress admin.
2. Go to **Plugins > Add New**.
3. Search for the **Yotta Pay Payment Gateway** plugin.
4. Click on **Install Now** and wait until the plugin is installed successfully.
5. You can activate the plugin immediately by clicking on **Activate** now on the success page. If you want to activate it later, you can do so via **Plugins > Installed Plugins**.

= Manual installation =

The manual installation method involves downloading our plugin and uploading it to your web server via your favorite FTP application. The WordPress support contains [instructions on how to do this here](https://wordpress.org/support/article/managing-plugins/).

= Setup and Configuration =

1. After you have activated the Yotta Pay Payment Gateway plugin, go to **WooCommerce  > Settings**.
2. Click the **Payments** tab.
3. Click on **Yotta Pay**.
4. Click the **Log in with Yotta Pay** button.
5. Scan a QR code with your mobile Yotta Pay app.
6. Click **Allow** button to perform authorization and grant access.
7. Click **Save changes**.

Learn more about the [online checkout](https://yottapay.co.uk/online-checkout).

== Screenshots ==

1. The seamless all-in-one system. Your clients pay in two clicks with UK mobile banking, Apple Pay, Google Pay, or card details.
2. No card numbers, expiry dates or security codes. Checkout is just a single tap away. Optional card payments included.
3. No intermediary, instant account-to-account transfer.
4. All major UK banks are supported. Payments confirmed with biometric authorisation.
5. Digital merchant vCard and receipt available.
6. An example of user facing interface for the Faster Checkout.
7. Setup your loyalty campaign in a matter of seconds.

== Changelog ==

= 3.0.2 =
* Add - Gateway Discount option.
* Edit - WooCommerce tested up to 9.3.

= 3.0.1 =
* Fix - Compatibility verify issue.

= 3.0.0 =
* Edit - Code redesigned.
* Edit - WooCommerce tested up to 9.2.

= 2.0.3 =
* Add - Loyalty Referral Offer.
* Edit - WordPress tested up to 6.6.
* Edit - WooCommerce tested up to 9.1.
* Edit - Minor improvements.

= 2.0.2 =
* Edit - WordPress tested up to 6.5.
* Edit - WooCommerce tested up to 8.7.
* Edit - Minor improvements.

= 2.0.1 =
* Edit - Minor improvements.
* Edit - WooCommerce tested up to 8.4.

= 2.0.0 =
* Add - New payment gateway API.
* Add - HPOS compatible.
* Add - Google Tag Manager Id option field and Sourcebuster.
* Add - Checkout Description option field to select a description of the payment method on the checkout page.
* Edit - Code organisation and other minor improvements.
* Edit - WordPress tested up to 6.4.
* Edit - WooCommerce tested up to 8.2.

= 1.4.2 =
* Fix - Calculating order total amount for orders with a discount.
* Fix - Version compare.

= 1.4.1 =
* Add - Additional logging.
* Fix - Bug fixes and other minor improvements.

= 1.4.0 =
* Add - Cancellation check immediately before payment.
* Fix - Bug fixes.
* Edit - Code organisation.

= 1.3.4 =
* Edit - WordPress tested up to 6.3.
* Edit - WooCommerce tested up to 8.0.
* Edit - Description of the loyalty program settings.

= 1.3.3 =
* Fix - Depricated functions for compatibility with PHP 8.2 (lost in previous update).

= 1.3.2 =
* Fix - Depricated functions for compatibility with PHP 8.2.
* Edit - Logo on checkout page.

= 1.3.1 =
* Add - Defined additional "ABSPATH" check to prevent public user to directly access plugin files through URL.

= 1.3.0 =
* Add - Loyalty program.
* Edit - Description on checkout page.
* Edit - Guide link in plugin settings.
* Fix - Checking order status in refund processing.
* Fix - Checking WooCommerce plugin status.

= 1.2.4 =
* Fix - Admin refund UI.

= 1.2.3 =
* Fix - Rollback last update.

= 1.2.2 =
* Add - Loyalty information settings.

= 1.2.1 =
* Edit - Checkout description.

= 1.2.0 =
* Add - Yotta Pay refund.
* Edit - Code reorganisation.
* Edit - Payment reference format.
* Edit - Checkout description.

= 1.1.4 =
* Fix - Increased page refresh timeout when in-app redirection.
* Fix - Changed timeout of the request for payment intent.
* Add - Added removal of spaces for Merchant Identifier and Payment Key config parameters.

= 1.1.3 =
* Fix - Incorrect rounding of the amount in some cases.

= 1.1.2 =
* Fix - Incorrect rounding of the amount in some cases.
* Add - Writing the request body in case of an exception.

= 1.1.1 =
* Fix - Change title and description on checkout.
* Fix - Change functions names.

= 1.1.0 =
* Add - Validate billing country.
* Add - Reload checkout page to disable incorrect blockUI.
* Add - Settings, Guidline and API docs links.
* Fix - Set transaction identifier and order statuses.
* Fix - Check order status in check_request_data.
* Fix - Build "url_merchant_page_success" and "url_merchant_page_cancel" links.
* Fix - Force clear cart.

= 1.0.2 =
* Fix - Incorrect sandbox API URL.

= 1.0.1 =
* Add - Check WooCommerce plugin status and version.
* Fix - "yottapay_payment_key" field type change to "password".
* Fix - Excessive logging.

= 1.0.0 =
* Initial release.