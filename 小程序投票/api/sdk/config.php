<?php

/**
 * 微信支付配置
 */
class WxPayConfig
{
    public static $BODY = '投票礼物';
    public static $DETAIL = '投票礼物订单支付';
    public static $APPID = 'wxbca9f8bd6e704988';
    public static $MCHID = '1493936112';
    public static $KEY = 'dcc14f7e77a29970bdaed6f2caa565b3';
    public static $APPSECRET = 'bf8f9b7291870a05ec6e07e86153f97c';
    public static $SSLCERT_PATH = '../sdk/cert/apiclient_cert.pem';
    public static $SSLKEY_PATH = '../sdk/cert/apiclient_key.pem';
    public static $CURL_PROXY_HOST = "0.0.0.0";
    public static $CURL_PROXY_PORT = 0;
    public static $REPORT_LEVENL = 1;
}
