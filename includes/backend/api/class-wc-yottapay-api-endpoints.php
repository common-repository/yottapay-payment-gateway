<?php

if (!defined('ABSPATH'))
{
    exit;
}

/**
 * Yotta Pay API Endpoints class
 */
class WC_YottaPay_API_Endpoints
{
    const BASE = 'https://apiconnect.yottapay.co.uk'; 
    
    const AUTH_NEW = '/api/client/auth/new';
    const USERDATA_UPDATE_AND_GET = '/api/client/userdata/update-and-get';
    const ORDER_NEW = '/api/client/order/new';
    const LOYALTY_IS_ACTIVE = '/api/client/loyalty/is-active';
    const LOYALTY_CREATE_DEFERRED = '/api/client/loyalty/create-deferred';
    const REFUND_NEW = '/api/client/refund/new';
}
