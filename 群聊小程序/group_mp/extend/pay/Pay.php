<?php

namespace pay;

use com\unionpay\acp\sdk\SDKConfig;
use com\unionpay\acp\sdk\AcpService;
use \WxPayConfig;

/**
 * 支付宝支付类
 * @author 许诺
 */
class Alipay
{

    //支付主题
    private $subject;
    //支付内容
    private $body;
    //APPID
    private $appId;
    //支付网关
    private $gateway;
    //签名类型
    private $signType;
    //私钥
    private $privateKey;
    //支付宝公钥
    private $alipayPublicKey;

    public function __construct()
    {
        $config = require_once ROOT_PATH . 'sdk/alipay/config.php';
        $this->subject = $config['SUBJECT'];
        $this->body = $config['BODY'];
        $this->appId = $config['APP_ID'];
        $this->gateway = $config['GATEWAY'];
        $this->signType = $config['SIGN_TYPE'];
        $this->privateKey = $config['PRIVATE_KEY'];
        $this->alipayPublicKey = $config['ALIPAY_PUBLIC_KEY'];
    }

    /**
     * WAP支付
     * @param string $orderNumber 订单号
     * @param float $totalAmount 总金额(0.00)
     * @param string $notifyUrl 回调地址
     * @param string $returnUrl 跳转地址
     */
    public function payWap($orderNumber, $totalAmount, $notifyUrl, $returnUrl)
    {
        require_once ROOT_PATH . 'sdk/alipay/AopSdk.php';
        $aop = new \AopClient();
        $aop->gatewayUrl = $this->gateway;
        $aop->appId = $this->appId;
        $aop->rsaPrivateKey = $this->privateKey;
        $aop->alipayrsaPublicKey = $this->alipayPublicKey;
        $aop->signType = $this->signType;
        $request = new \AlipayTradeWapPayRequest();
        $bizContent = [
            'subject' => $this->subject,
            'body' => $this->body,
            'out_trade_no' => $orderNumber,
            'total_amount' => number_format($totalAmount, 2),
            'product_code' => 'QUICK_WAP_PAY',
        ];
        $bizContent = strval(json_encode($bizContent));
        $request->setBizContent($bizContent);
        $request->setNotifyUrl($notifyUrl);
        $request->setReturnUrl($returnUrl);
        $result = $aop->pageExecute($request);
        return $result;
    }

    /**
     * APP支付
     * @param string $orderNumber 订单号
     * @param float $totalAmount 总金额(0.00)
     * @param string $notifyUrl 回调地址
     */
    public function payApp($orderNumber, $totalAmount, $notifyUrl)
    {
        require_once ROOT_PATH . 'sdk/alipay/AopSdk.php';
        $aop = new \AopClient();
        $aop->gatewayUrl = $this->gateway;
        $aop->appId = $this->appId;
        $aop->rsaPrivateKey = $this->privateKey;
        $aop->alipayrsaPublicKey = $this->alipayPublicKey;
        $aop->signType = $this->signType;
        $request = new \AlipayTradeAppPayRequest();
        $bizContent = [
            'subject' => $this->subject,
            'body' => $this->body,
            'out_trade_no' => $orderNumber,
            'total_amount' => number_format($totalAmount, 2),
            'product_code' => 'QUICK_MSECURITY_PAY',
        ];
        $bizContent = strval(json_encode($bizContent));
        $request->setBizContent($bizContent);
        $request->setNotifyUrl($notifyUrl);
        $result = $aop->sdkExecute($request);
        return $result;
    }

    /**
     * PC支付
     * @param string $orderNumber 订单号
     * @param float $totalAmount 总金额(0.00)
     * @param string $notifyUrl 回调地址
     * @param string $returnUrl 跳转地址
     */
    public function payPc($orderNumber, $totalAmount, $notifyUrl, $returnUrl)
    {
        require_once ROOT_PATH . 'sdk/alipay/AopSdk.php';
        $aop = new \AopClient();
        $aop->gatewayUrl = $this->gateway;
        $aop->appId = $this->appId;
        $aop->rsaPrivateKey = $this->privateKey;
        $aop->alipayrsaPublicKey = $this->alipayPublicKey;
        $aop->signType = $this->signType;
        $request = new \AlipayTradePagePayRequest();
        $bizContent = [
            'subject' => $this->subject,
            'body' => $this->body,
            'out_trade_no' => $orderNumber,
            'total_amount' => number_format($totalAmount, 2),
            'product_code' => 'FAST_INSTANT_TRADE_PAY',
        ];
        $bizContent = strval(json_encode($bizContent));
        $request->setBizContent($bizContent);
        $request->setNotifyUrl($notifyUrl);
        $request->setReturnUrl($returnUrl);
        $result = $aop->pageExecute($request, 'GET');
        return $result;
    }

    /**
     * 支付回调验证
     * @return array trade_no:支付宝支付单号 out_trade_no:订单号
     */
    public function notify()
    {
        require_once ROOT_PATH . 'sdk/alipay/AopSdk.php';
        $aop = new \AopClient();
        $aop->alipayrsaPublicKey = $this->alipayPublicKey;
        $result = $aop->rsaCheckV1($_POST, null, $this->signType);
        if ($result) {
            echo 'success';
            return [
                'trade_no' => $_POST['trade_no'],
                'out_trade_no' => $_POST['out_trade_no'],
            ];
        } else {
            echo 'fail';
            return false;
        }
    }
    /**
     * 退款
     * @param $orderNumber 订单号(退款专用，可与支付单号不同)
     * @param $tradeNumber 交易流水号
     * @param $totalAmount 退款金额
     * @return bool
     */
    public function refund($orderNumber, $tradeNumber, $totalAmount)
    {
        require_once ROOT_PATH . 'sdk/alipay/AopSdk.php';
        $aop = new \AopClient();
        $aop->gatewayUrl = $this->gateway;
        $aop->appId = $this->appId;
        $aop->rsaPrivateKey = $this->privateKey;
        $aop->alipayrsaPublicKey = $this->alipayPublicKey;
        $aop->signType = $this->signType;
        $request = new \AlipayTradeRefundRequest();
        $bizContent = [
            'trade_no' => $tradeNumber,
            'refund_amount' => $totalAmount,
            'out_request_no' => $orderNumber,
        ];
        $bizContent = strval(json_encode($bizContent));
        $request->setBizContent($bizContent);
        $result = $aop->execute($request);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if (!empty($resultCode) && $resultCode == 10000) {
            return true;
        } else {
            return false;
        }
    }
}

/**
 * 微信支付类
 */
class Wxpay
{

    /**
     * APP支付
     * @param string $orderNumber 订单号
     * @param float $totalAmount 总金额(0.00)
     * @param string $notifyUrl 回调地址
     * @return string
     */
    public function payApp($orderNumber, $totalAmount, $notifyUrl)
    {
        require_once ROOT_PATH . 'sdk/wxpay/config_app.php';
        require_once ROOT_PATH . 'sdk/wxpay/WxPay.Data.php';
        require_once ROOT_PATH . 'sdk/wxpay/WxPay.Api.php';
        require_once ROOT_PATH . 'sdk/wxpay/WxPay.Exception.php';
        $data = new \WxPayUnifiedOrder();
        $nonceStr = \WxPayApi::getNonceStr();
        $data->SetNonce_str($nonceStr);
        $data->SetBody(WxPayConfig::BODY);
        $data->SetDetail(WxPayConfig::DETAIL);
        $data->SetOut_trade_no($orderNumber);
        $data->SetTotal_fee($totalAmount * 100);
        $data->SetSpbill_create_ip(input('server.REMOTE_ADDR'));
        $data->SetNotify_url($notifyUrl);
        $data->SetTrade_type('APP');
        $data->SetSign();
        $result = \WxPayApi::unifiedOrder($data);
        if ($result['return_code'] != 'SUCCESS') {
            throw new \Exception($result['return_msg']);
        }
        if ($result['result_code'] != 'SUCCESS') {
            throw new \Exception($result['err_code_des']);
        }
        $nonceStr = \WxPayApi::getNonceStr();
        $return = array(
            'appid' => $result['appid'],
            'partnerid' => $result['mch_id'],
            'prepayid' => $result['prepay_id'],
            'package' => 'Sign=WXPay',
            'noncestr' => $nonceStr,
            'timestamp' => time(),
        );
        //计算签名

        ksort($return);

        $sign = http_build_query($return);
        $sign = urldecode($sign);
        $sign = $sign . "&key=" . WxPayConfig::KEY;
        $sign = md5($sign);
        $sign = strtoupper($sign);
        $return['sign'] = $sign;
        return json_encode($return);
    }

    /**
     * JSAPI支付
     * @param string $orderNumber 订单号
     * @param float $totalAmount 总金额(0.00)
     * @param string $notifyUrl 回调地址
     * @return array
     */
    public function payJsapi($orderNumber, $totalAmount, $notifyUrl)
    {
        $code = $_GET['code'];
        $openid = '';
        if (empty($code)) {
            $this->getCode();
        } else {
            $openid = $this->getOpenid($code);
        }
        if (empty($openid)) {
            throw new \Exception('openid获取失败');
        }
        require_once ROOT_PATH . 'sdk/wxpay/config_jsspi.php';
        require_once ROOT_PATH . 'sdk/wxpay/WxPay.Data.php';
        require_once ROOT_PATH . 'sdk/wxpay/WxPay.Api.php';
        require_once ROOT_PATH . 'sdk/wxpay/WxPay.Exception.php';
        $data = new \WxPayUnifiedOrder();
        $nonceStr = \WxPayApi::getNonceStr();
        $data->SetNonce_str($nonceStr);
        $data->SetBody(WxPayConfig::BODY);
        $data->SetDetail(WxPayConfig::DETAIL);
        $data->SetOut_trade_no($orderNumber);
        $data->SetTotal_fee($totalAmount * 100);
        $data->SetSpbill_create_ip(input('server.REMOTE_ADDR'));
        $data->SetNotify_url($notifyUrl);
        $data->SetTrade_type('JSAPI');
        $data->SetOpenid($openid);
        $data->SetSign();
        $result = \WxPayApi::unifiedOrder($data);
        if ($result['return_code'] != 'SUCCESS') {
            throw new \Exception($result['return_msg']);
        }
        if ($result['result_code'] != 'SUCCESS') {
            throw new \Exception($result['err_code_des']);
        }
        $nonceStr = \WxPayApi::getNonceStr();
        $return = array(
            'appId' => $result['appid'],
            'timeStamp' => time(),
            'nonceStr' => $nonceStr,
            'package' => 'prepay_id=' . $result['prepay_id'],
            'signType' => 'MD5',
        );
        //计算签名

        ksort($return);

        $sign = http_build_query($return);
        $sign = urldecode($sign);
        $sign = $sign . "&key=" . WxPayConfig::KEY;
        $sign = md5($sign);
        $sign = strtoupper($sign);
        $return['paySign'] = $sign;
        return $return;
    }

    /**
     * 获取CODE
     */
    private function getCode()
    {
        require_once ROOT_PATH . 'sdk/wxpay/config_jsspi.php';
        $appId = WxPayConfig::APPID;
        $pageUrl = 'http://' . input('server.HTTP_HOST') . input('server.REQUEST_URI');
        $pageUrl = urlencode($pageUrl);
        header("Location: https://open.weixin.qq.com/connect/oauth2/authorize"
            . "?appid={$appId}&redirect_uri={$pageUrl}&response_type=code&scope=snsapi_base#wechat_redirect");
        die;
    }

    private function getOpenid($code)
    {
        require_once ROOT_PATH . 'sdk/wxpay/config_jsspi.php';
        $appid = WxPayConfig::APPID;
        $secret = WxPayConfig::APPSECRET;
        $ch = curl_init("https://api.weixin.qq.com/sns/oauth2/access_token?appid={$appid}&secret={$secret}&code={$code}&grant_type=authorization_code");
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($ch);
        curl_close($ch);
        $openid = json_decode($data, true)['openid'];
        return $openid;
    }

    /**
     * JSAPI支付回调验证
     * @return array
     */
    public function notifyJsapi()
    {
        require_once ROOT_PATH . 'sdk/wxpay/config_jsspi.php';
        require_once ROOT_PATH . 'sdk/wxpay/WxPay.Data.php';
        require_once ROOT_PATH . 'sdk/wxpay/WxPay.Api.php';
        require_once ROOT_PATH . 'sdk/wxpay/WxPay.Exception.php';
        require_once ROOT_PATH . 'sdk/wxpay/WxPay.Notify.php';
        require_once ROOT_PATH . 'sdk/wxpay/NotifyCallback.php';
        $notify = new \PayNotifyCallBack();
        $notify->Handle(false);
        $data = $notify->getData();
        if ($data['result_code'] == 'SUCCESS' && $data['return_code'] == 'SUCCESS') {
            return [
                'trade_no' => $data['transaction_id'],
                'out_trade_no' => $data['out_trade_no'],
            ];
        }
    }

    /**
     * APP支付回调验证
     * @return type
     */
    public function notifyApp()
    {
        require_once ROOT_PATH . 'sdk/wxpay/config_app.php';
        require_once ROOT_PATH . 'sdk/wxpay/WxPay.Data.php';
        require_once ROOT_PATH . 'sdk/wxpay/WxPay.Api.php';
        require_once ROOT_PATH . 'sdk/wxpay/WxPay.Exception.php';
        require_once ROOT_PATH . 'sdk/wxpay/WxPay.Notify.php';
        require_once ROOT_PATH . 'sdk/wxpay/NotifyCallback.php';
        $notify = new \PayNotifyCallBack();
        $notify->Handle(false);
        $data = $notify->getData();
        if ($data['result_code'] == 'SUCCESS' && $data['return_code'] == 'SUCCESS') {
            return [
                'trade_no' => $data['transaction_id'],
                'out_trade_no' => $data['out_trade_no'],
            ];
        }
    }

}

/**
 * 银联支付类
 */
class Unionpay
{
    /**
     * WAP支付
     * @param string $orderNumber 订单号
     * @param float $totalAmount 总金额(0.00)
     * @param string $notifyUrl 回调地址
     * @param string $returnUrl 跳转地址
     * @return string
     */
    public function payWap($orderNumber, $totalAmount, $notifyUrl, $returnUrl)
    {
        require_once ROOT_PATH . 'sdk/unionpay/acp_service.php';
        $config = require_once ROOT_PATH . 'sdk/unionpay/config.php';
        $datetime = date('YmdHis');
        $param = array(
            'version' => SDKConfig::getSDKConfig()->version, //版本号
            'encoding' => 'utf-8',
            'txnType' => '01',
            'txnSubType' => '01',
            'bizType' => '000201',
            'frontUrl' => $returnUrl,
            'backUrl' => $notifyUrl,
            'signMethod' => SDKConfig::getSDKConfig()->signMethod,
            'channelType' => '08',
            'accessType' => '0',
            'currencyCode' => '156',
            'merId' => $config['merId'],
            'orderId' => $orderNumber,
            'txnTime' => $datetime,
            'txnAmt' => $totalAmount * 100,
            'payTimeout' => date('YmdHis', strtotime('+15 minutes')),
        );
        AcpService::sign($param);
        $url = SDKConfig::getSDKConfig()->frontTransUrl;
        $form = AcpService::createAutoFormHtml($param, $url);
        return $form;
    }

    /**
     * APP支付
     * @param string $orderNumber 订单号
     * @param float $totalAmount 总金额(0.00)
     * @param string $notifyUrl 回调地址
     * @param string $returnUrl 跳转地址
     * @return string
     */
    public function payApp($orderNumber, $totalAmount, $notifyUrl)
    {
        require_once ROOT_PATH . 'sdk/unionpay/acp_service.php';
        $config = require_once ROOT_PATH . 'sdk/unionpay/config.php';
        $datetime = date('YmdHis');
        $param = array(
            'version' => SDKConfig::getSDKConfig()->version, //版本号
            'encoding' => 'utf-8',
            'txnType' => '01',
            'txnSubType' => '01',
            'bizType' => '000201',
            'backUrl' => $notifyUrl,
            'signMethod' => SDKConfig::getSDKConfig()->signMethod,
            'channelType' => '08',
            'accessType' => '0',
            'currencyCode' => '156',
            'merId' => $config['merId'],
            'orderId' => $orderNumber,
            'txnTime' => $datetime,
            'txnAmt' => $totalAmount * 100,
            'payTimeout' => date('YmdHis', strtotime('+15 minutes')),
        );
        AcpService::sign($param);
        $url = SDKConfig::getSDKConfig()->appTransUrl;
        $result = AcpService::post($param, $url);
        return json_encode($result);
    }

    /**
     * 退款
     * @param $orderNumber 订单号(退款专用，可与支付单号不同)
     * @param $tradeNumber 交易流水号
     * @param $totalAmount 退款金额
     * @param $notifyUrl 回调地址
     * @return bool
     */
    public function refund($orderNumber, $tradeNumber, $totalAmount, $notifyUrl)
    {
        require_once ROOT_PATH . 'sdk/unionpay/acp_service.php';
        $config = require_once ROOT_PATH . 'sdk/unionpay/config.php';
        $datetime = date('YmdHis');
        $param = array(
            'version' => SDKConfig::getSDKConfig()->version,
            'encoding' => 'utf-8',
            'signMethod' => SDKConfig::getSDKConfig()->signMethod,
            'txnType' => '04',
            'txnSubType' => '00',
            'bizType' => '000201',
            'accessType' => '0',
            'channelType' => '07',
            'backUrl' => $notifyUrl,
            'orderId' => $orderNumber,
            'merId' => $config['merId'],
            'origQryId' => $tradeNumber,
            'txnTime' => $datetime,
            'txnAmt' => intval($totalAmount * 100),
        );
        AcpService::sign($param);
        $url = SDKConfig::getSDKConfig()->backTransUrl;
        $data = AcpService::post($param, $url);
        if ($data['respCode'] != '00') {
            return false;
        }
        $result = AcpService::validate($data);
        if (!$result) {
            return false;
        }
        return true;
    }

    /**
     * 银联支付回调
     */
    public function notify()
    {
        require_once ROOT_PATH . 'sdk/unionpay/acp_service.php';
        $data = $_POST;
        if ($data['respCode'] != '00') {
            die;
        }
        $result = AcpService::validate($data);
        if (!$result) {
            die;
        }
        $return = [
            'trade_no' => $data['queryId'],
            'out_trade_no' => $data['orderId'],
        ];
        return $return;
    }

}
