<?php

namespace worms\wechat\pay;

use worms\wechat\core\WechatException;

/**
 * Class RefundOrderRequest
 *
 * @package Omnipay\WechatPay\Message
 * @link    https://pay.weixin.qq.com/wiki/doc/api/app.php?chapter=9_4&index=6
 * @method RefundOrderResponse send()
 * @method RefundOrderResponse getResponse()
 *
 * @method RefundOrderRequest setOutTradeNo($val) 商户订单号
 * @method string getOutTradeNo() 商户订单号
 *
 * @method RefundOrderRequest setDeviceInfo($val) 设备号
 * @method string getDeviceInfo() 设备号
 *
 * @method RefundOrderRequest setTransactionId($val) 商户订单号
 * @method string getTransactionId() 商户订单号
 *
 * @method RefundOrderRequest setOutRefundNo($val) 商户退款单号
 * @method string getOutRefundNo() 商户退款单号
 *
 * @method RefundOrderRequest setTotalFee($val) 订单金额
 * @method string getTotalFee() 订单金额
 *
 * @method RefundOrderRequest setRefundFee($val) 退款总金额，订单总金额，单位为分，只能为整数，详见支付金额
 * @method string getRefundFee() 退款总金额，订单总金额，单位为分，只能为整数，详见支付金额
 *
 * @method RefundOrderRequest setRefundFeeType($val) 货币类型，符合ISO 4217标准的三位字母代码，默认人民币：CNY，其他值列表详见货币类型
 * @method string getRefundFeeType() 货币类型，符合ISO 4217标准的三位字母代码，默认人民币：CNY，其他值列表详见货币类型
 *
 * @method RefundOrderRequest setOpUserId($val) 操作员帐号, 默认为商户号
 * @method string getOpUserId() 操作员帐号, 默认为商户号
 *
 * @method RefundOrderRequest setCertPath($val) 获取cert文件路径
 * @method string getCertPath()
 *
 * @method RefundOrderRequest setKeyPath($val) 获取key文件路径
 * @method string getKeyPath()
 *
 * @method RefundOrderRequest setRootcaPath($val) 获取key文件路径
 * @method string getRootcaPath()
 *
 */
class RefundOrderRequest extends BaseAbstractRequest
{

    const END_POINT = 'https://api.mch.weixin.qq.com/secapi/pay/refund';

    /**
     * Get the raw data array for this message. The format of this varies from gateway to
     * gateway, but will usually be either an associative array, or a SimpleXMLElement.
     *
     * @return array
     * @throws WechatException
     */
    protected function getData()
    {
        $this->validate('app_id', 'mch_id', 'cert_path', 'key_path', 'out_refund_no', 'total_fee', 'refund_fee', 'rootca_path');
        if (!$this->getOutTradeNo() && !$this->getTransactionId()) {
            throw new WechatException("out_trade_no and transaction_id must choose one~");
        }

        $data = [
            'appid'           => $this->getAppId(),
            'mch_id'          => $this->getMchId(),
            'device_info'     => $this->getDeviceInfo(),
            'transaction_id'  => $this->getTransactionId(),
            'out_trade_no'    => $this->getOutTradeNo(),
            'out_refund_no'   => $this->getOutRefundNo(),
            'total_fee'       => $this->getTotalFee(),
            'refund_fee'      => $this->getRefundFee(),
            'refund_fee_type' => $this->getRefundFee(),//<>
            'op_user_id'      => $this->getOpUserId() ?: $this->getMchId(),
            'nonce_str'       => md5(uniqid()),
        ];

        $data = array_filter($data);

        $data['sign'] = Helper::sign($data, $this->getApiKey());

        return $data;
    }

    /**
     * Send the request with specified data
     *
     * @param  mixed $data The data to send
     *
     * @return BaseAbstractResponse
     */
    protected function sendData($data)
    {

        $request = $this->httpClient->post(self::END_POINT, [
            'body'    => Helper::array2xml($data),
            'cert'    => $this->getCertPath(),
            'ssl_key' => $this->getKeyPath(),
            'verify'  => $this->getRootcaPath(),
        ]);

        $response = $request->getBody()->getContents();

        return $this->response = new RefundOrderResponse($this, Helper::xml2array($response));
    }
}
