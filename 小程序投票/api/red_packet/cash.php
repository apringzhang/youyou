
<?php
require '../include/db.php';
require '../include/function.php';
require '../include/config.php';
require '../sdk/config.php';
require '../sdk/WxPay.Data.php';
require '../sdk/WxPay.Api.php';
require '../sdk/WxPay.Exception.php';
/**
 * 发送现金红包
 */
class WxPayRedpack extends WxPayDataBase
{
    /**
     * 随机字符串
     */
    public function set_nonce_str($value)
    {
        $this->values['nonce_str'] = $value;
    }
    /**
     * 商户订单号
     */
    public function set_mch_billno($value)
    {
        $this->values['mch_billno'] = $value;
    }
    /**
     * 商户号
     */
    public function set_mch_id($value)
    {
        $this->values['mch_id'] = $value;
    }
    /**
     * 公众账号appid
     */
    public function set_wxappid($value)
    {
        $this->values['wxappid'] = $value;
    }
    /**
     * 商户名称
     */
    public function set_send_name($value)
    {
        $this->values['send_name'] = $value;
    }
    /**
     * 用户openid
     */
    public function set_re_openid($value)
    {
        $this->values['re_openid'] = $value;
    }
    /**
     * 付款金额
     */
    public function set_total_amount($value)
    {
        $this->values['total_amount'] = $value;
    }
    /**
     * 红包发放总人数
     */
    public function set_total_num($value)
    {
        $this->values['total_num'] = $value;
    }
    /**
     * 红包祝福语
     */
    public function set_wishing($value)
    {
        $this->values['wishing'] = $value;
    }
    /**
     * Ip地址
     */
    public function set_client_ip($value)
    {
        $this->values['client_ip'] = $value;
    }
    /**
     * 活动名称
     */
    public function set_act_name($value)
    {
        $this->values['act_name'] = $value;
    }
    /**
     * 备注
     */
    public function set_remark($value)
    {
        $this->values['remark'] = $value;
    }
}

try {
    $openid = 'oyg-Z5SgNQv3G6lO5wnfXAVyNFxI';
    $total_amount = 100;
    WxPayConfig::$SSLCERT_PATH = realpath(WxPayConfig::$SSLCERT_PATH);
    WxPayConfig::$SSLKEY_PATH = realpath(WxPayConfig::$SSLKEY_PATH);
    $nonce_str = WxPayApi::getNonceStr();
    $data = new WxPayRedpack();
    $data->set_nonce_str($nonce_str);
    $data->set_mch_billno(date('YmdHis') . mt_rand());
    $data->set_mch_id(WxPayConfig::$MCHID);
    $data->set_wxappid('wx01452b8fef002926');
    $data->set_send_name('百城千店评选');
    $data->set_re_openid($openid);
    $data->set_total_amount($total_amount);
    $data->set_total_num(1);
    $data->set_wishing('您的一票至关重要，请明天继续为我投票');
    $data->set_client_ip($_SERVER['REMOTE_ADDR']);
    $data->set_act_name('百城千店评选');
    $data->set_remark('红包提现');
    $data->SetSign();
    $xml = $data->ToXml();
    $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack';
    $result = WxPayApi::postXmlCurl($xml, $url, true);
    var_dump($result);die;
} catch (Exception $e) {

}
