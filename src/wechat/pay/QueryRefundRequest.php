<?php

namespace worms\wechat\pay;

use worms\wechat\core\WechatException;

/**
 * Class QueryRefundRequest
 *
 * @package Omnipay\WechatPay\Message
 * @link    https://pay.weixin.qq.com/wiki/doc/api/app.php?chapter=9_5&index=7
 *
 * @method QueryRefundRequest setTransactionId($val) 商户订单号
 * @method string getTransactionId() 商户订单号
 *
 * @method QueryRefundRequest setOutRefundNo($val) 商户退款单号
 * @method string getOutRefundNo() 商户退款单号
 *
 * @method QueryRefundRequest setOutTradeNo($val) 商户订单号
 * @method string getOutTradeNo() 商户订单号
 *
 * @method QueryRefundRequest setRefundId($val) 退款id
 * @method string getRefundId()
 *
 * @method RefundOrderRequest setDeviceInfo($val) 设备号
 * @method string getDeviceInfo()
 *
 *
 * @method QueryRefundResponse send()
 * @method QueryRefundResponse getResponse()
 */
class QueryRefundRequest extends BaseAbstractRequest
{

    const END_POINT = 'https://api.mch.weixin.qq.com/pay/refundquery';

    /**
     * Get the raw data array for this message. The format of this varies from gateway to
     * gateway, but will usually be either an associative array, or a SimpleXMLElement.
     *
     * @return array
     * @throws WechatException
     */
    protected function getData()
    {
        $this->validate('app_id', 'mch_id');

        $queryIdEmpty = !$this->getTransactionId() && !$this->getOutTradeNo();
        $queryIdEmpty = ($queryIdEmpty && !$this->getOutRefundNo() && !$this->getRefundId());

        if ($queryIdEmpty) {
            $message = "The 'transaction_id' or 'out_trade_no' or 'out_refund_no' or 'refund_id' parameter is required";
            throw new WechatException($message);
        }

        $data = [
            'appid'          => $this->getAppId(),
            'mch_id'         => $this->getMchId(),
            'device_info'    => $this->getDeviceInfo(),
            'transaction_id' => $this->getTransactionId(),
            'out_trade_no'   => $this->getOutTradeNo(),
            'out_refund_no'  => $this->getOutRefundNo(),
            'refund_id'      => $this->getRefundId(),
            'nonce_str'      => md5(uniqid()),
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
        $request  = $this->httpClient->post(self::END_POINT, ['body' => Helper::array2xml($data)]);
        $response = $request->getBody()->getContents();

        return $this->response = new QueryOrderResponse($this, Helper::xml2array($response));
    }
}
