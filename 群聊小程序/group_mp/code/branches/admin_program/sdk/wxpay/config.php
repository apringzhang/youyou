<?php

/**
 * 微信APP支付配置
 */
class WxPayConfig
{

    const BODY            = '诚德超级群';
    const DETAIL          = '诚德超级群订单';
    const APPID           = 'wx3c829e47aaae3533';
    const MCHID           = '1422558302';
    const KEY             = 'dee96dd1aa6e4fc23d240b3264e4e4ce';
    const APPSECRET       = '2b32edfa8ab947479921c325b6871e78';
    const SSLCERT_PATH    = '/opt/lampp/htdocs/restaurant_mp/sdk/wxpay/cert/apiclient_cert.pem';
    const SSLKEY_PATH     = '/opt/lampp/htdocs/restaurant_mp/sdk/wxpay/cert/apiclient_key.pem';
    const CURL_PROXY_HOST = "0.0.0.0";
    const CURL_PROXY_PORT = 0;
    const REPORT_LEVENL   = 1;
}
